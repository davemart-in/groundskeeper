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
<body style="color: #1e293b; background-color: #f8fafc" class="font-sans min-h-screen flex flex-col">

    <!-- Top Navigation Bar -->
    <nav style="border-color: #e2e8f0; background-color: #ffffff" class="border-b sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center gap-2 mr-8">
                        <div style="color: #ffffff; background-color: #059669" class="w-8 h-8 rounded-lg flex items-center justify-center font-bold">
                            <i class="fa-solid fa-leaf"></i>
                        </div>
                        <span style="color: #0f172a" class="font-bold text-lg tracking-tight">Groundskeeper <span style="border-color: #94a3b8; background-color: #94a3b8" class="text-xs font-normal px-2 py-0.5 rounded-full ml-1 border">v0.1</span></span>
                    </div>
                    <div class="hidden sm:-my-px sm:flex sm:space-x-8">
                        <a href="<?php echo BASEURL; ?>" id="tab-dashboard" style="<?php echo (!isset($glob['active_tab']) || $glob['active_tab'] === 'dashboard') ? 'border-color: #10b981; color: #0f172a' : 'border-color: transparent; color: #64748b'; ?>" class="hover:text-slate-700 hover:border-slate-300 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Dashboard
                        </a>
                        <a href="<?php echo BASEURL; ?>settings" id="tab-settings" style="<?php echo (isset($glob['active_tab']) && $glob['active_tab'] === 'settings') ? 'border-color: #10b981; color: #0f172a' : 'border-color: transparent; color: #64748b'; ?>" class="hover:text-slate-700 hover:border-slate-300 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
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
            <div style="border-color: #e2e8f0; background-color: #ffffff" class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 p-4 rounded-lg shadow-sm border">
                <div class="relative w-full sm:w-80">
                    <label style="color: #94a3b8" class="block text-xs font-bold uppercase tracking-wider mb-1">Repository</label>
                    <?php if (!empty($glob['repositories'])): ?>
                        <select style="background-color: #f8fafc; border-color: #cbd5e1" class="block w-full pl-3 pr-10 py-2 text-base focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm rounded-md border">
                            <?php foreach ($glob['repositories'] as $repo): ?>
                                <option value="<?php echo $repo['id']; ?>"><?php echo htmlspecialchars($repo['full_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <div style="color: #64748b" class="text-sm py-2">
                            No repositories connected. <a href="<?php echo BASEURL; ?>settings" class="hover:underline font-medium">Add a repository</a>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($glob['repositories']) && isset($glob['selected_repo'])): ?>
                <div class="text-right">
                    <span style="color: #94a3b8" class="block text-xs font-bold uppercase tracking-wider mb-1">Analysis Status</span>
                    <?php if (!$glob['selected_repo']['last_audited_at']): ?>
                        <div style="color: #334155" class="flex items-center gap-2 text-sm">
                            <span style="background-color: #cbd5e1" class="w-2 h-2 rounded-full"></span>
                            Not yet audited
                            <form method="POST" action="<?php echo BASEURL; ?>audit/run/<?php echo $glob['selected_repo']['id']; ?>" class="inline" onsubmit="showAuditLoading()">
                                <button style="background-color: #ecfdf5; border-color: #a7f3d0; color: #059669" type="submit" class="ml-2 hover:text-emerald-800 text-xs font-medium border px-2 py-0.5 rounded"><i class="fa-solid fa-play mr-1"></i> Run Audit</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div style="color: #334155" class="flex items-center gap-2 text-sm">
                            <span style="background-color: #10b981" class="w-2 h-2 rounded-full"></span>
                            Last audited <?php echo date('M j, Y', $glob['selected_repo']['last_audited_at']); ?>
                            <form id="sync-form" method="POST" action="<?php echo BASEURL; ?>sync/run/<?php echo $glob['selected_repo']['id']; ?>" class="inline">
                                <button style="background-color: #eff6ff; border-color: #bfdbfe; color: #2563eb" type="submit" class="ml-2 hover:text-blue-800 text-xs font-medium border px-2 py-0.5 rounded"><i class="fa-solid fa-arrows-rotate mr-1"></i> Update issues and re-analyze</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($glob['issues'])): ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Left Column: The Findings Feed -->
                <div class="lg:col-span-2 space-y-4">
                    <div id="analysis-header" class="flex items-center gap-2">
                        <h3 style="color: #0f172a" class="text-lg font-bold">Analysis Findings</h3>
                    </div>

                    <!-- Stat Card: Total -->
                    <div style="border-color: #e2e8f0; background-color: #ffffff" class="p-4 rounded-lg shadow-sm border flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div style="color: #64748b; background-color: #f1f5f9" class="p-3 rounded-lg">
                                <i class="fa-solid fa-layer-group text-xl"></i>
                            </div>
                            <div>
                                <h4 style="color: #0f172a" class="text-2xl font-bold" id="stat-total"><?php echo count($glob['issues']); ?></h4>
                                <p style="color: #64748b" class="text-sm">Total open bugs</p>
                            </div>
                        </div>
                        <?php if (isset($glob['selected_repo'])): ?>
                            <a href="https://github.com/<?php echo htmlspecialchars($glob['selected_repo']['owner']); ?>/<?php echo htmlspecialchars($glob['selected_repo']['name']); ?>/issues?q=is%3Aissue%20state%3Aopen%20label%3A<?php echo urlencode($glob['selected_repo']['bug_label']); ?>" target="_blank" class="text-sm hover:text-slate-600"><i class="fa-brands fa-github mr-1"></i> View on GitHub</a>
                        <?php else: ?>
                            <a style="color: #94a3b8" href="#" class="text-sm hover:text-slate-600"><i class="fa-brands fa-github mr-1"></i> View on GitHub</a>
                        <?php endif; ?>
                    </div>

                    <!-- Action Card: High Signal -->
                    <div style="border-left-color: #ef4444; background-color: #ffffff; border-color: #e2e8f0" class="p-5 rounded-lg shadow-sm border border-l-4 group hover:shadow-md transition">
                        <div class="flex justify-between items-start">
                            <div class="flex gap-4">
                                <div class="pt-1">
                                    <div style="background-color: #fee2e2; color: #dc2626" class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm">
                                        <i class="fa-solid fa-fire"></i>
                                    </div>
                                </div>
                                <div>
                                    <h4 style="color: #0f172a" class="text-lg font-bold"><span id="stat-high-signal"><?php echo count($glob['high_signal_issues']); ?></span> High Signal Issues</h4>
                                    <p style="color: #64748b" class="text-sm mt-1">Valuable, actionable issues worth prioritizing</p>
                                </div>
                            </div>
                            <button style="background-color: #ffffff; border-color: #cbd5e1; color: #334155" onclick="GRNDSKPR.Dashboard.openModal('high-signal')" class="border hover:bg-slate-50 px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition">
                                View Issues
                            </button>
                        </div>
                    </div>

                    <!-- Action Card: Duplicates -->
                    <div style="border-left-color: #fbbf24; background-color: #ffffff; border-color: #e2e8f0" class="p-5 rounded-lg shadow-sm border border-l-4 group hover:shadow-md transition">
                        <div class="flex justify-between items-start">
                            <div class="flex gap-4">
                                <div class="pt-1">
                                    <div style="background-color: #fef3c7; color: #d97706" class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm">
                                        <i class="fa-solid fa-clone"></i>
                                    </div>
                                </div>
                                <div>
                                    <h4 style="color: #0f172a" class="text-lg font-bold"><span id="stat-duplicates"><?php echo count($glob['duplicates']); ?></span> Likely Duplicates</h4>
                                    <p style="color: #64748b" class="text-sm mt-1">Issues that appear to be semantically similar.</p>
                                </div>
                            </div>
                            <button style="background-color: #ffffff; border-color: #cbd5e1; color: #334155" onclick="GRNDSKPR.Dashboard.openModal('duplicates')" class="border hover:bg-slate-50 hover:text-emerald-700 hover:border-emerald-300 px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition">
                                View Issues
                            </button>
                        </div>
                    </div>

                    <!-- Action Card: Should Close -->
                    <div style="border-left-color: #94a3b8; background-color: #ffffff; border-color: #e2e8f0" class="p-5 rounded-lg shadow-sm border border-l-4 group hover:shadow-md transition">
                        <div class="flex justify-between items-start">
                            <div class="flex gap-4">
                                <div class="pt-1">
                                    <div style="background-color: #f1f5f9; color: #475569" class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm">
                                        <i class="fa-solid fa-archive"></i>
                                    </div>
                                </div>
                                <div>
                                    <h4 style="color: #0f172a" class="text-lg font-bold"><span id="stat-cleanup"><?php echo count($glob['cleanup_candidates']); ?></span> Cleanup Candidates</h4>
                                    <p style="color: #64748b" class="text-sm mt-1">Issues that should likely be closed</p>
                                </div>
                            </div>
                            <button style="background-color: #ffffff; border-color: #cbd5e1; color: #334155" onclick="GRNDSKPR.Dashboard.openModal('cleanup')" class="border hover:bg-slate-50 px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition">
                                View Issues
                            </button>
                        </div>
                    </div>

                    <!-- Action Card: Missing Info -->
                    <div style="border-left-color: #60a5fa; background-color: #ffffff; border-color: #e2e8f0" class="p-5 rounded-lg shadow-sm border border-l-4 group hover:shadow-md transition">
                        <div class="flex justify-between items-start">
                            <div class="flex gap-4">
                                <div class="pt-1">
                                    <div style="background-color: #dbeafe; color: #2563eb" class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm">
                                        <i class="fa-solid fa-circle-question"></i>
                                    </div>
                                </div>
                                <div>
                                    <h4 style="color: #0f172a" class="text-lg font-bold"><span id="stat-missing-info"><?php echo count($glob['missing_info_issues']); ?></span> Missing Critical Info</h4>
                                    <p style="color: #64748b" class="text-sm mt-1">Issues lacking critical information</p>
                                </div>
                            </div>
                            <button style="background-color: #ffffff; border-color: #cbd5e1; color: #334155" onclick="GRNDSKPR.Dashboard.openModal('missing-info')" class="border hover:bg-slate-50 px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition">
                                View Issues
                            </button>
                        </div>
                    </div>

                    <!-- Action Card: Suggestions -->
                    <div style="border-left-color: #c084fc; background-color: #ffffff; border-color: #e2e8f0" class="p-5 rounded-lg shadow-sm border border-l-4 group hover:shadow-md transition">
                        <div class="flex justify-between items-start">
                            <div class="flex gap-4">
                                <div class="pt-1">
                                    <div style="background-color: #f3e8ff; color: #9333ea" class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm">
                                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                                    </div>
                                </div>
                                <div>
                                    <h4 style="color: #0f172a" class="text-lg font-bold"><span id="stat-suggestions"><?php echo count($glob['label_suggestions']); ?></span> Label Suggestions</h4>
                                    <p style="color: #64748b" class="text-sm mt-1">AI-recommended labels to improve categorization</p>
                                </div>
                            </div>
                            <button style="background-color: #ffffff; border-color: #cbd5e1; color: #334155" onclick="GRNDSKPR.Dashboard.openModal('suggestions')" class="border hover:bg-slate-50 px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition">
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
                        <h3 style="color: #0f172a" class="text-lg font-bold mb-4">Issues by Priority</h3>
                        <div style="border-color: #e2e8f0; background-color: #ffffff" class="p-6 rounded-lg shadow-sm border">
                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span style="color: #991b1b" class="font-medium">Critical / High</span>
                                        <span style="color: #64748b" >12%</span>
                                    </div>
                                    <div style="background-color: #f1f5f9" class="w-full rounded-full h-2">
                                        <div class="h-2 rounded-full" style="width: 12%; background-color: #ef4444"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span style="color: #b45309" class="font-medium">Medium</span>
                                        <span style="color: #64748b" >34%</span>
                                    </div>
                                    <div style="background-color: #f1f5f9" class="w-full rounded-full h-2">
                                        <div class="h-2 rounded-full" style="width: 34%; background-color: #fbbf24"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span style="color: #1d4ed8" class="font-medium">Low / Enhancement</span>
                                        <span style="color: #64748b" >41%</span>
                                    </div>
                                    <div style="background-color: #f1f5f9" class="w-full rounded-full h-2">
                                        <div class="h-2 rounded-full" style="width: 41%; background-color: #60a5fa"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span style="color: #64748b" class="font-medium">Un-prioritized</span>
                                        <span style="color: #64748b" >13%</span>
                                    </div>
                                    <div style="background-color: #f1f5f9" class="w-full rounded-full h-2">
                                        <div class="h-2 rounded-full" style="width: 13%; background-color: #cbd5e1"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                     <!-- By Functionality -->
                     <div>
                        <h3 style="color: #0f172a" class="text-lg font-bold mb-4">Issues by Area</h3>
                        <?php if (!empty($glob['area_stats'])): ?>
                        <div style="border-color: #e2e8f0; background-color: #ffffff" class="p-2 rounded-lg shadow-sm border">
                           <table class="min-w-full text-sm">
                               <tbody style="border-color: #f1f5f9" class="divide-y">
                                   <?php
                                   $topCount = 10;
                                   foreach ($glob['area_stats'] as $index => $area):
                                       $isHidden = $index >= $topCount;
                                       $rowClass = $isHidden ? 'hidden area-hidden bg-slate-50/50 hover:bg-slate-100' : 'hover:bg-slate-50';
                                   ?>
                                   <tr class="area-row <?php echo $rowClass; ?> cursor-pointer group transition-all" data-area-id="<?php echo $area['id']; ?>" onclick="GRNDSKPR.Dashboard.filterDashboard('<?php echo htmlspecialchars(addslashes($area['name'])); ?>', <?php echo $area['count']; ?>, <?php echo $area['id']; ?>)">
                                       <td style="color: #334155" class="p-3 font-medium group-hover:text-emerald-700"><?php echo htmlspecialchars($area['name']); ?></td>
                                       <td style="color: #64748b" class="p-3 text-right whitespace-nowrap"><?php echo $area['count']; ?> <span style="color: #94a3b8" class="text-xs ml-1">(<?php echo $area['percentage']; ?>%)</span></td>
                                   </tr>
                                   <?php endforeach; ?>
                               </tbody>
                           </table>
                           <?php if (count($glob['area_stats']) > $topCount): ?>
                           <div style="border-color: #f8fafc" class="p-2 border-t text-center">
                                <button style="color: #059669" onclick="GRNDSKPR.Dashboard.toggleAreas()" id="btn-show-areas" class="text-xs font-medium hover:text-emerald-700">Show all</button>
                           </div>
                           <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <div style="background-color: #ffffff; border-color: #e2e8f0; color: #64748b" class="p-6 rounded-lg shadow-sm border text-center text-sm">
                            No areas defined yet. Run analysis to categorize issues.
                        </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- SETTINGS TAB -->
        <div id="view-settings" class="<?php echo (!isset($glob['active_tab']) || $glob['active_tab'] === 'dashboard') ? 'hidden' : ''; ?> animate-fade-in h-[calc(100vh-140px)]">
            <div style="border-color: #e2e8f0; background-color: #ffffff" class="flex h-full rounded-lg shadow-sm border overflow-hidden">
                
                <!-- Sidebar -->
                <div style="border-color: #e2e8f0; background-color: #f8fafc" class="w-64 border-r flex flex-col">
                    <div style="border-color: #e2e8f0" class="p-4 border-b flex justify-between items-center">
                        <span style="color: #64748b" class="text-xs font-bold uppercase tracking-wider">Repositories</span>
                        <button style="color: #059669" onclick="GRNDSKPR.Dashboard.openModal('add-repo')" class="hover:text-emerald-700 text-xs font-bold"><i class="fa-solid fa-plus"></i> Add</button>
                    </div>
                    <div class="flex-1 overflow-y-auto">
                        <nav class="space-y-1 p-2">
                            <?php if (!empty($glob['repositories'])): ?>
                                <?php foreach ($glob['repositories'] as $repo): ?>
                                    <a href="<?php echo BASEURL; ?>settings/<?php echo $repo['id']; ?>" style="<?php echo (isset($glob['selected_repo']) && $glob['selected_repo']['id'] === $repo['id']) ? 'background-color: #ffffff; border-color: #e2e8f0; color: #0f172a' : 'color: #475569'; ?>" class="<?php echo (isset($glob['selected_repo']) && $glob['selected_repo']['id'] === $repo['id']) ? 'border shadow-sm' : 'hover:bg-slate-100 hover:text-slate-900'; ?> group flex items-center px-3 py-2 text-sm font-medium rounded-md">
                                        <i style="color: #94a3b8" class="fa-brands fa-github mr-3"></i>
                                        <span class="truncate"><?php echo htmlspecialchars($repo['full_name']); ?></span>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div style="color: #94a3b8" class="p-4 text-center text-xs">
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
                                        <h2 style="color: #0f172a" class="text-xl font-bold"><?php echo htmlspecialchars($glob['selected_repo']['full_name']); ?></h2>
                                        <?php if (isset($glob['user']) && $glob['user']): ?>
                                            <div style="background-color: #f0fdf4; color: #15803d; border-color: #bbf7d0" class="flex items-center gap-2 px-3 py-1 rounded-full border text-xs font-medium">
                                                <span style="background-color: #22c55e" class="w-2 h-2 rounded-full"></span>
                                                Connected
                                            </div>
                                        <?php else: ?>
                                            <div style="background-color: #fef2f2; color: #991b1b; border-color: #fecaca" class="flex items-center gap-2 px-3 py-1 rounded-full border text-xs font-medium">
                                                <span style="background-color: #ef4444" class="w-2 h-2 rounded-full"></span>
                                                Disconnected
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <p style="color: #64748b" class="text-sm mt-1">Manage how Groundskeeper interacts with this repo.</p>
                                </div>
                                <div class="flex gap-3">
                                    <a href="<?php echo BASEURL; ?>reset/<?php echo $glob['selected_repo']['id']; ?>" class="text-sm hover:text-yellow-700 font-medium">
                                        <i class="fa-solid fa-rotate-left mr-1"></i> Reset Data
                                    </a>
                                    <form method="POST" action="<?php echo BASEURL; ?>settings/<?php echo $glob['selected_repo']['id']; ?>/delete" onsubmit="return confirm('Are you sure you want to remove this repository?');">
                                        <button style="color: #dc2626" type="submit" class="text-sm hover:text-red-700 font-medium">
                                            <i class="fa-solid fa-trash mr-1"></i> Remove
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- No repos - show blank slate -->
                            <div class="text-center">
                                <div style="background-color: #f1f5f9" class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i style="color: #94a3b8" class="fa-brands fa-github text-2xl"></i>
                                </div>
                                <h3 style="color: #0f172a" class="text-lg font-bold mb-2">No repositories connected</h3>
                                <p style="color: #64748b" class="text-sm mb-6">Add your first repository to start analyzing issues.</p>
                                <button style="background-color: #059669; color: #ffffff" onclick="GRNDSKPR.Dashboard.openModal('add-repo')" class="px-6 py-3 rounded-lg font-medium hover:bg-emerald-700">
                                    <i class="fa-solid fa-plus mr-2"></i>
                                    Add Your First Repository
                                </button>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($glob['repositories']) && isset($glob['selected_repo'])): ?>
                        <!-- Config Form -->
                        <div class="space-y-8">
                            <!-- Access Mode Section -->
                            <div style="border-color: #e2e8f0; background-color: #f8fafc" class="p-4 rounded-lg border mb-6">
                                <h3 style="color: #0f172a" class="text-sm font-bold mb-3">Access Mode</h3>
                                <p style="color: #64748b" class="text-xs mb-4">Choose how Groundskeeper connects to GitHub</p>

                                <div class="space-y-4">
                                    <!-- Read-only Mode -->
                                    <label style="border-color: #e2e8f0; <?php echo (!isset($glob['user']) || $glob['user']['access_mode'] === 'readonly') ? 'background-color: #ffffff' : 'background-color: #f8fafc'; ?>" class="flex items-start gap-3 p-3 border rounded-lg cursor-pointer hover:bg-white <?php echo (!isset($glob['user']) || $glob['user']['access_mode'] === 'readonly') ? 'ring-2 ring-emerald-500' : ''; ?>">
                                        <input style="border-color: #059669" type="radio" name="access_mode" value="readonly" class="mt-1 h-4 w-4 focus:ring-emerald-500" <?php echo (!isset($glob['user']) || $glob['user']['access_mode'] === 'readonly') ? 'checked' : ''; ?> onchange="toggleConnectionSection('readonly')">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span style="color: #0f172a" class="font-medium">Read-only</span>
                                                <span style="color: #047857; background-color: #d1fae5" class="text-xs px-2 py-0.5 rounded-full font-medium">Recommended</span>
                                            </div>
                                            <p style="color: #475569" class="text-xs">View and analyze issues without GitHub org approval. Uses Personal Access Token or public API (60 requests/hour).</p>
                                        </div>
                                    </label>

                                    <!-- Read/Write Mode -->
                                    <label style="border-color: #e2e8f0; <?php echo (isset($glob['user']) && $glob['user']['access_mode'] === 'readwrite') ? 'background-color: #ffffff' : 'background-color: #f8fafc'; ?>" class="flex items-start gap-3 p-3 border rounded-lg cursor-pointer hover:bg-white <?php echo (isset($glob['user']) && $glob['user']['access_mode'] === 'readwrite') ? 'ring-2 ring-emerald-500' : ''; ?>">
                                        <input style="border-color: #059669" type="radio" name="access_mode" value="readwrite" class="mt-1 h-4 w-4 focus:ring-emerald-500" <?php echo (isset($glob['user']) && $glob['user']['access_mode'] === 'readwrite') ? 'checked' : ''; ?> onchange="toggleConnectionSection('readwrite')">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span style="color: #0f172a" class="font-medium">Read/Write</span>
                                                <span style="color: #b45309; background-color: #fef3c7" class="text-xs px-2 py-0.5 rounded-full font-medium">Requires OAuth</span>
                                            </div>
                                            <p style="color: #475569" class="text-xs">Full access to close issues, add labels, and post comments. Requires org admin approval for OAuth app.</p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Auth Section -->
                            <div style="border-color: #e2e8f0; background-color: #f8fafc" class="p-4 rounded-lg border">
                                <h3 style="color: #0f172a" class="text-sm font-bold mb-2">GitHub Connection</h3>

                                <?php if (isset($glob['user']) && $glob['user']): ?>
                                    <!-- Connected state -->
                                    <div class="flex items-center gap-3 mb-4">
                                        <?php if (!empty($glob['user']['avatar_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($glob['user']['avatar_url']); ?>" alt="Avatar" class="w-10 h-10 rounded-full">
                                        <?php endif; ?>
                                        <div>
                                            <?php if ($glob['user']['access_mode'] === 'readwrite'): ?>
                                                <p style="color: #475569" class="text-sm">Connected via OAuth as <strong>@<?php echo htmlspecialchars($glob['user']['github_username']); ?></strong></p>
                                                <p style="color: #059669" class="text-xs mt-1"><i class="fa-solid fa-check-circle"></i> Full read/write access</p>
                                            <?php else: ?>
                                                <p style="color: #475569" class="text-sm">Connected as <strong>@<?php echo htmlspecialchars($glob['user']['github_username']); ?></strong></p>
                                                <p style="color: #64748b" class="text-xs mt-1"><i class="fa-solid fa-eye"></i> Read-only access</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="flex gap-3">
                                        <?php if ($glob['user']['access_mode'] === 'readwrite'): ?>
                                            <a href="<?php echo BASEURL; ?>auth/github" class="text-sm border px-3 py-1.5 rounded hover:bg-slate-50">Re-authenticate</a>
                                        <?php else: ?>
                                            <button style="color: #475569; border-color: #cbd5e1; background-color: #ffffff" onclick="document.getElementById('pat-form').classList.toggle('hidden')" class="text-sm border px-3 py-1.5 rounded hover:bg-slate-50">Update Token</button>
                                        <?php endif; ?>
                                        <a href="<?php echo BASEURL; ?>settings/disconnect" class="text-sm border px-3 py-1.5 rounded hover:bg-red-50">Disconnect</a>
                                    </div>

                                    <!-- PAT Update Form (hidden by default) -->
                                    <?php if ($glob['user']['access_mode'] === 'readonly'): ?>
                                    <div style="border-color: #e2e8f0" id="pat-form" class="hidden mt-4 pt-4 border-t">
                                        <form method="POST" action="<?php echo BASEURL; ?>settings/update-token">
                                            <label style="color: #334155" class="block text-xs font-medium mb-2">Personal Access Token</label>
                                            <input style="background-color: #ffffff; border-color: #cbd5e1" type="text" name="personal_access_token" class="block w-full px-3 py-2 text-sm rounded-md border" placeholder="ghp_xxxxxxxxxxxx">
                                            <p style="color: #64748b" class="text-xs mt-2">Update your PAT to increase rate limits (5000 req/hr)</p>
                                            <button style="background-color: #059669; color: #ffffff" type="submit" class="mt-3 px-3 py-1.5 rounded text-sm font-medium hover:bg-emerald-700">Update Token</button>
                                        </form>
                                    </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <!-- Not connected state -->
                                    <p style="color: #475569" class="text-sm mb-4">Connect your GitHub account to start analyzing issues.</p>

                                    <!-- Read-only connection form -->
                                    <div id="readonly-connection" class="<?php echo (!isset($glob['user']) || (isset($_POST['access_mode']) && $_POST['access_mode'] === 'readwrite')) ? 'hidden' : ''; ?>">
                                        <form method="POST" action="<?php echo BASEURL; ?>settings/connect-readonly" class="space-y-3">
                                            <div>
                                                <label style="color: #334155" class="block text-xs font-medium mb-2">GitHub Username</label>
                                                <input style="background-color: #ffffff; border-color: #cbd5e1" type="text" name="github_username" required class="block w-full px-3 py-2 text-sm rounded-md border" placeholder="your-github-username">
                                            </div>
                                            <div>
                                                <label style="color: #334155" class="block text-xs font-medium mb-2">Personal Access Token (optional)</label>
                                                <input style="background-color: #ffffff; border-color: #cbd5e1" type="text" name="personal_access_token" class="block w-full px-3 py-2 text-sm rounded-md border" placeholder="ghp_xxxxxxxxxxxx">
                                                <p style="color: #64748b" class="text-xs mt-1">Without token: 60 requests/hour. With token: 5000 requests/hour. <a style="color: #059669" href="https://github.com/settings/tokens/new?scopes=public_repo&description=Groundskeeper" target="_blank" class="hover:underline">Create token</a></p>
                                            </div>
                                            <button style="background-color: #059669; color: #ffffff" type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-700">
                                                <i class="fa-solid fa-eye"></i>
                                                Connect (Read-only)
                                            </button>
                                        </form>
                                    </div>

                                    <!-- Read/Write OAuth connection -->
                                    <div id="readwrite-connection" class="hidden">
                                        <p style="color: #64748b" class="text-xs mb-3">Connect with GitHub OAuth for full read/write access:</p>
                                        <a href="<?php echo BASEURL; ?>auth/github" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-800">
                                            <i class="fa-brands fa-github"></i>
                                            Sign in with GitHub OAuth
                                        </a>
                                        <p style="color: #64748b" class="text-xs mt-3"><i class="fa-solid fa-info-circle"></i> Requires org admin approval for OAuth app</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if ($glob['selected_repo']['last_synced_at']): ?>
                            <!-- Labels Section -->
                            <div>
                                <h3 style="border-color: #e2e8f0; color: #0f172a" class="text-lg font-bold mb-4 border-b pb-2">Label Mapping</h3>

                                <form method="POST" action="<?php echo BASEURL; ?>settings/<?php echo $glob['selected_repo']['id']; ?>/update">
                                    <div class="grid gap-6">
                                        <div>
                                            <label style="color: #334155" class="block text-sm font-medium mb-2">Bug Label</label>
                                            <p style="color: #64748b" class="text-xs mb-2">Which label indicates an issue is a bug?</p>
                                            <input type="text" name="bug_label" value="<?php echo htmlspecialchars($glob['selected_repo']['bug_label']); ?>" class="mt-1 block w-full pl-3 pr-10 py-2 text-base focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm rounded-md border" placeholder="bug">
                                            <p style="color: #64748b" class="text-xs mt-1">Examples: bug, type: bug, defect</p>
                                        </div>

                                        <div>
                                            <label style="color: #334155" class="block text-sm font-medium mb-2">Priority Labels</label>
                                            <p style="color: #64748b" class="text-xs mb-2">Enter labels used to denote priority levels, one per line.</p>

                                            <?php
                                            $priorityLabelsText = '';
                                            if (!empty($glob['selected_repo']['priority_labels'])) {
                                                $priorityLabels = json_decode($glob['selected_repo']['priority_labels'], true);
                                                if (is_array($priorityLabels)) {
                                                    $priorityLabelsText = implode("\n", $priorityLabels);
                                                }
                                            }
                                            ?>

                                            <textarea style="border-color: #cbd5e1; background-color: #ffffff" name="priority_labels_text" rows="4" class="block w-full px-3 py-2 text-sm rounded-md border focus:outline-none focus:ring-emerald-500 focus:border-emerald-500" placeholder="priority: high&#10;priority: medium&#10;priority: low"><?php echo htmlspecialchars($priorityLabelsText); ?></textarea>
                                            <p style="color: #64748b" class="text-xs mt-1">Leave blank if this repository doesn't use priority labels.</p>
                                        </div>

                                        <!-- Areas Section -->
                                        <div style="border-color: #e2e8f0" class="border-t pt-4">
                                            <label style="color: #334155" class="block text-sm font-medium mb-2">Functional Areas</label>
                                            <p style="color: #64748b" class="text-xs mb-3">Areas are auto-detected on first analysis using AI. They help categorize issues by codebase section.</p>

                                            <?php if (!empty($glob['areas'])): ?>
                                                <div style="background-color: #f8fafc" class="rounded-md p-3 mb-3">
                                                    <ul style="color: #334155" class="space-y-1 text-sm">
                                                        <?php foreach ($glob['areas'] as $area): ?>
                                                            <li class="flex items-center">
                                                                <i style="color: #10b981" class="fa-solid fa-circle text-xs mr-2"></i>
                                                                <?php echo htmlspecialchars($area['name']); ?>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                </div>
                                                <form method="POST" action="<?php echo BASEURL; ?>settings/<?php echo $glob['selected_repo']['id']; ?>/reset-areas" class="inline">
                                                    <button style="color: #dc2626" type="submit" class="text-sm hover:text-red-800 font-medium" onclick="return confirm('Are you sure you want to reset areas? This will clear all area categorizations and re-discover areas on next analysis.')">
                                                        <i class="fa-solid fa-rotate-left mr-1"></i> Reset Areas
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <div style="color: #475569; background-color: #f8fafc" class="rounded-md p-3 text-sm">
                                                    <i style="color: #94a3b8" class="fa-solid fa-info-circle mr-1"></i>
                                                    No areas detected yet. Run analysis to discover areas automatically.
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="pt-4 flex justify-end">
                                        <button style="background-color: #059669; color: #ffffff" type="submit" class="px-4 py-2 rounded-lg text-sm font-bold shadow-sm hover:bg-emerald-700">Save Changes</button>
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
        <div style="background-color: #0f172a" class="fixed inset-0 bg-opacity-75 transition-opacity"></div>

        <!-- Fixed wrapper for centering -->
        <div class="fixed inset-0 z-10 overflow-y-auto" onclick="GRNDSKPR.Dashboard.closeModal('add-repo')">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">

                <!-- Modal Panel -->
                <div style="background-color: #ffffff" class="relative transform overflow-hidden rounded-lg text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg" onclick="event.stopPropagation()">
                    
                    <!-- Modal Header -->
                    <div style="border-color: #f1f5f9; background-color: #ffffff" class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b flex justify-between items-center">
                        <div>
                            <h3 style="color: #0f172a" class="text-lg leading-6 font-medium" id="modal-title">Connect New Repository</h3>
                            <p style="color: #64748b" class="text-sm mt-1">Groundskeeper will scan this repo for issues.</p>
                        </div>
                        <button style="color: #94a3b8" onclick="GRNDSKPR.Dashboard.closeModal('add-repo')" class="hover:text-slate-500">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>

                    <!-- Content -->
                    <form method="POST" action="<?php echo BASEURL; ?>settings/add-repo">
                        <div class="p-6 space-y-6">
                            <div>
                                <label style="color: #334155" class="block text-sm font-medium mb-2">Repository Slug</label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i style="color: #94a3b8" class="fa-brands fa-github"></i>
                                    </div>
                                    <input style="border-color: #cbd5e1" type="text" name="repo_slug" required class="focus:ring-emerald-500 focus:border-emerald-500 block w-full pl-10 sm:text-sm rounded-md border py-2" placeholder="owner/repo-name">
                                </div>
                                <p style="color: #64748b" class="mt-2 text-xs">Format: owner/repo-name (e.g., woocommerce/woocommerce)</p>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div style="border-color: #e2e8f0; background-color: #f8fafc" class="px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t">
                            <button style="border-color: transparent; background-color: #059669; color: #ffffff" type="submit" class="w-full inline-flex justify-center rounded-md border shadow-sm px-4 py-2 text-base font-medium hover:bg-emerald-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                Load Repo
                            </button>
                            <button style="border-color: #cbd5e1; background-color: #ffffff; color: #334155" type="button" onclick="GRNDSKPR.Dashboard.closeModal('add-repo')" class="mt-3 w-full inline-flex justify-center rounded-md border shadow-sm px-4 py-2 text-base font-medium hover:bg-slate-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
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
        <div style="background-color: #0f172a" class="fixed inset-0 bg-opacity-75 transition-opacity"></div>

        <!-- Fixed wrapper for centering -->
        <div class="fixed inset-0 z-10 overflow-y-auto" onclick="GRNDSKPR.Dashboard.closeModal('duplicates')">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">

                <!-- Modal Panel -->
                <div style="background-color: #ffffff" class="relative transform overflow-hidden rounded-lg text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-6xl" onclick="event.stopPropagation()">
                    
                    <!-- Modal Header -->
                    <div style="border-color: #f1f5f9; background-color: #ffffff" class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b flex justify-between items-center">
                        <div>
                            <h3 style="color: #0f172a" class="text-lg leading-6 font-medium" id="modal-title">Review Likely Duplicates</h3>
                            <p style="color: #64748b" class="text-sm mt-1">Found <?php echo count($glob['duplicates']); ?> groups of issues that appear semantically similar (≥85% similarity).</p>
                        </div>
                        <button style="color: #94a3b8" onclick="GRNDSKPR.Dashboard.closeModal('duplicates')" class="hover:text-slate-500">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>

                    <!-- Bulk Actions Toolbar -->
                    <div style="border-color: #e2e8f0; background-color: #f8fafc" class="px-4 py-3 flex items-center justify-between border-b">
                        <div class="flex items-center gap-4">
                            <label class="inline-flex items-center">
                                <input style="border-color: #059669" type="checkbox" id="select-all-duplicates" onchange="toggleSelectAll('duplicates')" class="form-checkbox h-4 w-4 rounded">
                                <span style="color: #475569" class="ml-2 text-sm font-medium">Select All</span>
                            </label>
                            <span style="background-color: #cbd5e1" class="h-4 w-px mx-2"></span>
                            <span style="color: #64748b" class="text-sm" id="selected-count-duplicates">0 selected</span>
                        </div>
                        <div class="flex gap-2">
                            <?php if (!isset($glob['user']) || $glob['user']['access_mode'] !== 'readwrite'): ?>
                            <div class="tooltip-wrapper">
                                <button style="background-color: #ffffff; border-color: #cbd5e1; color: #475569" disabled class="border px-3 py-1.5 rounded text-sm font-medium shadow-sm opacity-50 cursor-not-allowed">
                                    Dismiss
                                </button>
                                <span class="tooltip-text">Disabled in read-only mode</span>
                            </div>
                            <div class="tooltip-wrapper">
                                <button style="background-color: #059669; color: #ffffff" disabled class="px-3 py-1.5 rounded text-sm font-medium shadow-sm opacity-50 cursor-not-allowed">
                                    Merge & Close Selected
                                </button>
                                <span class="tooltip-text">Disabled in read-only mode</span>
                            </div>
                            <?php else: ?>
                            <button style="background-color: #ffffff; border-color: #cbd5e1; color: #475569" class="border hover:text-red-600 hover:border-red-300 px-3 py-1.5 rounded text-sm font-medium shadow-sm transition">
                                Dismiss
                            </button>
                            <button style="background-color: #059669; color: #ffffff" class="hover:bg-emerald-700 px-3 py-1.5 rounded text-sm font-medium shadow-sm transition">
                                Merge & Close Selected
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- List Content -->
                    <div class="max-h-[60vh] overflow-y-auto">
                        <?php if (!empty($glob['duplicates'])): ?>
                        <table style="border-color: #e2e8f0" class="min-w-full divide-y">
                            <thead style="background-color: #f8fafc" class="sticky top-0">
                                <tr>
                                    <th style="color: #64748b" scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider w-10"></th>
                                    <th style="color: #64748b" scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Primary Issue</th>
                                    <th style="color: #64748b" scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Similar Issues</th>
                                    <th style="color: #64748b" scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Similarity</th>
                                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody style="border-color: #e2e8f0; background-color: #ffffff" id="tbody-duplicates" class="divide-y"></tbody>
                        </table>
                        <?php else: ?>
                        <div style="color: #64748b" class="p-12 text-center">
                            <i style="color: #cbd5e1" class="fa-solid fa-inbox text-4xl mb-4"></i>
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
        <div style="background-color: #0f172a" class="fixed inset-0 bg-opacity-75 transition-opacity"></div>

        <!-- Fixed wrapper for centering -->
        <div class="fixed inset-0 z-10 overflow-y-auto" onclick="GRNDSKPR.Dashboard.closeModal('high-signal')">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">

                <!-- Modal Panel -->
                <div style="background-color: #ffffff" class="relative transform overflow-hidden rounded-lg text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-6xl" onclick="event.stopPropagation()">
                    
                    <!-- Modal Header -->
                    <div style="border-color: #f1f5f9; background-color: #ffffff" class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b flex justify-between items-center">
                        <div>
                            <h3 style="color: #0f172a" class="text-lg leading-6 font-medium" id="modal-title">High Signal Issues Queue</h3>
                            <p style="color: #64748b" class="text-sm mt-1"><?php echo count($glob['high_signal_issues']); ?> valuable, actionable issues identified by AI analysis.</p>
                        </div>
                        <button style="color: #94a3b8" onclick="GRNDSKPR.Dashboard.closeModal('high-signal')" class="hover:text-slate-500">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>

                    <!-- Bulk Actions Toolbar -->
                    <div style="border-color: #e2e8f0; background-color: #f8fafc" class="px-4 py-3 flex items-center justify-between border-b">
                        <div class="flex items-center gap-4">
                            <label class="inline-flex items-center">
                                <input style="border-color: #059669" type="checkbox" id="select-all-high-signal" onchange="toggleSelectAll('high-signal')" class="form-checkbox h-4 w-4 rounded">
                                <span style="color: #475569" class="ml-2 text-sm font-medium">Select All</span>
                            </label>
                            <span style="background-color: #cbd5e1" class="h-4 w-px mx-2"></span>
                            <span style="color: #64748b" class="text-sm" id="selected-count-high-signal">0 selected</span>
                        </div>
                        <div class="flex gap-2">
                             <button style="background-color: #059669; border-color: #059669; color: #ffffff" onclick="GRNDSKPR.Dashboard.copySelectedIssueUrls('high-signal')" class="border hover:bg-emerald-700 hover:border-emerald-700 px-3 py-1.5 rounded text-sm font-medium shadow-sm transition">
                                <i class="fa-solid fa-copy mr-1.5"></i>
                                Copy Issue URLs
                            </button>
                        </div>
                    </div>

                    <!-- List Content -->
                    <div class="max-h-[60vh] overflow-y-auto">
                        <?php if (!empty($glob['high_signal_issues'])): ?>
                        <table style="border-color: #e2e8f0" class="min-w-full divide-y">
                            <thead style="background-color: #f8fafc" class="sticky top-0">
                                <tr>
                                    <th style="color: #64748b" scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider w-10"></th>
                                    <th style="color: #64748b" scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Priority</th>
                                    <th style="color: #64748b" scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Issue</th>
                                    <th style="color: #64748b" scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Engagement</th>
                                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody style="border-color: #e2e8f0; background-color: #ffffff" id="tbody-high-signal" class="divide-y"></tbody>
                        </table>
                        <?php else: ?>
                        <div style="color: #64748b" class="p-12 text-center">
                            <i style="color: #cbd5e1" class="fa-solid fa-inbox text-4xl mb-4"></i>
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
        <div style="background-color: #0f172a" class="fixed inset-0 bg-opacity-75 transition-opacity"></div>

        <!-- Fixed wrapper for centering -->
        <div class="fixed inset-0 z-10 overflow-y-auto" onclick="GRNDSKPR.Dashboard.closeModal('cleanup')">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">

                <!-- Modal Panel -->
                <div style="background-color: #ffffff" class="relative transform overflow-hidden rounded-lg text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-6xl" onclick="event.stopPropagation()">
                    
                    <!-- Modal Header -->
                    <div style="border-color: #f1f5f9; background-color: #ffffff" class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b flex justify-between items-center">
                        <div>
                            <h3 style="color: #0f172a" class="text-lg leading-6 font-medium" id="modal-title">Review Cleanup Candidates</h3>
                            <p style="color: #64748b" class="text-sm mt-1">Found <?php echo count($glob['cleanup_candidates']); ?> issues identified as candidates for closure by AI analysis.</p>
                        </div>
                        <button style="color: #94a3b8" onclick="GRNDSKPR.Dashboard.closeModal('cleanup')" class="hover:text-slate-500">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>

                    <!-- Bulk Actions Toolbar -->
                    <div style="border-color: #e2e8f0; background-color: #f8fafc" class="px-4 py-3 flex items-center justify-between border-b">
                        <div class="flex items-center gap-4">
                            <label class="inline-flex items-center">
                                <input style="border-color: #059669" type="checkbox" id="select-all-cleanup" onchange="toggleSelectAll('cleanup')" class="form-checkbox h-4 w-4 rounded">
                                <span style="color: #475569" class="ml-2 text-sm font-medium">Select All</span>
                            </label>
                            <span style="background-color: #cbd5e1" class="h-4 w-px mx-2"></span>
                            <span style="color: #64748b" class="text-sm" id="selected-count-cleanup">0 selected</span>
                        </div>
                        <div class="flex gap-2">
                            <?php if (!isset($glob['user']) || $glob['user']['access_mode'] !== 'readwrite'): ?>
                            <div class="tooltip-wrapper">
                                <button style="background-color: #ffffff; border-color: #cbd5e1; color: #475569" disabled class="border px-3 py-1.5 rounded text-sm font-medium shadow-sm opacity-50 cursor-not-allowed">
                                    Ignore
                                </button>
                                <span class="tooltip-text">Disabled in read-only mode</span>
                            </div>
                            <div class="tooltip-wrapper">
                                <button style="background-color: #059669; color: #ffffff" disabled class="px-3 py-1.5 rounded text-sm font-medium shadow-sm opacity-50 cursor-not-allowed">
                                    Close Selected
                                </button>
                                <span class="tooltip-text">Disabled in read-only mode</span>
                            </div>
                            <?php else: ?>
                            <button style="background-color: #ffffff; border-color: #cbd5e1; color: #475569" class="border hover:text-emerald-600 hover:border-emerald-300 px-3 py-1.5 rounded text-sm font-medium shadow-sm transition">
                                Ignore
                            </button>
                            <button style="background-color: #059669; color: #ffffff" class="hover:bg-emerald-700 px-3 py-1.5 rounded text-sm font-medium shadow-sm transition">
                                Close Selected
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- List Content -->
                    <div class="max-h-[60vh] overflow-y-auto">
                        <?php if (!empty($glob['cleanup_candidates'])): ?>
                        <table style="border-color: #e2e8f0" class="min-w-full divide-y">
                            <thead style="background-color: #f8fafc" class="sticky top-0">
                                <tr>
                                    <th style="color: #64748b" scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider w-10"></th>
                                    <th style="color: #64748b" scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Issue</th>
                                    <th style="color: #64748b" scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Labels</th>
                                    <th style="color: #64748b" scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Last Activity</th>
                                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody style="border-color: #e2e8f0; background-color: #ffffff" id="tbody-cleanup" class="divide-y"></tbody>
                        </table>
                        <?php else: ?>
                        <div style="color: #64748b" class="p-12 text-center">
                            <i style="color: #cbd5e1" class="fa-solid fa-inbox text-4xl mb-4"></i>
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
        <div style="background-color: #0f172a" class="fixed inset-0 bg-opacity-75 transition-opacity"></div>

        <!-- Fixed wrapper for centering -->
        <div class="fixed inset-0 z-10 overflow-y-auto" onclick="GRNDSKPR.Dashboard.closeModal('missing-info')">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">

                <!-- Modal Panel -->
                <div style="background-color: #ffffff" class="relative transform overflow-hidden rounded-lg text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-6xl" onclick="event.stopPropagation()">
                    
                    <!-- Modal Header -->
                    <div style="border-color: #f1f5f9; background-color: #ffffff" class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b flex justify-between items-center">
                        <div>
                            <h3 style="color: #0f172a" class="text-lg leading-6 font-medium" id="modal-title">Review Issues Missing Context</h3>
                            <p style="color: #64748b" class="text-sm mt-1">Found <?php echo count($glob['missing_info_issues']); ?> issues missing critical information identified by AI analysis.</p>
                        </div>
                        <button style="color: #94a3b8" onclick="GRNDSKPR.Dashboard.closeModal('missing-info')" class="hover:text-slate-500">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>

                    <!-- Bulk Actions Toolbar -->
                    <div style="border-color: #e2e8f0; background-color: #f8fafc" class="px-4 py-3 flex items-center justify-between border-b">
                        <div class="flex items-center gap-4">
                            <label class="inline-flex items-center">
                                <input style="border-color: #059669" type="checkbox" id="select-all-missing-info" onchange="toggleSelectAll('missing-info')" class="form-checkbox h-4 w-4 rounded">
                                <span style="color: #475569" class="ml-2 text-sm font-medium">Select All</span>
                            </label>
                            <span style="background-color: #cbd5e1" class="h-4 w-px mx-2"></span>
                            <span style="color: #64748b" class="text-sm" id="selected-count-missing-info">0 selected</span>
                        </div>
                        <div class="flex gap-2">
                            <?php if (!isset($glob['user']) || $glob['user']['access_mode'] !== 'readwrite'): ?>
                            <div class="tooltip-wrapper">
                                <button style="background-color: #ffffff; border-color: #cbd5e1; color: #475569" disabled class="border px-3 py-1.5 rounded text-sm font-medium shadow-sm opacity-50 cursor-not-allowed">
                                    Request Info (AI Draft)
                                </button>
                                <span class="tooltip-text">Disabled in read-only mode</span>
                            </div>
                            <?php else: ?>
                            <button style="background-color: #ffffff; border-color: #cbd5e1; color: #475569" class="border hover:text-emerald-600 hover:border-emerald-300 px-3 py-1.5 rounded text-sm font-medium shadow-sm transition">
                                Request Info (AI Draft)
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- List Content -->
                    <div class="max-h-[60vh] overflow-y-auto">
                        <?php if (!empty($glob['missing_info_issues'])): ?>
                        <table style="border-color: #e2e8f0" class="min-w-full divide-y">
                            <thead style="background-color: #f8fafc" class="sticky top-0">
                                <tr>
                                    <th style="color: #64748b" scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider w-10"></th>
                                    <th style="color: #64748b" scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Issue</th>
                                    <th style="color: #64748b" scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Missing Elements</th>
                                    <th style="color: #64748b" scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Labels</th>
                                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody style="border-color: #e2e8f0; background-color: #ffffff" id="tbody-missing-info" class="divide-y"></tbody>
                        </table>
                        <?php else: ?>
                        <div style="color: #64748b" class="p-12 text-center">
                            <i style="color: #cbd5e1" class="fa-solid fa-inbox text-4xl mb-4"></i>
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
        <div style="background-color: #0f172a" class="fixed inset-0 bg-opacity-75 transition-opacity"></div>

        <!-- Fixed wrapper for centering -->
        <div class="fixed inset-0 z-10 overflow-y-auto" onclick="GRNDSKPR.Dashboard.closeModal('suggestions')">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">

                <!-- Modal Panel -->
                <div style="background-color: #ffffff" class="relative transform overflow-hidden rounded-lg text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-6xl" onclick="event.stopPropagation()">
                    
                    <!-- Modal Header -->
                    <div style="border-color: #f1f5f9; background-color: #ffffff" class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b flex justify-between items-center">
                        <div>
                            <h3 style="color: #0f172a" class="text-lg leading-6 font-medium" id="modal-title">Review Label Suggestions</h3>
                            <p style="color: #64748b" class="text-sm mt-1">Found <?php echo count($glob['label_suggestions']); ?> issues with AI-recommended labels from the repository.</p>
                        </div>
                        <button style="color: #94a3b8" onclick="GRNDSKPR.Dashboard.closeModal('suggestions')" class="hover:text-slate-500">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>

                    <!-- Bulk Actions Toolbar -->
                    <div style="border-color: #e2e8f0; background-color: #f8fafc" class="px-4 py-3 flex items-center justify-between border-b">
                        <div class="flex items-center gap-4">
                            <label class="inline-flex items-center">
                                <input style="border-color: #059669" type="checkbox" id="select-all-suggestions" onchange="toggleSelectAll('suggestions')" class="form-checkbox h-4 w-4 rounded">
                                <span style="color: #475569" class="ml-2 text-sm font-medium">Select All</span>
                            </label>
                            <span style="background-color: #cbd5e1" class="h-4 w-px mx-2"></span>
                            <span style="color: #64748b" class="text-sm" id="selected-count-suggestions">0 selected</span>
                        </div>
                        <div class="flex gap-2">
                            <?php if (!isset($glob['user']) || $glob['user']['access_mode'] !== 'readwrite'): ?>
                            <div class="tooltip-wrapper">
                                <button style="background-color: #ffffff; border-color: #cbd5e1; color: #475569" disabled class="border px-3 py-1.5 rounded text-sm font-medium shadow-sm opacity-50 cursor-not-allowed">
                                    Dismiss
                                </button>
                                <span class="tooltip-text">Disabled in read-only mode</span>
                            </div>
                            <div class="tooltip-wrapper">
                                <button style="background-color: #059669; color: #ffffff" disabled class="px-3 py-1.5 rounded text-sm font-medium shadow-sm opacity-50 cursor-not-allowed">
                                    Apply Selected
                                </button>
                                <span class="tooltip-text">Disabled in read-only mode</span>
                            </div>
                            <?php else: ?>
                            <button style="background-color: #ffffff; border-color: #cbd5e1; color: #475569" class="border hover:text-red-600 hover:border-red-300 px-3 py-1.5 rounded text-sm font-medium shadow-sm transition">
                                Dismiss
                            </button>
                            <button style="background-color: #059669; color: #ffffff" class="hover:bg-emerald-700 px-3 py-1.5 rounded text-sm font-medium shadow-sm transition">
                                Apply Selected
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- List Content -->
                    <div class="max-h-[60vh] overflow-y-auto">
                        <table style="border-color: #e2e8f0" class="min-w-full divide-y">
                            <thead style="background-color: #f8fafc" class="sticky top-0">
                                <tr>
                                    <th style="color: #64748b" scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider w-10"></th>
                                    <th style="color: #64748b" scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Issue</th>
                                    <th style="color: #64748b" scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Suggested Changes</th>
                                    <th style="color: #64748b" scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Reasoning</th>
                                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody style="border-color: #e2e8f0; background-color: #ffffff" id="tbody-suggestions" class="divide-y"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
	<script type="text/javascript" src="<?php echo BASEURL; ?>js/groundskeeper-utility.js"></script>
	<script type="text/javascript" src="<?php echo BASEURL; ?>js/groundskeeper-core.js"></script>
    <script>
        // Initialize dashboard data
        window.GRNDSKPR_DATA = {
            stats: {
                total: <?php echo count($glob['issues']); ?>,
                highSignal: <?php echo count($glob['high_signal_issues']); ?>,
                duplicates: <?php echo count($glob['duplicates']); ?>,
                cleanup: <?php echo count($glob['cleanup_candidates']); ?>,
                missing: <?php echo count($glob['missing_info_issues']); ?>,
                suggestions: <?php echo count($glob['label_suggestions']); ?>
            },
            areaCounts: {
                highSignal: {},
                duplicates: {},
                cleanup: {},
                missing: {},
                suggestions: {}
            },
            issues: {
                highSignal: <?php echo json_encode($glob['high_signal_issues'], JSON_HEX_TAG | JSON_HEX_AMP); ?>,
                duplicates: <?php echo json_encode($glob['duplicates'], JSON_HEX_TAG | JSON_HEX_AMP); ?>,
                cleanup: <?php echo json_encode($glob['cleanup_candidates'], JSON_HEX_TAG | JSON_HEX_AMP); ?>,
                missing: <?php echo json_encode($glob['missing_info_issues'], JSON_HEX_TAG | JSON_HEX_AMP); ?>,
                suggestions: <?php echo json_encode($glob['label_suggestions'], JSON_HEX_TAG | JSON_HEX_AMP); ?>
            }
        };

        // Populate area counts
        <?php
        // Build area counts for each category
        foreach ($glob['high_signal_issues'] as $issue) {
            $areaId = $issue['area_id'] ?? 0;
            echo "GRNDSKPR_DATA.areaCounts.highSignal[$areaId] = (GRNDSKPR_DATA.areaCounts.highSignal[$areaId] || 0) + 1;\n        ";
        }
        foreach ($glob['cleanup_candidates'] as $issue) {
            $areaId = $issue['area_id'] ?? 0;
            echo "GRNDSKPR_DATA.areaCounts.cleanup[$areaId] = (GRNDSKPR_DATA.areaCounts.cleanup[$areaId] || 0) + 1;\n        ";
        }
        foreach ($glob['missing_info_issues'] as $issue) {
            $areaId = $issue['area_id'] ?? 0;
            echo "GRNDSKPR_DATA.areaCounts.missing[$areaId] = (GRNDSKPR_DATA.areaCounts.missing[$areaId] || 0) + 1;\n        ";
        }
        foreach ($glob['label_suggestions'] as $issue) {
            $areaId = $issue['area_id'] ?? 0;
            echo "GRNDSKPR_DATA.areaCounts.suggestions[$areaId] = (GRNDSKPR_DATA.areaCounts.suggestions[$areaId] || 0) + 1;\n        ";
        }
        // Duplicates are special - they have an array of issues
        foreach ($glob['duplicates'] as $duplicate) {
            if (!empty($duplicate['issues'])) {
                foreach ($duplicate['issues'] as $issue) {
                    $areaId = $issue['area_id'] ?? 0;
                    echo "GRNDSKPR_DATA.areaCounts.duplicates[$areaId] = (GRNDSKPR_DATA.areaCounts.duplicates[$areaId] || 0) + 1;\n        ";
                    break; // Only count the duplicate group once per area
                }
            }
        }
        ?>

        // Handle session messages
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($_SESSION['message'])): ?>
                GRNDSKPR.Dashboard.showToast('<?php echo addslashes($_SESSION['message']); ?>');
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                GRNDSKPR.Dashboard.showToast('<?php echo addslashes($_SESSION['success']); ?>');
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                GRNDSKPR.Dashboard.showToast('<?php echo addslashes($_SESSION['error']); ?>', true);
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
        });
    </script>
    <script type="text/javascript" src="<?php echo BASEURL; ?>js/groundskeeper-dashboard.js"></script>

    <!-- Toast Notification -->
    <div id="toast" class="alert"></div>

    <!-- Audit Loading Overlay -->
    <div style="background-color: #000000" id="audit-loading" class="hidden fixed inset-0 bg-opacity-50 items-center justify-center z-50">
        <div style="background-color: #ffffff" class="rounded-lg p-8 shadow-xl text-center">
            <div style="border-color: #059669" class="animate-spin rounded-full h-16 w-16 border-b-4 mx-auto mb-4"></div>
            <h3 style="color: #0f172a" class="text-lg font-semibold mb-2">Running Audit...</h3>
            <p style="color: #475569" class="text-sm">Importing issues from GitHub</p>
        </div>
    </div>

    <!-- Analyze Loading Overlay -->
    <div style="background-color: #000000" id="analyze-loading" class="hidden fixed inset-0 bg-opacity-50 items-center justify-center z-50">
        <div style="background-color: #ffffff" class="rounded-lg p-8 shadow-xl text-center max-w-md">
            <div style="border-color: #2563eb" class="animate-spin rounded-full h-16 w-16 border-b-4 mx-auto mb-4"></div>
            <h3 style="color: #0f172a" class="text-lg font-semibold mb-2">Analyzing Issues...</h3>
            <p style="color: #475569" class="text-sm mb-2">Processing bug reports with AI</p>
            <p style="color: #64748b" class="text-xs">This can take 5-15 minutes for large repos. Time to grab a coffee! ☕</p>
        </div>
    </div>

    <!-- Area Approval Modal -->
    <?php if (isset($glob['pending_areas']) && !empty($glob['pending_areas'])): ?>
    <div style="background-color: #000000" class="fixed inset-0 bg-opacity-50 flex items-center justify-center z-50">
        <div style="background-color: #ffffff" class="rounded-lg p-8 shadow-xl max-w-2xl w-full mx-4">
            <h3 style="color: #0f172a" class="text-lg font-semibold mb-4">
                <i style="color: #2563eb" class="fa-solid fa-sparkles mr-2"></i>
                Review Discovered Areas
            </h3>
            <p style="color: #475569" class="text-sm mb-4">
                The following functional areas were discovered by analyzing your issues.
                You can edit, add, or remove areas before saving (one per line).
            </p>

            <form method="POST" action="<?php echo BASEURL; ?>analyze/approve-areas">
                <textarea style="border-color: #cbd5e1"
                    name="areas"
                    rows="12"
                    class="w-full border rounded-md p-3 mb-4 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Enter areas (one per line)"
                ><?php echo htmlspecialchars(implode("\n", $glob['pending_areas']['areas'])); ?></textarea>

                <div class="flex justify-end gap-3">
                    <a href="<?php echo BASEURL; ?>" class="px-4 py-2 hover:text-slate-800 rounded-md hover:bg-slate-100">
                        Cancel
                    </a>
                    <button style="background-color: #059669; color: #ffffff" type="submit" class="px-4 py-2 rounded-md hover:bg-emerald-700 font-medium">
                        <i class="fa-solid fa-check mr-1"></i>
                        Approve & Save Areas
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Resume/Restart Analysis Modal -->
    <div style="background-color: #000000" id="resume-modal" class="hidden fixed inset-0 bg-opacity-50 flex items-center justify-center z-50">
        <div style="background-color: #ffffff" class="rounded-lg p-8 shadow-xl max-w-md w-full mx-4">
            <h3 style="color: #0f172a" class="text-lg font-semibold mb-4">
                <i style="color: #2563eb" class="fa-solid fa-pause-circle mr-2"></i>
                Incomplete Analysis Found
            </h3>
            <p style="color: #475569" class="text-sm mb-4">
                You have an incomplete analysis with <span id="resume-processed" class="font-semibold"></span> of <span id="resume-total" class="font-semibold"></span> issues processed.
            </p>
            <p style="color: #475569" class="text-sm mb-6">
                Would you like to continue from where you left off or start over?
            </p>

            <div class="flex gap-3">
                <button style="color: #ffffff; background-color: #475569" id="restart-btn" class="flex-1 px-4 py-2 rounded-md hover:bg-slate-700 font-medium">
                    <i class="fa-solid fa-rotate-right mr-1"></i>
                    Start Over
                </button>
                <button style="background-color: #2563eb; color: #ffffff" id="resume-btn" class="flex-1 px-4 py-2 rounded-md hover:bg-blue-700 font-medium">
                    <i class="fa-solid fa-play mr-1"></i>
                    Continue
                </button>
            </div>
        </div>
    </div>

    <!-- Unified Progress Modal -->
    <div style="background-color: #000000" id="progress-modal" class="hidden fixed inset-0 bg-opacity-50 flex items-center justify-center z-50">
        <div style="background-color: #ffffff" class="rounded-lg p-8 shadow-xl max-w-md w-full mx-4">
            <h3 style="color: #0f172a" class="text-lg font-semibold mb-4">
                <i style="color: #2563eb" id="progress-icon" class="fa-solid fa-arrows-rotate fa-spin mr-2"></i>
                <span id="progress-title">Syncing & Analyzing</span>
            </h3>
            <p style="color: #475569" id="progress-description" class="text-sm mb-4">
                Processing repository changes. This may take several minutes...
            </p>

            <!-- Step Indicators -->
            <div class="mb-6 flex items-center justify-between">
                <div class="flex flex-col items-center flex-1">
                    <div style="background-color: #2563eb; color: #ffffff" id="step-sync-icon" class="w-8 h-8 rounded-full flex items-center justify-center text-sm mb-1">
                        <i class="fa-solid fa-sync fa-spin"></i>
                    </div>
                    <span style="color: #475569" class="text-xs">Sync</span>
                </div>
                <div style="background-color: #e2e8f0" class="flex-1 h-0.5 mx-2" id="step-line-1"></div>
                <div class="flex flex-col items-center flex-1">
                    <div style="background-color: #e2e8f0; color: #94a3b8" id="step-areas-icon" class="w-8 h-8 rounded-full flex items-center justify-center text-sm mb-1">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <span style="color: #475569" class="text-xs">Areas</span>
                </div>
                <div style="background-color: #e2e8f0" class="flex-1 h-0.5 mx-2" id="step-line-2"></div>
                <div class="flex flex-col items-center flex-1">
                    <div style="background-color: #e2e8f0; color: #94a3b8" id="step-analyze-icon" class="w-8 h-8 rounded-full flex items-center justify-center text-sm mb-1">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <span style="color: #475569" class="text-xs">Analyze</span>
                </div>
            </div>

            <div class="mb-4">
                <div style="color: #334155" class="flex justify-between text-sm mb-2">
                    <span id="progress-text">Starting sync...</span>
                    <span id="progress-percent">0%</span>
                </div>
                <div style="background-color: #e2e8f0" class="w-full rounded-full h-3">
                    <div id="progress-bar" class="h-3 rounded-full transition-all duration-300" style="width: 0%; background-color: #2563eb"></div>
                </div>
            </div>

            <p style="color: #64748b" id="progress-details" class="text-xs mb-4">
                <span id="progress-count">0</span> of <span id="progress-total">0</span> issues processed
            </p>

            <div style="border-color: #fecaca; background-color: #fef2f2" id="progress-error" class="hidden border rounded-md p-3 mb-4">
                <p style="color: #991b1b" class="text-sm"></p>
            </div>
        </div>
    </div>

    <script>
    // Unified sync & analysis progress handling
    const BASEURL = '<?php echo BASEURL; ?>';
    let currentJobId = null;
    let currentStep = 'sync'; // sync, areas, analyze

    // Sync form submission
    document.getElementById('sync-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        startSync(<?php echo $glob['selected_repo']['id'] ?? 0; ?>);
    });

    function startSync(repoId) {
        fetch(BASEURL + 'sync/run/' + repoId, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                alert('Failed to start sync: ' + (data.error || 'Unknown error'));
                return;
            }

            if (data.has_existing_job) {
                showResumeModal(data.job_id, data.status, data.processed, data.total);
            } else {
                currentJobId = data.job_id;
                currentStep = 'sync';
                showProgressModal();
                processSyncStep();
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('Failed to start sync');
        });
    }

    function showProgressModal() {
        resetStepIndicators();
        document.getElementById('progress-modal').classList.remove('hidden');
    }

    // Helper functions for step indicators
    function setStepIcon(stepId, state, icon, spinning = false) {
        const baseClass = 'w-8 h-8 rounded-full flex items-center justify-center text-sm mb-1';
        const stateStyles = {
            inactive: 'background-color: #e2e8f0; color: #94a3b8',
            active: 'background-color: #2563eb; color: #ffffff',
            complete: 'background-color: #059669; color: #ffffff'
        };
        const el = document.getElementById(`step-${stepId}-icon`);
        el.className = baseClass;
        el.setAttribute('style', stateStyles[state]);
        el.innerHTML = `<i class="fa-solid fa-${icon}${spinning ? ' fa-spin' : ''}"></i>`;
    }

    function setStepLine(lineId, complete) {
        const el = document.getElementById(`step-line-${lineId}`);
        el.className = 'flex-1 h-0.5 mx-2';
        el.setAttribute('style', complete ? 'background-color: #059669' : 'background-color: #e2e8f0');
    }

    function resetStepIndicators() {
        setStepIcon('sync', 'inactive', 'sync');
        setStepIcon('areas', 'inactive', 'layer-group');
        setStepIcon('analyze', 'inactive', 'chart-line');
        setStepLine(1, false);
        setStepLine(2, false);
    }

    function updateStepIndicator(step) {
        if (step === 'sync') {
            setStepIcon('sync', 'active', 'sync');
        } else if (step === 'areas') {
            setStepIcon('sync', 'complete', 'check');
            setStepLine(1, true);
            setStepIcon('areas', 'active', 'layer-group');
        } else if (step === 'analyze') {
            setStepIcon('sync', 'complete', 'check');
            setStepLine(1, true);
            setStepIcon('areas', 'complete', 'check');
            setStepLine(2, true);
            setStepIcon('analyze', 'active', 'chart-line');
        }
    }

    function processSyncStep() {
        updateStepIndicator('sync');
        document.getElementById('progress-text').textContent = 'Syncing issues from GitHub...';
        document.getElementById('progress-percent').textContent = '33%';
        document.getElementById('progress-bar').style.width = '33%';
        document.getElementById('progress-details').textContent = 'Comparing local and remote issues...';

        fetch(BASEURL + 'sync/process-sync/' + currentJobId, {
            method: 'POST'
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                showError(data.error || 'Sync failed');
                return;
            }

            // Show sync stats
            const stats = data.stats;
            const parts = [];
            if (stats.added > 0) parts.push(`${stats.added} new`);
            if (stats.updated > 0) parts.push(`${stats.updated} updated`);
            if (stats.removed > 0) parts.push(`${stats.removed} removed`);
            if (stats.unchanged > 0) parts.push(`${stats.unchanged} unchanged`);

            document.getElementById('progress-details').textContent =
                `Synced ${stats.total} open issues: ${parts.join(', ')}`;

            // Move to area check step
            currentStep = 'areas';
            setTimeout(() => processAreasStep(), 1000);
        })
        .catch(err => {
            console.error('Error:', err);
            showError('Network error during sync. Retrying...');
            setTimeout(() => processSyncStep(), 5000);
        });
    }

    function processAreasStep() {
        updateStepIndicator('areas');
        document.getElementById('progress-text').textContent = 'Checking areas...';
        document.getElementById('progress-percent').textContent = '66%';
        document.getElementById('progress-bar').style.width = '66%';

        fetch(BASEURL + 'sync/check-areas/' + currentJobId, {
            method: 'POST'
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                showError(data.error || 'Area check failed');
                return;
            }

            if (data.needs_area_approval) {
                // Areas need approval - reload page to show modal
                document.getElementById('progress-modal').classList.add('hidden');
                window.location.reload();
            } else {
                // Areas exist, move to analysis
                currentStep = 'analyze';
                setTimeout(() => processAnalyzeChunk(), 1000);
            }
        })
        .catch(err => {
            console.error('Error:', err);
            showError('Network error during area check. Retrying...');
            setTimeout(() => processAreasStep(), 5000);
        });
    }

    function processAnalyzeChunk() {
        updateStepIndicator('analyze');
        document.getElementById('progress-text').textContent = 'Analyzing issues...';

        fetch(BASEURL + 'sync/process-analyze/' + currentJobId, {
            method: 'POST'
        })
        .then(res => {
            // Check if response is JSON before parsing
            const contentType = res.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                return res.text().then(text => {
                    console.error('Non-JSON response:', text.substring(0, 500));
                    throw new Error('Server returned HTML instead of JSON. Check server error logs.');
                });
            }
            return res.json();
        })
        .then(data => {
            if (!data.success) {
                showError(data.error || 'Analysis failed');
                return;
            }

            updateProgress(data.processed, data.total, data.percent);

            if (data.completed) {
                completeSync();
            } else {
                // Continue processing next chunk
                setTimeout(() => processAnalyzeChunk(), 1000);
            }
        })
        .catch(err => {
            console.error('Error:', err);
            showError('Network error during analysis. Retrying...');
            setTimeout(() => processAnalyzeChunk(), 5000);
        });
    }

    function showResumeModal(jobId, status, processed, total) {
        currentJobId = jobId;

        // Determine which step to resume from based on status and progress
        if (status === 'pending' || status === 'syncing') {
            // Resume from sync step
            currentStep = 'sync';
            showProgressModal();
            processSyncStep();
        } else if (status === 'processing') {
            // Resume from analysis step - skip sync and areas
            currentStep = 'analyze';
            showProgressModal();

            // Mark sync and areas as complete
            setStepIcon('sync', 'complete', 'check');
            setStepLine(1, true);
            setStepIcon('areas', 'complete', 'check');
            setStepLine(2, true);
            setStepIcon('analyze', 'active', 'chart-line');

            // Set progress to where we left off
            const percent = total > 0 ? Math.round((processed / total) * 100) : 0;
            const overallPercent = 67 + Math.round(percent * 0.33);
            document.getElementById('progress-percent').textContent = overallPercent + '%';
            document.getElementById('progress-bar').style.width = overallPercent + '%';
            document.getElementById('progress-text').textContent = 'Analyzing issues...';
            document.getElementById('progress-count').textContent = processed;
            document.getElementById('progress-total').textContent = total;
            document.getElementById('progress-details').textContent = `${processed} of ${total} issues analyzed`;

            // Continue processing
            processAnalyzeChunk();
        }
    }

    function updateProgress(processed, total, percent) {
        // Map analysis progress from 67% to 100%
        // 0% analysis = 67% overall, 100% analysis = 100% overall
        const overallPercent = 67 + Math.round(percent * 0.33);

        const countEl = document.getElementById('progress-count');
        const totalEl = document.getElementById('progress-total');
        const percentEl = document.getElementById('progress-percent');
        const barEl = document.getElementById('progress-bar');
        const detailsEl = document.getElementById('progress-details');

        if (countEl) countEl.textContent = processed;
        if (totalEl) totalEl.textContent = total;
        if (percentEl) percentEl.textContent = overallPercent + '%';
        if (barEl) barEl.style.width = overallPercent + '%';
        if (detailsEl) detailsEl.textContent = `${processed} of ${total} issues analyzed`;
    }

    function showError(message) {
        const errorDiv = document.getElementById('progress-error');
        errorDiv.querySelector('p').textContent = message;
        errorDiv.classList.remove('hidden');
    }

    function completeSync() {
        // Complete all steps
        setStepIcon('sync', 'complete', 'check');
        setStepLine(1, true);
        setStepIcon('areas', 'complete', 'check');
        setStepLine(2, true);
        setStepIcon('analyze', 'complete', 'check');

        document.getElementById('progress-text').textContent = 'Sync & analysis complete!';
        document.getElementById('progress-percent').textContent = '100%';
        document.getElementById('progress-bar').style.width = '100%';

        setTimeout(() => {
            window.location.reload();
        }, 1500);
    }
    </script>

    <!-- Template: High Signal Row -->
    <!-- Variables: title, url, issue_number, priority_score, priorityClass, priorityBg, priorityText, priorityIcon, timeText, reactions_total, comments_count -->
    <script type="text/template" id="tmpl-high-signal-row">
        <tr class="hover:bg-slate-50">
            <td class="px-6 py-4 whitespace-nowrap">
                <input style="border-color: #059669" type="checkbox" class="form-checkbox h-4 w-4 rounded">
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold border <%= priorityClass %>">
                        <i class="fa-solid <%= priorityIcon %>"></i>
                        <%= priorityText %>
                    </span>
                    <span style="color: #64748b" class="text-xs font-medium"><%= priority_score %></span>
                </div>
                <div style="background-color: #e2e8f0" class="mt-1.5 w-20 rounded-full h-1.5">
                    <div class="<%= priorityBg %> h-1.5 rounded-full" style="width: <%= priority_score %>%"></div>
                </div>
            </td>
            <td class="px-6 py-4">
                <div style="color: #0f172a" class="text-sm font-medium"><%- title %></div>
                <div style="color: #64748b" class="text-xs">
                    <a href="<%= url %>" target="_blank" class="hover:text-emerald-600 hover:underline">#<%= issue_number %> <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>
                    • opened <%= timeText %>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div style="color: #475569" class="flex items-center gap-3 text-sm">
                    <span class="flex items-center gap-1" title="Reactions">
                        <i style="color: #94a3b8" class="fa-solid fa-heart text-xs"></i>
                        <%= reactions_total %>
                    </span>
                    <span class="flex items-center gap-1" title="Comments">
                        <i style="color: #94a3b8" class="fa-solid fa-comment text-xs"></i>
                        <%= comments_count %>
                    </span>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <a href="<%= url %>" target="_blank" class="hover:text-emerald-900 font-medium">View</a>
            </td>
        </tr>
    </script>

    <!-- Template: Duplicate Row -->
    <!-- Variables: primary_title, primary_url, primary_number, primaryTimeText, duplicate_url, duplicate_number, duplicate_title, dupTimeText, similarityPercent, badgeClass -->
    <script type="text/template" id="tmpl-duplicate-row">
        <tr class="hover:bg-slate-50">
            <td class="px-6 py-4 whitespace-nowrap">
                <input style="border-color: #059669" type="checkbox" class="form-checkbox h-4 w-4 rounded">
            </td>
            <td class="px-6 py-4">
                <div style="color: #0f172a" class="text-sm font-medium"><%- primary_title %></div>
                <div style="color: #64748b" class="text-xs">
                    <a href="<%= primary_url %>" target="_blank" class="hover:text-emerald-600 hover:underline">#<%= primary_number %> <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>
                    • opened <%= primaryTimeText %>
                </div>
            </td>
            <td class="px-6 py-4">
                <div style="color: #0f172a" class="text-sm flex items-center gap-2">
                    <i style="color: #94a3b8" class="fa-solid fa-arrow-right-long"></i>
                    <div>
                        <a href="<%= duplicate_url %>" target="_blank" class="hover:underline">#<%= duplicate_number %>: <%- duplicate_title %> <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>
                        <div style="color: #94a3b8" class="text-xs">opened <%= dupTimeText %></div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <%= badgeClass %>"><%= similarityPercent %>% Match</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <a href="<%= duplicate_url %>" target="_blank" class="hover:text-emerald-900 font-medium">View</a>
            </td>
        </tr>
    </script>

    <!-- Template: Cleanup Row -->
    <!-- Variables: title, url, issue_number, openedText, labels (array), activityText -->
    <script type="text/template" id="tmpl-cleanup-row">
        <tr class="hover:bg-slate-50">
            <td class="px-6 py-4 whitespace-nowrap">
                <input style="border-color: #059669" type="checkbox" class="form-checkbox h-4 w-4 rounded">
            </td>
            <td class="px-6 py-4">
                <div style="color: #0f172a" class="text-sm font-medium"><%- title %></div>
                <div style="color: #64748b" class="text-xs">
                    <a href="<%= url %>" target="_blank" class="hover:text-emerald-600 hover:underline">#<%= issue_number %> <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>
                    • opened <%= openedText %>
                </div>
            </td>
            <td class="px-6 py-4">
                <div class="flex flex-wrap gap-2">
                    <% if (labels && labels.length > 0) { %>
                        <% labels.slice(0, 2).forEach(function(label) { %>
                            <span style="color: #475569; background-color: #f1f5f9" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"><%- label %></span>
                        <% }); %>
                        <% if (labels.length > 2) { %>
                            <span style="color: #64748b" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium">+<%= labels.length - 2 %></span>
                        <% } %>
                    <% } %>
                </div>
            </td>
            <td style="color: #64748b" class="px-6 py-4 whitespace-nowrap text-sm">
                <%= activityText %>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <a href="<%= url %>" target="_blank" class="hover:text-emerald-900 font-medium">View</a>
            </td>
        </tr>
    </script>

    <!-- Template: Missing Info Row -->
    <!-- Variables: title, url, issue_number, openedText, missingElements (array), labels (array) -->
    <script type="text/template" id="tmpl-missing-info-row">
        <tr class="hover:bg-slate-50">
            <td class="px-6 py-4 whitespace-nowrap">
                <input style="border-color: #059669" type="checkbox" class="form-checkbox h-4 w-4 rounded">
            </td>
            <td class="px-6 py-4">
                <div style="color: #0f172a" class="text-sm font-medium"><%- title %></div>
                <div style="color: #64748b" class="text-xs">
                    <a href="<%= url %>" target="_blank" class="hover:text-emerald-600 hover:underline">#<%= issue_number %> <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>
                    • opened <%= openedText %>
                </div>
            </td>
            <td class="px-6 py-4">
                <div class="flex flex-wrap gap-2">
                    <% if (missingElements && missingElements.length > 0) { %>
                        <% missingElements.slice(0, 3).forEach(function(element) { %>
                            <span style="color: #991b1b; background-color: #fef2f2" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"><%- element %></span>
                        <% }); %>
                        <% if (missingElements.length > 3) { %>
                            <span style="color: #64748b" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium">+<%= missingElements.length - 3 %></span>
                        <% } %>
                    <% } else { %>
                        <span style="color: #94a3b8" class="text-xs">No specific elements identified</span>
                    <% } %>
                </div>
            </td>
            <td class="px-6 py-4">
                <div class="flex flex-wrap gap-2">
                    <% if (labels && labels.length > 0) { %>
                        <% labels.slice(0, 2).forEach(function(label) { %>
                            <span style="color: #475569; background-color: #f1f5f9" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"><%- label %></span>
                        <% }); %>
                        <% if (labels.length > 2) { %>
                            <span style="color: #64748b" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium">+<%= labels.length - 2 %></span>
                        <% } %>
                    <% } %>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <a href="<%= url %>" target="_blank" class="hover:text-emerald-900 font-medium">View</a>
            </td>
        </tr>
    </script>

    <!-- Template: Suggestions Row -->
    <!-- Variables: title, url, issue_number, timeText, currentLabels (array), suggestedLabels (array), reasoning -->
    <script type="text/template" id="tmpl-suggestions-row">
        <tr class="hover:bg-slate-50">
            <td class="px-6 py-4 whitespace-nowrap">
                <input style="border-color: #059669" type="checkbox" class="form-checkbox h-4 w-4 rounded">
            </td>
            <td class="px-6 py-4">
                <div style="color: #0f172a" class="text-sm font-medium"><%- title %></div>
                <div style="color: #64748b" class="text-xs">
                    <a href="<%= url %>" target="_blank" class="hover:text-emerald-600 hover:underline">#<%= issue_number %> <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>
                    • opened <%= timeText %>
                </div>
                <% if (currentLabels && currentLabels.length > 0) { %>
                    <div class="mt-2 flex flex-wrap gap-1">
                        <span style="color: #94a3b8" class="text-xs">Current:</span>
                        <% currentLabels.slice(0, 2).forEach(function(label) { %>
                            <span style="color: #475569; background-color: #f1f5f9" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"><%- label %></span>
                        <% }); %>
                        <% if (currentLabels.length > 2) { %>
                            <span style="color: #94a3b8" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium">+<%= currentLabels.length - 2 %></span>
                        <% } %>
                    </div>
                <% } %>
            </td>
            <td class="px-6 py-4">
                <div class="space-y-1">
                    <% if (suggestedLabels && suggestedLabels.length > 0) { %>
                        <% suggestedLabels.forEach(function(label) { %>
                            <div class="flex items-center gap-2 text-xs">
                                <span style="color: #94a3b8" >Add:</span>
                                <span style="border-color: #047857; background-color: #ecfdf5" class="px-2 py-0.5 rounded border"><%- label %></span>
                            </div>
                        <% }); %>
                    <% } %>
                </div>
            </td>
            <td style="color: #64748b" class="px-6 py-4 text-sm">
                <%- reasoning && reasoning.length > 50 ? reasoning.substring(0, 50) + '...' : reasoning %>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <a href="<%= url %>" target="_blank" class="hover:text-emerald-800 font-medium">View</a>
            </td>
        </tr>
    </script>

</body>
</html>