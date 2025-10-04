/**
 * DekkImporter Modern Admin JavaScript
 *
 * @package DekkImporter
 * @since 2.2.2
 */

(function($) {
    'use strict';

    // ==========================================
    // TOAST NOTIFICATION SYSTEM
    // ==========================================

    const Toast = {
        container: null,

        init: function() {
            if (!this.container) {
                this.container = $('<div class="dekkimporter-toast-container"></div>');
                $('body').append(this.container);
            }
        },

        show: function(message, type = 'info', duration = 5000) {
            this.init();

            const icons = {
                success: '✓',
                error: '✕',
                warning: '⚠',
                info: 'ℹ'
            };

            const toast = $(`
                <div class="dekkimporter-toast dekkimporter-toast-${type}">
                    <span class="dekkimporter-toast-icon">${icons[type] || icons.info}</span>
                    <span class="dekkimporter-toast-message">${message}</span>
                    <button class="dekkimporter-toast-close" aria-label="Close">&times;</button>
                </div>
            `);

            this.container.append(toast);

            // Trigger animation
            setTimeout(() => toast.addClass('dekkimporter-toast-show'), 10);

            // Auto dismiss
            const dismissTimeout = setTimeout(() => {
                this.dismiss(toast);
            }, duration);

            // Manual dismiss
            toast.find('.dekkimporter-toast-close').on('click', () => {
                clearTimeout(dismissTimeout);
                this.dismiss(toast);
            });
        },

        dismiss: function(toast) {
            toast.removeClass('dekkimporter-toast-show');
            setTimeout(() => toast.remove(), 300);
        },

        success: function(message, duration) {
            this.show(message, 'success', duration);
        },

        error: function(message, duration) {
            this.show(message, 'error', duration);
        },

        warning: function(message, duration) {
            this.show(message, 'warning', duration);
        },

        info: function(message, duration) {
            this.show(message, 'info', duration);
        }
    };

    // ==========================================
    // LOADING OVERLAY
    // ==========================================

    const LoadingOverlay = {
        overlay: null,

        show: function(message = 'Processing...') {
            if (!this.overlay) {
                this.overlay = $(`
                    <div class="dekkimporter-loading-overlay">
                        <div class="dekkimporter-loading-content">
                            <div class="dekkimporter-loading-spinner"></div>
                            <p class="dekkimporter-loading-text">${message}</p>
                        </div>
                    </div>
                `);
                $('body').append(this.overlay);
            }

            this.overlay.fadeIn(200);
        },

        updateMessage: function(message) {
            if (this.overlay) {
                this.overlay.find('.dekkimporter-loading-text').text(message);
            }
        },

        hide: function() {
            if (this.overlay) {
                this.overlay.fadeOut(200, () => {
                    this.overlay.remove();
                    this.overlay = null;
                });
            }
        }
    };

    // ==========================================
    // COUNTDOWN TIMER
    // ==========================================

    function updateCountdown() {
        const $countdown = $('#dekkimporter-countdown, #dekkimporter-widget-countdown');

        if ($countdown.length === 0) {
            return;
        }

        $countdown.each(function() {
            const $el = $(this);
            const nextSync = parseInt($el.data('timestamp'), 10);

            // Explicit NaN check for invalid timestamps
            if (!nextSync || isNaN(nextSync)) {
                $el.html('<span class="dekkimporter-countdown">No sync scheduled</span>');
                return;
            }

            const now = Math.floor(Date.now() / 1000);
            const diff = nextSync - now;

            if (diff <= 0) {
                // Past scheduled time - check if sync is actually running
                // The progress container visibility indicates if sync is running
                const isRunning = $('#dekkimporter-progress-container').is(':visible');

                if (isRunning) {
                    $el.html('<span class="dekkimporter-countdown syncing">Sync in progress...</span>');
                } else {
                    // Overdue but not running - waiting for WordPress cron
                    $el.html('<span class="dekkimporter-countdown countdown-orange">Waiting for cron...</span>');
                }
                return;
            }

            const hours = Math.floor(diff / 3600);
            const minutes = Math.floor((diff % 3600) / 60);
            const seconds = diff % 60;

            // Color based on time remaining
            let colorClass = 'green';
            if (diff < 3600) {
                colorClass = 'orange';
            }
            if (diff < 900) {
                colorClass = 'red';
            }

            const html = `<span class="dekkimporter-countdown countdown-${colorClass}">
                ${String(hours).padStart(2, '0')}h ${String(minutes).padStart(2, '0')}m ${String(seconds).padStart(2, '0')}s
            </span>`;

            $el.html(html);
        });
    }

    // ==========================================
    // PROGRESS TRACKING
    // ==========================================

    const ProgressTracker = {
        pollInterval: null,
        isPolling: false,

        start: function() {
            this.isPolling = true;
            this.showProgressContainer();
            this.poll();
            this.pollInterval = setInterval(() => this.poll(), 2000); // Poll every 2 seconds
        },

        stop: function() {
            this.isPolling = false;
            if (this.pollInterval) {
                clearInterval(this.pollInterval);
                this.pollInterval = null;
            }
        },

        poll: function() {
            $.ajax({
                url: dekkimporterAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'dekkimporter_sync_progress'
                },
                success: (response) => {
                    console.log('Progress poll response:', response);
                    if (response.success && response.data.in_progress) {
                        console.log('Progress data:', response.data.progress);
                        this.updateProgress(response.data.progress);

                        // Check if sync is completed
                        if (response.data.progress.status === 'completed') {
                            console.log('Sync marked as completed');
                            // Stop polling and reload page after showing final state
                            setTimeout(() => {
                                this.stop();
                                this.hideProgressContainer();
                                Toast.success('Sync completed! Reloading page...', 3000);
                                setTimeout(() => {
                                    location.reload();
                                }, 2000);
                            }, 3000);
                        }
                    } else {
                        console.log('Sync completed or not in progress');
                        // No progress data available
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Progress poll error:', error);
                }
            });
        },

        showProgressContainer: function() {
            $('#dekkimporter-progress-container').slideDown(300);
            // Keep sync control visible so users can see both
        },

        hideProgressContainer: function() {
            $('#dekkimporter-progress-container').slideUp(300);
            // Sync control is always visible
        },

        updateProgress: function(progress) {
            console.log('Updating UI with progress:', progress);

            // Ensure we have valid data
            if (!progress) {
                console.warn('No progress data received');
                return;
            }

            // Update percentage
            const percentage = parseFloat(progress.percentage) || 0;
            $('#progress-percentage').text(percentage.toFixed(1) + '%');
            $('#progress-fill').css('width', percentage + '%');

            // Update message
            const message = progress.message || 'Processing...';
            $('#progress-message').text(message);

            // Update stats with proper defaults
            const processed = parseInt(progress.processed) || 0;
            const total = parseInt(progress.total) || 0;
            const created = parseInt(progress.created) || 0;
            const updated = parseInt(progress.updated) || 0;
            const skipped = parseInt(progress.skipped) || 0;

            $('#stat-processed').text(`${processed} / ${total}`);
            $('#stat-created').text(created);
            $('#stat-updated').text(updated);
            $('#stat-skipped').text(skipped);

            console.log(`Stats: ${processed}/${total} - Created: ${created}, Updated: ${updated}, Skipped: ${skipped}`);

            // Update time remaining with explicit NaN check
            const estimatedTime = parseInt(progress.estimated_time, 10);
            if (!isNaN(estimatedTime) && estimatedTime > 0) {
                const minutes = Math.floor(estimatedTime / 60);
                const seconds = estimatedTime % 60;
                if (minutes > 0) {
                    $('#stat-time-remaining').text(`${minutes}m ${seconds}s`);
                } else {
                    $('#stat-time-remaining').text(`${seconds}s`);
                }
            } else {
                $('#stat-time-remaining').text('Calculating...');
            }
        },

        reset: function() {
            $('#progress-percentage').text('0%');
            $('#progress-fill').css('width', '0%');
            $('#stat-processed').text('0 / 0');
            $('#stat-created').text('0');
            $('#stat-updated').text('0');
            $('#stat-skipped').text('0');
            $('#stat-time-remaining').text('--');
        }
    };

    // ==========================================
    // MANUAL SYNC HANDLER
    // ==========================================

    function handleManualSync(e) {
        e.preventDefault();

        const $btn = $(this);
        const originalText = $btn.text();

        // Disable button and show syncing state
        $btn.prop('disabled', true)
            .addClass('syncing')
            .html('<span class="spinner-border spinner-border-sm me-2" role="status"></span> Syncing...');

        // Show progress tracking (don't reset - let real data come in)
        ProgressTracker.start();

        // Show info toast
        Toast.info('Product sync started. This may take several minutes...', 8000);

        $.ajax({
            url: dekkimporterAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'dekkimporter_manual_sync',
                nonce: dekkimporterAdmin.nonces.manualSync
            },
            timeout: 30000, // 30 second timeout for the scheduling request
            success: function(response) {
                console.log('Manual sync response:', response);

                if (response.success) {
                    if (response.data.background) {
                        // Background sync started - keep polling, don't stop
                        console.log('Background sync started, polling will continue...');
                        // Progress tracker is already running, just wait for it to detect completion
                    } else {
                        // Synchronous completion (shouldn't happen with new code, but handle it)
                        ProgressTracker.stop();
                        ProgressTracker.hideProgressContainer();

                        const stats = response.data.stats || {};
                        const message = `Sync completed! Created: ${stats.products_created || 0}, Updated: ${stats.products_updated || 0}`;
                        Toast.success(message, 8000);

                        setTimeout(() => {
                            location.reload();
                        }, 2000);
                    }
                } else {
                    // Error starting sync
                    ProgressTracker.stop();
                    ProgressTracker.hideProgressContainer();

                    const errorMsg = response.data.message || 'Failed to start sync';
                    Toast.error(errorMsg, 8000);
                    $btn.prop('disabled', false)
                        .removeClass('syncing')
                        .html(originalText);
                }
            },
            error: function(xhr, status, error) {
                // Stop progress polling
                ProgressTracker.stop();
                ProgressTracker.hideProgressContainer();

                let errorMessage = 'Sync failed: ' + error;

                // Try to parse error response
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.data && response.data.message) {
                        errorMessage = response.data.message;
                    }
                } catch (e) {
                    // Use default error message
                }

                Toast.error(errorMessage, 8000);
                $btn.prop('disabled', false)
                    .removeClass('syncing')
                    .html(originalText);
            }
        });
    }

    /**
     * Handle stop sync button click
     */
    function handleStopSync(e) {
        e.preventDefault();

        if (!confirm('Are you sure you want to stop the current sync? This action cannot be undone.')) {
            return;
        }

        const $btn = $(this);
        const originalText = $btn.html();

        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status"></span> Stopping...');

        $.ajax({
            url: dekkimporterAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'dekkimporter_stop_sync',
                nonce: dekkimporterAdmin.nonces.stopSync
            },
            success: function(response) {
                console.log('Stop sync response:', response);

                if (response.success) {
                    // Stop progress polling
                    ProgressTracker.stop();
                    ProgressTracker.hideProgressContainer();

                    // Re-enable manual sync button
                    $('#dekkimporter-manual-sync').prop('disabled', false)
                        .removeClass('syncing')
                        .html('<i class="bi bi-arrow-repeat me-2"></i> Run Manual Sync Now');

                    Toast.warning('Sync stopped successfully', 3000);

                    // Reload page to refresh stats
                    setTimeout(() => location.reload(), 2000);
                } else {
                    Toast.error(response.data.message || 'Failed to stop sync', 5000);
                    $btn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr, status, error) {
                Toast.error('Error stopping sync: ' + error, 5000);
                $btn.prop('disabled', false).html(originalText);
            }
        });
    }

    // ==========================================
    // FORM VALIDATION ENHANCEMENTS
    // ==========================================

    function enhanceFormValidation() {
        // Add real-time validation for email fields
        $('input[type="email"]').on('blur', function() {
            const $input = $(this);
            const email = $input.val().trim();

            if (email && !isValidEmail(email)) {
                $input.addClass('dekkimporter-input-error');
                Toast.warning('Please enter a valid email address', 3000);
            } else {
                $input.removeClass('dekkimporter-input-error');
            }
        });

        // Add real-time validation for URL fields
        $('input[type="url"]').on('blur', function() {
            const $input = $(this);
            const url = $input.val().trim();

            if (url && !isValidUrl(url)) {
                $input.addClass('dekkimporter-input-error');
                Toast.warning('Please enter a valid URL', 3000);
            } else {
                $input.removeClass('dekkimporter-input-error');
            }
        });

        // Add validation for number fields
        $('input[type="number"]').on('blur', function() {
            const $input = $(this);
            const value = parseInt($input.val(), 10);
            const min = parseInt($input.attr('min'), 10);
            const max = parseInt($input.attr('max'), 10);

            if (!isNaN(min) && value < min) {
                $input.val(min);
                Toast.warning(`Value must be at least ${min}`, 3000);
            }

            if (!isNaN(max) && value > max) {
                $input.val(max);
                Toast.warning(`Value must be at most ${max}`, 3000);
            }
        });
    }

    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    function isValidUrl(url) {
        try {
            new URL(url);
            return true;
        } catch (e) {
            return false;
        }
    }

    // ==========================================
    // SETTINGS SAVE FEEDBACK
    // ==========================================

    function enhanceSettingsSave() {
        const $form = $('form[method="post"][action="options.php"]');

        if ($form.length) {
            $form.on('submit', function() {
                const $submitBtn = $(this).find('.button-primary');
                $submitBtn.prop('disabled', true).text('Saving...');
                LoadingOverlay.show('Saving settings...');
            });
        }

        // Check for WordPress settings update messages
        const $settingsUpdated = $('.settings-updated');
        if ($settingsUpdated.length) {
            Toast.success('Settings saved successfully!', 5000);
        }

        const $settingsError = $('.settings-error');
        if ($settingsError.length) {
            const errorText = $settingsError.text().trim();
            if (errorText) {
                Toast.error(errorText, 8000);
            }
        }
    }

    // ==========================================
    // SMOOTH SCROLL TO SECTIONS
    // ==========================================

    function enableSmoothScroll() {
        $('a[href^="#"]').on('click', function(e) {
            const target = $(this.getAttribute('href'));

            if (target.length) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: target.offset().top - 50
                }, 500);
            }
        });
    }

    // ==========================================
    // COPY TO CLIPBOARD FUNCTIONALITY
    // ==========================================

    function enableCopyToClipboard() {
        $('.dekkimporter-copy-btn').on('click', function() {
            const $btn = $(this);
            const text = $btn.data('copy');

            if (navigator.clipboard && text) {
                navigator.clipboard.writeText(text).then(() => {
                    Toast.success('Copied to clipboard!', 2000);
                }).catch(() => {
                    Toast.error('Failed to copy', 2000);
                });
            }
        });
    }

    // ==========================================
    // KEYBOARD SHORTCUTS
    // ==========================================

    function enableKeyboardShortcuts() {
        $(document).on('keydown', function(e) {
            // Ctrl/Cmd + S to save settings
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                const $form = $('form[method="post"][action="options.php"]');
                if ($form.length) {
                    e.preventDefault();
                    $form.submit();
                    Toast.info('Saving settings...', 2000);
                }
            }
        });
    }

    // ==========================================
    // ANIMATE STATS ON LOAD
    // ==========================================

    function animateStats() {
        $('.dekkimporter-stat-card .stat-value').each(function() {
            const $this = $(this);
            const finalValue = parseInt($this.text(), 10);

            if (!isNaN(finalValue)) {
                $this.text('0');

                $({ value: 0 }).animate({ value: finalValue }, {
                    duration: 1000,
                    easing: 'swing',
                    step: function() {
                        $this.text(Math.floor(this.value));
                    },
                    complete: function() {
                        $this.text(finalValue);
                    }
                });
            }
        });
    }

    // ==========================================
    // CHECK FOR EXISTING SYNC ON PAGE LOAD
    // ==========================================

    function checkSyncOnPageLoad() {
        console.log('Checking for existing sync on page load...');

        $.ajax({
            url: dekkimporterAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'dekkimporter_sync_progress'
            },
            success: function(response) {
                console.log('Initial sync check response:', response);

                if (response.success && response.data.in_progress) {
                    console.log('Sync already in progress! Starting progress tracker...');

                    // Show progress container
                    ProgressTracker.showProgressContainer();

                    // Update with current progress
                    ProgressTracker.updateProgress(response.data.progress);

                    // Start polling
                    ProgressTracker.start();

                    // Disable manual sync button
                    $('#dekkimporter-manual-sync').prop('disabled', true)
                        .addClass('syncing')
                        .html('<span class="spinner-border spinner-border-sm me-2" role="status"></span> Syncing...');
                } else {
                    console.log('No sync in progress');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error checking sync status:', error);
            }
        });
    }

    // ==========================================
    // INITIALIZATION
    // ==========================================

    $(document).ready(function() {
        // Initialize countdown timer
        setInterval(updateCountdown, 1000);
        updateCountdown();

        // Bind manual sync button
        $('#dekkimporter-manual-sync').on('click', handleManualSync);

        // Bind stop sync button
        $('#dekkimporter-stop-sync').on('click', handleStopSync);

        // Initialize enhancements
        enhanceFormValidation();
        enhanceSettingsSave();
        enableSmoothScroll();
        enableCopyToClipboard();
        enableKeyboardShortcuts();

        // Animate stats on page load
        setTimeout(animateStats, 300);

        // Check if sync is already in progress on page load
        checkSyncOnPageLoad();

        // Add CSS for toast notifications
        addToastStyles();

        // Add CSS for loading overlay text
        addLoadingStyles();
    });

    // ==========================================
    // DYNAMIC STYLE INJECTION
    // ==========================================

    function addToastStyles() {
        const styles = `
            <style>
                .dekkimporter-toast-container {
                    position: fixed;
                    top: 32px;
                    right: 20px;
                    z-index: 999999;
                    display: flex;
                    flex-direction: column;
                    gap: 10px;
                    max-width: 400px;
                }

                .dekkimporter-toast {
                    background: white;
                    border-radius: 8px;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                    padding: 16px;
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    opacity: 0;
                    transform: translateX(400px);
                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                }

                .dekkimporter-toast-show {
                    opacity: 1;
                    transform: translateX(0);
                }

                .dekkimporter-toast-icon {
                    font-size: 20px;
                    font-weight: bold;
                    flex-shrink: 0;
                    width: 24px;
                    height: 24px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border-radius: 50%;
                }

                .dekkimporter-toast-success {
                    border-left: 4px solid #10b981;
                }

                .dekkimporter-toast-success .dekkimporter-toast-icon {
                    background: #d1fae5;
                    color: #10b981;
                }

                .dekkimporter-toast-error {
                    border-left: 4px solid #ef4444;
                }

                .dekkimporter-toast-error .dekkimporter-toast-icon {
                    background: #fee2e2;
                    color: #ef4444;
                }

                .dekkimporter-toast-warning {
                    border-left: 4px solid #f59e0b;
                }

                .dekkimporter-toast-warning .dekkimporter-toast-icon {
                    background: #fef3c7;
                    color: #f59e0b;
                }

                .dekkimporter-toast-info {
                    border-left: 4px solid #3b82f6;
                }

                .dekkimporter-toast-info .dekkimporter-toast-icon {
                    background: #dbeafe;
                    color: #3b82f6;
                }

                .dekkimporter-toast-message {
                    flex: 1;
                    font-size: 14px;
                    color: #374151;
                    line-height: 1.5;
                }

                .dekkimporter-toast-close {
                    background: none;
                    border: none;
                    font-size: 20px;
                    color: #9ca3af;
                    cursor: pointer;
                    padding: 0;
                    width: 24px;
                    height: 24px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border-radius: 4px;
                    transition: all 0.2s;
                }

                .dekkimporter-toast-close:hover {
                    background: #f3f4f6;
                    color: #374151;
                }

                .dekkimporter-input-error {
                    border-color: #ef4444 !important;
                    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
                }

                @media screen and (max-width: 782px) {
                    .dekkimporter-toast-container {
                        left: 10px;
                        right: 10px;
                        max-width: none;
                    }
                }
            </style>
        `;
        $('head').append(styles);
    }

    function addLoadingStyles() {
        const styles = `
            <style>
                .dekkimporter-loading-content {
                    text-align: center;
                    background: white;
                    padding: 40px 60px;
                    border-radius: 12px;
                    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
                }

                .dekkimporter-loading-text {
                    margin: 20px 0 0 0;
                    color: #374151;
                    font-size: 16px;
                    font-weight: 600;
                }
            </style>
        `;
        $('head').append(styles);
    }

})(jQuery);
