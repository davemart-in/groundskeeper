<?php if (!defined('APPPATH')) exit('No direct script access allowed');

/**
 * Analyze Controller
 *
 * Handles repository analysis operations
 */

$repoModel = new Repository();
$issueModel = new Issue();
$areaModel = new Area();
$jobModel = new AnalysisJob();
$segment1 = $glob['route'][1] ?? '';
$segment2 = $glob['route'][2] ?? '';

// Route: /analyze/run/{repo_id}
if ($segment1 === 'run' && is_numeric($segment2) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $repoId = (int)$segment2;

    // Load repository
    $repo = $repoModel->findById($repoId);

    if (!$repo) {
        $_SESSION['error'] = 'Repository not found.';
        redirect('');
        exit;
    }

    // Get all issues for this repository
    $issues = $issueModel->findByRepository($repoId);
    $issueCount = count($issues);

    // Check if areas exist, if not trigger discovery
    $areas = $areaModel->findByRepository($repoId);

    if (empty($areas)) {
        // Discover areas using AI
        try {
            $discoveredAreas = discoverAreas($repoId, $issues);

            if ($discoveredAreas) {
                // Store for approval modal
                $_SESSION['pending_areas'] = [
                    'repo_id' => $repoId,
                    'areas' => $discoveredAreas
                ];
                $_SESSION['success'] = "Area discovery complete! Please review and approve the suggested areas.";
                redirect('');
                exit;
            } else {
                $_SESSION['error'] = 'Failed to discover areas. Please check your Claude API key.';
                redirect('');
                exit;
            }
        } catch (Exception $e) {
            error_log('Area discovery error: ' . $e->getMessage());
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
            redirect('');
            exit;
        }
    }

    // Check for existing incomplete job
    $existingJob = $jobModel->findActive($repoId);

    if ($existingJob && !isset($_POST['force_restart'])) {
        // Return existing job for user to decide
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'has_existing_job' => true,
            'job_id' => $existingJob['id'],
            'processed' => $existingJob['processed_issues'],
            'total' => $existingJob['total_issues']
        ]);
        exit;
    }

    // Cancel old job if restarting
    if ($existingJob && isset($_POST['force_restart'])) {
        $jobModel->fail($existingJob['id'], 'Restarted by user');
    }

    // Create new analysis job
    $jobId = $jobModel->create($repoId, $issueCount);

    // Return JSON for AJAX handling
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'has_existing_job' => false,
        'job_id' => $jobId,
        'total_issues' => $issueCount
    ]);
    exit;
}

// Route: /analyze/approve-areas (approve discovered areas)
if ($segment1 === 'approve-areas' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['pending_areas'])) {
        $_SESSION['error'] = 'No pending areas to approve.';
        redirect('');
        exit;
    }

    $pendingData = $_SESSION['pending_areas'];
    $repoId = $pendingData['repo_id'];
    $areasText = $_POST['areas'] ?? '';

    if (empty($areasText)) {
        $_SESSION['error'] = 'No areas provided.';
        redirect('');
        exit;
    }

    // Parse textarea input (one area per line)
    $lines = explode("\n", $areasText);
    $areas = array_filter(array_map('trim', $lines));

    if (empty($areas)) {
        $_SESSION['error'] = 'No valid areas provided.';
        redirect('');
        exit;
    }

    // Save approved areas to database
    foreach ($areas as $areaName) {
        if (!empty($areaName)) {
            $areaModel->create($repoId, $areaName);
        }
    }

    unset($_SESSION['pending_areas']);
    $_SESSION['success'] = 'Areas saved! Re-run analysis to categorize issues.';
    redirect('');
    exit;
}

// Route: /analyze/process-chunk/{job_id} - Process a chunk of issues
if ($segment1 === 'process-chunk' && is_numeric($segment2) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $jobId = (int)$segment2;
    $job = $jobModel->findById($jobId);

    if (!$job) {
        echo json_encode(['success' => false, 'error' => 'Job not found']);
        exit;
    }

    try {
        // Get repository and areas
        $repo = $repoModel->findById($job['repository_id']);
        $areas = $areaModel->findByRepository($job['repository_id']);

        // Get unanalyzed issues
        $unanalyzed = $issueModel->findUnanalyzed($job['repository_id']);

        if (empty($unanalyzed)) {
            // All done, finalize
            $jobModel->complete($jobId);

            // Run final analysis aggregation (may take a while for duplicate detection)
            set_time_limit(300); // 5 minutes for final analysis
            $allIssues = $issueModel->findByRepository($job['repository_id']);
            $results = runAnalysis($job['repository_id'], $allIssues);
            $_SESSION['analysis_results'] = $results;

            echo json_encode([
                'success' => true,
                'completed' => true,
                'processed' => $job['processed_issues'],
                'total' => $job['total_issues']
            ]);
            exit;
        }

        // Process chunk (5 issues at a time)
        $chunkSize = 5;
        $chunk = array_slice($unanalyzed, 0, $chunkSize);

        // Analyze chunk
        $success = analyzeIssueChunk($job['repository_id'], $chunk, $areas);

        if ($success) {
            $processed = $job['processed_issues'] + count($chunk);
            $jobModel->updateProgress($jobId, $processed, "Analyzing issues");

            echo json_encode([
                'success' => true,
                'completed' => false,
                'processed' => $processed,
                'total' => $job['total_issues'],
                'percent' => round(($processed / $job['total_issues']) * 100)
            ]);
        } else {
            throw new Exception('Failed to analyze chunk');
        }
    } catch (Exception $e) {
        error_log('Chunk processing error: ' . $e->getMessage());
        $jobModel->fail($jobId, $e->getMessage());

        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    exit;
}

// Route: /analyze/status/{job_id} - Get job status
if ($segment1 === 'status' && is_numeric($segment2) && $_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');

    $jobId = (int)$segment2;
    $job = $jobModel->findById($jobId);

    if (!$job) {
        echo json_encode(['success' => false, 'error' => 'Job not found']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'job' => $job
    ]);
    exit;
}

/**
 * Run all analysis processes on issues
 *
 * @param int $repoId Repository ID
 * @param array $issues Array of issues
 * @return array Results from each analyzer
 */
function runAnalysis($repoId, $issues) {
    $results = [];

    // Step 1: Basic stats
    $results['stats'] = analyzeStats($issues);

    // Step 2: Detect duplicates using embeddings
    $results['duplicates'] = findDuplicates($repoId);

    // Step 3: Detect missing information
    $results['missing_info'] = analyzeMissingInfo($repoId);

    // Step 4: Detect high signal issues
    $results['high_signal'] = analyzeHighSignal($repoId);

    // Step 5: Detect cleanup opportunities
    $results['cleanup'] = analyzeCleanup($repoId);

    // Step 6: Generate suggestions
    $results['suggestions'] = analyzeSuggestions($issues);

    // Step 7: Detect issue areas/categories
    $results['areas'] = analyzeAreas($issues);

    // Step 8: Analyze priority distribution
    $results['priorities'] = analyzePriorities($issues);

    return $results;
}

/**
 * Analyze basic statistics
 *
 * @param array $issues Array of issues
 * @return array Statistics
 */
function analyzeStats($issues) {
    return [
        'total' => count($issues),
        'open' => count(array_filter($issues, fn($i) => $i['state'] === 'open')),
        'closed' => count(array_filter($issues, fn($i) => $i['state'] === 'closed')),
        'with_assignees' => count(array_filter($issues, fn($i) => !empty($i['assignees']))),
        'with_milestone' => count(array_filter($issues, fn($i) => !empty($i['milestone']))),
    ];
}

/**
 * Detect duplicate issues
 *
 * @param array $issues Array of issues
 * @return array Duplicate detection results
 */
function analyzeDuplicates($issues) {
    // TODO: Implement actual duplicate detection logic
    // For now, return static data
    return [
        'count' => 72,
        'groups' => [
            [
                'primary' => [
                    'id' => 45123,
                    'number' => 1234,
                    'title' => 'Product page crashes on mobile Safari',
                    'url' => 'https://github.com/woocommerce/woocommerce/issues/1234',
                    'created_at' => time() - (86400 * 30),
                    'comments' => 15,
                    'reactions' => 23
                ],
                'duplicates' => [
                    [
                        'id' => 45156,
                        'number' => 1256,
                        'title' => 'Mobile product page not working on iOS',
                        'url' => 'https://github.com/woocommerce/woocommerce/issues/1256',
                        'similarity' => 0.92,
                        'created_at' => time() - (86400 * 25)
                    ],
                    [
                        'id' => 45189,
                        'number' => 1289,
                        'title' => 'Safari crashes when viewing products',
                        'url' => 'https://github.com/woocommerce/woocommerce/issues/1289',
                        'similarity' => 0.87,
                        'created_at' => time() - (86400 * 20)
                    ]
                ]
            ],
            [
                'primary' => [
                    'id' => 45234,
                    'number' => 1345,
                    'title' => 'Checkout button not responding',
                    'url' => 'https://github.com/woocommerce/woocommerce/issues/1345',
                    'created_at' => time() - (86400 * 40),
                    'comments' => 8,
                    'reactions' => 12
                ],
                'duplicates' => [
                    [
                        'id' => 45267,
                        'number' => 1378,
                        'title' => 'Cannot complete checkout process',
                        'url' => 'https://github.com/woocommerce/woocommerce/issues/1378',
                        'similarity' => 0.89,
                        'created_at' => time() - (86400 * 35)
                    ]
                ]
            ]
        ]
    ];
}

/**
 * Detect issues missing information
 *
 * @param array $issues Array of issues
 * @return array Missing information results
 */
function analyzeMissingInfo($repoId) {
    $issueModel = new Issue();
    $allIssues = $issueModel->findByRepository($repoId);

    $missingInfoIssues = array_filter($allIssues, fn($i) => $i['is_missing_context']);

    $issuesList = array_slice(array_map(function($issue) {
        return [
            'id' => $issue['id'],
            'number' => $issue['issue_number'],
            'title' => $issue['title'],
            'url' => $issue['url'],
            'missing' => $issue['missing_elements'] ?? [],
            'severity' => 'medium',
            'created_at' => $issue['created_at'],
            'body_length' => strlen($issue['body'] ?? '')
        ];
    }, $missingInfoIssues), 0, 10);

    return [
        'count' => count($missingInfoIssues),
        'issues' => $issuesList
    ];
}

/**
 * Detect high signal issues
 *
 * @param int $repoId Repository ID
 * @return array High signal detection results
 */
function analyzeHighSignal($repoId) {
    $issueModel = new Issue();
    $allIssues = $issueModel->findByRepository($repoId);

    $highSignalIssues = array_filter($allIssues, fn($i) => $i['is_high_signal']);

    // Sort by reactions + comments (engagement)
    usort($highSignalIssues, function($a, $b) {
        $scoreA = ($a['reactions_total'] ?? 0) + ($a['comments_count'] ?? 0);
        $scoreB = ($b['reactions_total'] ?? 0) + ($b['comments_count'] ?? 0);
        return $scoreB - $scoreA;
    });

    $issuesList = array_slice(array_map(function($issue) {
        $score = ($issue['reactions_total'] ?? 0) + ($issue['comments_count'] ?? 0);
        return [
            'id' => $issue['id'],
            'number' => $issue['issue_number'],
            'title' => $issue['title'],
            'url' => $issue['url'],
            'signals' => ['ai_detected'],
            'score' => min(100, $score * 2),
            'reactions' => $issue['reactions_total'] ?? 0,
            'comments' => $issue['comments_count'] ?? 0,
            'created_at' => $issue['created_at']
        ];
    }, $highSignalIssues), 0, 10);

    return [
        'count' => count($highSignalIssues),
        'issues' => $issuesList
    ];
}

/**
 * Detect cleanup opportunities
 *
 * @param int $repoId Repository ID
 * @return array Cleanup opportunities
 */
function analyzeCleanup($repoId) {
    $issueModel = new Issue();
    $allIssues = $issueModel->findByRepository($repoId);

    $cleanupIssues = array_filter($allIssues, fn($i) => $i['is_cleanup_candidate']);

    $issuesList = array_slice(array_map(function($issue) {
        return [
            'id' => $issue['id'],
            'number' => $issue['issue_number'],
            'title' => $issue['title'],
            'url' => $issue['url'],
            'cleanup_type' => 'ai_detected',
            'reason' => 'Identified by AI as cleanup candidate',
            'last_activity' => $issue['last_activity_at'] ?? $issue['updated_at'],
            'created_at' => $issue['created_at'],
            'comments' => $issue['comments_count'] ?? 0,
            'reactions' => $issue['reactions_total'] ?? 0
        ];
    }, $cleanupIssues), 0, 10);

    return [
        'count' => count($cleanupIssues),
        'issues' => $issuesList
    ];
}

/**
 * Generate label suggestions
 *
 * @param array $issues Array of issues
 * @return array Label suggestions
 */
function analyzeSuggestions($issues) {
    // TODO: Implement actual suggestion generation
    // For now, return static data
    return [
        'count' => 498,
        'suggestions' => [
            [
                'id' => 46156,
                'number' => 1967,
                'title' => 'Payment processing fails intermittently',
                'url' => 'https://github.com/woocommerce/woocommerce/issues/1967',
                'current_labels' => ['bug'],
                'suggested_labels' => [
                    ['name' => 'priority: high', 'reason' => 'Revenue impact mentioned', 'confidence' => 0.91],
                    ['name' => 'area: payments', 'reason' => 'Issue related to payment processing', 'confidence' => 0.95],
                    ['name' => 'status: needs-investigation', 'reason' => 'Intermittent issue requires debugging', 'confidence' => 0.87]
                ],
                'created_at' => time() - (86400 * 8),
                'comments' => 14
            ],
            [
                'id' => 46167,
                'number' => 1978,
                'title' => 'Admin dashboard slow to load',
                'url' => 'https://github.com/woocommerce/woocommerce/issues/1978',
                'current_labels' => ['bug'],
                'suggested_labels' => [
                    ['name' => 'priority: medium', 'reason' => 'Performance issue affecting admins', 'confidence' => 0.85],
                    ['name' => 'area: admin', 'reason' => 'Issue is in admin dashboard', 'confidence' => 0.98],
                    ['name' => 'type: performance', 'reason' => 'Loading speed issue', 'confidence' => 0.92]
                ],
                'created_at' => time() - (86400 * 12),
                'comments' => 8
            ],
            [
                'id' => 46178,
                'number' => 1989,
                'title' => 'Shipping calculator returns wrong values',
                'url' => 'https://github.com/woocommerce/woocommerce/issues/1989',
                'current_labels' => ['bug'],
                'suggested_labels' => [
                    ['name' => 'priority: high', 'reason' => 'Incorrect calculations affect customer trust', 'confidence' => 0.88],
                    ['name' => 'area: shipping', 'reason' => 'Issue in shipping calculator', 'confidence' => 0.96],
                    ['name' => 'status: confirmed', 'reason' => 'Multiple users confirmed issue', 'confidence' => 0.90]
                ],
                'created_at' => time() - (86400 * 6),
                'comments' => 22,
                'reactions' => 18
            ],
            [
                'id' => 46189,
                'number' => 2000,
                'title' => 'Translation strings missing in checkout',
                'url' => 'https://github.com/woocommerce/woocommerce/issues/2000',
                'current_labels' => ['bug'],
                'suggested_labels' => [
                    ['name' => 'priority: low', 'reason' => 'Cosmetic issue, not blocking functionality', 'confidence' => 0.82],
                    ['name' => 'area: i18n', 'reason' => 'Internationalization/translation issue', 'confidence' => 0.94],
                    ['name' => 'good-first-issue', 'reason' => 'Simple string addition fix', 'confidence' => 0.79]
                ],
                'created_at' => time() - (86400 * 18),
                'comments' => 4
            ]
        ]
    ];
}

/**
 * Detect issue areas/categories
 *
 * @param array $issues Array of issues
 * @return array Area detection results
 */
function analyzeAreas($issues) {
    // TODO: Implement actual area detection using AI/embeddings
    // For now, return static data
    return [
        'areas' => [
            [
                'name' => 'Checkout & Payments',
                'count' => 187,
                'percentage' => 21,
                'icon' => 'credit-card',
                'color' => 'blue'
            ],
            [
                'name' => 'Product Management',
                'count' => 156,
                'percentage' => 17,
                'icon' => 'box',
                'color' => 'purple'
            ],
            [
                'name' => 'Admin & Dashboard',
                'count' => 134,
                'percentage' => 15,
                'icon' => 'gauge',
                'color' => 'green'
            ],
            [
                'name' => 'Shipping & Tax',
                'count' => 98,
                'percentage' => 11,
                'icon' => 'truck',
                'color' => 'orange'
            ],
            [
                'name' => 'Email & Notifications',
                'count' => 76,
                'percentage' => 8,
                'icon' => 'envelope',
                'color' => 'pink'
            ],
            [
                'name' => 'Performance',
                'count' => 67,
                'percentage' => 7,
                'icon' => 'bolt',
                'color' => 'yellow'
            ],
            [
                'name' => 'API & Integrations',
                'count' => 54,
                'percentage' => 6,
                'icon' => 'plug',
                'color' => 'indigo'
            ],
            [
                'name' => 'Other',
                'count' => 134,
                'percentage' => 15,
                'icon' => 'ellipsis',
                'color' => 'slate'
            ]
        ]
    ];
}

/**
 * Analyze priority distribution
 *
 * @param array $issues Array of issues
 * @return array Priority analysis results
 */
function analyzePriorities($issues) {
    // TODO: Implement actual priority detection based on labels, reactions, severity signals
    // For now, return static data
    return [
        'priorities' => [
            [
                'level' => 'Critical',
                'count' => 23,
                'percentage' => 3,
                'color' => 'red',
                'description' => 'Security vulnerabilities, data loss, revenue blocking'
            ],
            [
                'level' => 'High',
                'count' => 145,
                'percentage' => 16,
                'color' => 'orange',
                'description' => 'Significant functionality broken, affects many users'
            ],
            [
                'level' => 'Medium',
                'count' => 412,
                'percentage' => 45,
                'color' => 'yellow',
                'description' => 'Feature not working as expected, workarounds exist'
            ],
            [
                'level' => 'Low',
                'count' => 248,
                'percentage' => 27,
                'color' => 'blue',
                'description' => 'Minor issues, cosmetic problems, edge cases'
            ],
            [
                'level' => 'Needs Triage',
                'count' => 78,
                'percentage' => 9,
                'color' => 'slate',
                'description' => 'Not yet assessed for priority'
            ]
        ]
    ];
}

/**
 * Discover functional areas using Claude AI
 *
 * @param int $repoId Repository ID
 * @param array $issues Array of issues
 * @return array|false Array of area names or false on failure
 */
function discoverAreas($repoId, $issues) {
    $claude = new ClaudeAPI();
    $batchSize = 25;
    $batches = array_chunk($issues, $batchSize);
    $allSuggestions = [];

    foreach ($batches as $index => $batch) {
        $batchNum = $index + 1;
        $totalBatches = count($batches);

        // Build prompt
        $prompt = "Analyze these GitHub issues and suggest high-level, top-level functional areas that best represent the main codebase sections. Avoid secondary or tertiary categories. Suggest as many or as few as naturally emerge from the issues.\n\n";

        if (!empty($allSuggestions)) {
            $prompt .= "Previous batches suggested: " . implode(', ', $allSuggestions) . "\n\n";
        }

        $prompt .= "Issues (batch $batchNum of $totalBatches):\n";
        foreach ($batch as $issue) {
            $prompt .= "- Title: {$issue['title']}\n";
            $body = $issue['body'] ?? '';
            if (!empty($body)) {
                $prompt .= "  Body: " . substr($body, 0, 200) . "\n";
            }
        }

        $prompt .= "\nReturn ONLY a JSON array of area names, no other text. Example: [\"Area 1\", \"Area 2\"]";

        // Retry logic with exponential backoff
        $maxRetries = 3;
        $response = false;
        for ($retry = 0; $retry < $maxRetries; $retry++) {
            $response = $claude->getText($prompt, 2048);
            if ($response) break;

            if ($retry < $maxRetries - 1) {
                sleep(pow(2, $retry) * 10); // 10s, 20s, 40s
            }
        }

        if (!$response) {
            error_log("Area discovery: No response for batch $batchNum after $maxRetries attempts");
            continue;
        }

        // Parse response
        $json = extractJSON($response);
        if (!$json) {
            error_log("Area discovery: Failed to extract JSON from batch $batchNum");
            continue;
        }

        $suggestions = json_decode($json, true);
        if (!is_array($suggestions)) {
            error_log("Area discovery: Invalid JSON in batch $batchNum");
            continue;
        }

        $allSuggestions = array_unique(array_merge($allSuggestions, $suggestions));

        // Delay between batches to avoid rate limits
        if ($batchNum < $totalBatches) {
            sleep(5);
        }
    }

    // Consolidate suggestions from all batches
    if (empty($allSuggestions)) {
        return false;
    }

    $prompt = "Here are area suggestions from multiple batches: " . implode(', ', $allSuggestions) . "\n\n";
    $prompt .= "Consolidate these into a clear, distinct set of high-level functional areas. Merge similar areas and remove duplicates. Return however many areas make sense - don't artificially limit the count.\n\n";
    $prompt .= "Return ONLY a JSON array of final area names, no other text. Example: [\"Area 1\", \"Area 2\"]";

    $response = $claude->getText($prompt, 2048);
    if (!$response) {
        return false;
    }

    $json = extractJSON($response);
    if (!$json) {
        return false;
    }

    $finalAreas = json_decode($json, true);
    return is_array($finalAreas) ? $finalAreas : false;
}

/**
 * Categorize issues by area using Claude AI
 *
 * @param int $repoId Repository ID
 * @param array $issues Array of issues
 * @param array $areas Array of area objects
 */
function categorizeIssues($repoId, $issues, $areas) {
    $claude = new ClaudeAPI();
    $issueModel = new Issue();
    $batchSize = 100;
    $batches = array_chunk($issues, $batchSize);

    // Extract area names
    $areaNames = array_map(function($area) { return $area['name']; }, $areas);
    $areaMap = [];
    foreach ($areas as $area) {
        $areaMap[$area['name']] = $area['id'];
    }

    foreach ($batches as $batch) {
        // Prepare issue data
        $issueData = [];
        foreach ($batch as $issue) {
            $issueData[] = [
                'id' => $issue['id'],
                'title' => $issue['title'],
                'body' => substr($issue['body'] ?? '', 0, 300)
            ];
        }

        // Build prompt
        $prompt = "For each issue, select the ONE area that best matches from this list: " . implode(', ', $areaNames) . "\n\n";
        $prompt .= "Issues:\n";
        foreach ($issueData as $issue) {
            $prompt .= "ID: {$issue['id']}\n";
            $prompt .= "Title: {$issue['title']}\n";
            if (!empty($issue['body'])) {
                $prompt .= "Body: {$issue['body']}\n";
            }
            $prompt .= "\n";
        }

        $prompt .= "Return ONLY a JSON array of objects with this format: [{\"issue_id\": 123, \"area\": \"Area Name\"}, ...]\nNo other text.";

        $response = $claude->getText($prompt, 4096);

        if ($response) {
            $json = extractJSON($response);
            if ($json) {
                $categorizations = json_decode($json, true);
                if (is_array($categorizations)) {
                    foreach ($categorizations as $cat) {
                        if (isset($cat['issue_id'], $cat['area'], $areaMap[$cat['area']])) {
                            $issueModel->updateArea($cat['issue_id'], $areaMap[$cat['area']]);
                        }
                    }
                }
            }
        }
    }
}

/**
 * Extract JSON from Claude response (handles cases where response includes extra text)
 *
 * @param string $text Response text
 * @return string|false JSON string or false
 */
function extractJSON($text) {
    // Try to find JSON array in the text
    if (preg_match('/\[.*\]/s', $text, $matches)) {
        return $matches[0];
    }
    return false;
}

/**
 * Analyze a small chunk of issues (for AJAX chunked processing)
 *
 * @param int $repoId Repository ID
 * @param array $chunk Small array of issues (typically 5)
 * @param array $areas Array of areas
 * @return bool Success status
 */
function analyzeIssueChunk($repoId, $chunk, $areas) {
    $openai = new OpenAIAPI();
    $issueModel = new Issue();
    $repoModel = new Repository();

    // Get repository for label context
    $repo = $repoModel->findById($repoId);
    if (!$repo) {
        return false;
    }

    // Get all labels from repo
    $githubToken = null;
    $githubApi = new GitHubAPI($githubToken);
    $repoLabels = [];
    try {
        $labels = $githubApi->getLabels($repo['owner'], $repo['name']);
        if ($labels) {
            $repoLabels = array_column($labels, 'name');
        }
    } catch (Exception $e) {
        error_log('Failed to fetch labels: ' . $e->getMessage());
    }

    // Analyze this chunk with retry logic
    $maxRetries = 3;
    $analysisResults = false;
    for ($retry = 0; $retry < $maxRetries; $retry++) {
        $analysisResults = analyzeIssueBatch($chunk, $areas, $repoLabels, $openai);
        if ($analysisResults) break;

        if ($retry < $maxRetries - 1) {
            sleep(pow(2, $retry) * 10); // Exponential backoff: 10s, 20s, 40s
        }
    }

    if (!$analysisResults) {
        error_log("Failed to analyze chunk after $maxRetries attempts");
        return false;
    }

    // Generate embeddings for summaries
    $summaries = array_column($analysisResults, 'summary');
    $embeddings = generateEmbeddings($summaries, $openai);

    // Save results to database
    foreach ($analysisResults as $i => $analysis) {
        $issueId = $analysis['issue_id'];
        $data = [
            'is_high_signal' => $analysis['is_high_signal'],
            'is_cleanup_candidate' => $analysis['is_cleanup_candidate'],
            'is_missing_context' => $analysis['is_missing_context'],
            'missing_elements' => $analysis['missing_elements'],
            'is_missing_labels' => $analysis['is_missing_labels'],
            'suggested_labels' => $analysis['suggested_labels'],
            'summary' => $analysis['summary'],
            'embedding' => $embeddings[$i] ?? null
        ];

        $issueModel->updateAnalysis($issueId, $data);

        // Update area if provided
        if (isset($analysis['proposed_area_id'])) {
            $issueModel->updateArea($issueId, $analysis['proposed_area_id']);
        }
    }

    return true;
}

/**
 * Analyze all issues using OpenAI GPT-4o-mini
 *
 * @param int $repoId Repository ID
 * @param array $issues Array of issues
 * @param array $areas Array of areas
 * @return bool Success status
 */
function analyzeAllIssues($repoId, $issues, $areas) {
    $openai = new OpenAIAPI();
    $issueModel = new Issue();
    $repoModel = new Repository();

    // Get repository for label context
    $repo = $repoModel->findById($repoId);
    if (!$repo) {
        return false;
    }

    // Get all labels from repo
    $githubToken = null; // TODO: Get from user if available
    $githubApi = new GitHubAPI($githubToken);
    $repoLabels = [];
    try {
        $labels = $githubApi->getLabels($repo['owner'], $repo['name']);
        if ($labels) {
            $repoLabels = array_column($labels, 'name');
        }
    } catch (Exception $e) {
        error_log('Failed to fetch labels: ' . $e->getMessage());
    }

    // Batch process issues
    $batchSize = 25;
    $batches = array_chunk($issues, $batchSize);

    foreach ($batches as $index => $batch) {
        $batchNum = $index + 1;
        $totalBatches = count($batches);

        // Analyze batch with retry logic
        $maxRetries = 3;
        $analysisResults = false;
        for ($retry = 0; $retry < $maxRetries; $retry++) {
            $analysisResults = analyzeIssueBatch($batch, $areas, $repoLabels, $openai);
            if ($analysisResults) break;

            if ($retry < $maxRetries - 1) {
                sleep(pow(2, $retry) * 10);
            }
        }

        if (!$analysisResults) {
            error_log("Issue analysis: Failed batch $batchNum after $maxRetries attempts");
            continue;
        }

        // Generate embeddings for summaries
        $summaries = array_column($analysisResults, 'summary');
        $embeddings = generateEmbeddings($summaries, $openai);

        // Save results to database
        foreach ($analysisResults as $i => $analysis) {
            $issueId = $analysis['issue_id'];
            $data = [
                'is_high_signal' => $analysis['is_high_signal'],
                'is_cleanup_candidate' => $analysis['is_cleanup_candidate'],
                'is_missing_context' => $analysis['is_missing_context'],
                'missing_elements' => $analysis['missing_elements'],
                'is_missing_labels' => $analysis['is_missing_labels'],
                'suggested_labels' => $analysis['suggested_labels'],
                'summary' => $analysis['summary'],
                'embedding' => $embeddings[$i] ?? null
            ];

            $issueModel->updateAnalysis($issueId, $data);

            // Update area if provided
            if (isset($analysis['proposed_area_id'])) {
                $issueModel->updateArea($issueId, $analysis['proposed_area_id']);
            }
        }

        // Delay between batches
        if ($batchNum < $totalBatches) {
            sleep(5);
        }
    }

    return true;
}

/**
 * Sanitize text for safe JSON encoding
 *
 * @param string $text Text to sanitize
 * @return string Sanitized text
 */
function sanitizeText($text) {
    if (empty($text)) {
        return '';
    }

    // Remove control characters except newlines and tabs
    $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text);

    // Remove invalid UTF-8 sequences
    return iconv('UTF-8', 'UTF-8//IGNORE', $text);
}

/**
 * Analyze a batch of issues with GPT-4o-mini
 *
 * @param array $batch Issues to analyze
 * @param array $areas Available areas
 * @param array $repoLabels Available labels in repo
 * @param OpenAIAPI $openai OpenAI API instance
 * @return array|false Analysis results or false on failure
 */
function analyzeIssueBatch($batch, $areas, $repoLabels, $openai) {
    // Build area context
    $areaContext = "Available areas:\n";
    foreach ($areas as $area) {
        $areaName = sanitizeText($area['name']);
        $areaContext .= "- ID {$area['id']}: {$areaName}\n";
    }

    // Build label context
    if (!empty($repoLabels)) {
        $sanitizedLabels = array_map('sanitizeText', $repoLabels);
        $labelContext = "Available labels: " . implode(', ', $sanitizedLabels);
    } else {
        $labelContext = "No existing labels found.";
    }

    // Build issues data
    $issuesData = "";
    foreach ($batch as $issue) {
        $title = sanitizeText($issue['title'] ?? '');
        $body = sanitizeText($issue['body'] ?? '');

        $issuesData .= "Issue ID: {$issue['id']}\n";
        $issuesData .= "Title: $title\n";
        if (!empty($body)) {
            $issuesData .= "Body: " . substr($body, 0, 500) . "\n";
        }
        $issuesData .= "\n";
    }

    // Build prompt
    $prompt = "Analyze these GitHub issues and provide structured analysis for each one.

$areaContext

$labelContext

For each issue, determine:
1. is_high_signal (bool): Is this a valuable, actionable issue worth prioritizing?
2. is_cleanup_candidate (bool): Should this issue be closed (stale, duplicate, not actionable)?
3. is_missing_context (bool): Does it lack critical information?
4. missing_elements (array): What specific information is missing (e.g., \"steps to reproduce\", \"error logs\")?
5. is_missing_labels (bool): Would additional labels from the available labels help categorize this?
6. suggested_labels (array): Suggest labels from the available labels list
7. proposed_area_id (int): Which area ID best matches this issue?
8. summary (string): Write a clear 2-3 sentence summary suitable for semantic search

Issues:
$issuesData

Return ONLY a JSON array of objects with this exact format:
[{
  \"issue_id\": 123,
  \"is_high_signal\": true,
  \"is_cleanup_candidate\": false,
  \"is_missing_context\": false,
  \"missing_elements\": [],
  \"is_missing_labels\": true,
  \"suggested_labels\": [\"bug\", \"needs-triage\"],
  \"proposed_area_id\": 5,
  \"summary\": \"Brief summary here...\"
}]";

    // Final sanitization - ensure entire prompt is valid UTF-8
    $prompt = sanitizeText($prompt);

    $messages = [
        ['role' => 'user', 'content' => $prompt]
    ];

    $response = $openai->getChatText($messages, null, 2000);

    if (!$response) {
        return false;
    }

    $json = extractJSON($response);
    if (!$json) {
        error_log("analyzeIssueBatch: Failed to extract JSON from response");
        return false;
    }

    $results = json_decode($json, true);
    if (!is_array($results)) {
        error_log("analyzeIssueBatch: Invalid JSON array");
        return false;
    }

    return $results;
}

/**
 * Generate embeddings for text summaries
 *
 * @param array $summaries Array of text summaries
 * @param OpenAIAPI $openai OpenAI API instance
 * @return array Array of embedding vectors
 */
function generateEmbeddings($summaries, $openai) {
    $embeddings = [];

    foreach ($summaries as $summary) {
        if (empty($summary)) {
            $embeddings[] = null;
            continue;
        }

        $maxRetries = 3;
        $embedding = false;
        for ($retry = 0; $retry < $maxRetries; $retry++) {
            $embedding = $openai->embedding($summary);
            if ($embedding) break;

            if ($retry < $maxRetries - 1) {
                sleep(2);
            }
        }

        $embeddings[] = $embedding;

        // Small delay between embeddings
        sleep(1);
    }

    return $embeddings;
}

/**
 * Calculate cosine similarity between two vectors
 *
 * @param array $vec1 First vector
 * @param array $vec2 Second vector
 * @return float Similarity score (0-1)
 */
function cosineSimilarity($vec1, $vec2) {
    if (empty($vec1) || empty($vec2) || count($vec1) !== count($vec2)) {
        return 0;
    }

    $dotProduct = 0;
    $magnitude1 = 0;
    $magnitude2 = 0;

    for ($i = 0; $i < count($vec1); $i++) {
        $dotProduct += $vec1[$i] * $vec2[$i];
        $magnitude1 += $vec1[$i] * $vec1[$i];
        $magnitude2 += $vec2[$i] * $vec2[$i];
    }

    $magnitude1 = sqrt($magnitude1);
    $magnitude2 = sqrt($magnitude2);

    if ($magnitude1 == 0 || $magnitude2 == 0) {
        return 0;
    }

    return $dotProduct / ($magnitude1 * $magnitude2);
}

/**
 * Find duplicate issues using embedding similarity
 *
 * @param int $repoId Repository ID
 * @return array Duplicate groups
 */
function findDuplicates($repoId) {
    $issueModel = new Issue();
    $issues = $issueModel->findWithEmbeddings($repoId);

    if (empty($issues)) {
        return [];
    }

    $duplicates = [];
    $threshold = 0.85;
    $processed = [];

    for ($i = 0; $i < count($issues); $i++) {
        if (isset($processed[$issues[$i]['id']])) {
            continue;
        }

        $embedding1 = $issues[$i]['embedding'];
        if (!$embedding1) continue;

        $similarIssues = [];

        for ($j = $i + 1; $j < count($issues); $j++) {
            if (isset($processed[$issues[$j]['id']])) {
                continue;
            }

            $embedding2 = $issues[$j]['embedding'];
            if (!$embedding2) continue;

            $similarity = cosineSimilarity($embedding1, $embedding2);

            if ($similarity >= $threshold) {
                $similarIssues[] = [
                    'id' => $issues[$j]['id'],
                    'number' => $issues[$j]['issue_number'],
                    'title' => $issues[$j]['title'],
                    'url' => $issues[$j]['url'],
                    'similarity' => $similarity,
                    'created_at' => $issues[$j]['created_at']
                ];
                $processed[$issues[$j]['id']] = true;
            }
        }

        if (!empty($similarIssues)) {
            $duplicates[] = [
                'primary' => [
                    'id' => $issues[$i]['id'],
                    'number' => $issues[$i]['issue_number'],
                    'title' => $issues[$i]['title'],
                    'url' => $issues[$i]['url'],
                    'created_at' => $issues[$i]['created_at']
                ],
                'duplicates' => $similarIssues
            ];
            $processed[$issues[$i]['id']] = true;
        }
    }

    return $duplicates;
}

// If we get here, invalid route
redirect('');
exit;
