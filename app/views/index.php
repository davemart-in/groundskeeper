<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Groundskeeper v0.1 POC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<link rel="stylesheet" type="text/css" charset="utf-8"  media="screen, projection" href="<?php echo BASEURL; ?>css/groundskeeper-core.css?<?php getVersionNumber(); ?>" />
	<link rel="stylesheet" type="text/css" charset="utf-8"  media="screen, projection" href="<?php echo BASEURL; ?>css/libraries/tailwind.min.css?<?php getVersionNumber(); ?>" />
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        emerald: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            600: '#059669',
                            700: '#047857',
                            900: '#064e3b',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans min-h-screen flex flex-col">

    <!-- Top Navigation Bar -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center gap-2 mr-8">
                        <div class="w-8 h-8 bg-emerald-600 rounded-lg flex items-center justify-center text-white font-bold">
                            <i class="fa-solid fa-leaf"></i>
                        </div>
                        <span class="font-bold text-slate-900 text-lg tracking-tight">Groundskeeper <span class="text-xs font-normal text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full ml-1 border border-slate-200">v0.1</span></span>
                    </div>
                    <div class="hidden sm:-my-px sm:flex sm:space-x-8">
                        <a href="<?php echo BASEURL; ?>" id="tab-dashboard" class="<?php echo (!isset($glob['active_tab']) || $glob['active_tab'] === 'dashboard') ? 'border-emerald-500 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Dashboard
                        </a>
                        <a href="<?php echo BASEURL; ?>settings" id="tab-settings" class="<?php echo (isset($glob['active_tab']) && $glob['active_tab'] === 'settings') ? 'border-emerald-500 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full flex-1 relative">

        <!-- DASHBOARD TAB -->
        <div id="view-dashboard" class="space-y-6 animate-fade-in <?php echo (isset($glob['active_tab']) && $glob['active_tab'] === 'settings') ? 'hidden' : ''; ?>">
            
            <!-- Dashboard Controls -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-4 rounded-lg shadow-sm border border-slate-200">
                <div class="relative w-full sm:w-80">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Repository</label>
                    <?php if (!empty($glob['repositories'])): ?>
                        <select class="block w-full pl-3 pr-10 py-2 text-base border-slate-300 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm rounded-md bg-slate-50 border">
                            <?php foreach ($glob['repositories'] as $repo): ?>
                                <option value="<?php echo $repo['id']; ?>"><?php echo htmlspecialchars($repo['full_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <div class="text-sm text-slate-500 py-2">
                            No repositories connected. <a href="<?php echo BASEURL; ?>settings" class="text-emerald-600 hover:underline font-medium">Add a repository</a>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($glob['repositories']) && isset($glob['selected_repo'])): ?>
                <div class="text-right">
                    <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Analysis Status</span>
                    <?php if (!$glob['selected_repo']['last_audited_at']): ?>
                        <div class="flex items-center gap-2 text-sm text-slate-700">
                            <span class="w-2 h-2 rounded-full bg-slate-300"></span>
                            Not yet audited
                            <form method="POST" action="<?php echo BASEURL; ?>audit/run/<?php echo $glob['selected_repo']['id']; ?>" class="inline" onsubmit="showAuditLoading()">
                                <button type="submit" class="ml-2 text-emerald-600 hover:text-emerald-800 text-xs font-medium border border-emerald-200 px-2 py-0.5 rounded bg-emerald-50"><i class="fa-solid fa-play mr-1"></i> Run Audit</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="flex items-center gap-2 text-sm text-slate-700">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Last audited <?php echo date('M j, Y', $glob['selected_repo']['last_audited_at']); ?>
                            <form method="POST" action="<?php echo BASEURL; ?>audit/run/<?php echo $glob['selected_repo']['id']; ?>" class="inline" onsubmit="showAuditLoading()">
                                <button type="submit" class="ml-2 text-emerald-600 hover:text-emerald-800 text-xs font-medium border border-emerald-200 px-2 py-0.5 rounded bg-emerald-50"><i class="fa-solid fa-rotate mr-1"></i> Re-audit</button>
                            </form>
                            <form id="analyze-form" method="POST" action="<?php echo BASEURL; ?>analyze/run/<?php echo $glob['selected_repo']['id']; ?>" class="inline">
                                <button type="submit" class="text-blue-600 hover:text-blue-800 text-xs font-medium border border-blue-200 px-2 py-0.5 rounded bg-blue-50"><i class="fa-solid fa-chart-line mr-1"></i> Re-analyze</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Left Column: The Findings Feed -->
                <div class="lg:col-span-2 space-y-4">
                    <div id="analysis-header" class="flex items-center gap-2">
                        <h3 class="text-lg font-bold text-slate-900">Analysis Findings</h3>
                    </div>

                    <!-- Stat Card: Total -->
                    <div class="bg-white p-4 rounded-lg shadow-sm border border-slate-200 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-slate-100 rounded-lg text-slate-500">
                                <i class="fa-solid fa-layer-group text-xl"></i>
                            </div>
                            <div>
                                <h4 class="text-2xl font-bold text-slate-900" id="stat-total"><?php echo count($glob['issues']); ?></h4>
                                <p class="text-sm text-slate-500">Total open bugs</p>
                            </div>
                        </div>
                        <?php if (isset($glob['selected_repo'])): ?>
                            <a href="https://github.com/<?php echo htmlspecialchars($glob['selected_repo']['owner']); ?>/<?php echo htmlspecialchars($glob['selected_repo']['name']); ?>/issues?q=is%3Aissue%20state%3Aopen%20label%3A<?php echo urlencode($glob['selected_repo']['bug_label']); ?>" target="_blank" class="text-sm text-slate-400 hover:text-slate-600"><i class="fa-brands fa-github mr-1"></i> View on GitHub</a>
                        <?php else: ?>
                            <a href="#" class="text-sm text-slate-400 hover:text-slate-600"><i class="fa-brands fa-github mr-1"></i> View on GitHub</a>
                        <?php endif; ?>
                    </div>

                    <!-- Action Card: High Signal -->
                    <div class="bg-white p-5 rounded-lg shadow-sm border border-slate-200 border-l-4 border-l-red-500 group hover:shadow-md transition">
                        <div class="flex justify-between items-start">
                            <div class="flex gap-4">
                                <div class="pt-1">
                                    <div class="w-8 h-8 rounded-full bg-red-100 text-red-600 flex items-center justify-center font-bold text-sm">
                                        <i class="fa-solid fa-fire"></i>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-slate-900"><span id="stat-high-signal"><?php echo count($glob['high_signal_issues']); ?></span> High Signal Issues</h4>
                                    <p class="text-sm text-slate-500 mt-1">Valuable, actionable issues worth prioritizing</p>
                                </div>
                            </div>
                            <button onclick="openModal('high-signal')" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition">
                                View Issues
                            </button>
                        </div>
                    </div>

                    <!-- Action Card: Duplicates -->
                    <div class="bg-white p-5 rounded-lg shadow-sm border border-slate-200 border-l-4 border-l-amber-400 group hover:shadow-md transition">
                        <div class="flex justify-between items-start">
                            <div class="flex gap-4">
                                <div class="pt-1">
                                    <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center font-bold text-sm">
                                        <i class="fa-solid fa-clone"></i>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-slate-900"><span id="stat-duplicates"><?php echo count($glob['duplicates']); ?></span> Likely Duplicates</h4>
                                    <p class="text-sm text-slate-500 mt-1">Issues that appear to be semantically similar.</p>
                                </div>
                            </div>
                            <button onclick="openModal('duplicates')" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 hover:text-emerald-700 hover:border-emerald-300 px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition">
                                View Issues
                            </button>
                        </div>
                    </div>

                    <!-- Action Card: Should Close -->
                    <div class="bg-white p-5 rounded-lg shadow-sm border border-slate-200 border-l-4 border-l-slate-400 group hover:shadow-md transition">
                        <div class="flex justify-between items-start">
                            <div class="flex gap-4">
                                <div class="pt-1">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-sm">
                                        <i class="fa-solid fa-archive"></i>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-slate-900"><span id="stat-cleanup"><?php echo count($glob['cleanup_candidates']); ?></span> Cleanup Candidates</h4>
                                    <p class="text-sm text-slate-500 mt-1">Issues that should likely be closed</p>
                                </div>
                            </div>
                            <button onclick="openModal('cleanup')" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition">
                                View Issues
                            </button>
                        </div>
                    </div>

                    <!-- Action Card: Missing Info -->
                    <div class="bg-white p-5 rounded-lg shadow-sm border border-slate-200 border-l-4 border-l-blue-400 group hover:shadow-md transition">
                        <div class="flex justify-between items-start">
                            <div class="flex gap-4">
                                <div class="pt-1">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm">
                                        <i class="fa-solid fa-circle-question"></i>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-slate-900"><span id="stat-missing-info"><?php echo count($glob['missing_info_issues']); ?></span> Missing Critical Info</h4>
                                    <p class="text-sm text-slate-500 mt-1">Issues lacking critical information</p>
                                </div>
                            </div>
                            <button onclick="openModal('missing-info')" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition">
                                View Issues
                            </button>
                        </div>
                    </div>

                    <!-- Action Card: Suggestions -->
                    <div class="bg-white p-5 rounded-lg shadow-sm border border-slate-200 border-l-4 border-l-purple-400 group hover:shadow-md transition">
                        <div class="flex justify-between items-start">
                            <div class="flex gap-4">
                                <div class="pt-1">
                                    <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center font-bold text-sm">
                                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-slate-900"><span id="stat-suggestions"><?php echo count($glob['label_suggestions']); ?></span> Label Suggestions</h4>
                                    <p class="text-sm text-slate-500 mt-1">AI-recommended labels to improve categorization</p>
                                </div>
                            </div>
                            <button onclick="openModal('suggestions')" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition">
                                Review Suggestions
                            </button>
                        </div>
                    </div>

                </div>

                <!-- Right Column: Breakdowns -->
                <div class="space-y-8">

                    <?php if ($hasPriorityLabels): ?>
                    <!-- By Priority -->
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 mb-4">Issues by Priority</h3>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200">
                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="font-medium text-red-700">Critical / High</span>
                                        <span class="text-slate-500">12%</span>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-2">
                                        <div class="bg-red-500 h-2 rounded-full" style="width: 12%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="font-medium text-amber-700">Medium</span>
                                        <span class="text-slate-500">34%</span>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-2">
                                        <div class="bg-amber-400 h-2 rounded-full" style="width: 34%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="font-medium text-blue-700">Low / Enhancement</span>
                                        <span class="text-slate-500">41%</span>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-2">
                                        <div class="bg-blue-400 h-2 rounded-full" style="width: 41%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="font-medium text-slate-500">Un-prioritized</span>
                                        <span class="text-slate-500">13%</span>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-2">
                                        <div class="bg-slate-300 h-2 rounded-full" style="width: 13%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                     <!-- By Functionality -->
                     <div>
                        <h3 class="text-lg font-bold text-slate-900 mb-4">Issues by Area</h3>
                        <?php if (!empty($glob['area_stats'])): ?>
                        <div class="bg-white p-2 rounded-lg shadow-sm border border-slate-200">
                           <table class="min-w-full text-sm">
                               <tbody class="divide-y divide-slate-100">
                                   <?php
                                   $topCount = 10;
                                   foreach ($glob['area_stats'] as $index => $area):
                                       $isHidden = $index >= $topCount;
                                       $rowClass = $isHidden ? 'hidden area-hidden bg-slate-50/50 hover:bg-slate-100' : 'hover:bg-slate-50';
                                   ?>
                                   <tr class="<?php echo $rowClass; ?> cursor-pointer group" onclick="filterDashboard('<?php echo htmlspecialchars(addslashes($area['name'])); ?>', <?php echo $area['count']; ?>)">
                                       <td class="p-3 font-medium text-slate-700 group-hover:text-emerald-700"><?php echo htmlspecialchars($area['name']); ?></td>
                                       <td class="p-3 text-right text-slate-500 whitespace-nowrap"><?php echo $area['count']; ?> <span class="text-xs text-slate-400 ml-1">(<?php echo $area['percentage']; ?>%)</span></td>
                                   </tr>
                                   <?php endforeach; ?>
                               </tbody>
                           </table>
                           <?php if (count($glob['area_stats']) > $topCount): ?>
                           <div class="p-2 border-t border-slate-50 text-center">
                                <button onclick="toggleAreas()" id="btn-show-areas" class="text-xs font-medium text-emerald-600 hover:text-emerald-700">Show all</button>
                           </div>
                           <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200 text-center text-slate-500 text-sm">
                            No areas defined yet. Run analysis to categorize issues.
                        </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>

        <!-- SETTINGS TAB -->
        <div id="view-settings" class="<?php echo (!isset($glob['active_tab']) || $glob['active_tab'] === 'dashboard') ? 'hidden' : ''; ?> animate-fade-in h-[calc(100vh-140px)]">
            <div class="flex h-full bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                
                <!-- Sidebar -->
                <div class="w-64 bg-slate-50 border-r border-slate-200 flex flex-col">
                    <div class="p-4 border-b border-slate-200 flex justify-between items-center">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Repositories</span>
                        <button onclick="openModal('add-repo')" class="text-emerald-600 hover:text-emerald-700 text-xs font-bold"><i class="fa-solid fa-plus"></i> Add</button>
                    </div>
                    <div class="flex-1 overflow-y-auto">
                        <nav class="space-y-1 p-2">
                            <?php if (!empty($glob['repositories'])): ?>
                                <?php foreach ($glob['repositories'] as $repo): ?>
                                    <a href="<?php echo BASEURL; ?>settings/<?php echo $repo['id']; ?>" class="<?php echo (isset($glob['selected_repo']) && $glob['selected_repo']['id'] === $repo['id']) ? 'bg-white border border-slate-200 text-slate-900 shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'; ?> group flex items-center px-3 py-2 text-sm font-medium rounded-md">
                                        <i class="fa-brands fa-github text-slate-400 mr-3"></i>
                                        <span class="truncate"><?php echo htmlspecialchars($repo['full_name']); ?></span>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="p-4 text-center text-slate-400 text-xs">
                                    No repositories yet
                                </div>
                            <?php endif; ?>
                        </nav>
                    </div>
                </div>

                <!-- Content -->
                <div class="flex-1 overflow-y-auto p-8 <?php echo (empty($glob['repositories']) || !isset($glob['selected_repo'])) ? 'flex items-center justify-center' : ''; ?>">
                    <?php if (!empty($glob['repositories']) && isset($glob['selected_repo'])): ?>
                    <div class="max-w-2xl">
                            <!-- Repo selected - show header -->
                            <div class="flex justify-between items-start mb-8">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3">
                                        <h2 class="text-xl font-bold text-slate-900"><?php echo htmlspecialchars($glob['selected_repo']['full_name']); ?></h2>
                                        <?php if (isset($glob['user']) && $glob['user']): ?>
                                            <div class="flex items-center gap-2 px-3 py-1 bg-green-50 text-green-700 rounded-full border border-green-200 text-xs font-medium">
                                                <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                                Connected
                                            </div>
                                        <?php else: ?>
                                            <div class="flex items-center gap-2 px-3 py-1 bg-red-50 text-red-700 rounded-full border border-red-200 text-xs font-medium">
                                                <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                                                Disconnected
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-sm text-slate-500 mt-1">Manage how Groundskeeper interacts with this repo.</p>
                                </div>
                                <form method="POST" action="<?php echo BASEURL; ?>settings/<?php echo $glob['selected_repo']['id']; ?>/delete" onsubmit="return confirm('Are you sure you want to remove this repository?');">
                                    <button type="submit" class="text-sm text-red-600 hover:text-red-700 font-medium">
                                        <i class="fa-solid fa-trash mr-1"></i> Remove
                                    </button>
                                </form>
                            </div>
                        <?php else: ?>
                            <!-- No repos - show blank slate -->
                            <div class="text-center">
                                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fa-brands fa-github text-slate-400 text-2xl"></i>
                                </div>
                                <h3 class="text-lg font-bold text-slate-900 mb-2">No repositories connected</h3>
                                <p class="text-sm text-slate-500 mb-6">Add your first repository to start analyzing issues.</p>
                                <button onclick="openModal('add-repo')" class="bg-emerald-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-emerald-700">
                                    <i class="fa-solid fa-plus mr-2"></i>
                                    Add Your First Repository
                                </button>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($glob['repositories']) && isset($glob['selected_repo'])): ?>
                        <!-- Config Form -->
                        <div class="space-y-8">
                            <!-- Access Mode Section -->
                            <div class="bg-slate-50 p-4 rounded-lg border border-slate-200 mb-6">
                                <h3 class="text-sm font-bold text-slate-900 mb-3">Access Mode</h3>
                                <p class="text-xs text-slate-500 mb-4">Choose how Groundskeeper connects to GitHub</p>

                                <div class="space-y-4">
                                    <!-- Read-only Mode -->
                                    <label class="flex items-start gap-3 p-3 border border-slate-200 rounded-lg cursor-pointer hover:bg-white <?php echo (!isset($glob['user']) || $glob['user']['access_mode'] === 'readonly') ? 'bg-white ring-2 ring-emerald-500' : 'bg-slate-50'; ?>">
                                        <input type="radio" name="access_mode" value="readonly" class="mt-1 h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-slate-300" <?php echo (!isset($glob['user']) || $glob['user']['access_mode'] === 'readonly') ? 'checked' : ''; ?> onchange="toggleConnectionSection('readonly')">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="font-medium text-slate-900">Read-only</span>
                                                <span class="text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-medium">Recommended</span>
                                            </div>
                                            <p class="text-xs text-slate-600">View and analyze issues without GitHub org approval. Uses Personal Access Token or public API (60 requests/hour).</p>
                                        </div>
                                    </label>

                                    <!-- Read/Write Mode -->
                                    <label class="flex items-start gap-3 p-3 border border-slate-200 rounded-lg cursor-pointer hover:bg-white <?php echo (isset($glob['user']) && $glob['user']['access_mode'] === 'readwrite') ? 'bg-white ring-2 ring-emerald-500' : 'bg-slate-50'; ?>">
                                        <input type="radio" name="access_mode" value="readwrite" class="mt-1 h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-slate-300" <?php echo (isset($glob['user']) && $glob['user']['access_mode'] === 'readwrite') ? 'checked' : ''; ?> onchange="toggleConnectionSection('readwrite')">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="font-medium text-slate-900">Read/Write</span>
                                                <span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full font-medium">Requires OAuth</span>
                                            </div>
                                            <p class="text-xs text-slate-600">Full access to close issues, add labels, and post comments. Requires org admin approval for OAuth app.</p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Auth Section -->
                            <div class="bg-slate-50 p-4 rounded-lg border border-slate-200">
                                <h3 class="text-sm font-bold text-slate-900 mb-2">GitHub Connection</h3>

                                <?php if (isset($glob['user']) && $glob['user']): ?>
                                    <!-- Connected state -->
                                    <div class="flex items-center gap-3 mb-4">
                                        <?php if (!empty($glob['user']['avatar_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($glob['user']['avatar_url']); ?>" alt="Avatar" class="w-10 h-10 rounded-full">
                                        <?php endif; ?>
                                        <div>
                                            <?php if ($glob['user']['access_mode'] === 'readwrite'): ?>
                                                <p class="text-sm text-slate-600">Connected via OAuth as <strong>@<?php echo htmlspecialchars($glob['user']['github_username']); ?></strong></p>
                                                <p class="text-xs text-emerald-600 mt-1"><i class="fa-solid fa-check-circle"></i> Full read/write access</p>
                                            <?php else: ?>
                                                <p class="text-sm text-slate-600">Connected as <strong>@<?php echo htmlspecialchars($glob['user']['github_username']); ?></strong></p>
                                                <p class="text-xs text-slate-500 mt-1"><i class="fa-solid fa-eye"></i> Read-only access</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="flex gap-3">
                                        <?php if ($glob['user']['access_mode'] === 'readwrite'): ?>
                                            <a href="<?php echo BASEURL; ?>auth/github" class="text-sm text-slate-600 border border-slate-300 bg-white px-3 py-1.5 rounded hover:bg-slate-50">Re-authenticate</a>
                                        <?php else: ?>
                                            <button onclick="document.getElementById('pat-form').classList.toggle('hidden')" class="text-sm text-slate-600 border border-slate-300 bg-white px-3 py-1.5 rounded hover:bg-slate-50">Update Token</button>
                                        <?php endif; ?>
                                        <a href="<?php echo BASEURL; ?>settings/disconnect" class="text-sm text-red-600 border border-red-200 bg-white px-3 py-1.5 rounded hover:bg-red-50">Disconnect</a>
                                    </div>

                                    <!-- PAT Update Form (hidden by default) -->
                                    <?php if ($glob['user']['access_mode'] === 'readonly'): ?>
                                    <div id="pat-form" class="hidden mt-4 pt-4 border-t border-slate-200">
                                        <form method="POST" action="<?php echo BASEURL; ?>settings/update-token">
                                            <label class="block text-xs font-medium text-slate-700 mb-2">Personal Access Token</label>
                                            <input type="text" name="personal_access_token" class="block w-full px-3 py-2 text-sm border-slate-300 rounded-md bg-white border" placeholder="ghp_xxxxxxxxxxxx">
                                            <p class="text-xs text-slate-500 mt-2">Update your PAT to increase rate limits (5000 req/hr)</p>
                                            <button type="submit" class="mt-3 bg-emerald-600 text-white px-3 py-1.5 rounded text-sm font-medium hover:bg-emerald-700">Update Token</button>
                                        </form>
                                    </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <!-- Not connected state -->
                                    <p class="text-sm text-slate-600 mb-4">Connect your GitHub account to start analyzing issues.</p>

                                    <!-- Read-only connection form -->
                                    <div id="readonly-connection" class="<?php echo (!isset($glob['user']) || (isset($_POST['access_mode']) && $_POST['access_mode'] === 'readwrite')) ? 'hidden' : ''; ?>">
                                        <form method="POST" action="<?php echo BASEURL; ?>settings/connect-readonly" class="space-y-3">
                                            <div>
                                                <label class="block text-xs font-medium text-slate-700 mb-2">GitHub Username</label>
                                                <input type="text" name="github_username" required class="block w-full px-3 py-2 text-sm border-slate-300 rounded-md bg-white border" placeholder="your-github-username">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-slate-700 mb-2">Personal Access Token (optional)</label>
                                                <input type="text" name="personal_access_token" class="block w-full px-3 py-2 text-sm border-slate-300 rounded-md bg-white border" placeholder="ghp_xxxxxxxxxxxx">
                                                <p class="text-xs text-slate-500 mt-1">Without token: 60 requests/hour. With token: 5000 requests/hour. <a href="https://github.com/settings/tokens/new?scopes=public_repo&description=Groundskeeper" target="_blank" class="text-emerald-600 hover:underline">Create token</a></p>
                                            </div>
                                            <button type="submit" class="inline-flex items-center gap-2 bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-700">
                                                <i class="fa-solid fa-eye"></i>
                                                Connect (Read-only)
                                            </button>
                                        </form>
                                    </div>

                                    <!-- Read/Write OAuth connection -->
                                    <div id="readwrite-connection" class="hidden">
                                        <p class="text-xs text-slate-500 mb-3">Connect with GitHub OAuth for full read/write access:</p>
                                        <a href="<?php echo BASEURL; ?>auth/github" class="inline-flex items-center gap-2 bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-800">
                                            <i class="fa-brands fa-github"></i>
                                            Sign in with GitHub OAuth
                                        </a>
                                        <p class="text-xs text-slate-500 mt-3"><i class="fa-solid fa-info-circle"></i> Requires org admin approval for OAuth app</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if ($glob['selected_repo']['last_synced_at']): ?>
                            <!-- Labels Section -->
                            <div>
                                <h3 class="text-lg font-bold text-slate-900 mb-4 border-b border-slate-200 pb-2">Label Mapping</h3>

                                <form method="POST" action="<?php echo BASEURL; ?>settings/<?php echo $glob['selected_repo']['id']; ?>/update">
                                    <div class="grid gap-6">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-2">Bug Label</label>
                                            <p class="text-xs text-slate-500 mb-2">Which label indicates an issue is a bug?</p>
                                            <input type="text" name="bug_label" value="<?php echo htmlspecialchars($glob['selected_repo']['bug_label']); ?>" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-slate-300 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm rounded-md bg-white border" placeholder="bug">
                                            <p class="text-xs text-slate-500 mt-1">Examples: bug, type: bug, defect</p>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-2">Priority Labels</label>
                                            <p class="text-xs text-slate-500 mb-2">Enter labels used to denote priority levels, one per line.</p>

                                            <?php
                                            $priorityLabelsText = '';
                                            if (!empty($glob['selected_repo']['priority_labels'])) {
                                                $priorityLabels = json_decode($glob['selected_repo']['priority_labels'], true);
                                                if (is_array($priorityLabels)) {
                                                    $priorityLabelsText = implode("\n", $priorityLabels);
                                                }
                                            }
                                            ?>

                                            <textarea name="priority_labels_text" rows="4" class="block w-full px-3 py-2 text-sm border-slate-300 rounded-md bg-white border focus:outline-none focus:ring-emerald-500 focus:border-emerald-500" placeholder="priority: high&#10;priority: medium&#10;priority: low"><?php echo htmlspecialchars($priorityLabelsText); ?></textarea>
                                            <p class="text-xs text-slate-500 mt-1">Leave blank if this repository doesn't use priority labels.</p>
                                        </div>

                                        <!-- Areas Section -->
                                        <div class="border-t border-slate-200 pt-4">
                                            <label class="block text-sm font-medium text-slate-700 mb-2">Functional Areas</label>
                                            <p class="text-xs text-slate-500 mb-3">Areas are auto-detected on first analysis using AI. They help categorize issues by codebase section.</p>

                                            <?php if (!empty($glob['areas'])): ?>
                                                <div class="bg-slate-50 rounded-md p-3 mb-3">
                                                    <ul class="space-y-1 text-sm text-slate-700">
                                                        <?php foreach ($glob['areas'] as $area): ?>
                                                            <li class="flex items-center">
                                                                <i class="fa-solid fa-circle text-xs text-emerald-500 mr-2"></i>
                                                                <?php echo htmlspecialchars($area['name']); ?>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                </div>
                                                <form method="POST" action="<?php echo BASEURL; ?>settings/<?php echo $glob['selected_repo']['id']; ?>/reset-areas" class="inline">
                                                    <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium" onclick="return confirm('Are you sure you want to reset areas? This will clear all area categorizations and re-discover areas on next analysis.')">
                                                        <i class="fa-solid fa-rotate-left mr-1"></i> Reset Areas
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <div class="bg-slate-50 rounded-md p-3 text-sm text-slate-600">
                                                    <i class="fa-solid fa-info-circle mr-1 text-slate-400"></i>
                                                    No areas detected yet. Run analysis to discover areas automatically.
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="pt-4 flex justify-end">
                                        <button type="submit" class="bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-sm hover:bg-emerald-700">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    <!-- MODAL: Add Repo -->
    <div id="modal-add-repo" class="hidden relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity"></div>

        <!-- Fixed wrapper for centering -->
        <div class="fixed inset-0 z-10 overflow-y-auto" onclick="closeModal('add-repo')">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">

                <!-- Modal Panel -->
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg" onclick="event.stopPropagation()">
                    
                    <!-- Modal Header -->
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-slate-100 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-slate-900" id="modal-title">Connect New Repository</h3>
                            <p class="text-sm text-slate-500 mt-1">Groundskeeper will scan this repo for issues.</p>
                        </div>
                        <button onclick="closeModal('add-repo')" class="text-slate-400 hover:text-slate-500">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>

                    <!-- Content -->
                    <form method="POST" action="<?php echo BASEURL; ?>settings/add-repo">
                        <div class="p-6 space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Repository Slug</label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fa-brands fa-github text-slate-400"></i>
                                    </div>
                                    <input type="text" name="repo_slug" required class="focus:ring-emerald-500 focus:border-emerald-500 block w-full pl-10 sm:text-sm border-slate-300 rounded-md border py-2" placeholder="owner/repo-name">
                                </div>
                                <p class="mt-2 text-xs text-slate-500">Format: owner/repo-name (e.g., woocommerce/woocommerce)</p>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-slate-200">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-emerald-600 text-base font-medium text-white hover:bg-emerald-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                Load Repo
                            </button>
                            <button type="button" onclick="closeModal('add-repo')" class="mt-3 w-full inline-flex justify-center rounded-md border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: Bulk Action View -->
    <div id="modal-duplicates" class="hidden relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity"></div>

        <!-- Fixed wrapper for centering -->
        <div class="fixed inset-0 z-10 overflow-y-auto" onclick="closeModal('duplicates')">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">

                <!-- Modal Panel -->
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-6xl" onclick="event.stopPropagation()">
                    
                    <!-- Modal Header -->
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-slate-100 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-slate-900" id="modal-title">Review Likely Duplicates</h3>
                            <p class="text-sm text-slate-500 mt-1">Found <?php echo count($glob['duplicates']); ?> groups of issues that appear semantically similar (≥85% similarity).</p>
                        </div>
                        <button onclick="closeModal('duplicates')" class="text-slate-400 hover:text-slate-500">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>

                    <!-- Bulk Actions Toolbar -->
                    <div class="bg-slate-50 px-4 py-3 flex items-center justify-between border-b border-slate-200">
                        <div class="flex items-center gap-4">
                            <label class="inline-flex items-center">
                                <input type="checkbox" id="select-all-duplicates" onchange="toggleSelectAll('duplicates')" class="form-checkbox h-4 w-4 text-emerald-600 rounded border-slate-300">
                                <span class="ml-2 text-sm text-slate-600 font-medium">Select All</span>
                            </label>
                            <span class="h-4 w-px bg-slate-300 mx-2"></span>
                            <span class="text-sm text-slate-500" id="selected-count-duplicates">0 selected</span>
                        </div>
                        <div class="flex gap-2">
                            <?php if (!isset($glob['user']) || $glob['user']['access_mode'] !== 'readwrite'): ?>
                            <div class="tooltip-wrapper">
                                <button disabled class="bg-white border border-slate-300 text-slate-600 px-3 py-1.5 rounded text-sm font-medium shadow-sm opacity-50 cursor-not-allowed">
                                    Dismiss
                                </button>
                                <span class="tooltip-text">Disabled in read-only mode</span>
                            </div>
                            <div class="tooltip-wrapper">
                                <button disabled class="bg-emerald-600 text-white px-3 py-1.5 rounded text-sm font-medium shadow-sm opacity-50 cursor-not-allowed">
                                    Merge & Close Selected
                                </button>
                                <span class="tooltip-text">Disabled in read-only mode</span>
                            </div>
                            <?php else: ?>
                            <button class="bg-white border border-slate-300 text-slate-600 hover:text-red-600 hover:border-red-300 px-3 py-1.5 rounded text-sm font-medium shadow-sm transition">
                                Dismiss
                            </button>
                            <button class="bg-emerald-600 text-white hover:bg-emerald-700 px-3 py-1.5 rounded text-sm font-medium shadow-sm transition">
                                Merge & Close Selected
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- List Content -->
                    <div class="max-h-[60vh] overflow-y-auto">
                        <?php if (!empty($glob['duplicates'])): ?>
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50 sticky top-0">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider w-10"></th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Primary Issue</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Similar Issues</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Similarity</th>
                                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                <?php foreach ($glob['duplicates'] as $group):
                                    $primary = $group['primary'];
                                    $duplicates = $group['duplicates'];
                                    $primaryTime = time() - $primary['created_at'];
                                    $primaryTimeText = $primaryTime < 86400 ? floor($primaryTime/3600) . ' hours ago' : floor($primaryTime/86400) . ' days ago';
                                ?>
                                <?php foreach ($duplicates as $duplicate):
                                    $dupTime = time() - $duplicate['created_at'];
                                    $dupTimeText = $dupTime < 86400 ? floor($dupTime/3600) . ' hours ago' : floor($dupTime/86400) . ' days ago';
                                    $similarityPercent = round($duplicate['similarity'] * 100);
                                    $badgeClass = $similarityPercent >= 90 ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
                                ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" class="form-checkbox h-4 w-4 text-emerald-600 rounded border-slate-300">
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-slate-900"><?php echo htmlspecialchars($primary['title']); ?></div>
                                        <div class="text-xs text-slate-500">
                                            <a href="<?php echo htmlspecialchars($primary['url']); ?>" target="_blank" class="hover:text-emerald-600 hover:underline">#<?php echo $primary['number']; ?> <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>
                                            • opened <?php echo $primaryTimeText; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-slate-900 flex items-center gap-2">
                                            <i class="fa-solid fa-arrow-right-long text-slate-400"></i>
                                            <div>
                                                <a href="<?php echo htmlspecialchars($duplicate['url']); ?>" target="_blank" class="hover:underline text-emerald-700">#<?php echo $duplicate['number']; ?>: <?php echo htmlspecialchars(substr($duplicate['title'], 0, 50)); ?><?php echo strlen($duplicate['title']) > 50 ? '...' : ''; ?> <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>
                                                <div class="text-xs text-slate-400">opened <?php echo $dupTimeText; ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $badgeClass; ?>"><?php echo $similarityPercent; ?>% Match</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="<?php echo htmlspecialchars($duplicate['url']); ?>" target="_blank" class="text-emerald-600 hover:text-emerald-900 font-medium">View</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <div class="p-12 text-center text-slate-500">
                            <i class="fa-solid fa-inbox text-4xl mb-4 text-slate-300"></i>
                            <p class="text-sm">No duplicate issues found. Run analysis to detect similar issues.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Footer Removed -->
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: High Signal Issues -->
    <div id="modal-high-signal" class="hidden relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity"></div>

        <!-- Fixed wrapper for centering -->
        <div class="fixed inset-0 z-10 overflow-y-auto" onclick="closeModal('high-signal')">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">

                <!-- Modal Panel -->
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-6xl" onclick="event.stopPropagation()">
                    
                    <!-- Modal Header -->
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-slate-100 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-slate-900" id="modal-title">High Signal Issues Queue</h3>
                            <p class="text-sm text-slate-500 mt-1"><?php echo count($glob['high_signal_issues']); ?> valuable, actionable issues identified by AI analysis.</p>
                        </div>
                        <button onclick="closeModal('high-signal')" class="text-slate-400 hover:text-slate-500">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>

                    <!-- Bulk Actions Toolbar -->
                    <div class="bg-slate-50 px-4 py-3 flex items-center justify-between border-b border-slate-200">
                        <div class="flex items-center gap-4">
                            <label class="inline-flex items-center">
                                <input type="checkbox" id="select-all-high-signal" onchange="toggleSelectAll('high-signal')" class="form-checkbox h-4 w-4 text-emerald-600 rounded border-slate-300">
                                <span class="ml-2 text-sm text-slate-600 font-medium">Select All</span>
                            </label>
                            <span class="h-4 w-px bg-slate-300 mx-2"></span>
                            <span class="text-sm text-slate-500" id="selected-count-high-signal">0 selected</span>
                        </div>
                        <div class="flex gap-2">
                             <button onclick="copySelectedIssueUrls('high-signal')" class="bg-emerald-600 border border-emerald-600 text-white hover:bg-emerald-700 hover:border-emerald-700 px-3 py-1.5 rounded text-sm font-medium shadow-sm transition">
                                <i class="fa-solid fa-copy mr-1.5"></i>
                                Copy Issue URLs
                            </button>
                        </div>
                    </div>

                    <!-- List Content -->
                    <div class="max-h-[60vh] overflow-y-auto">
                        <?php if (!empty($glob['high_signal_issues'])): ?>
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50 sticky top-0">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider w-10"></th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Priority</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Issue</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Engagement</th>
                                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                <?php foreach ($glob['high_signal_issues'] as $issue):
                                    $labels = is_array($issue['labels']) ? $issue['labels'] : json_decode($issue['labels'], true);
                                    if (!is_array($labels)) $labels = [];
                                    $timeAgo = time() - $issue['created_at'];
                                    $timeText = $timeAgo < 3600 ? floor($timeAgo/60) . ' minutes ago' :
                                               ($timeAgo < 86400 ? floor($timeAgo/3600) . ' hours ago' :
                                               floor($timeAgo/86400) . ' days ago');

                                    // Priority score visualization
                                    $priorityScore = $issue['priority_score'] ?? 0;
                                    $priorityClass = '';
                                    $priorityBg = '';
                                    $priorityText = '';
                                    $priorityIcon = '';

                                    if ($priorityScore >= 80) {
                                        $priorityClass = 'bg-red-100 text-red-800 border-red-200';
                                        $priorityBg = 'bg-red-500';
                                        $priorityText = 'Critical';
                                        $priorityIcon = 'fa-fire';
                                    } elseif ($priorityScore >= 60) {
                                        $priorityClass = 'bg-orange-100 text-orange-800 border-orange-200';
                                        $priorityBg = 'bg-orange-500';
                                        $priorityText = 'High';
                                        $priorityIcon = 'fa-chevron-up';
                                    } elseif ($priorityScore >= 40) {
                                        $priorityClass = 'bg-yellow-100 text-yellow-800 border-yellow-200';
                                        $priorityBg = 'bg-yellow-500';
                                        $priorityText = 'Medium';
                                        $priorityIcon = 'fa-minus';
                                    } else {
                                        $priorityClass = 'bg-blue-100 text-blue-800 border-blue-200';
                                        $priorityBg = 'bg-blue-500';
                                        $priorityText = 'Standard';
                                        $priorityIcon = 'fa-info-circle';
                                    }

                                    $engagement = ($issue['reactions_total'] ?? 0) + ($issue['comments_count'] ?? 0);
                                ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" class="form-checkbox h-4 w-4 text-emerald-600 rounded border-slate-300">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold border <?php echo $priorityClass; ?>">
                                                <i class="fa-solid <?php echo $priorityIcon; ?>"></i>
                                                <?php echo $priorityText; ?>
                                            </span>
                                            <span class="text-xs font-medium text-slate-500"><?php echo $priorityScore; ?></span>
                                        </div>
                                        <div class="mt-1.5 w-20 bg-slate-200 rounded-full h-1.5">
                                            <div class="<?php echo $priorityBg; ?> h-1.5 rounded-full" style="width: <?php echo $priorityScore; ?>%"></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-slate-900"><?php echo htmlspecialchars($issue['title']); ?></div>
                                        <div class="text-xs text-slate-500">
                                            <a href="<?php echo htmlspecialchars($issue['url']); ?>" target="_blank" class="hover:text-emerald-600 hover:underline">#<?php echo $issue['issue_number']; ?> <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>
                                            • opened <?php echo $timeText; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3 text-sm text-slate-600">
                                            <span class="flex items-center gap-1" title="Reactions">
                                                <i class="fa-solid fa-heart text-slate-400 text-xs"></i>
                                                <?php echo $issue['reactions_total'] ?? 0; ?>
                                            </span>
                                            <span class="flex items-center gap-1" title="Comments">
                                                <i class="fa-solid fa-comment text-slate-400 text-xs"></i>
                                                <?php echo $issue['comments_count'] ?? 0; ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="<?php echo htmlspecialchars($issue['url']); ?>" target="_blank" class="text-emerald-600 hover:text-emerald-900 font-medium">View</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <div class="p-12 text-center text-slate-500">
                            <i class="fa-solid fa-inbox text-4xl mb-4 text-slate-300"></i>
                            <p class="text-sm">No high signal issues found. Run analysis to identify valuable issues.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: Cleanup Candidates -->
    <div id="modal-cleanup" class="hidden relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity"></div>

        <!-- Fixed wrapper for centering -->
        <div class="fixed inset-0 z-10 overflow-y-auto" onclick="closeModal('cleanup')">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">

                <!-- Modal Panel -->
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-6xl" onclick="event.stopPropagation()">
                    
                    <!-- Modal Header -->
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-slate-100 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-slate-900" id="modal-title">Review Cleanup Candidates</h3>
                            <p class="text-sm text-slate-500 mt-1">Found <?php echo count($glob['cleanup_candidates']); ?> issues identified as candidates for closure by AI analysis.</p>
                        </div>
                        <button onclick="closeModal('cleanup')" class="text-slate-400 hover:text-slate-500">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>

                    <!-- Bulk Actions Toolbar -->
                    <div class="bg-slate-50 px-4 py-3 flex items-center justify-between border-b border-slate-200">
                        <div class="flex items-center gap-4">
                            <label class="inline-flex items-center">
                                <input type="checkbox" id="select-all-cleanup" onchange="toggleSelectAll('cleanup')" class="form-checkbox h-4 w-4 text-emerald-600 rounded border-slate-300">
                                <span class="ml-2 text-sm text-slate-600 font-medium">Select All</span>
                            </label>
                            <span class="h-4 w-px bg-slate-300 mx-2"></span>
                            <span class="text-sm text-slate-500" id="selected-count-cleanup">0 selected</span>
                        </div>
                        <div class="flex gap-2">
                            <?php if (!isset($glob['user']) || $glob['user']['access_mode'] !== 'readwrite'): ?>
                            <div class="tooltip-wrapper">
                                <button disabled class="bg-white border border-slate-300 text-slate-600 px-3 py-1.5 rounded text-sm font-medium shadow-sm opacity-50 cursor-not-allowed">
                                    Ignore
                                </button>
                                <span class="tooltip-text">Disabled in read-only mode</span>
                            </div>
                            <div class="tooltip-wrapper">
                                <button disabled class="bg-emerald-600 text-white px-3 py-1.5 rounded text-sm font-medium shadow-sm opacity-50 cursor-not-allowed">
                                    Close Selected
                                </button>
                                <span class="tooltip-text">Disabled in read-only mode</span>
                            </div>
                            <?php else: ?>
                            <button class="bg-white border border-slate-300 text-slate-600 hover:text-emerald-600 hover:border-emerald-300 px-3 py-1.5 rounded text-sm font-medium shadow-sm transition">
                                Ignore
                            </button>
                            <button class="bg-emerald-600 text-white hover:bg-emerald-700 px-3 py-1.5 rounded text-sm font-medium shadow-sm transition">
                                Close Selected
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- List Content -->
                    <div class="max-h-[60vh] overflow-y-auto">
                        <?php if (!empty($glob['cleanup_candidates'])): ?>
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50 sticky top-0">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider w-10"></th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Issue</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Labels</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Last Activity</th>
                                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                <?php foreach ($glob['cleanup_candidates'] as $issue):
                                    $labels = is_array($issue['labels']) ? $issue['labels'] : json_decode($issue['labels'], true);
                                    if (!is_array($labels)) $labels = [];
                                    $timeAgo = time() - $issue['created_at'];
                                    $openedText = $timeAgo < 86400 ? floor($timeAgo/3600) . ' hours ago' :
                                                 ($timeAgo < 2592000 ? floor($timeAgo/86400) . ' days ago' :
                                                 floor($timeAgo/2592000) . ' months ago');
                                    $lastActivity = $issue['last_activity_at'] ?? $issue['updated_at'];
                                    $activityAgo = time() - $lastActivity;
                                    $activityText = $activityAgo < 86400 ? floor($activityAgo/3600) . ' hours ago' :
                                                   ($activityAgo < 2592000 ? floor($activityAgo/86400) . ' days ago' :
                                                   floor($activityAgo/2592000) . ' months ago');
                                ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" class="form-checkbox h-4 w-4 text-emerald-600 rounded border-slate-300">
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-slate-900"><?php echo htmlspecialchars($issue['title']); ?></div>
                                        <div class="text-xs text-slate-500">
                                            <a href="<?php echo htmlspecialchars($issue['url']); ?>" target="_blank" class="hover:text-emerald-600 hover:underline">#<?php echo $issue['issue_number']; ?> <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>
                                            • opened <?php echo $openedText; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            <?php foreach (array_slice($labels, 0, 2) as $label): ?>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600"><?php echo htmlspecialchars($label); ?></span>
                                            <?php endforeach; ?>
                                            <?php if (count($labels) > 2): ?>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium text-slate-500">+<?php echo count($labels) - 2; ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                        <?php echo $activityText; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="<?php echo htmlspecialchars($issue['url']); ?>" target="_blank" class="text-emerald-600 hover:text-emerald-900 font-medium">View</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <div class="p-12 text-center text-slate-500">
                            <i class="fa-solid fa-inbox text-4xl mb-4 text-slate-300"></i>
                            <p class="text-sm">No cleanup candidates found. Run analysis to identify issues for closure.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: Missing Info -->
    <div id="modal-missing-info" class="hidden relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity"></div>

        <!-- Fixed wrapper for centering -->
        <div class="fixed inset-0 z-10 overflow-y-auto" onclick="closeModal('missing-info')">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">

                <!-- Modal Panel -->
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-6xl" onclick="event.stopPropagation()">
                    
                    <!-- Modal Header -->
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-slate-100 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-slate-900" id="modal-title">Review Issues Missing Context</h3>
                            <p class="text-sm text-slate-500 mt-1">Found <?php echo count($glob['missing_info_issues']); ?> issues missing critical information identified by AI analysis.</p>
                        </div>
                        <button onclick="closeModal('missing-info')" class="text-slate-400 hover:text-slate-500">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>

                    <!-- Bulk Actions Toolbar -->
                    <div class="bg-slate-50 px-4 py-3 flex items-center justify-between border-b border-slate-200">
                        <div class="flex items-center gap-4">
                            <label class="inline-flex items-center">
                                <input type="checkbox" id="select-all-missing-info" onchange="toggleSelectAll('missing-info')" class="form-checkbox h-4 w-4 text-emerald-600 rounded border-slate-300">
                                <span class="ml-2 text-sm text-slate-600 font-medium">Select All</span>
                            </label>
                            <span class="h-4 w-px bg-slate-300 mx-2"></span>
                            <span class="text-sm text-slate-500" id="selected-count-missing-info">0 selected</span>
                        </div>
                        <div class="flex gap-2">
                            <?php if (!isset($glob['user']) || $glob['user']['access_mode'] !== 'readwrite'): ?>
                            <div class="tooltip-wrapper">
                                <button disabled class="bg-white border border-slate-300 text-slate-600 px-3 py-1.5 rounded text-sm font-medium shadow-sm opacity-50 cursor-not-allowed">
                                    Request Info (AI Draft)
                                </button>
                                <span class="tooltip-text">Disabled in read-only mode</span>
                            </div>
                            <?php else: ?>
                            <button class="bg-white border border-slate-300 text-slate-600 hover:text-emerald-600 hover:border-emerald-300 px-3 py-1.5 rounded text-sm font-medium shadow-sm transition">
                                Request Info (AI Draft)
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- List Content -->
                    <div class="max-h-[60vh] overflow-y-auto">
                        <?php if (!empty($glob['missing_info_issues'])): ?>
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50 sticky top-0">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider w-10"></th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Issue</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Missing Elements</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Labels</th>
                                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                <?php foreach ($glob['missing_info_issues'] as $issue):
                                    $labels = is_array($issue['labels']) ? $issue['labels'] : json_decode($issue['labels'], true);
                                    if (!is_array($labels)) $labels = [];
                                    $missingElements = is_array($issue['missing_elements']) ? $issue['missing_elements'] : json_decode($issue['missing_elements'], true);
                                    if (!is_array($missingElements)) $missingElements = [];
                                    $timeAgo = time() - $issue['created_at'];
                                    $openedText = $timeAgo < 86400 ? floor($timeAgo/3600) . ' hours ago' :
                                                 ($timeAgo < 2592000 ? floor($timeAgo/86400) . ' days ago' :
                                                 floor($timeAgo/2592000) . ' months ago');
                                ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" class="form-checkbox h-4 w-4 text-emerald-600 rounded border-slate-300">
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-slate-900"><?php echo htmlspecialchars($issue['title']); ?></div>
                                        <div class="text-xs text-slate-500">
                                            <a href="<?php echo htmlspecialchars($issue['url']); ?>" target="_blank" class="hover:text-emerald-600 hover:underline">#<?php echo $issue['issue_number']; ?> <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>
                                            • opened <?php echo $openedText; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            <?php if (!empty($missingElements)): ?>
                                                <?php foreach (array_slice($missingElements, 0, 3) as $element): ?>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-50 text-red-700"><?php echo htmlspecialchars($element); ?></span>
                                                <?php endforeach; ?>
                                                <?php if (count($missingElements) > 3): ?>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium text-slate-500">+<?php echo count($missingElements) - 3; ?></span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-xs text-slate-400">No specific elements identified</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            <?php foreach (array_slice($labels, 0, 2) as $label): ?>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600"><?php echo htmlspecialchars($label); ?></span>
                                            <?php endforeach; ?>
                                            <?php if (count($labels) > 2): ?>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium text-slate-500">+<?php echo count($labels) - 2; ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="<?php echo htmlspecialchars($issue['url']); ?>" target="_blank" class="text-emerald-600 hover:text-emerald-900 font-medium">View</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <div class="p-12 text-center text-slate-500">
                            <i class="fa-solid fa-inbox text-4xl mb-4 text-slate-300"></i>
                            <p class="text-sm">No issues missing critical information. Run analysis to identify incomplete issues.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: Suggestions -->
    <div id="modal-suggestions" class="hidden relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity"></div>

        <!-- Fixed wrapper for centering -->
        <div class="fixed inset-0 z-10 overflow-y-auto" onclick="closeModal('suggestions')">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">

                <!-- Modal Panel -->
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-6xl" onclick="event.stopPropagation()">
                    
                    <!-- Modal Header -->
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-slate-100 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-slate-900" id="modal-title">Review Label Suggestions</h3>
                            <p class="text-sm text-slate-500 mt-1">Found <?php echo count($glob['label_suggestions']); ?> issues with AI-recommended labels from the repository.</p>
                        </div>
                        <button onclick="closeModal('suggestions')" class="text-slate-400 hover:text-slate-500">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>

                    <!-- Bulk Actions Toolbar -->
                    <div class="bg-slate-50 px-4 py-3 flex items-center justify-between border-b border-slate-200">
                        <div class="flex items-center gap-4">
                            <label class="inline-flex items-center">
                                <input type="checkbox" id="select-all-suggestions" onchange="toggleSelectAll('suggestions')" class="form-checkbox h-4 w-4 text-emerald-600 rounded border-slate-300">
                                <span class="ml-2 text-sm text-slate-600 font-medium">Select All</span>
                            </label>
                            <span class="h-4 w-px bg-slate-300 mx-2"></span>
                            <span class="text-sm text-slate-500" id="selected-count-suggestions">0 selected</span>
                        </div>
                        <div class="flex gap-2">
                            <?php if (!isset($glob['user']) || $glob['user']['access_mode'] !== 'readwrite'): ?>
                            <div class="tooltip-wrapper">
                                <button disabled class="bg-white border border-slate-300 text-slate-600 px-3 py-1.5 rounded text-sm font-medium shadow-sm opacity-50 cursor-not-allowed">
                                    Dismiss
                                </button>
                                <span class="tooltip-text">Disabled in read-only mode</span>
                            </div>
                            <div class="tooltip-wrapper">
                                <button disabled class="bg-emerald-600 text-white px-3 py-1.5 rounded text-sm font-medium shadow-sm opacity-50 cursor-not-allowed">
                                    Apply Selected
                                </button>
                                <span class="tooltip-text">Disabled in read-only mode</span>
                            </div>
                            <?php else: ?>
                            <button class="bg-white border border-slate-300 text-slate-600 hover:text-red-600 hover:border-red-300 px-3 py-1.5 rounded text-sm font-medium shadow-sm transition">
                                Dismiss
                            </button>
                            <button class="bg-emerald-600 text-white hover:bg-emerald-700 px-3 py-1.5 rounded text-sm font-medium shadow-sm transition">
                                Apply Selected
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- List Content -->
                    <div class="max-h-[60vh] overflow-y-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50 sticky top-0">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider w-10"></th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Issue</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Suggested Changes</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Reasoning</th>
                                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                <?php if (!empty($glob['label_suggestions'])): ?>
                                    <?php foreach ($glob['label_suggestions'] as $issue):
                                        $currentLabels = is_array($issue['labels']) ? $issue['labels'] : json_decode($issue['labels'], true);
                                        if (!is_array($currentLabels)) $currentLabels = [];

                                        $suggestedLabels = is_array($issue['suggested_labels']) ? $issue['suggested_labels'] : json_decode($issue['suggested_labels'], true);
                                        if (!is_array($suggestedLabels)) $suggestedLabels = [];

                                        $reasoning = $issue['label_reasoning'] ?? 'Recommended based on issue content';

                                        $timeAgo = time() - $issue['created_at'];
                                        if ($timeAgo < 3600) {
                                            $timeText = floor($timeAgo / 60) . ' minutes ago';
                                        } elseif ($timeAgo < 86400) {
                                            $timeText = floor($timeAgo / 3600) . ' hours ago';
                                        } elseif ($timeAgo < 2592000) {
                                            $timeText = floor($timeAgo / 86400) . ' days ago';
                                        } else {
                                            $timeText = floor($timeAgo / 2592000) . ' months ago';
                                        }
                                    ?>
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="checkbox" class="form-checkbox h-4 w-4 text-emerald-600 rounded border-slate-300">
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-slate-900"><?php echo htmlspecialchars($issue['title']); ?></div>
                                            <div class="text-xs text-slate-500">
                                                <a href="<?php echo htmlspecialchars($issue['url']); ?>" target="_blank" class="hover:text-emerald-600 hover:underline">#<?php echo $issue['issue_number']; ?> <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>
                                                • opened <?php echo $timeText; ?>
                                            </div>
                                            <?php if (!empty($currentLabels)): ?>
                                                <div class="mt-2 flex flex-wrap gap-1">
                                                    <span class="text-xs text-slate-400">Current:</span>
                                                    <?php foreach (array_slice($currentLabels, 0, 2) as $label): ?>
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600"><?php echo htmlspecialchars($label); ?></span>
                                                    <?php endforeach; ?>
                                                    <?php if (count($currentLabels) > 2): ?>
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium text-slate-400">+<?php echo count($currentLabels) - 2; ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="space-y-1">
                                                <?php foreach ($suggestedLabels as $label): ?>
                                                    <div class="flex items-center gap-2 text-xs">
                                                        <span class="text-slate-400">Add:</span>
                                                        <span class="bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded border border-emerald-200"><?php echo htmlspecialchars($label); ?></span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-500">
                                            <?php echo htmlspecialchars(substr($reasoning, 0, 50)) . (strlen($reasoning) > 50 ? '...' : ''); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="<?php echo htmlspecialchars($issue['url']); ?>" target="_blank" class="text-emerald-600 hover:text-emerald-800 font-medium">View</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-sm text-slate-500">
                                            No label suggestions found.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
	<script type="text/javascript" src="<?php echo BASEURL; ?>js/groundskeeper-core.js"></script>
    <script>
        function switchTab(tabName) {
            // Hide all views
            ['dashboard', 'settings'].forEach(view => {
                document.getElementById(`view-${view}`).classList.add('hidden');
            });
            
            // Show selected view
            document.getElementById(`view-${tabName}`).classList.remove('hidden');

            // Reset tabs
            const tabDash = document.getElementById('tab-dashboard');
            const tabSet = document.getElementById('tab-settings');

            if(tabName === 'dashboard') {
                tabDash.classList.add('border-emerald-500', 'text-slate-900');
                tabDash.classList.remove('border-transparent', 'text-slate-500');
                
                tabSet.classList.remove('border-emerald-500', 'text-slate-900');
                tabSet.classList.add('border-transparent', 'text-slate-500');
            } else {
                tabSet.classList.add('border-emerald-500', 'text-slate-900');
                tabSet.classList.remove('border-transparent', 'text-slate-500');

                tabDash.classList.remove('border-emerald-500', 'text-slate-900');
                tabDash.classList.add('border-transparent', 'text-slate-500');
            }
        }

        function openModal(modalId) {
            document.getElementById(`modal-${modalId}`).classList.remove('hidden');
        }

        function closeModal(modalId) {
            document.getElementById(`modal-${modalId}`).classList.add('hidden');
        }

        function toggleSelectAll(modalId) {
            const selectAllCheckbox = document.getElementById(`select-all-${modalId}`);
            const modal = document.getElementById(`modal-${modalId}`);
            const checkboxes = modal.querySelectorAll('tbody input[type="checkbox"]');
            const selectedCountEl = document.getElementById(`selected-count-${modalId}`);

            // Toggle all checkboxes based on the "Select All" state
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });

            // Update count
            const count = selectAllCheckbox.checked ? checkboxes.length : 0;
            selectedCountEl.textContent = `${count} selected`;
        }

        function copySelectedIssueUrls(modalId) {
            // Get all checked checkboxes in this modal
            const modal = document.getElementById(`modal-${modalId}`);
            const checkboxes = modal.querySelectorAll('tbody input[type="checkbox"]:checked');

            if (checkboxes.length === 0) {
                showToast('Please select at least one issue to copy', true);
                return;
            }

            // Extract URLs from the table rows
            const urls = [];
            checkboxes.forEach(checkbox => {
                const row = checkbox.closest('tr');
                const link = row.querySelector('a[href*="github.com"]');
                if (link) {
                    urls.push(link.href);
                }
            });

            if (urls.length === 0) {
                showToast('No URLs found for selected issues', true);
                return;
            }

            // Copy to clipboard
            const urlText = urls.join('\n');
            navigator.clipboard.writeText(urlText).then(() => {
                showToast(`Copied ${urls.length} issue URL${urls.length > 1 ? 's' : ''} to clipboard`);
            }).catch(err => {
                console.error('Failed to copy:', err);
                showToast('Failed to copy to clipboard', true);
            });
        }

        function toggleAreas() {
            const hiddenRows = document.querySelectorAll('.area-hidden');
            const btn = document.getElementById('btn-show-areas');
            const isHidden = hiddenRows[0].classList.contains('hidden');

            hiddenRows.forEach(row => {
                if(isHidden) {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });

            btn.textContent = isHidden ? 'Show less' : 'Show all';
        }

        // Store original stats from actual data
        const originalStats = {
            total: <?php echo count($glob['issues']); ?>,
            highSignal: 31,
            duplicates: 47,
            cleanup: 83,
            missing: 656,
            suggestions: 544
        };

        function filterDashboard(areaName, count) {
            // Update Header
            const header = document.getElementById('analysis-header');
            header.innerHTML = `
                <button onclick="resetDashboard()" class="text-slate-400 hover:text-emerald-600 transition mr-2">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <h3 class="text-lg font-bold text-slate-900">Analysis Findings</h3>
                <span class="text-slate-300 mx-2">/</span>
                <h3 class="text-lg font-bold text-emerald-700">${areaName}</h3>
            `;

            // Update Stats (Mock Logic: simple proportions for the demo)
            document.getElementById('stat-total').innerText = count;
            document.getElementById('stat-high-signal').innerText = Math.ceil(count * 0.05);
            document.getElementById('stat-duplicates').innerText = Math.ceil(count * 0.08);
            document.getElementById('stat-cleanup').innerText = Math.ceil(count * 0.12);
            document.getElementById('stat-missing-info').innerText = Math.ceil(count * 0.65);
            document.getElementById('stat-suggestions').innerText = Math.ceil(count * 0.55);
        }

        function resetDashboard() {
            // Restore Header
            const header = document.getElementById('analysis-header');
            header.innerHTML = `<h3 class="text-lg font-bold text-slate-900">Analysis Findings</h3>`;

            // Restore Stats
            document.getElementById('stat-total').innerText = originalStats.total;
            document.getElementById('stat-high-signal').innerText = originalStats.highSignal;
            document.getElementById('stat-duplicates').innerText = originalStats.duplicates;
            document.getElementById('stat-cleanup').innerText = originalStats.cleanup;
            document.getElementById('stat-missing-info').innerText = originalStats.missing;
            document.getElementById('stat-suggestions').innerText = originalStats.suggestions;
        }

        // Toggle connection section based on access mode
        function toggleConnectionSection(mode) {
            const readonlySection = document.getElementById('readonly-connection');
            const readwriteSection = document.getElementById('readwrite-connection');

            if (mode === 'readonly') {
                if (readonlySection) readonlySection.classList.remove('hidden');
                if (readwriteSection) readwriteSection.classList.add('hidden');
            } else if (mode === 'readwrite') {
                if (readonlySection) readonlySection.classList.add('hidden');
                if (readwriteSection) readwriteSection.classList.remove('hidden');
            }
        }

        // Set initial state on page load
        document.addEventListener('DOMContentLoaded', function() {
            const selectedMode = document.querySelector('input[name="access_mode"]:checked');
            if (selectedMode) {
                toggleConnectionSection(selectedMode.value);
            }

            // Check for session messages and display toast
            <?php if (isset($_SESSION['message'])): ?>
                showToast('<?php echo addslashes($_SESSION['message']); ?>');
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                showToast('<?php echo addslashes($_SESSION['success']); ?>');
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                showToast('<?php echo addslashes($_SESSION['error']); ?>', true);
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
        });

        // Toast notification function
        function showToast(message, isError = false) {
            const toast = document.getElementById('toast');
            toast.textContent = message;

            if (isError) {
                toast.classList.add('error');
            } else {
                toast.classList.remove('error');
            }

            toast.classList.add('show');

            setTimeout(() => {
                toast.classList.remove('show');
            }, 5000);
        }

        // Show loading overlay for audit
        function showAuditLoading() {
            const overlay = document.getElementById('audit-loading');
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
        }

        // Show loading overlay for analyze
        function showAnalyzeLoading() {
            const overlay = document.getElementById('analyze-loading');
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
        }
    </script>

    <!-- Toast Notification -->
    <div id="toast" class="alert"></div>

    <!-- Audit Loading Overlay -->
    <div id="audit-loading" class="hidden fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 shadow-xl text-center">
            <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-emerald-600 mx-auto mb-4"></div>
            <h3 class="text-lg font-semibold text-slate-900 mb-2">Running Audit...</h3>
            <p class="text-sm text-slate-600">Importing issues from GitHub</p>
        </div>
    </div>

    <!-- Analyze Loading Overlay -->
    <div id="analyze-loading" class="hidden fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 shadow-xl text-center max-w-md">
            <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-blue-600 mx-auto mb-4"></div>
            <h3 class="text-lg font-semibold text-slate-900 mb-2">Analyzing Issues...</h3>
            <p class="text-sm text-slate-600 mb-2">Processing bug reports with AI</p>
            <p class="text-xs text-slate-500">This can take 5-15 minutes for large repos. Time to grab a coffee! ☕</p>
        </div>
    </div>

    <!-- Area Approval Modal -->
    <?php if (isset($glob['pending_areas']) && !empty($glob['pending_areas'])): ?>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 shadow-xl max-w-2xl w-full mx-4">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">
                <i class="fa-solid fa-sparkles text-blue-600 mr-2"></i>
                Review Discovered Areas
            </h3>
            <p class="text-sm text-slate-600 mb-4">
                The following functional areas were discovered by analyzing your issues.
                You can edit, add, or remove areas before saving (one per line).
            </p>

            <form method="POST" action="<?php echo BASEURL; ?>analyze/approve-areas">
                <textarea
                    name="areas"
                    rows="12"
                    class="w-full border border-slate-300 rounded-md p-3 mb-4 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Enter areas (one per line)"
                ><?php echo htmlspecialchars(implode("\n", $glob['pending_areas']['areas'])); ?></textarea>

                <div class="flex justify-end gap-3">
                    <a href="<?php echo BASEURL; ?>" class="px-4 py-2 text-slate-600 hover:text-slate-800 rounded-md hover:bg-slate-100">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 font-medium">
                        <i class="fa-solid fa-check mr-1"></i>
                        Approve & Save Areas
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Resume/Restart Analysis Modal -->
    <div id="resume-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 shadow-xl max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">
                <i class="fa-solid fa-pause-circle text-blue-600 mr-2"></i>
                Incomplete Analysis Found
            </h3>
            <p class="text-sm text-slate-600 mb-4">
                You have an incomplete analysis with <span id="resume-processed" class="font-semibold"></span> of <span id="resume-total" class="font-semibold"></span> issues processed.
            </p>
            <p class="text-sm text-slate-600 mb-6">
                Would you like to continue from where you left off or start over?
            </p>

            <div class="flex gap-3">
                <button id="restart-btn" class="flex-1 px-4 py-2 bg-slate-600 text-white rounded-md hover:bg-slate-700 font-medium">
                    <i class="fa-solid fa-rotate-right mr-1"></i>
                    Start Over
                </button>
                <button id="resume-btn" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium">
                    <i class="fa-solid fa-play mr-1"></i>
                    Continue
                </button>
            </div>
        </div>
    </div>

    <!-- Analysis Progress Modal -->
    <div id="progress-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 shadow-xl max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">
                <i class="fa-solid fa-gear fa-spin text-blue-600 mr-2"></i>
                Analyzing Issues
            </h3>
            <p class="text-sm text-slate-600 mb-4">
                Processing issues with AI. This may take several minutes...
            </p>

            <div class="mb-4">
                <div class="flex justify-between text-sm text-slate-700 mb-2">
                    <span id="progress-text">Analyzing issues...</span>
                    <span id="progress-percent">0%</span>
                </div>
                <div class="w-full bg-slate-200 rounded-full h-3">
                    <div id="progress-bar" class="bg-blue-600 h-3 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
            </div>

            <p class="text-xs text-slate-500 mb-4">
                <span id="progress-count">0</span> of <span id="progress-total">0</span> issues processed
            </p>

            <div id="progress-error" class="hidden bg-red-50 border border-red-200 rounded-md p-3 mb-4">
                <p class="text-sm text-red-700"></p>
            </div>
        </div>
    </div>

    <script>
    // Analysis progress handling
    const BASEURL = '<?php echo BASEURL; ?>';
    let currentJobId = null;
    let processingInterval = null;

    // Analyze form submission
    document.getElementById('analyze-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        startAnalysis(<?php echo $glob['selected_repo']['id'] ?? 0; ?>);
    });

    function startAnalysis(repoId) {
        fetch(BASEURL + 'analyze/run/' + repoId, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        })
        .then(res => res.json())
        .then(data => {
            if (data.has_existing_job) {
                showResumeModal(data.job_id, data.processed, data.total);
            } else {
                currentJobId = data.job_id;
                startProcessing();
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('Failed to start analysis');
        });
    }

    function showResumeModal(jobId, processed, total) {
        currentJobId = jobId;
        document.getElementById('resume-processed').textContent = processed;
        document.getElementById('resume-total').textContent = total;
        document.getElementById('resume-modal').classList.remove('hidden');

        document.getElementById('resume-btn').onclick = () => {
            document.getElementById('resume-modal').classList.add('hidden');
            startProcessing();
        };

        document.getElementById('restart-btn').onclick = () => {
            document.getElementById('resume-modal').classList.add('hidden');
            restartAnalysis(<?php echo $glob['selected_repo']['id'] ?? 0; ?>);
        };
    }

    function restartAnalysis(repoId) {
        fetch(BASEURL + 'analyze/run/' + repoId, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'force_restart=1'
        })
        .then(res => res.json())
        .then(data => {
            currentJobId = data.job_id;
            startProcessing();
        })
        .catch(err => {
            console.error('Error:', err);
            alert('Failed to restart analysis');
        });
    }

    function startProcessing() {
        document.getElementById('progress-modal').classList.remove('hidden');
        processNextChunk();
    }

    function processNextChunk() {
        fetch(BASEURL + 'analyze/process-chunk/' + currentJobId, {
            method: 'POST'
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                showError(data.error || 'Processing failed');
                return;
            }

            updateProgress(data.processed, data.total, data.percent);

            if (data.completed) {
                completeAnalysis();
            } else {
                // Continue processing next chunk
                setTimeout(() => processNextChunk(), 1000);
            }
        })
        .catch(err => {
            console.error('Error:', err);
            showError('Network error. Retrying...');
            setTimeout(() => processNextChunk(), 5000);
        });
    }

    function updateProgress(processed, total, percent) {
        document.getElementById('progress-count').textContent = processed;
        document.getElementById('progress-total').textContent = total;
        document.getElementById('progress-percent').textContent = percent + '%';
        document.getElementById('progress-bar').style.width = percent + '%';
    }

    function showError(message) {
        const errorDiv = document.getElementById('progress-error');
        errorDiv.querySelector('p').textContent = message;
        errorDiv.classList.remove('hidden');
    }

    function completeAnalysis() {
        document.getElementById('progress-text').textContent = 'Analysis complete!';
        document.getElementById('progress-percent').textContent = '100%';
        document.getElementById('progress-bar').style.width = '100%';

        setTimeout(() => {
            window.location.reload();
        }, 1500);
    }
    </script>
</body>
</html>