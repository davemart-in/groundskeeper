<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Groundskeeper v0.1 POC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<link rel="stylesheet" type="text/css" charset="utf-8"  media="screen, projection" href="<?php echo BASEURL; ?>css/groundskeeper-core.css?<?php getVersionNumber(); ?>" />
	<link rel="stylesheet" type="text/css" charset="utf-8"  media="screen, projection" href="<?php echo BASEURL; ?>css/groundskeeper-dashboard.css?<?php getVersionNumber(); ?>" />
	<link rel="stylesheet" type="text/css" charset="utf-8"  media="screen, projection" href="<?php echo BASEURL; ?>css/groundskeeper-modals.css?<?php getVersionNumber(); ?>" />
	<link rel="stylesheet" type="text/css" charset="utf-8"  media="screen, projection" href="<?php echo BASEURL; ?>css/groundskeeper-settings.css?<?php getVersionNumber(); ?>" />
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
<body>

    <!-- Top Navigation Bar -->
    <nav class="top-nav">
        <div class="top-nav__container">
            <div class="top-nav__inner">
                <div class="top-nav__left">
                    <div class="top-nav__logo">
                        <div class="top-nav__logo-icon">
                            <i class="fa-solid fa-leaf"></i>
                        </div>
                        <span class="top-nav__logo-text">Groundskeeper <span class="top-nav__logo-badge">v0.1</span></span>
                    </div>
                    <div class="top-nav__tabs">
                        <a href="<?php echo BASEURL; ?>" id="tab-dashboard" class="top-nav__tab <?php echo (!isset($glob['active_tab']) || $glob['active_tab'] === 'dashboard') ? 'top-nav__tab--active' : ''; ?>">
                            Dashboard
                        </a>
                        <a href="<?php echo BASEURL; ?>settings" id="tab-settings" class="top-nav__tab <?php echo (isset($glob['active_tab']) && $glob['active_tab'] === 'settings') ? 'top-nav__tab--active' : ''; ?>">
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
            <div class="dashboard-controls">
                <div class="dashboard-controls__repo-section">
                    <label class="dashboard-controls__label">Repository</label>
                    <?php if (!empty($glob['repositories'])): ?>
                        <select class="dashboard-controls__select">
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
                <div class="dashboard-controls__status-section">
                    <span class="dashboard-controls__label">Analysis Status</span>
                    <?php if (!$glob['selected_repo']['last_audited_at']): ?>
                        <div class="dashboard-controls__status-info">
                            <span class="dashboard-controls__status-indicator" style="background: #cbd5e1;"></span>
                            Not yet audited
                            <form method="POST" action="<?php echo BASEURL; ?>audit/run/<?php echo $glob['selected_repo']['id']; ?>" class="dashboard-controls__sync-form" onsubmit="showAuditLoading()">
                                <button type="submit" class="dashboard-controls__sync-btn" style="color: #059669; border-color: #a7f3d0; background: #d1fae5;"><i class="fa-solid fa-play mr-1"></i> Run Audit</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="dashboard-controls__status-info">
                            <span class="dashboard-controls__status-indicator"></span>
                            Last audited <?php echo date('M j, Y', $glob['selected_repo']['last_audited_at']); ?>
                            <form id="sync-form" method="POST" action="<?php echo BASEURL; ?>sync/run/<?php echo $glob['selected_repo']['id']; ?>" class="dashboard-controls__sync-form">
                                <button type="submit" class="dashboard-controls__sync-btn"><i class="fa-solid fa-arrows-rotate mr-1"></i> Update issues and re-analyze</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($glob['total_issues'] > 0): ?>
            <div class="dashboard-layout">

                <!-- Left Column: The Findings Feed -->
                <div class="findings-feed">
                    <div id="analysis-header" class="findings-header">
                        <h3 class="findings-header__title">Analysis Findings</h3>
                    </div>

                    <!-- Stat Card: Total -->
                    <div class="total-stats-card">
                        <div class="total-stats-card__content">
                            <div class="total-stats-card__icon">
                                <i class="fa-solid fa-layer-group"></i>
                            </div>
                            <div class="total-stats-card__stats">
                                <h4 class="total-stats-card__number" id="stat-total"><?php echo $glob['total_issues']; ?></h4>
                                <p class="total-stats-card__label">Total open bugs</p>
                            </div>
                        </div>
                        <?php if (isset($glob['selected_repo'])): ?>
                            <a href="https://github.com/<?php echo htmlspecialchars($glob['selected_repo']['owner']); ?>/<?php echo htmlspecialchars($glob['selected_repo']['name']); ?>/issues?q=is%3Aissue%20state%3Aopen%20label%3A<?php echo urlencode($glob['selected_repo']['bug_label']); ?>" target="_blank" class="total-stats-card__github-link"><i class="fa-brands fa-github"></i> View on GitHub</a>
                        <?php else: ?>
                            <a href="#" class="total-stats-card__github-link"><i class="fa-brands fa-github"></i> View on GitHub</a>
                        <?php endif; ?>
                    </div>

                    <!-- Action Card: High Signal -->
                    <div class="action-card action-card--high-signal">
                        <div class="action-card__content">
                            <div class="action-card__icon action-card__icon--high-signal">
                                <i class="fa-solid fa-fire"></i>
                            </div>
                            <div class="action-card__text">
                                <h4 class="action-card__title"><span id="stat-high-signal"><?php echo $glob['high_signal_count']; ?></span> High Signal Issues</h4>
                                <p class="action-card__description">Valuable, actionable issues worth prioritizing</p>
                            </div>
                        </div>
                        <button onclick="GRNDSKPR.Dashboard.openModal('high-signal')" class="action-card__button">
                            View Issues
                        </button>
                    </div>

                    <!-- Action Card: Duplicates -->
                    <div class="action-card action-card--duplicates">
                        <div class="action-card__content">
                            <div class="action-card__icon action-card__icon--duplicates">
                                <i class="fa-solid fa-clone"></i>
                            </div>
                            <div class="action-card__text">
                                <h4 class="action-card__title"><span id="stat-duplicates"><?php echo $glob['duplicates_count']; ?></span> Likely Duplicates</h4>
                                <p class="action-card__description">Issues that appear to be semantically similar.</p>
                            </div>
                        </div>
                        <button onclick="GRNDSKPR.Dashboard.openModal('duplicates')" class="action-card__button action-card__button--duplicates">
                            View Issues
                        </button>
                    </div>

                    <!-- Action Card: Should Close -->
                    <div class="action-card action-card--cleanup">
                        <div class="action-card__content">
                            <div class="action-card__icon action-card__icon--cleanup">
                                <i class="fa-solid fa-archive"></i>
                            </div>
                            <div class="action-card__text">
                                <h4 class="action-card__title"><span id="stat-cleanup"><?php echo $glob['cleanup_count']; ?></span> Cleanup Candidates</h4>
                                <p class="action-card__description">Issues that should likely be closed</p>
                            </div>
                        </div>
                        <button onclick="GRNDSKPR.Dashboard.openModal('cleanup')" class="action-card__button">
                            View Issues
                        </button>
                    </div>

                    <!-- Action Card: Missing Info -->
                    <div class="action-card action-card--missing-info">
                        <div class="action-card__content">
                            <div class="action-card__icon action-card__icon--missing-info">
                                <i class="fa-solid fa-circle-question"></i>
                            </div>
                            <div class="action-card__text">
                                <h4 class="action-card__title"><span id="stat-missing-info"><?php echo $glob['missing_info_count']; ?></span> Missing Critical Info</h4>
                                <p class="action-card__description">Issues lacking critical information</p>
                            </div>
                        </div>
                        <button onclick="GRNDSKPR.Dashboard.openModal('missing-info')" class="action-card__button">
                            View Issues
                        </button>
                    </div>

                    <!-- Action Card: Suggestions -->
                    <div class="action-card action-card--suggestions">
                        <div class="action-card__content">
                            <div class="action-card__icon action-card__icon--suggestions">
                                <i class="fa-solid fa-wand-magic-sparkles"></i>
                            </div>
                            <div class="action-card__text">
                                <h4 class="action-card__title"><span id="stat-suggestions"><?php echo $glob['suggestions_count']; ?></span> Label Suggestions</h4>
                                <p class="action-card__description">AI-recommended labels to improve categorization</p>
                            </div>
                        </div>
                        <button onclick="GRNDSKPR.Dashboard.openModal('suggestions')" class="action-card__button">
                            Review Suggestions
                        </button>
                    </div>

                </div>

                <!-- Right Column: Breakdowns -->
                <div class="breakdowns-column">

                    <?php if ($hasPriorityLabels): ?>
                    <!-- By Priority -->
                    <div class="breakdown-section">
                        <h3 class="breakdown-section__title">Issues by Priority</h3>
                        <div class="breakdown-section__card">
                            <div class="priority-list">
                                <div class="priority-item">
                                    <div class="priority-item__header">
                                        <span class="priority-item__label priority-item__label--critical">Critical / High</span>
                                        <span class="priority-item__percentage">12%</span>
                                    </div>
                                    <div class="priority-item__bar-bg">
                                        <div class="priority-item__bar-fill priority-item__bar-fill--critical" style="width: 12%"></div>
                                    </div>
                                </div>
                                <div class="priority-item">
                                    <div class="priority-item__header">
                                        <span class="priority-item__label priority-item__label--medium">Medium</span>
                                        <span class="priority-item__percentage">34%</span>
                                    </div>
                                    <div class="priority-item__bar-bg">
                                        <div class="priority-item__bar-fill priority-item__bar-fill--medium" style="width: 34%"></div>
                                    </div>
                                </div>
                                <div class="priority-item">
                                    <div class="priority-item__header">
                                        <span class="priority-item__label priority-item__label--low">Low / Enhancement</span>
                                        <span class="priority-item__percentage">41%</span>
                                    </div>
                                    <div class="priority-item__bar-bg">
                                        <div class="priority-item__bar-fill priority-item__bar-fill--low" style="width: 41%"></div>
                                    </div>
                                </div>
                                <div class="priority-item">
                                    <div class="priority-item__header">
                                        <span class="priority-item__label priority-item__label--unprioritized">Un-prioritized</span>
                                        <span class="priority-item__percentage">13%</span>
                                    </div>
                                    <div class="priority-item__bar-bg">
                                        <div class="priority-item__bar-fill priority-item__bar-fill--unprioritized" style="width: 13%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                     <!-- By Functionality -->
                     <div class="breakdown-section">
                        <h3 class="breakdown-section__title">Issues by Area</h3>
                        <?php if (!empty($glob['area_stats'])): ?>
                        <div class="area-table-container">
                           <table class="area-table">
                               <tbody class="area-table__body">
                                   <?php
                                   $topCount = 10;
                                   foreach ($glob['area_stats'] as $index => $area):
                                       $isHidden = $index >= $topCount;
                                       $hiddenClass = $isHidden ? 'area-table__row--hidden area-hidden' : '';
                                   ?>
                                   <tr class="area-row area-table__row <?php echo $hiddenClass; ?>" data-area-id="<?php echo $area['id']; ?>" onclick="GRNDSKPR.Dashboard.filterDashboard('<?php echo htmlspecialchars(addslashes($area['name'])); ?>', <?php echo $area['count']; ?>, <?php echo $area['id']; ?>)">
                                       <td class="area-table__cell area-table__cell--name"><?php echo htmlspecialchars($area['name']); ?></td>
                                       <td class="area-table__cell area-table__cell--count"><?php echo $area['count']; ?> <span class="area-table__percentage">(<?php echo $area['percentage']; ?>%)</span></td>
                                   </tr>
                                   <?php endforeach; ?>
                               </tbody>
                           </table>
                           <?php if (count($glob['area_stats']) > $topCount): ?>
                           <div class="area-table__toggle">
                                <button onclick="GRNDSKPR.Dashboard.toggleAreas()" id="btn-show-areas" class="area-table__toggle-btn">Show all</button>
                           </div>
                           <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <div class="area-empty-state">
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
            <div class="flex h-full bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                
                <!-- Sidebar -->
                <div class="w-64 bg-slate-50 border-r border-slate-200 flex flex-col">
                    <div class="p-4 border-b border-slate-200 flex justify-between items-center">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Repositories</span>
                        <button onclick="GRNDSKPR.Dashboard.openModal('add-repo')" class="text-emerald-600 hover:text-emerald-700 text-xs font-bold"><i class="fa-solid fa-plus"></i> Add</button>
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
                                <div class="flex gap-3">
                                    <a href="<?php echo BASEURL; ?>reset/<?php echo $glob['selected_repo']['id']; ?>" class="text-sm text-yellow-600 hover:text-yellow-700 font-medium">
                                        <i class="fa-solid fa-rotate-left mr-1"></i> Reset Data
                                    </a>
                                    <form method="POST" action="<?php echo BASEURL; ?>settings/<?php echo $glob['selected_repo']['id']; ?>/delete" onsubmit="return confirm('Are you sure you want to remove this repository?');">
                                        <button type="submit" class="text-sm text-red-600 hover:text-red-700 font-medium">
                                            <i class="fa-solid fa-trash mr-1"></i> Remove
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- No repos - show blank slate -->
                            <div class="text-center">
                                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fa-brands fa-github text-slate-400 text-2xl"></i>
                                </div>
                                <h3 class="text-lg font-bold text-slate-900 mb-2">No repositories connected</h3>
                                <p class="text-sm text-slate-500 mb-6">Add your first repository to start analyzing issues.</p>
                                <button onclick="GRNDSKPR.Dashboard.openModal('add-repo')" class="bg-emerald-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-emerald-700">
                                    <i class="fa-solid fa-plus mr-2"></i>
                                    Add Your First Repository
                                </button>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($glob['repositories']) && isset($glob['selected_repo'])): ?>
                        <!-- Config Form -->
                        <div class="space-y-8">
                            <!-- GitHub Connection Section -->
                            <div class="bg-slate-50 p-4 rounded-lg border border-slate-200">
                                <h3 class="text-sm font-bold text-slate-900 mb-2">GitHub Connection</h3>
                                <p class="text-xs text-slate-500 mb-4">View and analyze issues using your GitHub username and optional Personal Access Token.</p>

                                <?php if (isset($glob['user']) && $glob['user']): ?>
                                    <!-- Connected state -->
                                    <div class="flex items-center gap-3 mb-4">
                                        <?php if (!empty($glob['user']['avatar_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($glob['user']['avatar_url']); ?>" alt="Avatar" class="w-10 h-10 rounded-full">
                                        <?php endif; ?>
                                        <div>
                                            <p class="text-sm text-slate-600">Connected as <strong>@<?php echo htmlspecialchars($glob['user']['github_username']); ?></strong></p>
                                            <p class="text-xs text-slate-500 mt-1"><i class="fa-solid fa-eye"></i> Read-only access</p>
                                        </div>
                                    </div>
                                    <div class="flex gap-3">
                                        <button onclick="document.getElementById('pat-form').classList.toggle('hidden')" class="text-sm text-slate-600 border border-slate-300 bg-white px-3 py-1.5 rounded hover:bg-slate-50">Update Token</button>
                                        <a href="<?php echo BASEURL; ?>settings/disconnect" class="text-sm text-red-600 border border-red-200 bg-white px-3 py-1.5 rounded hover:bg-red-50">Disconnect</a>
                                    </div>

                                    <!-- PAT Update Form (hidden by default) -->
                                    <div id="pat-form" class="hidden mt-4 pt-4 border-t border-slate-200">
                                        <form method="POST" action="<?php echo BASEURL; ?>settings/update-token">
                                            <label class="block text-xs font-medium text-slate-700 mb-2">Personal Access Token</label>
                                            <input type="text" name="personal_access_token" class="block w-full px-3 py-2 text-sm border-slate-300 rounded-md bg-white border" placeholder="ghp_xxxxxxxxxxxx">
                                            <p class="text-xs text-slate-500 mt-2">Update your PAT to increase rate limits (5000 req/hr)</p>
                                            <button type="submit" class="mt-3 bg-emerald-600 text-white px-3 py-1.5 rounded text-sm font-medium hover:bg-emerald-700">Update Token</button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <!-- Not connected state -->
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
                                            <i class="fa-brands fa-github"></i>
                                            Connect GitHub
                                        </button>
                                    </form>
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
        <div class="modal__backdrop"></div>

        <!-- Fixed wrapper for centering -->
        <div class="fixed inset-0 z-10 overflow-y-auto" onclick="GRNDSKPR.Dashboard.closeModal('add-repo')">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">

                <!-- Modal Panel -->
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg" onclick="event.stopPropagation()">
                    
                    <!-- Modal Header -->
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-slate-100 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-slate-900" id="modal-title">Connect New Repository</h3>
                            <p class="text-sm text-slate-500 mt-1">Groundskeeper will scan this repo for issues.</p>
                        </div>
                        <button onclick="GRNDSKPR.Dashboard.closeModal('add-repo')" class="text-slate-400 hover:text-slate-500">
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
                            <button type="button" onclick="GRNDSKPR.Dashboard.closeModal('add-repo')" class="mt-3 w-full inline-flex justify-center rounded-md border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
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
        <div class="modal__backdrop"></div>

        <!-- Fixed wrapper for centering -->
        <div class="fixed inset-0 z-10 overflow-y-auto" onclick="GRNDSKPR.Dashboard.closeModal('duplicates')">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">

                <!-- Modal Panel -->
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-6xl" onclick="event.stopPropagation()">
                    
                    <!-- Modal Header -->
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-slate-100 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-slate-900" id="modal-title">Review Likely Duplicates</h3>
                            <p class="text-sm text-slate-500 mt-1">Found <?php echo $glob['duplicates_count']; ?> groups of issues that appear semantically similar (≥85% similarity).</p>
                        </div>
                        <button onclick="GRNDSKPR.Dashboard.closeModal('duplicates')" class="text-slate-400 hover:text-slate-500">
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
                             <button onclick="GRNDSKPR.Dashboard.copySelectedIssueUrls('duplicates')" class="modal__action-btn">
                                <i class="fa-solid fa-copy"></i>
                                Copy Issue URLs
                            </button>
                        </div>
                    </div>

                    <!-- List Content -->
                    <div class="max-h-[60vh] overflow-y-auto">
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
                            <tbody id="tbody-duplicates" class="bg-white divide-y divide-slate-200"></tbody>
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
        <div class="modal__backdrop"></div>

        <!-- Fixed wrapper for centering -->
        <div class="fixed inset-0 z-10 overflow-y-auto" onclick="GRNDSKPR.Dashboard.closeModal('high-signal')">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">

                <!-- Modal Panel -->
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-6xl" onclick="event.stopPropagation()">
                    
                    <!-- Modal Header -->
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-slate-100 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-slate-900" id="modal-title">High Signal Issues Queue</h3>
                            <p class="text-sm text-slate-500 mt-1"><?php echo $glob['high_signal_count']; ?> valuable, actionable issues identified by AI analysis.</p>
                        </div>
                        <button onclick="GRNDSKPR.Dashboard.closeModal('high-signal')" class="text-slate-400 hover:text-slate-500">
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
                             <button onclick="GRNDSKPR.Dashboard.copySelectedIssueUrls('high-signal')" class="modal__action-btn">
                                <i class="fa-solid fa-copy"></i>
                                Copy Issue URLs
                            </button>
                        </div>
                    </div>

                    <!-- List Content -->
                    <div class="max-h-[60vh] overflow-y-auto">
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
                            <tbody id="tbody-high-signal" class="bg-white divide-y divide-slate-200"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: Cleanup Candidates -->
    <div id="modal-cleanup" class="hidden relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="modal__backdrop"></div>

        <!-- Fixed wrapper for centering -->
        <div class="fixed inset-0 z-10 overflow-y-auto" onclick="GRNDSKPR.Dashboard.closeModal('cleanup')">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">

                <!-- Modal Panel -->
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-6xl" onclick="event.stopPropagation()">
                    
                    <!-- Modal Header -->
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-slate-100 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-slate-900" id="modal-title">Review Cleanup Candidates</h3>
                            <p class="text-sm text-slate-500 mt-1">Found <?php echo $glob['cleanup_count']; ?> issues identified as candidates for closure by AI analysis.</p>
                        </div>
                        <button onclick="GRNDSKPR.Dashboard.closeModal('cleanup')" class="text-slate-400 hover:text-slate-500">
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
                             <button onclick="GRNDSKPR.Dashboard.copySelectedIssueUrls('cleanup')" class="modal__action-btn">
                                <i class="fa-solid fa-copy"></i>
                                Copy Issue URLs
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
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Labels</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Last Activity</th>
                                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody id="tbody-cleanup" class="bg-white divide-y divide-slate-200"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: Missing Info -->
    <div id="modal-missing-info" class="hidden relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="modal__backdrop"></div>

        <!-- Fixed wrapper for centering -->
        <div class="fixed inset-0 z-10 overflow-y-auto" onclick="GRNDSKPR.Dashboard.closeModal('missing-info')">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">

                <!-- Modal Panel -->
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-6xl" onclick="event.stopPropagation()">
                    
                    <!-- Modal Header -->
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-slate-100 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-slate-900" id="modal-title">Review Issues Missing Context</h3>
                            <p class="text-sm text-slate-500 mt-1">Found <?php echo $glob['missing_info_count']; ?> issues missing critical information identified by AI analysis.</p>
                        </div>
                        <button onclick="GRNDSKPR.Dashboard.closeModal('missing-info')" class="text-slate-400 hover:text-slate-500">
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
                             <button onclick="GRNDSKPR.Dashboard.copySelectedIssueUrls('missing-info')" class="modal__action-btn">
                                <i class="fa-solid fa-copy"></i>
                                Copy Issue URLs
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
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Labels</th>
                                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody id="tbody-missing-info" class="bg-white divide-y divide-slate-200"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: Suggestions -->
    <div id="modal-suggestions" class="hidden relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="modal__backdrop"></div>

        <!-- Fixed wrapper for centering -->
        <div class="fixed inset-0 z-10 overflow-y-auto" onclick="GRNDSKPR.Dashboard.closeModal('suggestions')">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">

                <!-- Modal Panel -->
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-6xl" onclick="event.stopPropagation()">
                    
                    <!-- Modal Header -->
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-slate-100 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-slate-900" id="modal-title">Review Label Suggestions</h3>
                            <p class="text-sm text-slate-500 mt-1">Found <?php echo $glob['suggestions_count']; ?> issues with AI-recommended labels from the repository.</p>
                        </div>
                        <button onclick="GRNDSKPR.Dashboard.closeModal('suggestions')" class="text-slate-400 hover:text-slate-500">
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
                             <button onclick="GRNDSKPR.Dashboard.copySelectedIssueUrls('suggestions')" class="modal__action-btn">
                                <i class="fa-solid fa-copy"></i>
                                Copy Issue URLs
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
                            <tbody id="tbody-suggestions" class="bg-white divide-y divide-slate-200"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
	<script type="text/javascript" src="<?php echo BASEURL; ?>js/groundskeeper-utility.js"></script>
	<script type="text/javascript" src="<?php echo BASEURL; ?>js/groundskeeper-core.js"></script>
    <script>
        // Initialize dashboard configuration
        window.GRNDSKPR_CONFIG = {
            baseUrl: '<?php echo BASEURL; ?>',
            repositoryId: <?php echo isset($glob['selected_repo']) ? $glob['selected_repo']['id'] : 'null'; ?>
        };

        // Cache for modal data to avoid refetching
        window.GRNDSKPR_CACHE = {};

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

    <!-- Unified Progress Modal -->
    <div id="progress-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 shadow-xl max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">
                <i id="progress-icon" class="fa-solid fa-arrows-rotate fa-spin text-blue-600 mr-2"></i>
                <span id="progress-title">Syncing & Analyzing</span>
            </h3>
            <p id="progress-description" class="text-sm text-slate-600 mb-4">
                Processing repository changes. This may take several minutes...
            </p>

            <!-- Step Indicators -->
            <div class="mb-6 flex items-center justify-between">
                <div class="flex flex-col items-center flex-1">
                    <div id="step-sync-icon" class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white text-sm mb-1">
                        <i class="fa-solid fa-sync fa-spin"></i>
                    </div>
                    <span class="text-xs text-slate-600">Sync</span>
                </div>
                <div class="flex-1 h-0.5 bg-slate-200 mx-2" id="step-line-1"></div>
                <div class="flex flex-col items-center flex-1">
                    <div id="step-areas-icon" class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-400 text-sm mb-1">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <span class="text-xs text-slate-600">Areas</span>
                </div>
                <div class="flex-1 h-0.5 bg-slate-200 mx-2" id="step-line-2"></div>
                <div class="flex flex-col items-center flex-1">
                    <div id="step-analyze-icon" class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-400 text-sm mb-1">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <span class="text-xs text-slate-600">Analyze</span>
                </div>
            </div>

            <div class="mb-4">
                <div class="flex justify-between text-sm text-slate-700 mb-2">
                    <span id="progress-text">Starting sync...</span>
                    <span id="progress-percent">0%</span>
                </div>
                <div class="w-full bg-slate-200 rounded-full h-3">
                    <div id="progress-bar" class="bg-blue-600 h-3 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
            </div>

            <p id="progress-details" class="text-xs text-slate-500 mb-4">
                <span id="progress-count">0</span> of <span id="progress-total">0</span> issues processed
            </p>

            <div id="progress-error" class="hidden bg-red-50 border border-red-200 rounded-md p-3 mb-4">
                <p class="text-sm text-red-700"></p>
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
        const stateClasses = {
            inactive: 'bg-slate-200 text-slate-400',
            active: 'bg-blue-600 text-white',
            complete: 'bg-emerald-600 text-white'
        };
        const el = document.getElementById(`step-${stepId}-icon`);
        el.className = `${baseClass} ${stateClasses[state]}`;
        el.innerHTML = `<i class="fa-solid fa-${icon}${spinning ? ' fa-spin' : ''}"></i>`;
    }

    function setStepLine(lineId, complete) {
        document.getElementById(`step-line-${lineId}`).className =
            `flex-1 h-0.5 ${complete ? 'bg-emerald-600' : 'bg-slate-200'} mx-2`;
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
                <input type="checkbox" class="form-checkbox h-4 w-4 text-emerald-600 rounded border-slate-300">
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold border <%= priorityClass %>">
                        <i class="fa-solid <%= priorityIcon %>"></i>
                        <%= priorityText %>
                    </span>
                    <span class="text-xs font-medium text-slate-500"><%= priority_score %></span>
                </div>
                <div class="mt-1.5 w-20 bg-slate-200 rounded-full h-1.5">
                    <div class="<%= priorityBg %> h-1.5 rounded-full" style="width: <%= priority_score %>%"></div>
                </div>
            </td>
            <td class="px-6 py-4">
                <div class="text-sm font-medium text-slate-900"><%- title %></div>
                <div class="text-xs text-slate-500">
                    <a href="<%= url %>" target="_blank" class="hover:text-emerald-600 hover:underline">#<%= issue_number %> <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>
                    • opened <%= timeText %>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center gap-3 text-sm text-slate-600">
                    <span class="flex items-center gap-1" title="Reactions">
                        <i class="fa-solid fa-heart text-slate-400 text-xs"></i>
                        <%= reactions_total %>
                    </span>
                    <span class="flex items-center gap-1" title="Comments">
                        <i class="fa-solid fa-comment text-slate-400 text-xs"></i>
                        <%= comments_count %>
                    </span>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <a href="<%= url %>" target="_blank" class="text-emerald-600 hover:text-emerald-900 font-medium">View</a>
            </td>
        </tr>
    </script>

    <!-- Template: Duplicate Row -->
    <!-- Variables: primary_title, primary_url, primary_number, primaryTimeText, duplicate_url, duplicate_number, duplicate_title, dupTimeText, similarityPercent, badgeClass -->
    <script type="text/template" id="tmpl-duplicate-row">
        <tr class="hover:bg-slate-50">
            <td class="px-6 py-4 whitespace-nowrap">
                <input type="checkbox" class="form-checkbox h-4 w-4 text-emerald-600 rounded border-slate-300">
            </td>
            <td class="px-6 py-4">
                <div class="text-sm font-medium text-slate-900"><%- primary_title %></div>
                <div class="text-xs text-slate-500">
                    <a href="<%= primary_url %>" target="_blank" class="hover:text-emerald-600 hover:underline">#<%= primary_number %> <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>
                    • opened <%= primaryTimeText %>
                </div>
            </td>
            <td class="px-6 py-4">
                <div class="text-sm text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-arrow-right-long text-slate-400"></i>
                    <div>
                        <a href="<%= duplicate_url %>" target="_blank" class="hover:underline text-emerald-700">#<%= duplicate_number %>: <%- duplicate_title %> <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>
                        <div class="text-xs text-slate-400">opened <%= dupTimeText %></div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <%= badgeClass %>"><%= similarityPercent %>% Match</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <a href="<%= duplicate_url %>" target="_blank" class="text-emerald-600 hover:text-emerald-900 font-medium">View</a>
            </td>
        </tr>
    </script>

    <!-- Template: Cleanup Row -->
    <!-- Variables: title, url, issue_number, openedText, labels (array), activityText -->
    <script type="text/template" id="tmpl-cleanup-row">
        <tr class="hover:bg-slate-50">
            <td class="px-6 py-4 whitespace-nowrap">
                <input type="checkbox" class="form-checkbox h-4 w-4 text-emerald-600 rounded border-slate-300">
            </td>
            <td class="px-6 py-4">
                <div class="text-sm font-medium text-slate-900"><%- title %></div>
                <div class="text-xs text-slate-500">
                    <a href="<%= url %>" target="_blank" class="hover:text-emerald-600 hover:underline">#<%= issue_number %> <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>
                    • opened <%= openedText %>
                </div>
            </td>
            <td class="px-6 py-4">
                <div class="flex flex-wrap gap-2">
                    <% if (labels && labels.length > 0) { %>
                        <% labels.slice(0, 2).forEach(function(label) { %>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600"><%- label %></span>
                        <% }); %>
                        <% if (labels.length > 2) { %>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium text-slate-500">+<%= labels.length - 2 %></span>
                        <% } %>
                    <% } %>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                <%= activityText %>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <a href="<%= url %>" target="_blank" class="text-emerald-600 hover:text-emerald-900 font-medium">View</a>
            </td>
        </tr>
    </script>

    <!-- Template: Missing Info Row -->
    <!-- Variables: title, url, issue_number, openedText, missingElements (array), labels (array) -->
    <script type="text/template" id="tmpl-missing-info-row">
        <tr class="hover:bg-slate-50">
            <td class="px-6 py-4 whitespace-nowrap">
                <input type="checkbox" class="form-checkbox h-4 w-4 text-emerald-600 rounded border-slate-300">
            </td>
            <td class="px-6 py-4">
                <div class="text-sm font-medium text-slate-900"><%- title %></div>
                <div class="text-xs text-slate-500">
                    <a href="<%= url %>" target="_blank" class="hover:text-emerald-600 hover:underline">#<%= issue_number %> <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>
                    • opened <%= openedText %>
                </div>
            </td>
            <td class="px-6 py-4">
                <div class="flex flex-wrap gap-2">
                    <% if (missingElements && missingElements.length > 0) { %>
                        <% missingElements.slice(0, 3).forEach(function(element) { %>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-50 text-red-700"><%- element %></span>
                        <% }); %>
                        <% if (missingElements.length > 3) { %>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium text-slate-500">+<%= missingElements.length - 3 %></span>
                        <% } %>
                    <% } else { %>
                        <span class="text-xs text-slate-400">No specific elements identified</span>
                    <% } %>
                </div>
            </td>
            <td class="px-6 py-4">
                <div class="flex flex-wrap gap-2">
                    <% if (labels && labels.length > 0) { %>
                        <% labels.slice(0, 2).forEach(function(label) { %>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600"><%- label %></span>
                        <% }); %>
                        <% if (labels.length > 2) { %>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium text-slate-500">+<%= labels.length - 2 %></span>
                        <% } %>
                    <% } %>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <a href="<%= url %>" target="_blank" class="text-emerald-600 hover:text-emerald-900 font-medium">View</a>
            </td>
        </tr>
    </script>

    <!-- Template: Suggestions Row -->
    <!-- Variables: title, url, issue_number, timeText, currentLabels (array), suggestedLabels (array), reasoning -->
    <script type="text/template" id="tmpl-suggestions-row">
        <tr class="hover:bg-slate-50">
            <td class="px-6 py-4 whitespace-nowrap">
                <input type="checkbox" class="form-checkbox h-4 w-4 text-emerald-600 rounded border-slate-300">
            </td>
            <td class="px-6 py-4">
                <div class="text-sm font-medium text-slate-900"><%- title %></div>
                <div class="text-xs text-slate-500">
                    <a href="<%= url %>" target="_blank" class="hover:text-emerald-600 hover:underline">#<%= issue_number %> <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>
                    • opened <%= timeText %>
                </div>
                <% if (currentLabels && currentLabels.length > 0) { %>
                    <div class="mt-2 flex flex-wrap gap-1">
                        <span class="text-xs text-slate-400">Current:</span>
                        <% currentLabels.slice(0, 2).forEach(function(label) { %>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600"><%- label %></span>
                        <% }); %>
                        <% if (currentLabels.length > 2) { %>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium text-slate-400">+<%= currentLabels.length - 2 %></span>
                        <% } %>
                    </div>
                <% } %>
            </td>
            <td class="px-6 py-4">
                <div class="space-y-1">
                    <% if (suggestedLabels && suggestedLabels.length > 0) { %>
                        <% suggestedLabels.forEach(function(label) { %>
                            <div class="flex items-center gap-2 text-xs">
                                <span class="text-slate-400">Add:</span>
                                <span class="bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded border border-emerald-200"><%- label %></span>
                            </div>
                        <% }); %>
                    <% } %>
                </div>
            </td>
            <td class="px-6 py-4 text-sm text-slate-500">
                <%- reasoning && reasoning.length > 50 ? reasoning.substring(0, 50) + '...' : reasoning %>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <a href="<%= url %>" target="_blank" class="text-emerald-600 hover:text-emerald-800 font-medium">View</a>
            </td>
        </tr>
    </script>

</body>
</html>