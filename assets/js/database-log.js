/**
 * Combined Database Logging JavaScript
 * Save this as: assets/js/database-log.js
 */

// Global variables
let autoRefreshEnabled = true;
let autoRefreshInterval;

/**
 * Quick filter functions for log levels
 */
function quickFilter(level) {
    const rows = document.querySelectorAll('.log-entry');
    let visibleCount = 0;

    rows.forEach(row => {
        const rowLevel = row.dataset.level;
        const show = level === '' || rowLevel === level;
        row.style.display = show ? '' : 'none';
        if (show) visibleCount++;
    });

    updateVisibleCount(visibleCount);

    // Update URL without reload
    if (level) {
        window.history.replaceState(null, null, `?log_level=${level}#log`);
    }
}

/**
 * Advanced filtering using form filters
 */
function filterLogs() {
    const levelFilter = document.getElementById('logLevelFilter')?.value || '';
    const tableFilter = document.getElementById('tableFilter')?.value || '';
    const actionFilter = document.getElementById('actionFilter')?.value || '';
    const dateFilter = document.getElementById('dateFilter')?.value || '';
    const rows = document.querySelectorAll('.log-entry');
    let visibleCount = 0;

    rows.forEach(row => {
        let show = true;

        if (levelFilter && row.dataset.level !== levelFilter) show = false;
        if (tableFilter && row.dataset.table !== tableFilter) show = false;
        if (actionFilter && row.dataset.action !== actionFilter) show = false;

        if (dateFilter) {
            const rowDate = row.cells[1].textContent.split(' ')[0];
            if (rowDate !== dateFilter) show = false;
        }

        row.style.display = show ? '' : 'none';
        if (show) visibleCount++;
    });

    updateVisibleCount(visibleCount);
}

/**
 * Update visible log count
 */
function updateVisibleCount(count) {
    const visibleElement = document.getElementById('visibleLogCount');
    if (visibleElement) {
        visibleElement.textContent = count;
    }
}

/**
 * Update log statistics
 */
function updateLogStats() {
    const rows = document.querySelectorAll('.log-entry');
    let visible = 0;

    rows.forEach(row => {
        if (row.style.display !== 'none') {
            visible++;
        }
    });

    updateVisibleCount(visible);
}

/**
 * Export logs to CSV
 */
function exportLogs() {
    const table = document.getElementById('logsTable');
    if (!table) {
        alert('No log table found');
        return;
    }

    const rows = Array.from(table.querySelectorAll('tbody tr')).filter(row =>
        row.style.display !== 'none' && row.id !== 'noLogsRow'
    );

    if (rows.length === 0) {
        alert('No logs to export');
        return;
    }

    let csv = 'Number,Timestamp,Level,Table,Action,User,Message\n';

    rows.forEach(row => {
        const cells = Array.from(row.cells).slice(0, 7);
        const csvRow = cells.map(cell => {
            let text = cell.textContent.trim();
            text = text.replace(/\n/g, ' ').replace(/"/g, '""');
            return `"${text}"`;
        }).join(',');
        csv += csvRow + '\n';
    });

    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `database_logs_${new Date().toISOString().split('T')[0]}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);

    // Show success toast
    showToast(`Exported ${rows.length} log entries successfully`, 'success');
}

/**
 * Clear old logs (server-side operation)
 */
function clearOldLogs() {
    if (confirm('Are you sure you want to clear logs older than 30 days? This action cannot be undone.')) {
        // Create a form to submit the clear logs request
        const form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';

        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'clear_logs';

        form.appendChild(actionInput);
        document.body.appendChild(form);
        form.submit();
    }
}

/**
 * Refresh logs
 */
function refreshLogs() {
    window.location.reload();
}

/**
 * Copy log details to clipboard
 */
function copyLogDetails(logId) {
    const modal = document.getElementById('logDetailModal' + logId);
    if (!modal) return;

    const details = modal.querySelector('pre code');
    if (!details) return;

    navigator.clipboard.writeText(details.textContent).then(() => {
        showToast("Log details copied to clipboard", 'info');
    }).catch(() => {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = details.textContent;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showToast("Log details copied to clipboard", 'info');
    });
}

/**
 * Scroll to top of logs section
 */
function scrollToTop() {
    const logSection = document.getElementById('log');
    if (logSection) {
        logSection.scrollIntoView({ behavior: 'smooth' });
    }
}

/**
 * Toggle auto-refresh functionality
 */
function toggleAutoRefresh() {
    autoRefreshEnabled = !autoRefreshEnabled;
    const statusElement = document.getElementById('autoRefreshStatus');
    const toggleText = document.getElementById('refreshToggleText');

    if (autoRefreshEnabled) {
        if (statusElement) statusElement.textContent = 'ON';
        if (toggleText) toggleText.textContent = 'Pause';
        startAutoRefresh();
    } else {
        if (statusElement) statusElement.textContent = 'OFF';
        if (toggleText) toggleText.textContent = 'Resume';
        stopAutoRefresh();
    }
}

/**
 * Start auto-refresh interval
 */
function startAutoRefresh() {
    if (autoRefreshEnabled && !autoRefreshInterval) {
        autoRefreshInterval = setInterval(() => {
            if (document.visibilityState === 'visible') {
                // Subtle refresh indication
                const refreshIcon = document.querySelector('.btn-outline-primary .ti-refresh');
                if (refreshIcon) {
                    refreshIcon.style.animation = 'spin 1s linear';
                    setTimeout(() => refreshIcon.style.animation = '', 1000);
                }

                // Update log stats without full reload
                updateLogStats();
            }
        }, 30000); // 30 seconds
    }
}

/**
 * Stop auto-refresh interval
 */
function stopAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
    }
}

/**
 * Search within logs
 */
function searchLogs(searchTerm) {
    const rows = document.querySelectorAll('.log-entry');
    let visibleCount = 0;

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const matches = text.includes(searchTerm.toLowerCase());
        row.style.display = matches || searchTerm === '' ? '' : 'none';
        if (matches || searchTerm === '') visibleCount++;
    });

    updateVisibleCount(visibleCount);
}

/**
 * Show toast notification
 */
function showToast(message, type = 'info') {
    if (typeof Toastify !== 'undefined') {
        const colors = {
            'success': '#28a745',
            'error': '#dc3545',
            'warning': '#ffc107',
            'info': '#17a2b8'
        };

        Toastify({
            text: message,
            duration: 3000,
            close: true,
            gravity: 'top',
            position: 'right',
            backgroundColor: colors[type] || colors.info,
            stopOnFocus: true
        }).showToast();
    } else {
        // Fallback to alert if Toastify is not available
        alert(message);
    }
}

/**
 * Initialize everything when DOM is loaded
 */
document.addEventListener('DOMContentLoaded', function () {
    // Initialize search functionality
    const searchInput = document.getElementById('logSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            searchLogs(this.value);
        });
    }

    // Initialize filter change handlers
    const filterElements = document.querySelectorAll('#logFilters select, #logFilters input[type="date"]');
    filterElements.forEach(element => {
        element.addEventListener('change', filterLogs);
    });

    // Start auto-refresh
    startAutoRefresh();

    // Initialize log stats
    updateLogStats();

    // Handle page visibility changes
    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            stopAutoRefresh();
        } else {
            if (autoRefreshEnabled) {
                startAutoRefresh();
            }
        }
    });

    // Scroll to logs if hash is present
    if (window.location.hash === '#log') {
        setTimeout(() => {
            scrollToTop();
        }, 100);
    }

    // Add smooth scrolling to log navigation
    const logNavLink = document.querySelector('a[href="#log"]');
    if (logNavLink) {
        logNavLink.addEventListener('click', function (e) {
            e.preventDefault();
            scrollToTop();
            window.history.pushState(null, null, '#log');
        });
    }

    // Highlight recent log entries (last 5 minutes)
    highlightRecentLogs();
});

/**
 * Highlight recent log entries
 */
function highlightRecentLogs() {
    const fiveMinutesAgo = new Date(Date.now() - 5 * 60 * 1000);
    const logRows = document.querySelectorAll('.log-entry');

    logRows.forEach(row => {
        const timestampCell = row.cells[1];
        if (timestampCell) {
            try {
                // Try to parse the timestamp
                const timeText = timestampCell.textContent.trim();
                const logTime = new Date(timeText);

                if (logTime > fiveMinutesAgo) {
                    row.classList.add('table-warning');
                    // Remove highlight after 10 seconds
                    setTimeout(() => {
                        row.classList.remove('table-warning');
                    }, 10000);
                }
            } catch (e) {
                // Ignore parsing errors
            }
        }
    });
}

/**
 * Add CSS animations
 */
const style = document.createElement('style');
style.textContent = `
    .log-message {
        word-wrap: break-word;
        white-space: normal;
        line-height: 1.4;
    }
    /* Keep other styles like animations */
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .log-entry.table-warning {
        animation: fadeIn 0.5s ease-in;
    }
`;
document.head.appendChild(style);

/**
 * Clean up on page unload
 */
window.addEventListener('beforeunload', function () {
    stopAutoRefresh();
});

/**
 * Export functions for global access
 */
window.logFunctions = {
    quickFilter,
    filterLogs,
    exportLogs,
    refreshLogs,
    copyLogDetails,
    scrollToTop,
    toggleAutoRefresh,
    searchLogs,
    showToast
};

function toggleLogChevron() {
    const chevron = document.getElementById('logChevron');
    const content = document.getElementById('systemLogsContent');

    // Toggle chevron rotation
    setTimeout(() => {
        if (content.classList.contains('show')) {
            chevron.style.transform = 'rotate(180deg)';
        } else {
            chevron.style.transform = 'rotate(0deg)';
        }
    }, 100);
}

// Listen for Bootstrap collapse events
document.addEventListener('DOMContentLoaded', function () {
    const systemLogsContent = document.getElementById('systemLogsContent');
    if (systemLogsContent) {
        systemLogsContent.addEventListener('shown.bs.collapse', function () {
            // Start auto-refresh when expanded
            if (typeof startAutoRefresh === 'function') {
                startAutoRefresh();
            }
        });

        systemLogsContent.addEventListener('hidden.bs.collapse', function () {
            // Stop auto-refresh when collapsed
            if (typeof stopAutoRefresh === 'function') {
                stopAutoRefresh();
            }
        });
    }
});