/**
 * DekkImporter Admin JavaScript
 *
 * @package DekkImporter
 * @since 1.4.0
 */

(function($) {
    'use strict';

    /**
     * Countdown Timer
     * Updates the countdown display for next scheduled sync
     */
    function updateCountdown() {
        var $countdown = $('#dekkimporter-countdown, #dekkimporter-widget-countdown');

        if ($countdown.length === 0) {
            return;
        }

        $countdown.each(function() {
            var $el = $(this);
            var nextSync = parseInt($el.data('timestamp'), 10);

            if (!nextSync) {
                return;
            }

            var now = Math.floor(Date.now() / 1000);
            var diff = nextSync - now;

            if (diff <= 0) {
                $el.html('<span class="dekkimporter-countdown syncing">Sync in progress...</span>');
                return;
            }

            var hours = Math.floor(diff / 3600);
            var minutes = Math.floor((diff % 3600) / 60);
            var seconds = diff % 60;

            // Color based on time remaining
            var colorClass = 'green';
            if (diff < 3600) {
                colorClass = 'orange';
            }
            if (diff < 900) {
                colorClass = 'red';
            }

            var html = '<span class="dekkimporter-countdown countdown-' + colorClass + '">';
            html += hours + 'h ' + minutes + 'm ' + seconds + 's';
            html += '</span>';

            $el.html(html);
        });
    }

    /**
     * Manual Sync Handler
     * Triggers a manual product sync via AJAX
     */
    function handleManualSync(e) {
        e.preventDefault();

        var $btn = $(this);
        var originalText = $btn.text();

        // Disable button and show loading state
        $btn.prop('disabled', true).text('Syncing...');

        $.ajax({
            url: dekkimporterAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'dekkimporter_manual_sync',
                nonce: dekkimporterAdmin.nonces.manualSync
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert('Error: ' + response.data.message);
                    $btn.prop('disabled', false).text(originalText);
                }
            },
            error: function(xhr, status, error) {
                alert('Sync failed: ' + error);
                $btn.prop('disabled', false).text(originalText);
            }
        });
    }

    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        // Start countdown timer
        if (typeof dekkimporterAdmin !== 'undefined' && dekkimporterAdmin.nextSyncTime) {
            setInterval(updateCountdown, 1000);
            updateCountdown();
        }

        // Manual sync button
        $('#dekkimporter-manual-sync').on('click', handleManualSync);
    });

})(jQuery);
