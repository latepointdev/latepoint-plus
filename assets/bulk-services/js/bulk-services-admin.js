/**
 * Bulk Services Admin JavaScript
 * Handles the simple bulk services selection interface
 */

(function($) {
    'use strict';

    var BulkServices = {
        selectedServices: [],

        init: function() {
            this.bindEvents();
            this.updateSelectionSummary();
        },

        bindEvents: function() {
            var self = this;

            // Service selection
            $(document).on('change', '.bulk-service-checkbox-input', function() {
                self.handleServiceSelection($(this));
            });

            // Service item click (for better UX)
            $(document).on('click', '.bulk-service-item', function(e) {
                if (!$(e.target).is('input, label')) {
                    var checkbox = $(this).find('.bulk-service-checkbox-input');
                    checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
                }
            });

            // Cancel button
            $(document).on('click', '.bulk-services-cancel-btn', function(e) {
                e.preventDefault();
                $('.latepoint-lightbox-close').trigger('click');
            });

            // Add services button
            $(document).on('click', '.bulk-add-services-btn', function(e) {
                e.preventDefault();
                self.addSelectedServices();
            });
        },

        handleServiceSelection: function($checkbox) {
            var serviceId = $checkbox.val();
            var $serviceItem = $checkbox.closest('.bulk-service-item');
            
            if ($checkbox.is(':checked')) {
                // Add service to selection
                var serviceData = {
                    id: serviceId,
                    name: $serviceItem.data('service-name'),
                    duration: parseInt($serviceItem.data('service-duration')),
                    price: parseFloat($serviceItem.data('service-price'))
                };
                
                this.selectedServices.push(serviceData);
                $serviceItem.addClass('selected');
            } else {
                // Remove service from selection
                this.selectedServices = this.selectedServices.filter(function(service) {
                    return service.id !== serviceId;
                });
                $serviceItem.removeClass('selected');
            }

            this.updateSelectionSummary();
        },

        updateSelectionSummary: function() {
            var count = this.selectedServices.length;
            var totalDuration = this.selectedServices.reduce(function(sum, service) {
                return sum + service.duration;
            }, 0);
            var totalPrice = this.selectedServices.reduce(function(sum, service) {
                return sum + service.price;
            }, 0);

            $('.selected-count').text(count + ' ' + (count === 1 ? 'service selected' : 'services selected'));
            $('.total-duration').text('Total: ' + totalDuration + ' min');
            $('.total-price').text(this.formatPrice(totalPrice));

            // Enable/disable add button
            $('.bulk-add-services-btn').prop('disabled', count === 0);
        },

        addSelectedServices: function() {
            var selectedServiceIds = this.selectedServices.map(function(service) {
                return service.id;
            });

            if (selectedServiceIds.length === 0) {
                alert('Please select at least one service');
                return;
            }

            var $submitBtn = $('.bulk-add-services-btn');
            var originalText = $submitBtn.find('span').text();
            
            $submitBtn.prop('disabled', true).find('span').text('Adding...');

            // Add each service individually using LatePoint's native method
            var servicesAdded = 0;
            var totalServices = selectedServiceIds.length;
            var self = this;

            selectedServiceIds.forEach(function(serviceId) {
                // Use the same route that "Add Another Item" uses
                var data = {
                    action: latepoint_helper.route_action,
                    route_name: latepoint_helper.orders_generate_booking_order_item_block_route,
                    params: {
                        service_id: serviceId,
                        order_id: $('input[name="order_id"]').val() || '',
                        customer_id: $('input[name="customer_id"]').val() || ''
                    },
                    return_format: 'json'
                };

                $.post(latepoint_helper.ajaxurl, data, function(response) {
                    if (response.status === 'success') {
                        // Add the new order item to the order items list
                        $('.order-items-list').prepend(response.message);
                        
                        // Remove "Order is empty" message if it exists
                        $('.order-items-list .no-results').remove();
                        
                        servicesAdded++;
                        
                        // If all services have been added, close the lightbox
                        if (servicesAdded === totalServices) {
                            $('.latepoint-lightbox-close').trigger('click');
                            
                            // Trigger LatePoint events for form reinitialization
                            if (typeof latepoint_init_booking_form_fields === 'function') {
                                latepoint_init_booking_form_fields();
                            }
                            if (typeof latepoint_reload_price_breakdown === 'function') {
                                latepoint_reload_price_breakdown();
                            }
                        }
                    } else {
                        alert(response.message || 'Error adding service');
                    }
                }).fail(function() {
                    alert('Error adding service');
                }).always(function() {
                    if (servicesAdded === totalServices) {
                        $submitBtn.prop('disabled', false).find('span').text(originalText);
                    }
                });
            });
        },

        formatPrice: function(price) {
            // Use LatePoint's money formatting if available
            if (typeof latepoint_format_price === 'function') {
                return latepoint_format_price(price);
            }
            
            // Fallback formatting
            var symbol = latepoint_helper.currency_symbol_before || '$';
            return symbol + parseFloat(price).toFixed(2);
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        if ($('.bulk-services-selection-wrapper').length) {
            BulkServices.init();
        }
    });

    // Make BulkServices available globally for debugging
    window.BulkServices = BulkServices;

})(jQuery);
