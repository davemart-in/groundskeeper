/**
 * Groundskeeper Sync & Analysis Progress Handler
 * Manages the sync/analyze workflow including progress tracking and step indicators
 */

// Ensure GRNDSKPR namespace exists
if (typeof window.GRNDSKPR === 'undefined') {
    window.GRNDSKPR = {};
}

window.GRNDSKPR.Sync = (function() {
    'use strict';

    let currentJobId = null;
    let currentStep = 'sync'; // sync, areas, analyze
    let baseUrl = '';

    /**
     * Initialize sync module
     */
    function init(configBaseUrl) {
        baseUrl = configBaseUrl;

        // Attach event listeners
        const auditForm = document.getElementById('audit-form');
        if (auditForm) {
            auditForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const repoId = auditForm.getAttribute('data-repo-id');
                runAuditThenSync(repoId);
            });
        }

        const syncForm = document.getElementById('sync-form');
        if (syncForm) {
            syncForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const repoId = syncForm.getAttribute('data-repo-id');
                startSync(repoId);
            });
        }

        const areaApprovalForm = document.getElementById('area-approval-form');
        if (areaApprovalForm) {
            areaApprovalForm.addEventListener('submit', function(e) {
                e.preventDefault();
                approveAreasAndContinue();
            });
        }
    }

    function approveAreasAndContinue() {
        const areasText = document.getElementById('area-approval-textarea').value;

        fetch(baseUrl + 'analyze/approve-areas', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'areas=' + encodeURIComponent(areasText)
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                alert('Failed to save areas: ' + (data.error || 'Unknown error'));
                return;
            }

            // Hide area approval modal
            document.getElementById('area-approval-modal').classList.add('hidden');

            // Continue with analysis if we have a job ID
            if (data.job_id) {
                currentJobId = data.job_id;
                currentStep = 'analyze';
                showProgressModal();

                // Mark sync and areas as complete
                setStepIcon('sync', 'complete', 'check');
                setStepLine(1, true);
                setStepIcon('areas', 'complete', 'check');
                setStepLine(2, true);

                // Start analysis
                processAnalyzeChunk();
            } else {
                // No job ID, just reload
                window.location.reload();
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('Failed to save areas');
        });
    }

    function runAuditThenSync(repoId) {
        // Show progress modal immediately
        showProgressModal();
        document.getElementById('progress-text').textContent = 'Fetching issues from GitHub...';
        document.getElementById('progress-percent').textContent = '10%';
        document.getElementById('progress-bar').style.width = '10%';
        document.getElementById('progress-details').textContent = 'Running initial audit...';

        fetch(baseUrl + 'audit/run/' + repoId, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                showError(data.error || 'Audit failed');
                return;
            }

            // Audit complete, now start the sync/analyze flow
            document.getElementById('progress-details').textContent =
                `Imported ${data.issue_count} issues. Starting analysis...`;

            // Chain to startSync
            startSync(repoId);
        })
        .catch(err => {
            console.error('Error:', err);
            showError('Failed to run audit');
        });
    }

    function startSync(repoId) {
        fetch(baseUrl + 'sync/run/' + repoId, {
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
        const stateClasses = {
            inactive: 'progress-modal__step-icon--inactive',
            active: 'progress-modal__step-icon--active',
            complete: 'progress-modal__step-icon--complete'
        };
        const el = document.getElementById(`step-${stepId}-icon`);
        el.className = `progress-modal__step-icon ${stateClasses[state]}`;
        el.innerHTML = `<i class="fa-solid fa-${icon}${spinning ? ' fa-spin' : ''}"></i>`;
    }

    function setStepLine(lineId, complete) {
        const el = document.getElementById(`step-line-${lineId}`);
        el.className = `progress-modal__step-line ${complete ? 'progress-modal__step-line--complete' : 'progress-modal__step-line--inactive'}`;
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

        fetch(baseUrl + 'sync/process-sync/' + currentJobId, {
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

        fetch(baseUrl + 'sync/check-areas/' + currentJobId, {
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

        fetch(baseUrl + 'sync/process-analyze/' + currentJobId, {
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
        const descEl = document.getElementById('progress-description');

        if (countEl) countEl.textContent = processed;
        if (totalEl) totalEl.textContent = total;
        if (percentEl) percentEl.textContent = overallPercent + '%';
        if (barEl) barEl.style.width = overallPercent + '%';
        if (detailsEl) detailsEl.textContent = `${processed} of ${total} issues analyzed`;

        // Update estimated time remaining
        if (descEl && total > 0) {
            const remaining = total - processed;
            const minutesRemaining = Math.ceil((remaining * 15) / 60);
            if (minutesRemaining <= 1) {
                descEl.textContent = 'Estimated time: about 1 minute';
            } else {
                descEl.textContent = `Estimated time: about ${minutesRemaining} minutes`;
            }
        }
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

    // Public API
    return {
        init: init
    };
})();
