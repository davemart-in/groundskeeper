/**
 * Groundskeeper Dashboard JavaScript
 * Handles modal rendering, filtering, and UI interactions for the dashboard
 */

// Ensure GRNDSKPR namespace exists
if (typeof window.GRNDSKPR === 'undefined') {
    window.GRNDSKPR = {};
}

window.GRNDSKPR.Dashboard = (function() {
    'use strict';

    // Current filter context
    let currentAreaFilter = null;

    /**
     * Switch between dashboard tabs
     */
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

    /**
     * Fetch modal data via AJAX
     */
    async function fetchModalData(modalId, areaId = null) {
        const config = window.GRNDSKPR_CONFIG;

        if (!config || !config.repositoryId) {
            console.error('Repository ID not available');
            return null;
        }

        // Build cache key
        const cacheKey = `${modalId}-${areaId || 'all'}`;

        // Check cache first
        if (window.GRNDSKPR_CACHE[cacheKey]) {
            return window.GRNDSKPR_CACHE[cacheKey];
        }

        // Build API URL
        let url = `${config.baseUrl}api-dashboard/${modalId}?repo_id=${config.repositoryId}`;
        if (areaId) {
            url += `&area_id=${areaId}`;
        }

        try {
            const response = await fetch(url);
            const result = await response.json();

            if (result.success) {
                // Cache the result
                window.GRNDSKPR_CACHE[cacheKey] = result.data;
                return result.data;
            } else {
                console.error('API error:', result.error);
                return null;
            }
        } catch (error) {
            console.error('Fetch error:', error);
            return null;
        }
    }

    /**
     * Render modal table with data
     */
    async function renderModalTable(modalId, areaId = null) {
        let templateId, tbodyId;

        // Determine which template and tbody to use
        switch(modalId) {
            case 'high-signal':
                templateId = 'tmpl-high-signal-row';
                tbodyId = 'tbody-high-signal';
                break;
            case 'duplicates':
                templateId = 'tmpl-duplicate-row';
                tbodyId = 'tbody-duplicates';
                break;
            case 'cleanup':
                templateId = 'tmpl-cleanup-row';
                tbodyId = 'tbody-cleanup';
                break;
            case 'missing-info':
                templateId = 'tmpl-missing-info-row';
                tbodyId = 'tbody-missing-info';
                break;
            case 'suggestions':
                templateId = 'tmpl-suggestions-row';
                tbodyId = 'tbody-suggestions';
                break;
            case 'all-issues':
                templateId = 'tmpl-all-issues-row';
                tbodyId = 'tbody-all-issues';
                break;
            default:
                console.error('Unknown modal type:', modalId);
                return;
        }

        // Get template and tbody
        const template = document.getElementById(templateId);
        const tbody = document.getElementById(tbodyId);

        if (!template || !tbody) {
            console.error('Template or tbody not found:', templateId, tbodyId);
            return;
        }

        // Show loading state
        tbody.innerHTML = '<tr><td colspan="10" style="text-align: center; padding: 2rem; color: #64748b;">Loading...</td></tr>';

        // Fetch data
        const data = await fetchModalData(modalId, areaId);

        if (!data) {
            tbody.innerHTML = '<tr><td colspan="10" style="text-align: center; padding: 2rem; color: #ef4444;">Error loading data</td></tr>';
            return;
        }

        // Clear loading state
        tbody.innerHTML = '';

        // Render each row
        if (modalId === 'duplicates') {
            renderDuplicates(data, template, tbody);
        } else {
            renderIssues(data, template, tbody, modalId);
        }
    }

    /**
     * Render regular issue rows
     */
    function renderIssues(data, template, tbody, modalId) {
        data.forEach(issue => {
            const rowData = prepareIssueData(issue, modalId);
            const html = _tmpl(template.innerHTML, rowData);
            tbody.insertAdjacentHTML('beforeend', html);
        });
    }

    /**
     * Render duplicate issue rows
     */
    function renderDuplicates(data, template, tbody) {
        data.forEach(group => {
            const primary = group.primary;
            const duplicates = group.duplicates;

            duplicates.forEach(duplicate => {
                const rowData = {
                    primary_title: primary.title,
                    primary_url: primary.url,
                    primary_number: primary.number,
                    primaryTimeText: formatTimeAgo(primary.created_at),
                    duplicate_url: duplicate.url,
                    duplicate_number: duplicate.number,
                    duplicate_title: duplicate.title,
                    dupTimeText: formatTimeAgo(duplicate.created_at),
                    similarityPercent: Math.round(duplicate.similarity * 100),
                    badgeClass: duplicate.similarity >= 0.9 ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                };
                const html = _tmpl(template.innerHTML, rowData);
                tbody.insertAdjacentHTML('beforeend', html);
            });
        });
    }

    /**
     * Prepare issue data for template rendering
     */
    function prepareIssueData(issue, modalId) {
        const data = {
            title: issue.title,
            url: issue.url,
            issue_number: issue.issue_number,
            timeText: formatTimeAgo(issue.created_at)
        };

        // Add modal-specific data
        if (modalId === 'high-signal') {
            const score = issue.priority_score || 0;
            const priority = getPriorityInfo(score);

            data.priority_score = score;
            data.priorityClass = priority.class;
            data.priorityBg = priority.bg;
            data.priorityText = priority.text;
            data.priorityIcon = priority.icon;
            data.reactions_total = issue.reactions_total || 0;
            data.comments_count = issue.comments_count || 0;

            // Check if issue has a priority label
            const priorityLabel = findPriorityLabel(issue.labels);
            data.priority_label = priorityLabel;
            data.priority_label_class = priorityLabel ? getPriorityLabelClass(priorityLabel) : '';
        } else if (modalId === 'cleanup') {
            data.openedText = formatTimeAgo(issue.created_at);
            data.labels = parseLabels(issue.labels);
            data.activityText = issue.updated_at ? formatTimeAgo(issue.updated_at) : 'Unknown';
        } else if (modalId === 'missing-info') {
            data.openedText = formatTimeAgo(issue.created_at);
            data.missingElements = parseMissingElements(issue.missing_elements);
            data.labels = parseLabels(issue.labels);
        } else if (modalId === 'suggestions') {
            data.currentLabels = parseLabels(issue.labels);
            data.suggestedLabels = parseLabels(issue.suggested_labels);
            data.reasoning = (issue.label_reasoning || 'Recommended based on issue content').substring(0, 50);
        } else if (modalId === 'all-issues') {
            data.labels = parseLabels(issue.labels);
            data.reactions_total = issue.reactions_total || 0;
            data.comments_count = issue.comments_count || 0;
        }

        return data;
    }

    /**
     * Get priority info based on score
     */
    function getPriorityInfo(score) {
        if (score >= 80) {
            return {
                class: 'bg-red-100 text-red-800 border-red-200',
                bg: 'bg-red-500',
                text: 'Critical',
                icon: 'fa-fire'
            };
        } else if (score >= 60) {
            return {
                class: 'bg-orange-100 text-orange-800 border-orange-200',
                bg: 'bg-orange-500',
                text: 'High',
                icon: 'fa-chevron-up'
            };
        } else if (score >= 40) {
            return {
                class: 'bg-yellow-100 text-yellow-800 border-yellow-200',
                bg: 'bg-yellow-500',
                text: 'Medium',
                icon: 'fa-minus'
            };
        } else {
            return {
                class: 'bg-blue-100 text-blue-800 border-blue-200',
                bg: 'bg-blue-500',
                text: 'Standard',
                icon: 'fa-info-circle'
            };
        }
    }

    /**
     * Format timestamp to human-readable time ago
     */
    function formatTimeAgo(timestamp) {
        const now = Math.floor(Date.now() / 1000);
        const diff = now - timestamp;

        if (diff < 3600) {
            return Math.floor(diff / 60) + ' minutes ago';
        } else if (diff < 86400) {
            return Math.floor(diff / 3600) + ' hours ago';
        } else if (diff < 2592000) {
            return Math.floor(diff / 86400) + ' days ago';
        } else {
            return Math.floor(diff / 2592000) + ' months ago';
        }
    }

    /**
     * Parse labels (handle both array and JSON string)
     */
    function parseLabels(labels) {
        if (Array.isArray(labels)) {
            return labels;
        }
        if (typeof labels === 'string') {
            try {
                return JSON.parse(labels);
            } catch (e) {
                return [];
            }
        }
        return [];
    }

    /**
     * Find priority label from issue labels
     */
    function findPriorityLabel(labels) {
        const parsedLabels = parseLabels(labels);
        const priorityKeywords = ['priority', 'critical', 'urgent', 'p0', 'p1', 'p2', 'p3', 'severity', 'blocker'];

        for (const label of parsedLabels) {
            const labelLower = label.toLowerCase();
            for (const keyword of priorityKeywords) {
                if (labelLower.includes(keyword)) {
                    return label;
                }
            }
        }
        return null;
    }

    /**
     * Get CSS class for priority label display
     */
    function getPriorityLabelClass(label) {
        const labelLower = label.toLowerCase();

        if (labelLower.includes('critical') || labelLower.includes('p0') || labelLower.includes('blocker')) {
            return 'bg-red-100 text-red-800 border-red-200';
        } else if (labelLower.includes('high') || labelLower.includes('p1') || labelLower.includes('urgent')) {
            return 'bg-orange-100 text-orange-800 border-orange-200';
        } else if (labelLower.includes('medium') || labelLower.includes('p2')) {
            return 'bg-yellow-100 text-yellow-800 border-yellow-200';
        } else if (labelLower.includes('low') || labelLower.includes('p3')) {
            return 'bg-blue-100 text-blue-800 border-blue-200';
        }

        return 'bg-slate-100 text-slate-800 border-slate-200';
    }

    /**
     * Parse missing elements
     */
    function parseMissingElements(elements) {
        if (Array.isArray(elements)) {
            return elements;
        }
        if (typeof elements === 'string') {
            try {
                return JSON.parse(elements);
            } catch (e) {
                return [];
            }
        }
        return [];
    }

    /**
     * Open a modal
     */
    function openModal(modalId) {
        const modal = document.getElementById(`modal-${modalId}`);
        modal.classList.remove('hidden');

        // Update modal title and count for all-issues
        if (modalId === 'all-issues') {
            const areaName = window.GRNDSKPR_CURRENT_AREA_NAME || 'Area';
            const count = window.GRNDSKPR_CURRENT_AREA_COUNT || 0;

            modal.querySelector('.modal__title').textContent = `All ${areaName} Issues`;
            modal.querySelector('.modal__description').innerHTML =
                `<span id="all-issues-count">${count}</span> issues in ${areaName} area.`;
        }

        // Render content when opening
        renderModalTable(modalId, currentAreaFilter);
    }

    /**
     * Close a modal
     */
    function closeModal(modalId) {
        document.getElementById(`modal-${modalId}`).classList.add('hidden');
    }

    /**
     * Toggle select all checkboxes in a modal
     */
    function toggleSelectAll(modalId) {
        const selectAllCheckbox = document.getElementById(`select-all-${modalId}`);
        const modal = document.getElementById(`modal-${modalId}`);
        const checkboxes = modal.querySelectorAll('tbody input[type="checkbox"]');

        // Toggle all checkboxes based on the "Select All" state
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });

        // Update count
        updateSelectedCount(modalId);
    }

    /**
     * Update the selected count for a modal
     */
    function updateSelectedCount(modalId) {
        const modal = document.getElementById(`modal-${modalId}`);
        const checkboxes = modal.querySelectorAll('tbody input[type="checkbox"]');
        const checkedBoxes = modal.querySelectorAll('tbody input[type="checkbox"]:checked');
        const selectedCountEl = document.getElementById(`selected-count-${modalId}`);
        const selectAllCheckbox = document.getElementById(`select-all-${modalId}`);

        // Update count display
        selectedCountEl.textContent = `${checkedBoxes.length} selected`;

        // Update "Select All" checkbox state
        if (checkedBoxes.length === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        } else if (checkedBoxes.length === checkboxes.length) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
        }
    }

    /**
     * Copy selected issue URLs to clipboard
     */
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

    // Store pending modal ID for popup blocker flow
    let pendingOpenTabsModalId = null;

    /**
     * Open selected issue URLs in new tabs
     */
    function openSelectedIssueUrls(modalId) {
        // Check if user has seen the popup blocker instructions
        const hasSeenPopupInstructions = localStorage.getItem('grndskpr_popup_instructions_seen');
        
        if (!hasSeenPopupInstructions) {
            // Store the modal ID and show instructions first
            pendingOpenTabsModalId = modalId;
            showPopupBlockerModal();
            return;
        }

        // Proceed with opening tabs
        doOpenSelectedIssueUrls(modalId);
    }

    /**
     * Actually open the selected issue URLs (after instructions shown)
     */
    function doOpenSelectedIssueUrls(modalId) {
        // Get all checked checkboxes in this modal
        const modal = document.getElementById(`modal-${modalId}`);
        const checkboxes = modal.querySelectorAll('tbody input[type="checkbox"]:checked');

        if (checkboxes.length === 0) {
            showToast('Please select at least one issue to open', true);
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

        // Open each URL in a new tab, tracking blocked popups
        let opened = 0;
        let blocked = 0;
        urls.forEach(url => {
            const newWindow = window.open(url, '_blank');
            if (newWindow === null || typeof newWindow === 'undefined') {
                blocked++;
            } else {
                opened++;
            }
        });

        // Show appropriate message
        if (blocked > 0 && opened === 0) {
            showToast('Popups blocked. Please allow popups for this site.', true);
        } else if (blocked > 0) {
            showToast(`Opened ${opened} tab${opened > 1 ? 's' : ''}, ${blocked} blocked. Allow popups for all tabs.`, true);
        } else {
            showToast(`Opened ${opened} issue${opened > 1 ? 's' : ''} in new tabs`);
        }
    }

    /**
     * Show the popup blocker instructions modal
     */
    function showPopupBlockerModal() {
        const modal = document.getElementById('modal-popup-blocker');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    /**
     * Close the popup blocker instructions modal
     */
    function closePopupBlockerModal(proceed = false) {
        const modal = document.getElementById('modal-popup-blocker');
        modal.classList.add('hidden');
        document.body.style.overflow = '';

        // Mark as seen
        localStorage.setItem('grndskpr_popup_instructions_seen', 'true');

        // If user clicked "Got it, continue", proceed with opening tabs
        if (proceed && pendingOpenTabsModalId) {
            doOpenSelectedIssueUrls(pendingOpenTabsModalId);
            pendingOpenTabsModalId = null;
        }
    }

    /**
     * Toggle visibility of area list
     */
    function toggleAreas() {
        const hiddenRows = document.querySelectorAll('.area-hidden');
        const btn = document.getElementById('btn-show-areas');
        const isHidden = hiddenRows[0].classList.contains('area-table__row--hidden');

        hiddenRows.forEach(row => {
            if(isHidden) {
                row.classList.remove('area-table__row--hidden');
            } else {
                row.classList.add('area-table__row--hidden');
            }
        });

        btn.textContent = isHidden ? 'Show less' : 'Show all';
    }

    /**
     * Filter dashboard by area
     */
    function filterDashboard(areaName, count, areaId) {
        // Store current filter context
        currentAreaFilter = areaId;
        window.GRNDSKPR_CURRENT_AREA_NAME = areaName;
        window.GRNDSKPR_CURRENT_AREA_COUNT = count;
        window.GRNDSKPR_CACHE = {};

        // Update header with breadcrumb
        document.getElementById('analysis-header').innerHTML = `
            <button onclick="GRNDSKPR.Dashboard.resetDashboard()" class="findings-header__back-btn">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <h3 class="text-lg font-bold text-slate-900">Analysis Findings</h3>
            <span class="text-slate-300 mx-2">/</span>
            <h3 class="text-lg font-bold text-emerald-700">${areaName}</h3>
        `;

        // Show and populate area total card
        document.getElementById('area-total-card').classList.remove('hidden');
        document.getElementById('area-total-count').textContent = count;
        document.getElementById('area-total-label').textContent = `All Issues in ${areaName}`;
        document.getElementById('stat-total').textContent = count;

        // Update area row selection
        document.querySelectorAll('.area-row').forEach(row => {
            row.classList.toggle('area-table__row--selected', row.dataset.areaId == areaId);
        });

        // Fetch and update filtered stats from API
        updateFilteredStats(areaId);
    }

    /**
     * Update filtered stats by fetching counts for a specific area
     */
    async function updateFilteredStats(areaId) {
        const categories = ['high-signal', 'duplicates', 'cleanup', 'missing-info', 'suggestions'];

        for (const category of categories) {
            try {
                const data = await fetchModalData(category, areaId);
                const count = data ? data.length : 0;

                // Update the stat display
                const statId = category === 'missing-info' ? 'stat-missing-info' : `stat-${category}`;
                const statEl = document.getElementById(statId);
                if (statEl) {
                    statEl.innerText = count;
                }
            } catch (error) {
                console.error(`Error fetching ${category} count:`, error);
            }
        }
    }

    /**
     * Reset dashboard to show all data
     */
    function resetDashboard() {
        // Clear filter context
        currentAreaFilter = null;
        window.GRNDSKPR_CURRENT_AREA_NAME = null;
        window.GRNDSKPR_CURRENT_AREA_COUNT = null;
        window.GRNDSKPR_CACHE = {};

        // Hide area total card and restore header
        document.getElementById('area-total-card')?.classList.add('hidden');
        document.getElementById('analysis-header').innerHTML =
            `<h3 class="text-lg font-bold text-slate-900">Analysis Findings</h3>`;

        // Remove all area row selections
        document.querySelectorAll('.area-row').forEach(row => {
            row.classList.remove('area-table__row--selected');
        });

        // Reload the page to restore original stats
        location.reload();
    }

    /**
     * Show toast notification
     */
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

    /**
     * Show loading overlay for audit
     */
    function showAuditLoading() {
        const overlay = document.getElementById('audit-loading');
        overlay.classList.remove('loading-overlay--hidden');
    }

    /**
     * Show loading overlay for analyze
     */
    function showAnalyzeLoading() {
        const overlay = document.getElementById('analyze-loading');
        overlay.classList.remove('loading-overlay--hidden');
    }

    /**
     * Switch to a different repository
     */
    function switchRepository(repoId) {
        // Redirect to dashboard with repo parameter
        window.location.href = window.GRNDSKPR_CONFIG.baseUrl + '?repo=' + repoId;
    }

    /**
     * Initialize dashboard functionality
     */
    function init() {
        // Initialization complete
    }

    // Public API
    return {
        init: init,
        switchTab: switchTab,
        switchRepository: switchRepository,
        openModal: openModal,
        closeModal: closeModal,
        toggleSelectAll: toggleSelectAll,
        updateSelectedCount: updateSelectedCount,
        copySelectedIssueUrls: copySelectedIssueUrls,
        openSelectedIssueUrls: openSelectedIssueUrls,
        closePopupBlockerModal: closePopupBlockerModal,
        toggleAreas: toggleAreas,
        filterDashboard: filterDashboard,
        resetDashboard: resetDashboard,
        showToast: showToast,
        showAuditLoading: showAuditLoading,
        showAnalyzeLoading: showAnalyzeLoading
    };
})();

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', window.GRNDSKPR.Dashboard.init);
} else {
    window.GRNDSKPR.Dashboard.init();
}
