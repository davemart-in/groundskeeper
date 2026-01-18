<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Groundskeeper v0.1 POC</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<link rel="stylesheet" type="text/css" charset="utf-8"  media="screen, projection" href="<?php echo BASEURL; ?>css/groundskeeper-core.css?<?php getVersionNumber(); ?>" />
	<link rel="stylesheet" type="text/css" charset="utf-8"  media="screen, projection" href="<?php echo BASEURL; ?>css/groundskeeper-dashboard.css?<?php getVersionNumber(); ?>" />
	<link rel="stylesheet" type="text/css" charset="utf-8"  media="screen, projection" href="<?php echo BASEURL; ?>css/groundskeeper-modals.css?<?php getVersionNumber(); ?>" />
	<link rel="stylesheet" type="text/css" charset="utf-8"  media="screen, projection" href="<?php echo BASEURL; ?>css/groundskeeper-settings.css?<?php getVersionNumber(); ?>" />
	<link rel="stylesheet" type="text/css" charset="utf-8"  media="screen, projection" href="<?php echo BASEURL; ?>css/groundskeeper-utility.css?<?php getVersionNumber(); ?>" />
</head>
<body>

    <!-- Top Navigation Bar -->
    <nav class="top-nav">
        <div class="top-nav__container">
            <div class="top-nav__inner">
                <div class="top-nav__left">
                    <a href="<?php echo BASEURL; ?>" class="top-nav__logo">
                        <div class="top-nav__logo-icon">
                            <i class="fa-solid fa-leaf"></i>
                        </div>
                        <span class="top-nav__logo-text">Groundskeeper <span class="top-nav__logo-badge">v0.1</span></span>
                    </a>
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
    <main class="main-container">

        <!-- DASHBOARD TAB -->
        <div id="view-dashboard" class="dashboard-view <?php echo (isset($glob['active_tab']) && $glob['active_tab'] === 'settings') ? 'hidden' : ''; ?>">
            
            <!-- Dashboard Controls -->
            <div class="dashboard-controls">
                <div class="dashboard-controls__repo-section">
                    <label class="dashboard-controls__label">Repository</label>
                    <?php if (!empty($glob['repositories'])): ?>
                        <select id="repo-selector" class="dashboard-controls__select" onchange="GRNDSKPR.Dashboard.switchRepository(this.value)">
                            <?php foreach ($glob['repositories'] as $repo): ?>
                                <option value="<?php echo $repo['id']; ?>" <?php echo (isset($glob['selected_repo']) && $glob['selected_repo']['id'] == $repo['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($repo['full_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <div class="dashboard-view__empty">
                            No repositories connected. <a href="<?php echo BASEURL; ?>settings">Add a repository</a>
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
                            <form id="audit-form" method="POST" action="<?php echo BASEURL; ?>audit/run/<?php echo $glob['selected_repo']['id']; ?>" class="dashboard-controls__sync-form" data-repo-id="<?php echo $glob['selected_repo']['id']; ?>">
                                <button type="submit" class="dashboard-controls__sync-btn" style="color: #059669; border-color: #a7f3d0; background: #d1fae5;"><i class="fa-solid fa-play mr-1"></i> Run Audit</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="dashboard-controls__status-info">
                            <span class="dashboard-controls__status-indicator"></span>
                            Last audited <?php echo date('M j, Y', $glob['selected_repo']['last_audited_at']); ?>
                            <form id="sync-form" method="POST" action="<?php echo BASEURL; ?>sync/run/<?php echo $glob['selected_repo']['id']; ?>" class="dashboard-controls__sync-form" data-repo-id="<?php echo $glob['selected_repo']['id']; ?>">
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

                    <!-- Action Card: Missing Priority Labels -->
                    <div class="action-card action-card--high-signal">
                        <div class="action-card__content">
                            <div class="action-card__icon action-card__icon--high-signal">
                                <i class="fa-solid fa-fire"></i>
                            </div>
                            <div class="action-card__text">
                                <h4 class="action-card__title"><span id="stat-high-signal"><?php echo $glob['high_signal_count']; ?></span> Missing Priority Labels</h4>
                                <p class="action-card__description">Critical/High/Medium issues missing priority labels</p>
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

		<?php include APPPATH . 'views/settings.php'; ?>

    <!-- MODAL: Add Repo -->
    <div id="modal-add-repo" class="modal hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="modal__backdrop"></div>

        <!-- Fixed wrapper for centering -->
        <div class="modal__wrapper" onclick="GRNDSKPR.Dashboard.closeModal('add-repo')">
            <div class="modal__container">

                <!-- Modal Panel -->
                <div class="modal__panel modal__panel--small" onclick="event.stopPropagation()">

                    <!-- Modal Header -->
                    <div class="modal__header">
                        <div class="modal__header-content">
                            <h3 class="modal__title" id="modal-title">Connect New Repository</h3>
                            <p class="modal__description">Groundskeeper will scan this repo for issues.</p>
                        </div>
                        <button onclick="GRNDSKPR.Dashboard.closeModal('add-repo')" class="modal__close-btn">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <!-- Content -->
                    <form method="POST" action="<?php echo BASEURL; ?>settings/add-repo">
                        <div class="modal__form-content">
                            <div class="modal__form-group">
                                <div class="modal__form-field">
                                    <label class="modal__label">Repository Slug</label>
                                    <div class="modal__input-wrapper">
                                        <div class="modal__input-icon">
                                            <i class="fa-brands fa-github"></i>
                                        </div>
                                        <input type="text" name="repo_slug" required class="modal__input modal__input--with-icon" placeholder="owner/repo-name">
                                    </div>
                                    <p class="modal__input-help">Format: owner/repo-name (e.g., woocommerce/woocommerce)</p>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="modal__footer">
                            <button type="submit" class="modal__footer-btn modal__footer-btn--primary">
                                Load Repo
                            </button>
                            <button type="button" onclick="GRNDSKPR.Dashboard.closeModal('add-repo')" class="modal__footer-btn modal__footer-btn--secondary">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: Bulk Action View -->
    <div id="modal-duplicates" class="modal hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="modal__backdrop"></div>

        <!-- Fixed wrapper for centering -->
        <div class="modal__wrapper" onclick="GRNDSKPR.Dashboard.closeModal('duplicates')">
            <div class="modal__container">

                <!-- Modal Panel -->
                <div class="modal__panel modal__panel--large" onclick="event.stopPropagation()">

                    <!-- Modal Header -->
                    <div class="modal__header">
                        <div class="modal__header-content">
                            <h3 class="modal__title" id="modal-title">Review Likely Duplicates</h3>
                            <p class="modal__description">Found <?php echo $glob['duplicates_count']; ?> groups of issues that appear semantically similar (≥85% similarity).</p>
                        </div>
                        <button onclick="GRNDSKPR.Dashboard.closeModal('duplicates')" class="modal__close-btn">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <!-- Bulk Actions Toolbar -->
                    <div class="modal__toolbar">
                        <div class="modal__toolbar-left">
                            <label class="modal__select-all-label">
                                <input type="checkbox" id="select-all-duplicates" onchange="GRNDSKPR.Dashboard.toggleSelectAll('duplicates')" class="modal__select-all-checkbox">
                                <span class="modal__select-all-text">Select All</span>
                            </label>
                            <span class="modal__toolbar-divider"></span>
                            <span class="modal__selected-count" id="selected-count-duplicates">0 selected</span>
                        </div>
                        <div class="modal__toolbar-right">
                            <button onclick="GRNDSKPR.Dashboard.openSelectedIssueUrls('duplicates')" class="modal__action-btn modal__action-btn--secondary">
                                <i class="fa-solid fa-up-right-from-square"></i>
                                Open in Tabs
                            </button>
                            <button onclick="GRNDSKPR.Dashboard.copySelectedIssueUrls('duplicates')" class="modal__action-btn">
                                <i class="fa-solid fa-copy"></i>
                                Copy Issue URLs
                            </button>
                        </div>
                    </div>

                    <!-- List Content -->
                    <div class="modal__content">
                        <table class="modal__table">
                            <thead class="modal__table-head">
                                <tr>
                                    <th scope="col" class="modal__table-header modal__table-header--checkbox"></th>
                                    <th scope="col" class="modal__table-header">Primary Issue</th>
                                    <th scope="col" class="modal__table-header">Similar Issues</th>
                                    <th scope="col" class="modal__table-header">Similarity</th>
                                    <th scope="col" class="modal__table-header modal__table-header--actions"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody id="tbody-duplicates" class="modal__table-body"></tbody>
                        </table>
                    </div>

                    <!-- Footer Removed -->
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: High Signal Issues -->
    <div id="modal-high-signal" class="modal hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="modal__backdrop"></div>

        <!-- Fixed wrapper for centering -->
        <div class="modal__wrapper" onclick="GRNDSKPR.Dashboard.closeModal('high-signal')">
            <div class="modal__container">

                <!-- Modal Panel -->
                <div class="modal__panel modal__panel--large" onclick="event.stopPropagation()">

                    <!-- Modal Header -->
                    <div class="modal__header">
                        <div class="modal__header-content">
                            <h3 class="modal__title">Missing Priority Labels</h3>
                            <p class="modal__description"><?php echo $glob['high_signal_count']; ?> Critical/High/Medium issues that need priority labels.</p>
                        </div>
                        <button onclick="GRNDSKPR.Dashboard.closeModal('high-signal')" class="modal__close-btn">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>

                    <!-- Bulk Actions Toolbar -->
                    <div class="modal__toolbar">
                        <div class="modal__toolbar-left">
                            <label class="modal__select-all-label">
                                <input type="checkbox" id="select-all-high-signal" onchange="GRNDSKPR.Dashboard.toggleSelectAll('high-signal')" class="modal__select-all-checkbox">
                                <span class="modal__select-all-text">Select All</span>
                            </label>
                            <span class="modal__toolbar-divider"></span>
                            <span class="modal__selected-count" id="selected-count-high-signal">0 selected</span>
                        </div>
                        <div class="modal__toolbar-right">
                            <button onclick="GRNDSKPR.Dashboard.openSelectedIssueUrls('high-signal')" class="modal__action-btn modal__action-btn--secondary">
                                <i class="fa-solid fa-up-right-from-square"></i>
                                Open in Tabs
                            </button>
                            <button onclick="GRNDSKPR.Dashboard.copySelectedIssueUrls('high-signal')" class="modal__action-btn">
                                <i class="fa-solid fa-copy"></i>
                                Copy Issue URLs
                            </button>
                        </div>
                    </div>

                    <!-- List Content -->
                    <div class="modal__content">
                        <table class="modal__table">
                            <thead class="modal__table-head">
                                <tr>
                                    <th scope="col" class="modal__table-header modal__table-header--checkbox"></th>
                                    <th scope="col" class="modal__table-header">Priority</th>
                                    <th scope="col" class="modal__table-header">Issue</th>
                                    <th scope="col" class="modal__table-header" style="white-space: nowrap;">Current Priority</th>
                                    <th scope="col" class="modal__table-header modal__table-header--actions"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody id="tbody-high-signal" class="modal__table-body"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: Cleanup Candidates -->
    <div id="modal-cleanup" class="modal hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="modal__backdrop"></div>

        <!-- Fixed wrapper for centering -->
        <div class="modal__wrapper" onclick="GRNDSKPR.Dashboard.closeModal('cleanup')">
            <div class="modal__container">

                <!-- Modal Panel -->
                <div class="modal__panel modal__panel--large" onclick="event.stopPropagation()">

                    <!-- Modal Header -->
                    <div class="modal__header">
                        <div class="modal__header-content">
                            <h3 class="modal__title">Review Cleanup Candidates</h3>
                            <p class="modal__description">Found <?php echo $glob['cleanup_count']; ?> issues identified as candidates for closure by AI analysis.</p>
                        </div>
                        <button onclick="GRNDSKPR.Dashboard.closeModal('cleanup')" class="modal__close-btn">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>

                    <!-- Bulk Actions Toolbar -->
                    <div class="modal__toolbar">
                        <div class="modal__toolbar-left">
                            <label class="modal__select-all-label">
                                <input type="checkbox" id="select-all-cleanup" onchange="GRNDSKPR.Dashboard.toggleSelectAll('cleanup')" class="modal__select-all-checkbox">
                                <span class="modal__select-all-text">Select All</span>
                            </label>
                            <span class="modal__toolbar-divider"></span>
                            <span class="modal__selected-count" id="selected-count-cleanup">0 selected</span>
                        </div>
                        <div class="modal__toolbar-right">
                            <button onclick="GRNDSKPR.Dashboard.openSelectedIssueUrls('cleanup')" class="modal__action-btn modal__action-btn--secondary">
                                <i class="fa-solid fa-up-right-from-square"></i>
                                Open in Tabs
                            </button>
                            <button onclick="GRNDSKPR.Dashboard.copySelectedIssueUrls('cleanup')" class="modal__action-btn">
                                <i class="fa-solid fa-copy"></i>
                                Copy Issue URLs
                            </button>
                        </div>
                    </div>

                    <!-- List Content -->
                    <div class="modal__content">
                        <table class="modal__table">
                            <thead class="modal__table-head">
                                <tr>
                                    <th scope="col" class="modal__table-header modal__table-header--checkbox"></th>
                                    <th scope="col" class="modal__table-header">Issue</th>
                                    <th scope="col" class="modal__table-header">Labels</th>
                                    <th scope="col" class="modal__table-header">Last Activity</th>
                                    <th scope="col" class="modal__table-header modal__table-header--actions"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody id="tbody-cleanup" class="modal__table-body"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: Missing Info -->
    <div id="modal-missing-info" class="modal hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="modal__backdrop"></div>

        <!-- Fixed wrapper for centering -->
        <div class="modal__wrapper" onclick="GRNDSKPR.Dashboard.closeModal('missing-info')">
            <div class="modal__container">

                <!-- Modal Panel -->
                <div class="modal__panel modal__panel--large" onclick="event.stopPropagation()">

                    <!-- Modal Header -->
                    <div class="modal__header">
                        <div class="modal__header-content">
                            <h3 class="modal__title">Review Issues Missing Context</h3>
                            <p class="modal__description">Found <?php echo $glob['missing_info_count']; ?> issues missing critical information identified by AI analysis.</p>
                        </div>
                        <button onclick="GRNDSKPR.Dashboard.closeModal('missing-info')" class="modal__close-btn">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>

                    <!-- Bulk Actions Toolbar -->
                    <div class="modal__toolbar">
                        <div class="modal__toolbar-left">
                            <label class="modal__select-all-label">
                                <input type="checkbox" id="select-all-missing-info" onchange="GRNDSKPR.Dashboard.toggleSelectAll('missing-info')" class="modal__select-all-checkbox">
                                <span class="modal__select-all-text">Select All</span>
                            </label>
                            <span class="modal__toolbar-divider"></span>
                            <span class="modal__selected-count" id="selected-count-missing-info">0 selected</span>
                        </div>
                        <div class="modal__toolbar-right">
                            <button onclick="GRNDSKPR.Dashboard.openSelectedIssueUrls('missing-info')" class="modal__action-btn modal__action-btn--secondary">
                                <i class="fa-solid fa-up-right-from-square"></i>
                                Open in Tabs
                            </button>
                            <button onclick="GRNDSKPR.Dashboard.copySelectedIssueUrls('missing-info')" class="modal__action-btn">
                                <i class="fa-solid fa-copy"></i>
                                Copy Issue URLs
                            </button>
                        </div>
                    </div>

                    <!-- List Content -->
                    <div class="modal__content">
                        <table class="modal__table">
                            <thead class="modal__table-head">
                                <tr>
                                    <th scope="col" class="modal__table-header modal__table-header--checkbox"></th>
                                    <th scope="col" class="modal__table-header">Issue</th>
                                    <th scope="col" class="modal__table-header">Missing Elements</th>
                                    <th scope="col" class="modal__table-header">Labels</th>
                                    <th scope="col" class="modal__table-header modal__table-header--actions"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody id="tbody-missing-info" class="modal__table-body"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: Suggestions -->
    <div id="modal-suggestions" class="modal hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="modal__backdrop"></div>

        <!-- Fixed wrapper for centering -->
        <div class="modal__wrapper" onclick="GRNDSKPR.Dashboard.closeModal('suggestions')">
            <div class="modal__container">

                <!-- Modal Panel -->
                <div class="modal__panel modal__panel--large" onclick="event.stopPropagation()">

                    <!-- Modal Header -->
                    <div class="modal__header">
                        <div class="modal__header-content">
                            <h3 class="modal__title">Review Label Suggestions</h3>
                            <p class="modal__description">Found <?php echo $glob['suggestions_count']; ?> issues with AI-recommended labels from the repository.</p>
                        </div>
                        <button onclick="GRNDSKPR.Dashboard.closeModal('suggestions')" class="modal__close-btn">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>

                    <!-- Bulk Actions Toolbar -->
                    <div class="modal__toolbar">
                        <div class="modal__toolbar-left">
                            <label class="modal__select-all-label">
                                <input type="checkbox" id="select-all-suggestions" onchange="GRNDSKPR.Dashboard.toggleSelectAll('suggestions')" class="modal__select-all-checkbox">
                                <span class="modal__select-all-text">Select All</span>
                            </label>
                            <span class="modal__toolbar-divider"></span>
                            <span class="modal__selected-count" id="selected-count-suggestions">0 selected</span>
                        </div>
                        <div class="modal__toolbar-right">
                            <button onclick="GRNDSKPR.Dashboard.openSelectedIssueUrls('suggestions')" class="modal__action-btn modal__action-btn--secondary">
                                <i class="fa-solid fa-up-right-from-square"></i>
                                Open in Tabs
                            </button>
                            <button onclick="GRNDSKPR.Dashboard.copySelectedIssueUrls('suggestions')" class="modal__action-btn">
                                <i class="fa-solid fa-copy"></i>
                                Copy Issue URLs
                            </button>
                        </div>
                    </div>

                    <!-- List Content -->
                    <div class="modal__content">
                        <table class="modal__table">
                            <thead class="modal__table-head">
                                <tr>
                                    <th scope="col" class="modal__table-header modal__table-header--checkbox"></th>
                                    <th scope="col" class="modal__table-header">Issue</th>
                                    <th scope="col" class="modal__table-header">Suggested Changes</th>
                                    <th scope="col" class="modal__table-header">Reasoning</th>
                                    <th scope="col" class="modal__table-header modal__table-header--actions"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody id="tbody-suggestions" class="modal__table-body"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: Popup Blocker Instructions -->
    <div id="modal-popup-blocker" class="modal hidden" aria-labelledby="popup-blocker-title" role="dialog" aria-modal="true">
        <div class="modal__backdrop"></div>
        <div class="modal__wrapper" onclick="GRNDSKPR.Dashboard.closePopupBlockerModal()">
            <div class="modal__container">
                <div class="modal__panel" onclick="event.stopPropagation()" style="max-width: 32rem;">
                    <div class="modal__header">
                        <div class="modal__header-content">
                            <h3 class="modal__title" id="popup-blocker-title">Allow Pop-ups to Open Multiple Tabs</h3>
                            <p class="modal__description">Your browser blocks multiple tabs from opening at once. To use this feature, you'll need to allow pop-ups for this site.</p>
                        </div>
                        <button type="button" onclick="GRNDSKPR.Dashboard.closePopupBlockerModal()" class="modal__close-btn">
                            <span class="sr-only">Close</span>
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div class="modal__form-content">
                        <p style="font-size: 0.875rem; color: #475569; margin-bottom: 1rem;">When you click "Open in Tabs", look for the pop-up blocked icon in your browser's address bar and select "Always allow pop-ups":</p>
                        <img src="<?php echo BASEURL; ?>img/popup-blocker.png" alt="Browser popup blocker settings" style="width: 100%; border-radius: 0.5rem; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                        <div style="margin-top: 1.5rem; display: flex; justify-content: flex-end; gap: 0.75rem;">
                            <button type="button" onclick="GRNDSKPR.Dashboard.closePopupBlockerModal(true)" class="modal__action-btn">
                                Got it, continue
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

	<script type="text/javascript" src="<?php echo BASEURL; ?>js/groundskeeper-utility.js"></script>
    <script type="text/javascript" src="<?php echo BASEURL; ?>js/groundskeeper-app.js"></script>
    <script type="text/javascript" src="<?php echo BASEURL; ?>js/groundskeeper-sync.js"></script>
    <script>
        // Initialize dashboard configuration
        window.GRNDSKPR_CONFIG = {
            baseUrl: '<?php echo BASEURL; ?>',
            repositoryId: <?php echo isset($glob['selected_repo']) ? $glob['selected_repo']['id'] : 'null'; ?>
        };

        // Cache for modal data to avoid refetching
        window.GRNDSKPR_CACHE = {};

        // Initialize modules when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize sync module
            GRNDSKPR.Sync.init(GRNDSKPR_CONFIG.baseUrl);

            // Handle session messages
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

    <!-- Toast Notification -->
    <div id="toast" class="alert"></div>

    <!-- Audit Loading Overlay -->
    <div id="audit-loading" class="loading-overlay loading-overlay--hidden">
        <div class="loading-overlay__panel">
            <div class="loading-overlay__spinner loading-overlay__spinner--emerald"></div>
            <h3 class="loading-overlay__title">Running Audit...</h3>
            <p class="loading-overlay__message">Importing issues from GitHub</p>
        </div>
    </div>

    <!-- Analyze Loading Overlay -->
    <div id="analyze-loading" class="loading-overlay loading-overlay--hidden">
        <div class="loading-overlay__panel loading-overlay__panel--wide">
            <div class="loading-overlay__spinner loading-overlay__spinner--blue"></div>
            <h3 class="loading-overlay__title">Analyzing Issues...</h3>
            <p class="loading-overlay__message">Processing bug reports with AI</p>
            <p class="loading-overlay__submessage">This can take 5-15 minutes for large repos. Time to grab a coffee! ☕</p>
        </div>
    </div>

    <!-- Area Approval Modal -->
    <?php if (isset($glob['pending_areas']) && !empty($glob['pending_areas'])): ?>
    <div id="area-approval-modal" class="area-approval-modal">
        <div class="area-approval-modal__content">
            <h3 class="area-approval-modal__title">
                <i class="fa-solid fa-sparkles"></i>
                Review Discovered Areas
            </h3>
            <p class="area-approval-modal__description">
                The following functional areas were discovered by analyzing your issues.
                You can edit, add, or remove areas before saving (one per line).
            </p>

            <form id="area-approval-form" method="POST" action="<?php echo BASEURL; ?>analyze/approve-areas">
                <textarea
                    id="area-approval-textarea"
                    name="areas"
                    rows="12"
                    class="area-approval-modal__textarea"
                    placeholder="Enter areas (one per line)"
                ><?php echo htmlspecialchars(implode("\n", $glob['pending_areas']['areas'])); ?></textarea>

                <div class="area-approval-modal__actions">
                    <a href="<?php echo BASEURL; ?>" class="area-approval-modal__btn area-approval-modal__btn--cancel">
                        Cancel
                    </a>
                    <button type="submit" class="area-approval-modal__btn area-approval-modal__btn--approve">
                        <i class="fa-solid fa-check mr-1"></i>
                        Approve & Save Areas
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Unified Progress Modal -->
    <div id="progress-modal" class="progress-modal hidden">
        <div class="progress-modal__panel">
            <h3 class="progress-modal__header">
                <i id="progress-icon" class="fa-solid fa-arrows-rotate fa-spin"></i>
                <span id="progress-title">Syncing & Analyzing</span>
            </h3>
            <p id="progress-description" class="progress-modal__description">
                Processing repository changes. This may take a while...
            </p>

            <!-- Step Indicators -->
            <div class="progress-modal__steps">
                <div class="progress-modal__step">
                    <div id="step-sync-icon" class="progress-modal__step-icon progress-modal__step-icon--active">
                        <i class="fa-solid fa-sync fa-spin"></i>
                    </div>
                    <span class="progress-modal__step-label">Sync</span>
                </div>
                <div class="progress-modal__step-line progress-modal__step-line--inactive" id="step-line-1"></div>
                <div class="progress-modal__step">
                    <div id="step-areas-icon" class="progress-modal__step-icon progress-modal__step-icon--inactive">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <span class="progress-modal__step-label">Areas</span>
                </div>
                <div class="progress-modal__step-line progress-modal__step-line--inactive" id="step-line-2"></div>
                <div class="progress-modal__step">
                    <div id="step-analyze-icon" class="progress-modal__step-icon progress-modal__step-icon--inactive">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <span class="progress-modal__step-label">Analyze</span>
                </div>
            </div>

            <div class="progress-modal__progress">
                <div class="progress-modal__progress-header">
                    <span id="progress-text">Starting sync...</span>
                    <span id="progress-percent">0%</span>
                </div>
                <div class="progress-modal__progress-bar-bg">
                    <div id="progress-bar" class="progress-modal__progress-bar" style="width: 0%"></div>
                </div>
            </div>

            <p id="progress-details" class="progress-modal__details">
                <span id="progress-count">0</span> of <span id="progress-total">0</span> issues processed
            </p>

            <div id="progress-error" class="progress-modal__error hidden">
                <p class="progress-modal__error-text"></p>
            </div>
        </div>
    </div>

    <!-- Template: Missing Priority Labels Row -->
    <!-- Variables: title, url, issue_number, priority_score, priorityClass, priorityBg, priorityText, priorityIcon, timeText, priority_label, priority_label_class -->
    <script type="text/template" id="tmpl-high-signal-row">
        <tr class="modal__table-row">
            <td class="modal__table-cell modal__table-cell--nowrap">
                <input type="checkbox" class="modal__select-all-checkbox" onchange="GRNDSKPR.Dashboard.updateSelectedCount(&#39;high-signal&#39;)">
            </td>
            <td class="modal__table-cell modal__table-cell--nowrap">
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
            <td class="modal__table-cell">
                <div class="text-sm font-medium text-slate-900"><%- title %></div>
                <div class="text-xs text-slate-500">
                    <a href="<%= url %>" target="_blank" class="text-slate-500 hover:text-emerald-600 hover:underline">#<%= issue_number %> <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i></a>
                    • opened <%= timeText %>
                </div>
            </td>
            <td class="modal__table-cell modal__table-cell--nowrap">
                <% if (priority_label) { %>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold <%= priority_label_class %>">
                        <%= priority_label %>
                    </span>
                <% } else { %>
                    <span class="text-sm text-slate-500 italic">Unprioritized</span>
                <% } %>
            </td>
            <td class="modal__table-cell modal__table-cell--nowrap modal__table-cell--right">
                <a href="<%= url %>" target="_blank" class="text-emerald-600 hover:text-emerald-900 font-medium">View</a>
            </td>
        </tr>
    </script>

    <!-- Template: Duplicate Row -->
    <!-- Variables: primary_title, primary_url, primary_number, primaryTimeText, duplicate_url, duplicate_number, duplicate_title, dupTimeText, similarityPercent, badgeClass -->
    <script type="text/template" id="tmpl-duplicate-row">
        <tr class="modal__table-row">
            <td class="modal__table-cell modal__table-cell--nowrap">
                <input type="checkbox" class="form-checkbox h-4 w-4 text-emerald-600 rounded border-slate-300" onchange="GRNDSKPR.Dashboard.updateSelectedCount(&#39;duplicates&#39;)">
            </td>
            <td class="modal__table-cell">
                <div class="text-sm font-medium text-slate-900"><%- primary_title %></div>
                <div class="text-xs text-slate-500">
                    <a href="<%= primary_url %>" target="_blank" class="text-slate-500 hover:text-emerald-600 hover:underline">#<%= primary_number %> <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i></a>
                    • opened <%= primaryTimeText %>
                </div>
            </td>
            <td class="modal__table-cell">
                <div class="text-sm text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-arrow-right-long text-slate-400"></i>
                    <div>
                        <a href="<%= duplicate_url %>" target="_blank" class="hover:underline text-emerald-700">#<%= duplicate_number %>: <%- duplicate_title %> <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i></a>
                        <div class="text-xs text-slate-400">opened <%= dupTimeText %></div>
                    </div>
                </div>
            </td>
            <td class="modal__table-cell modal__table-cell--nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <%= badgeClass %>"><%= similarityPercent %>% Match</span>
            </td>
            <td class="modal__table-cell modal__table-cell--nowrap modal__table-cell--right">
                <a href="<%= duplicate_url %>" target="_blank" class="text-emerald-600 hover:text-emerald-900 font-medium">View</a>
            </td>
        </tr>
    </script>

    <!-- Template: Cleanup Row -->
    <!-- Variables: title, url, issue_number, openedText, labels (array), activityText -->
    <script type="text/template" id="tmpl-cleanup-row">
        <tr class="modal__table-row">
            <td class="modal__table-cell modal__table-cell--nowrap">
                <input type="checkbox" class="modal__select-all-checkbox" onchange="GRNDSKPR.Dashboard.updateSelectedCount(&#39;cleanup&#39;)">
            </td>
            <td class="modal__table-cell">
                <div class="text-sm font-medium text-slate-900"><%- title %></div>
                <div class="text-xs text-slate-500">
                    <a href="<%= url %>" target="_blank" class="text-slate-500 hover:text-emerald-600 hover:underline">#<%= issue_number %> <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i></a>
                    • opened <%= openedText %>
                </div>
            </td>
            <td class="modal__table-cell">
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
            <td class="modal__table-cell modal__table-cell--nowrap text-sm text-slate-500">
                <%= activityText %>
            </td>
            <td class="modal__table-cell modal__table-cell--nowrap modal__table-cell--right">
                <a href="<%= url %>" target="_blank" class="text-emerald-600 hover:text-emerald-900 font-medium">View</a>
            </td>
        </tr>
    </script>

    <!-- Template: Missing Info Row -->
    <!-- Variables: title, url, issue_number, openedText, missingElements (array), labels (array) -->
    <script type="text/template" id="tmpl-missing-info-row">
        <tr class="modal__table-row">
            <td class="modal__table-cell modal__table-cell--nowrap">
                <input type="checkbox" class="modal__select-all-checkbox" onchange="GRNDSKPR.Dashboard.updateSelectedCount(&#39;missing-info&#39;)">
            </td>
            <td class="modal__table-cell">
                <div class="text-sm font-medium text-slate-900"><%- title %></div>
                <div class="text-xs text-slate-500">
                    <a href="<%= url %>" target="_blank" class="text-slate-500 hover:text-emerald-600 hover:underline">#<%= issue_number %> <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i></a>
                    • opened <%= openedText %>
                </div>
            </td>
            <td class="modal__table-cell">
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
            <td class="modal__table-cell">
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
            <td class="modal__table-cell modal__table-cell--nowrap modal__table-cell--right">
                <a href="<%= url %>" target="_blank" class="text-emerald-600 hover:text-emerald-900 font-medium">View</a>
            </td>
        </tr>
    </script>

    <!-- Template: Suggestions Row -->
    <!-- Variables: title, url, issue_number, timeText, currentLabels (array), suggestedLabels (array), reasoning -->
    <script type="text/template" id="tmpl-suggestions-row">
        <tr class="modal__table-row">
            <td class="modal__table-cell modal__table-cell--nowrap">
                <input type="checkbox" class="modal__select-all-checkbox" onchange="GRNDSKPR.Dashboard.updateSelectedCount(&#39;suggestions&#39;)">
            </td>
            <td class="modal__table-cell">
                <div class="text-sm font-medium text-slate-900"><%- title %></div>
                <div class="text-xs text-slate-500">
                    <a href="<%= url %>" target="_blank" class="text-slate-500 hover:text-emerald-600 hover:underline">#<%= issue_number %> <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i></a>
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
            <td class="modal__table-cell">
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
            <td class="modal__table-cell text-sm text-slate-500">
                <%- reasoning && reasoning.length > 50 ? reasoning.substring(0, 50) + '...' : reasoning %>
            </td>
            <td class="modal__table-cell modal__table-cell--nowrap modal__table-cell--right">
                <a href="<%= url %>" target="_blank" class="text-emerald-600 hover:text-emerald-800 font-medium">View</a>
            </td>
        </tr>
    </script>

</body>
</html>