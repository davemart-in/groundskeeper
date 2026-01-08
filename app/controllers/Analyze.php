<?php if (!defined('APPPATH')) exit('No direct script access allowed');

/**
 * Analyze Controller
 *
 * Handles repository analysis operations
 */

$repoModel = new Repository();
$issueModel = new Issue();
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

    // Run all analysis processes
    $results = runAnalysis($repoId, $issues);

    // Store analysis results in session for the dashboard to use
    $_SESSION['analysis_results'] = $results;

    // Set success message
    $_SESSION['success'] = "Analysis complete! Analyzed {$issueCount} issue" . ($issueCount !== 1 ? 's' : '') . ".";

    // Redirect back to dashboard
    redirect('');
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

    // Step 2: Detect duplicates
    $results['duplicates'] = analyzeDuplicates($issues);

    // Step 3: Detect missing information
    $results['missing_info'] = analyzeMissingInfo($issues);

    // Step 4: Detect high signal issues
    $results['high_signal'] = analyzeHighSignal($issues);

    // Step 5: Detect cleanup opportunities
    $results['cleanup'] = analyzeCleanup($issues);

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
function analyzeMissingInfo($issues) {
    // TODO: Implement actual missing info detection
    // For now, return static data
    return [
        'count' => 589,
        'issues' => [
            [
                'id' => 45678,
                'number' => 1567,
                'title' => 'Cart not updating',
                'url' => 'https://github.com/woocommerce/woocommerce/issues/1567',
                'missing' => ['reproduction_steps', 'environment_info'],
                'severity' => 'high',
                'created_at' => time() - (86400 * 15),
                'body_length' => 45
            ],
            [
                'id' => 45689,
                'number' => 1578,
                'title' => 'Error on checkout',
                'url' => 'https://github.com/woocommerce/woocommerce/issues/1578',
                'missing' => ['reproduction_steps', 'error_logs'],
                'severity' => 'high',
                'created_at' => time() - (86400 * 12),
                'body_length' => 32
            ],
            [
                'id' => 45701,
                'number' => 1590,
                'title' => 'Product images not showing',
                'url' => 'https://github.com/woocommerce/woocommerce/issues/1590',
                'missing' => ['environment_info', 'browser_version'],
                'severity' => 'medium',
                'created_at' => time() - (86400 * 10),
                'body_length' => 28
            ],
            [
                'id' => 45723,
                'number' => 1612,
                'title' => 'Issue with plugin',
                'url' => 'https://github.com/woocommerce/woocommerce/issues/1612',
                'missing' => ['reproduction_steps', 'expected_behavior', 'actual_behavior'],
                'severity' => 'high',
                'created_at' => time() - (86400 * 8),
                'body_length' => 18
            ]
        ]
    ];
}

/**
 * Detect high signal issues
 *
 * @param array $issues Array of issues
 * @return array High signal detection results
 */
function analyzeHighSignal($issues) {
    // TODO: Implement actual high signal detection
    // For now, return static data
    return [
        'count' => 45,
        'issues' => [
            [
                'id' => 45890,
                'number' => 1701,
                'title' => 'Critical security vulnerability in payment gateway',
                'url' => 'https://github.com/woocommerce/woocommerce/issues/1701',
                'signals' => ['security', 'high_engagement', 'clear_reproduction'],
                'score' => 95,
                'reactions' => 48,
                'comments' => 32,
                'created_at' => time() - (86400 * 5)
            ],
            [
                'id' => 45901,
                'number' => 1712,
                'title' => 'Data loss when updating product inventory',
                'url' => 'https://github.com/woocommerce/woocommerce/issues/1712',
                'signals' => ['data_loss', 'clear_reproduction', 'affects_many'],
                'score' => 92,
                'reactions' => 41,
                'comments' => 28,
                'created_at' => time() - (86400 * 7)
            ],
            [
                'id' => 45912,
                'number' => 1723,
                'title' => 'Orders not processing in high traffic',
                'url' => 'https://github.com/woocommerce/woocommerce/issues/1723',
                'signals' => ['revenue_impact', 'clear_reproduction'],
                'score' => 88,
                'reactions' => 35,
                'comments' => 24,
                'created_at' => time() - (86400 * 9)
            ],
            [
                'id' => 45923,
                'number' => 1734,
                'title' => 'Customer emails not being sent',
                'url' => 'https://github.com/woocommerce/woocommerce/issues/1734',
                'signals' => ['affects_many', 'high_engagement'],
                'score' => 85,
                'reactions' => 29,
                'comments' => 19,
                'created_at' => time() - (86400 * 11)
            ]
        ]
    ];
}

/**
 * Detect cleanup opportunities
 *
 * @param array $issues Array of issues
 * @return array Cleanup opportunities
 */
function analyzeCleanup($issues) {
    // TODO: Implement actual cleanup detection
    // For now, return static data
    return [
        'count' => 109,
        'issues' => [
            [
                'id' => 46012,
                'number' => 1823,
                'title' => 'Feature request from 2019',
                'url' => 'https://github.com/woocommerce/woocommerce/issues/1823',
                'cleanup_type' => 'stale',
                'reason' => 'No activity in 18+ months',
                'last_activity' => time() - (86400 * 547),
                'created_at' => time() - (86400 * 1825),
                'comments' => 3
            ],
            [
                'id' => 46023,
                'number' => 1834,
                'title' => 'Bug fixed in v5.2 but issue still open',
                'url' => 'https://github.com/woocommerce/woocommerce/issues/1834',
                'cleanup_type' => 'resolved_but_open',
                'reason' => 'Comments indicate fix was released',
                'last_activity' => time() - (86400 * 45),
                'created_at' => time() - (86400 * 120),
                'comments' => 12
            ],
            [
                'id' => 46034,
                'number' => 1845,
                'title' => 'Missing priority label',
                'url' => 'https://github.com/woocommerce/woocommerce/issues/1845',
                'cleanup_type' => 'needs_labels',
                'reason' => 'High engagement but no priority set',
                'last_activity' => time() - (86400 * 3),
                'created_at' => time() - (86400 * 60),
                'comments' => 28,
                'reactions' => 34
            ],
            [
                'id' => 46045,
                'number' => 1856,
                'title' => 'Cannot reproduce - needs more info',
                'url' => 'https://github.com/woocommerce/woocommerce/issues/1856',
                'cleanup_type' => 'stale',
                'reason' => 'Waiting for OP response for 6 months',
                'last_activity' => time() - (86400 * 180),
                'created_at' => time() - (86400 * 365),
                'comments' => 5
            ]
        ]
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

// If we get here, invalid route
redirect('');
exit;
