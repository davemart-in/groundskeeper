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
    <style>
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
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
                    <select class="block w-full pl-3 pr-10 py-2 text-base border-slate-300 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm rounded-md bg-slate-50 border">
                        <option>woocommerce/woocommerce</option>
                        <option>automattic/jetpack</option>
                    </select>
                </div>
                <div class="text-right">
                    <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Analysis Status</span>
                    <div class="flex items-center gap-2 text-sm text-slate-700">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Last audited Jan 6, 2026
                        <button class="ml-2 text-emerald-600 hover:text-emerald-800 text-xs font-medium border border-emerald-200 px-2 py-0.5 rounded bg-emerald-50"><i class="fa-solid fa-rotate mr-1"></i> Re-audit</button>
                    </div>
                </div>
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
                                <h4 class="text-2xl font-bold text-slate-900" id="stat-total">907</h4>
                                <p class="text-sm text-slate-500">Total open bugs</p>
                            </div>
                        </div>
                        <a href="https://github.com/woocommerce/woocommerce/issues" target="_blank" class="text-sm text-slate-400 hover:text-slate-600"><i class="fa-brands fa-github mr-1"></i> View on GitHub</a>
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
                                    <h4 class="text-lg font-bold text-slate-900"><span id="stat-high-signal">31</span> High Signal Issues</h4>
                                    <p class="text-sm text-slate-500 mt-1">Severe or high priority bugs we should address ASAP</p>
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
                                    <h4 class="text-lg font-bold text-slate-900"><span id="stat-duplicates">47</span> Likely Duplicates</h4>
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
                                    <h4 class="text-lg font-bold text-slate-900"><span id="stat-cleanup">83</span> Cleanup Candidates</h4>
                                    <p class="text-sm text-slate-500 mt-1">Stale, non-reproducible, or already solved.</p>
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
                                    <h4 class="text-lg font-bold text-slate-900"><span id="stat-missing-info">656</span> Missing Critical Info</h4>
                                    <p class="text-sm text-slate-500 mt-1">Issues without enough context for engineers to pick up</p>
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
                                    <h4 class="text-lg font-bold text-slate-900"><span id="stat-suggestions">544</span> Suggestions</h4>
                                    <p class="text-sm text-slate-500 mt-1">Recommended priority, status, and functionality label updates.</p>
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

                     <!-- By Functionality -->
                     <div>
                        <h3 class="text-lg font-bold text-slate-900 mb-4">Issues by Area</h3>
                        <div class="bg-white p-2 rounded-lg shadow-sm border border-slate-200">
                           <table class="min-w-full text-sm">
                               <tbody class="divide-y divide-slate-100">
                                   <tr class="hover:bg-slate-50 cursor-pointer group" onclick="filterDashboard('Checkout / Payment', 214)">
                                       <td class="p-3 font-medium text-slate-700 group-hover:text-emerald-700">Checkout / Payment</td>
                                       <td class="p-3 text-right text-slate-500">214 <span class="text-xs text-slate-400 ml-1">(24%)</span></td>
                                   </tr>
                                   <tr class="hover:bg-slate-50 cursor-pointer group" onclick="filterDashboard('Admin Dashboard', 189)">
                                       <td class="p-3 font-medium text-slate-700 group-hover:text-emerald-700">Admin Dashboard</td>
                                       <td class="p-3 text-right text-slate-500">189 <span class="text-xs text-slate-400 ml-1">(21%)</span></td>
                                   </tr>
                                   <tr class="hover:bg-slate-50 cursor-pointer group" onclick="filterDashboard('REST API', 102)">
                                       <td class="p-3 font-medium text-slate-700 group-hover:text-emerald-700">REST API</td>
                                       <td class="p-3 text-right text-slate-500">102 <span class="text-xs text-slate-400 ml-1">(11%)</span></td>
                                   </tr>
                                   <tr class="hover:bg-slate-50 cursor-pointer group" onclick="filterDashboard('Emails', 86)">
                                       <td class="p-3 font-medium text-slate-700 group-hover:text-emerald-700">Emails</td>
                                       <td class="p-3 text-right text-slate-500">86 <span class="text-xs text-slate-400 ml-1">(9%)</span></td>
                                   </tr>
                                   <tr class="hover:bg-slate-50 cursor-pointer group" onclick="filterDashboard('Themes / Blocks', 74)">
                                       <td class="p-3 font-medium text-slate-700 group-hover:text-emerald-700">Themes / Blocks</td>
                                       <td class="p-3 text-right text-slate-500">74 <span class="text-xs text-slate-400 ml-1">(8%)</span></td>
                                   </tr>
                                   <!-- Hidden Rows -->
                                   <tr class="hidden area-hidden bg-slate-50/50 hover:bg-slate-100 cursor-pointer group" onclick="filterDashboard('Coupons', 45)">
                                       <td class="p-3 font-medium text-slate-700 group-hover:text-emerald-700">Coupons</td>
                                       <td class="p-3 text-right text-slate-500">45 <span class="text-xs text-slate-400 ml-1">(5%)</span></td>
                                   </tr>
                                   <tr class="hidden area-hidden bg-slate-50/50 hover:bg-slate-100 cursor-pointer group" onclick="filterDashboard('Shipping', 32)">
                                       <td class="p-3 font-medium text-slate-700 group-hover:text-emerald-700">Shipping</td>
                                       <td class="p-3 text-right text-slate-500">32 <span class="text-xs text-slate-400 ml-1">(4%)</span></td>
                                   </tr>
                                   <tr class="hidden area-hidden bg-slate-50/50 hover:bg-slate-100 cursor-pointer group" onclick="filterDashboard('Analytics', 28)">
                                       <td class="p-3 font-medium text-slate-700 group-hover:text-emerald-700">Analytics</td>
                                       <td class="p-3 text-right text-slate-500">28 <span class="text-xs text-slate-400 ml-1">(3%)</span></td>
                                   </tr>
                               </tbody>
                           </table>
                           <div class="p-2 border-t border-slate-50 text-center">
                                <button onclick="toggleAreas()" id="btn-show-areas" class="text-xs font-medium text-emerald-600 hover:text-emerald-700">Show all</button>
                           </div>
                        </div>
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
                                <div>
                                    <h2 class="text-xl font-bold text-slate-900"><?php echo htmlspecialchars($glob['selected_repo']['full_name']); ?></h2>
                                    <p class="text-sm text-slate-500">Manage how Groundskeeper interacts with this repo.</p>
                                </div>
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

                        <!-- Config Form -->
                        <div class="space-y-8">

                            <?php if (!empty($glob['repositories']) && isset($glob['selected_repo'])): ?>
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
                                            <p class="text-xs text-slate-500 mb-2">Select all labels used to denote priority levels.</p>
                                            <div class="space-y-2 max-h-40 overflow-y-auto border border-slate-200 rounded-md p-3 bg-white">
                                                <label class="flex items-center">
                                                    <input type="checkbox" checked class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-slate-300 rounded">
                                                    <span class="ml-2 text-sm text-slate-700">priority: critical</span>
                                                </label>
                                                <label class="flex items-center">
                                                    <input type="checkbox" checked class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-slate-300 rounded">
                                                    <span class="ml-2 text-sm text-slate-700">priority: high</span>
                                                </label>
                                                <label class="flex items-center">
                                                    <input type="checkbox" checked class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-slate-300 rounded">
                                                    <span class="ml-2 text-sm text-slate-700">priority: normal</span>
                                                </label>
                                                <label class="flex items-center">
                                                    <input type="checkbox" checked class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-slate-300 rounded">
                                                    <span class="ml-2 text-sm text-slate-700">priority: low</span>
                                                </label>
                                            </div>
                                            <p class="text-xs text-slate-500 mt-2 italic">Priority label configuration coming soon</p>
                                        </div>
                                    </div>

                                    <div class="pt-4 flex justify-end">
                                        <button type="submit" class="bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-sm hover:bg-emerald-700">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php if (!empty($glob['repositories']) && isset($glob['selected_repo'])): ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    <!-- MODAL: Add Repo -->
    <div id="modal-add-repo" class="hidden relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity" onclick="closeModal('add-repo')"></div>

        <!-- Fixed wrapper for centering -->
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                
                <!-- Modal Panel -->
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    
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

                            <div class="bg-slate-50 p-4 rounded border border-slate-200">
                                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4">Initial Configuration</h4>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Bug Label</label>
                                    <input type="text" name="bug_label" value="bug" class="block w-full pl-3 pr-3 py-2 text-base border-slate-300 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm rounded-md bg-white border" placeholder="bug">
                                    <p class="mt-1 text-xs text-slate-500">The label used to identify bugs in this repository</p>
                                </div>
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
        <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity" onclick="closeModal('duplicates')"></div>

        <!-- Fixed wrapper for centering -->
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                
                <!-- Modal Panel -->
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl">
                    
                    <!-- Modal Header -->
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-slate-100 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-slate-900" id="modal-title">Review Likely Duplicates</h3>
                            <p class="text-sm text-slate-500 mt-1">Found 47 issues that appear to duplicate existing bugs.</p>
                        </div>
                        <button onclick="closeModal('duplicates')" class="text-slate-400 hover:text-slate-500">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>

                    <!-- Bulk Actions Toolbar -->
                    <div class="bg-slate-50 px-4 py-3 flex items-center justify-between border-b border-slate-200">
                        <div class="flex items-center gap-4">
                            <label class="inline-flex items-center">
                                <input type="checkbox" class="form-checkbox h-4 w-4 text-emerald-600 rounded border-slate-300">
                                <span class="ml-2 text-sm text-slate-600 font-medium">Select All</span>
                            </label>
                            <span class="h-4 w-px bg-slate-300 mx-2"></span>
                            <span class="text-sm text-slate-500">2 selected</span>
                        </div>
                        <div class="flex gap-2">
                             <button class="bg-white border border-slate-300 text-slate-600 hover:text-red-600 hover:border-red-300 px-3 py-1.5 rounded text-sm font-medium shadow-sm transition">
                                Dismiss
                            </button>
                            <button class="bg-emerald-600 text-white hover:bg-emerald-700 px-3 py-1.5 rounded text-sm font-medium shadow-sm transition">
                                Merge & Close Selected
                            </button>
                        </div>
                    </div>

                    <!-- List Content -->
                    <div class="max-h-[60vh] overflow-y-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50 sticky top-0">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider w-10"></th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">New Issue</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Suggested Duplicate Of</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Confidence</th>
                                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                <!-- Row 1 -->
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" class="form-checkbox h-4 w-4 text-emerald-600 rounded border-slate-300">
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-slate-900">Checkout spins forever on iOS</div>
                                        <div class="text-xs text-slate-500">
                                            <a href="#" target="_blank" class="hover:text-emerald-600 hover:underline">#19422 <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>
                                            • opened 2 days ago
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-slate-900 flex items-center gap-2">
                                            <i class="fa-solid fa-arrow-right-long text-slate-400"></i>
                                            <a href="#" target="_blank" class="hover:underline text-emerald-700">#19401: Apple Pay timeout <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">98% Match</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button class="text-emerald-600 hover:text-emerald-900 font-medium">Merge & close</button>
                                    </td>
                                </tr>
                                <!-- Row 2 -->
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" class="form-checkbox h-4 w-4 text-emerald-600 rounded border-slate-300">
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-slate-900">Error 500 on Analytics page</div>
                                        <div class="text-xs text-slate-500">
                                            <a href="#" target="_blank" class="hover:text-emerald-600 hover:underline">#19455 <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>
                                            • opened 5 hours ago
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-slate-900 flex items-center gap-2">
                                            <i class="fa-solid fa-arrow-right-long text-slate-400"></i>
                                            <a href="#" target="_blank" class="hover:underline text-emerald-700">#19200: Analytics data fetch fail <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">92% Match</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button class="text-emerald-600 hover:text-emerald-900 font-medium">Merge & close</button>
                                    </td>
                                </tr>
                                <!-- Row 3 -->
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" class="form-checkbox h-4 w-4 text-emerald-600 rounded border-slate-300">
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-slate-900">Typo in German translation</div>
                                        <div class="text-xs text-slate-500">
                                            <a href="#" target="_blank" class="hover:text-emerald-600 hover:underline">#18999 <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>
                                            • opened 1 week ago
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-slate-900 flex items-center gap-2">
                                            <i class="fa-solid fa-arrow-right-long text-slate-400"></i>
                                            <a href="#" target="_blank" class="hover:underline text-emerald-700">#18880: i18n corrections <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">75% Match</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button class="text-emerald-600 hover:text-emerald-900 font-medium">Merge & close</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Footer Removed -->
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: High Signal Issues -->
    <div id="modal-high-signal" class="hidden relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity" onclick="closeModal('high-signal')"></div>

        <!-- Fixed wrapper for centering -->
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                
                <!-- Modal Panel -->
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl">
                    
                    <!-- Modal Header -->
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-slate-100 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-slate-900" id="modal-title">High Signal Issues Queue</h3>
                            <p class="text-sm text-slate-500 mt-1">31 issues flagged for immediate attention based on severity, activity, and keywords.</p>
                        </div>
                        <button onclick="closeModal('high-signal')" class="text-slate-400 hover:text-slate-500">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>

                    <!-- Bulk Actions Toolbar -->
                    <div class="bg-slate-50 px-4 py-3 flex items-center justify-between border-b border-slate-200">
                        <div class="flex items-center gap-4">
                            <label class="inline-flex items-center">
                                <input type="checkbox" class="form-checkbox h-4 w-4 text-emerald-600 rounded border-slate-300">
                                <span class="ml-2 text-sm text-slate-600 font-medium">Select All</span>
                            </label>
                            <span class="h-4 w-px bg-slate-300 mx-2"></span>
                            <span class="text-sm text-slate-500">0 selected</span>
                        </div>
                        <div class="flex gap-2">
                             <button class="bg-white border border-slate-300 text-slate-600 hover:text-emerald-600 hover:border-emerald-300 px-3 py-1.5 rounded text-sm font-medium shadow-sm transition">
                                Assign to Me
                            </button>
                        </div>
                    </div>

                    <!-- List Content -->
                    <div class="max-h-[60vh] overflow-y-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50 sticky top-0">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider w-10"></th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Issue</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Signals Detected</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Score</th>
                                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                <!-- Row 1 -->
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" class="form-checkbox h-4 w-4 text-emerald-600 rounded border-slate-300">
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-slate-900">Critical DB Error on Checkout</div>
                                        <div class="text-xs text-slate-500">
                                            <a href="#" target="_blank" class="hover:text-emerald-600 hover:underline">#19500 <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>
                                            • opened 1 hour ago
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Critical Label</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700">Keyword: "Data Loss"</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-bold text-slate-900">98</span>
                                        <span class="text-xs text-slate-400">/100</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button class="text-emerald-600 hover:text-emerald-900 font-medium">Triage</button>
                                    </td>
                                </tr>
                                <!-- Row 2 -->
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" class="form-checkbox h-4 w-4 text-emerald-600 rounded border-slate-300">
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-slate-900">Security Vulnerability in API Auth</div>
                                        <div class="text-xs text-slate-500">
                                            <a href="#" target="_blank" class="hover:text-emerald-600 hover:underline">#19488 <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>
                                            • opened 4 hours ago
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">Security</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700">High Activity</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-bold text-slate-900">95</span>
                                        <span class="text-xs text-slate-400">/100</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button class="text-emerald-600 hover:text-emerald-900 font-medium">Triage</button>
                                    </td>
                                </tr>
                                <!-- Row 3 -->
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" class="form-checkbox h-4 w-4 text-emerald-600 rounded border-slate-300">
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-slate-900">Payment Gateway Timeout</div>
                                        <div class="text-xs text-slate-500">
                                            <a href="#" target="_blank" class="hover:text-emerald-600 hover:underline">#19450 <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>
                                            • opened 1 day ago
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">High Priority</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700">Multiple Reports</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-bold text-slate-900">89</span>
                                        <span class="text-xs text-slate-400">/100</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button class="text-emerald-600 hover:text-emerald-900 font-medium">Triage</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: Cleanup Candidates -->
    <div id="modal-cleanup" class="hidden relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity" onclick="closeModal('cleanup')"></div>

        <!-- Fixed wrapper for centering -->
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                
                <!-- Modal Panel -->
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl">
                    
                    <!-- Modal Header -->
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-slate-100 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-slate-900" id="modal-title">Review Cleanup Candidates</h3>
                            <p class="text-sm text-slate-500 mt-1">Found 83 issues that appear stale, non-reproducible, or best handled elsewhere.</p>
                        </div>
                        <button onclick="closeModal('cleanup')" class="text-slate-400 hover:text-slate-500">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>

                    <!-- Bulk Actions Toolbar -->
                    <div class="bg-slate-50 px-4 py-3 flex items-center justify-between border-b border-slate-200">
                        <div class="flex items-center gap-4">
                            <label class="inline-flex items-center">
                                <input type="checkbox" class="form-checkbox h-4 w-4 text-emerald-600 rounded border-slate-300">
                                <span class="ml-2 text-sm text-slate-600 font-medium">Select All</span>
                            </label>
                            <span class="h-4 w-px bg-slate-300 mx-2"></span>
                            <span class="text-sm text-slate-500">0 selected</span>
                        </div>
                        <div class="flex gap-2">
                             <button class="bg-white border border-slate-300 text-slate-600 hover:text-emerald-600 hover:border-emerald-300 px-3 py-1.5 rounded text-sm font-medium shadow-sm transition">
                                Ignore
                            </button>
                            <button class="bg-emerald-600 text-white hover:bg-emerald-700 px-3 py-1.5 rounded text-sm font-medium shadow-sm transition">
                                Close Selected
                            </button>
                        </div>
                    </div>

                    <!-- List Content -->
                    <div class="max-h-[60vh] overflow-y-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50 sticky top-0">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider w-10"></th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Issue</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Reasoning</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Last Activity</th>
                                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                <!-- Row 1 -->
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" class="form-checkbox h-4 w-4 text-emerald-600 rounded border-slate-300">
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-slate-900">Layout broken on Internet Explorer 11</div>
                                        <div class="text-xs text-slate-500">
                                            <a href="#" target="_blank" class="hover:text-emerald-600 hover:underline">#14200 <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>
                                            • opened 3 years ago
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600">Unsupported Browser</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                        2 years ago
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button class="text-red-600 hover:text-red-800 font-medium">Close</button>
                                    </td>
                                </tr>
                                <!-- Row 2 -->
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" class="form-checkbox h-4 w-4 text-emerald-600 rounded border-slate-300">
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-slate-900">How do I change the color of the button?</div>
                                        <div class="text-xs text-slate-500">
                                            <a href="#" target="_blank" class="hover:text-emerald-600 hover:underline">#19300 <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>
                                            • opened 1 week ago
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700">Support Request</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                        6 days ago
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button class="text-emerald-600 hover:text-emerald-800 font-medium">Refer to Forum</button>
                                    </td>
                                </tr>
                                <!-- Row 3 -->
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" class="form-checkbox h-4 w-4 text-emerald-600 rounded border-slate-300">
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-slate-900">Crash on WC 3.2 legacy import</div>
                                        <div class="text-xs text-slate-500">
                                            <a href="#" target="_blank" class="hover:text-emerald-600 hover:underline">#15100 <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>
                                            • opened 2 years ago
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-50 text-orange-700">Deprecated Version</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                        1 year ago
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button class="text-red-600 hover:text-red-800 font-medium">Close</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: Missing Info -->
    <div id="modal-missing-info" class="hidden relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity" onclick="closeModal('missing-info')"></div>

        <!-- Fixed wrapper for centering -->
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                
                <!-- Modal Panel -->
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl">
                    
                    <!-- Modal Header -->
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-slate-100 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-slate-900" id="modal-title">Review Issues Missing Context</h3>
                            <p class="text-sm text-slate-500 mt-1">Found 656 issues missing critical information required for triage.</p>
                        </div>
                        <button onclick="closeModal('missing-info')" class="text-slate-400 hover:text-slate-500">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>

                    <!-- Bulk Actions Toolbar -->
                    <div class="bg-slate-50 px-4 py-3 flex items-center justify-between border-b border-slate-200">
                        <div class="flex items-center gap-4">
                            <label class="inline-flex items-center">
                                <input type="checkbox" class="form-checkbox h-4 w-4 text-emerald-600 rounded border-slate-300">
                                <span class="ml-2 text-sm text-slate-600 font-medium">Select All</span>
                            </label>
                            <span class="h-4 w-px bg-slate-300 mx-2"></span>
                            <span class="text-sm text-slate-500">0 selected</span>
                        </div>
                        <div class="flex gap-2">
                             <button class="bg-white border border-slate-300 text-slate-600 hover:text-emerald-600 hover:border-emerald-300 px-3 py-1.5 rounded text-sm font-medium shadow-sm transition">
                                Request Info (AI Draft)
                            </button>
                        </div>
                    </div>

                    <!-- List Content -->
                    <div class="max-h-[60vh] overflow-y-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50 sticky top-0">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider w-10"></th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Issue</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Missing Elements</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Severity</th>
                                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                <!-- Row 1 -->
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" class="form-checkbox h-4 w-4 text-emerald-600 rounded border-slate-300">
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-slate-900">Cart page is blank</div>
                                        <div class="text-xs text-slate-500">
                                            <a href="#" target="_blank" class="hover:text-emerald-600 hover:underline">#19512 <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>
                                            • opened 3 hours ago
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-50 text-red-700">Missing Steps to Reproduce</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-50 text-red-700">Missing Version Info</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                        High
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button class="text-emerald-600 hover:text-emerald-800 font-medium">Draft Reply</button>
                                    </td>
                                </tr>
                                <!-- Row 2 -->
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" class="form-checkbox h-4 w-4 text-emerald-600 rounded border-slate-300">
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-slate-900">Plugin conflict with Elementor</div>
                                        <div class="text-xs text-slate-500">
                                            <a href="#" target="_blank" class="hover:text-emerald-600 hover:underline">#19490 <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>
                                            • opened 1 day ago
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-50 text-red-700">Missing Error Logs</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                        Medium
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button class="text-emerald-600 hover:text-emerald-800 font-medium">Draft Reply</button>
                                    </td>
                                </tr>
                                <!-- Row 3 -->
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" class="form-checkbox h-4 w-4 text-emerald-600 rounded border-slate-300">
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-slate-900">Orders not syncing</div>
                                        <div class="text-xs text-slate-500">
                                            <a href="#" target="_blank" class="hover:text-emerald-600 hover:underline">#19485 <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>
                                            • opened 1 day ago
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-50 text-red-700">Missing Environment Report</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-50 text-red-700">Vague Description</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                        High
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button class="text-emerald-600 hover:text-emerald-800 font-medium">Draft Reply</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: Suggestions -->
    <div id="modal-suggestions" class="hidden relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity" onclick="closeModal('suggestions')"></div>

        <!-- Fixed wrapper for centering -->
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                
                <!-- Modal Panel -->
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl">
                    
                    <!-- Modal Header -->
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-slate-100 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-slate-900" id="modal-title">Review Triage Suggestions</h3>
                            <p class="text-sm text-slate-500 mt-1">Found 544 issues where labels or priority can be inferred automatically.</p>
                        </div>
                        <button onclick="closeModal('suggestions')" class="text-slate-400 hover:text-slate-500">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>

                    <!-- Bulk Actions Toolbar -->
                    <div class="bg-slate-50 px-4 py-3 flex items-center justify-between border-b border-slate-200">
                        <div class="flex items-center gap-4">
                            <label class="inline-flex items-center">
                                <input type="checkbox" class="form-checkbox h-4 w-4 text-emerald-600 rounded border-slate-300">
                                <span class="ml-2 text-sm text-slate-600 font-medium">Select All</span>
                            </label>
                            <span class="h-4 w-px bg-slate-300 mx-2"></span>
                            <span class="text-sm text-slate-500">0 selected</span>
                        </div>
                        <div class="flex gap-2">
                             <button class="bg-white border border-slate-300 text-slate-600 hover:text-red-600 hover:border-red-300 px-3 py-1.5 rounded text-sm font-medium shadow-sm transition">
                                Dismiss
                            </button>
                            <button class="bg-emerald-600 text-white hover:bg-emerald-700 px-3 py-1.5 rounded text-sm font-medium shadow-sm transition">
                                Apply Selected
                            </button>
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
                                <!-- Row 1 -->
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" class="form-checkbox h-4 w-4 text-emerald-600 rounded border-slate-300">
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-slate-900">Product images overlapping on mobile</div>
                                        <div class="text-xs text-slate-500">
                                            <a href="#" target="_blank" class="hover:text-emerald-600 hover:underline">#18221 <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>
                                            • opened 2 months ago
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-2 text-xs">
                                                <span class="text-slate-400">Add Label:</span>
                                                <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded border border-slate-200">type: bug</span>
                                            </div>
                                            <div class="flex items-center gap-2 text-xs">
                                                <span class="text-slate-400">Set Priority:</span>
                                                <span class="bg-amber-100 text-amber-800 px-2 py-0.5 rounded border border-amber-200">Medium</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                        Visual regression detected
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button class="text-emerald-600 hover:text-emerald-800 font-medium">Apply</button>
                                    </td>
                                </tr>
                                <!-- Row 2 -->
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" class="form-checkbox h-4 w-4 text-emerald-600 rounded border-slate-300">
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-slate-900">API returns 404 for valid order ID</div>
                                        <div class="text-xs text-slate-500">
                                            <a href="#" target="_blank" class="hover:text-emerald-600 hover:underline">#19002 <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>
                                            • opened 3 weeks ago
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-2 text-xs">
                                                <span class="text-slate-400">Add Label:</span>
                                                <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded border border-blue-200">area: api</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                        Endpoint URL in body
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button class="text-emerald-600 hover:text-emerald-800 font-medium">Apply</button>
                                    </td>
                                </tr>
                                <!-- Row 3 -->
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" class="form-checkbox h-4 w-4 text-emerald-600 rounded border-slate-300">
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-slate-900">Documentation typo in readme</div>
                                        <div class="text-xs text-slate-500">
                                            <a href="#" target="_blank" class="hover:text-emerald-600 hover:underline">#18111 <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>
                                            • opened 4 months ago
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-2 text-xs">
                                                <span class="text-slate-400">Set Priority:</span>
                                                <span class="bg-green-100 text-green-800 px-2 py-0.5 rounded border border-green-200">Low</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                        Non-functional issue
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button class="text-emerald-600 hover:text-emerald-800 font-medium">Apply</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
	<script type="text/javascript" src="<?php echo BASEURL; ?>js/groundskeeper-utility.js"></script>
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

        // Simulating filtered data since there's no backend
        const originalStats = {
            total: 907,
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
        });
    </script>
</body>
</html>