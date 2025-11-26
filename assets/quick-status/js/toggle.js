jQuery(document).ready(function($) {
    'use strict';
    
    // Configuration
    const CONFIG = {
        selectors: {
            toggleButton: '.lp-toggle-service',
            serviceTile: '.os-service',
            footSection: '.os-service-foot'
        },
        classes: {
            loading: 'os-loading',
            error: 'os-error',
            btnPrimary: 'latepoint-btn-success',
            btnWarning: 'latepoint-btn-danger'
        },
        loadingDelay: 300,
        reloadDelay: 1500
    };
    
    /**
     * Move toggle buttons to the service foot section for better integration
     */
    function moveToggleButtonsToFoot() {
        $(CONFIG.selectors.toggleButton).each(function() {
            const $button = $(this);
            const $serviceTile = $button.closest(CONFIG.selectors.serviceTile);
            const $footSection = $serviceTile.find(CONFIG.selectors.footSection);
            
            if ($footSection.length && !$button.closest(CONFIG.selectors.footSection).length) {
                // Move button to foot section
                $button.appendTo($footSection);
                
                // Apply proper button classes based on status
                updateButtonAppearance($button);
            }
        });
    }
    
    /**
     * Update button appearance based on service status
     */
    function updateButtonAppearance($button) {
        const status = $button.data('status');
        
        // Remove existing button classes
        $button.removeClass([
            CONFIG.classes.btnPrimary,
            CONFIG.classes.btnWarning,
            CONFIG.classes.loading,
            CONFIG.classes.error
        ].join(' '));
        
        // Apply appropriate class based on status
        if (status === 'active') {
            $button.addClass(CONFIG.classes.btnWarning);
        } else {
            $button.addClass(CONFIG.classes.btnPrimary);
        }
    }
    
    /**
     * Show loading state on button
     */
    function showLoadingState($button) {
        $button.addClass(CONFIG.classes.loading)
               .prop('disabled', true)
               .data('original-text', $button.text());
    }
    
    /**
     * Hide loading state and restore button
     */
    function hideLoadingState($button) {
        $button.removeClass(CONFIG.classes.loading)
               .prop('disabled', false);
    }
    
    /**
     * Show error state temporarily
     */
    function showErrorState($button) {
        $button.addClass(CONFIG.classes.error);
        setTimeout(() => {
            $button.removeClass(CONFIG.classes.error);
        }, 1000);
    }
    
    /**
     * Show notification using LatePoint's notification system
     */
    function showNotification(message, type = 'success') {
        if (typeof latepoint_show_message_inside_element !== 'undefined') {
            // Use LatePoint's built-in notification system
            latepoint_show_message_inside_element(message, $('.latepoint-admin'), type);
        } else if (typeof OsNotificationHelper !== 'undefined') {
            // Fallback to older notification system
            OsNotificationHelper.show(message, type);
        } else {
            // Final fallback to browser alert
            console.log(`${type.toUpperCase()}: ${message}`);
            if (type === 'error') {
                alert(message);
            }
        }
    }
    
    /**
     * Handle successful toggle response
     */
    function handleToggleSuccess($button, response) {
        hideLoadingState($button);
        
        // Update button text and data-status
        $button.text(response.data.button_text)
               .data('status', response.data.new_status);
        
        // Update button appearance
        updateButtonAppearance($button);
        
        // Show success notification
        showNotification(response.data.message, 'success');
        
        // Reload page after delay to reflect changes
        setTimeout(() => {
            if (typeof latepoint_reload_after_action !== 'undefined') {
                // Use LatePoint's reload function if available
                latepoint_reload_after_action();
            } else {
                location.reload();
            }
        }, CONFIG.reloadDelay);
    }
    
    /**
     * Handle toggle error response
     */
    function handleToggleError($button, response) {
        hideLoadingState($button);
        showErrorState($button);
        
        // Restore original text
        const originalText = $button.data('original-text');
        if (originalText) {
            $button.text(originalText);
        }
        
        // Show error notification
        const errorMessage = response && response.data && response.data.message 
            ? response.data.message 
            : 'An error occurred while toggling the service status.';
        showNotification(errorMessage, 'error');
    }
    
    /**
     * Handle network error
     */
    function handleNetworkError($button) {
        hideLoadingState($button);
        showErrorState($button);
        
        // Restore original text
        const originalText = $button.data('original-text');
        if (originalText) {
            $button.text(originalText);
        }
        
        showNotification('Network error occurred. Please check your connection and try again.', 'error');
    }
    
    /**
     * Perform the AJAX toggle request
     */
    function performToggle($button) {
        const serviceId = $button.data('id');
        
        if (!serviceId) {
            showNotification('Invalid service ID.', 'error');
            return;
        }
        
        // Show loading state
        showLoadingState($button);
        
        // Perform AJAX request
        $.ajax({
            url: LPServiceToggle.ajax_url,
            type: 'POST',
            data: {
                action: 'lp_toggle_service',
                service_id: serviceId,
                nonce: LPServiceToggle.nonce
            },
            timeout: 10000, // 10 second timeout
            success: function(response) {
                if (response && response.success) {
                    handleToggleSuccess($button, response);
                } else {
                    handleToggleError($button, response);
                }
            },
            error: function(xhr, status, error) {
                console.error('Toggle service error:', { xhr, status, error });
                handleNetworkError($button);
            }
        });
    }
    
    // Initialize on document ready
    function initialize() {
        // Move toggle buttons to proper location
        moveToggleButtonsToFoot();
        
        // Set up event delegation for toggle buttons
        $(document).off('click.lpToggle', CONFIG.selectors.toggleButton)
                   .on('click.lpToggle', CONFIG.selectors.toggleButton, function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const $button = $(this);
            
            // Prevent double-clicks
            if ($button.prop('disabled') || $button.hasClass(CONFIG.classes.loading)) {
                return;
            }
            
            performToggle($button);
        });
        
        // Re-initialize after AJAX operations (for dynamic content)
        $(document).on('latepoint_after_ajax_success', function() {
            setTimeout(moveToggleButtonsToFoot, 100);
        });
    }
    
    // Initialize the plugin
    initialize();
    
    // Expose public methods for external use
    window.LPQuickStatus = {
        moveToggleButtonsToFoot: moveToggleButtonsToFoot,
        updateButtonAppearance: updateButtonAppearance,
        reinitialize: initialize
    };
});
