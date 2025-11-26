<?php
/**
 * Bulk Services Module
 * 
 * Adds bulk service selection to order forms
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('OsPlusBulkServicesModule')):

    /**
     * Bulk Services Module Class
     */
    class OsPlusBulkServicesModule extends OsPlusModuleBase
    {

        /**
         * Module name
         */
        protected static $module_name = 'bulk-services';

        /**
         * Initialize the module
         */
        public static function init()
        {
            // NOTE: The latepoint_order_quick_edit_form_content_after hook is registered
            // in the main plugin class (LatePointPlus::init_hooks) to ensure proper timing.
            // We only register the asset enqueue hook here.

            // Enqueue admin assets
            add_action('latepoint_plus_enqueue_admin_assets', array(__CLASS__, 'enqueue_admin_assets'));
        }

        /**
         * Enqueue admin scripts and styles
         */
        public static function enqueue_admin_assets()
        {
            // Enqueue stylesheet
            self::enqueue_style('bulk-services-admin', 'bulk-services-admin.css');

            // Enqueue JavaScript
            self::enqueue_script('bulk-services-admin', 'bulk-services-admin.js', array('jquery'));
        }

        /**
         * Add bulk services interface to order form
         */
        public static function add_bulk_services_to_order_form($order)
        {
            // Ensure helper is loaded
            if (!class_exists('OsPlusBulkServicesHelper')) {
                return;
            }

            // Check if bulk services can be added
            if (!OsPlusBulkServicesHelper::can_add_bulk_services($order)) {
                return;
            }

            // Get available services and categories for the bulk add interface
            $services = OsPlusBulkServicesHelper::get_available_services();
            $categories = OsPlusBulkServicesHelper::get_service_categories();

            if (empty($services)) {
                return; // No services available
            }
            ?>
            <!-- Bulk services interface embedded directly in Order Items section -->
            <div class="bulk-services-wrapper">
                <div class="os-form-sub-header">
                    <h3><?php esc_html_e('Bulk Add Services', 'latepoint-plus'); ?></h3>
                    <div class="os-form-sub-header-actions">
                        <a href="#" class="latepoint-btn latepoint-btn-sm latepoint-btn-link bulk-services-cancel-btn">
                            <span><?php esc_html_e('Cancel', 'latepoint-plus'); ?></span>
                        </a>
                    </div>
                </div>
                <?php if (count($categories) > 1): ?>
                    <div class="bulk-services-category-filter">
                        <label for="bulk-services-category-select"><?php esc_html_e('Filter by Category:', 'latepoint-plus'); ?></label>
                        <select id="bulk-services-category-select" class="bulk-services-category-select">
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo esc_attr($category['id']); ?>"><?php echo esc_html($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
                <div class="os-complex-connections-selector bulk-services-selector">
                    <?php foreach ($services as $service): ?>
                        <div class="connection bulk-service-connection" data-service-id="<?php echo esc_attr($service['id']); ?>"
                            data-category-id="<?php echo esc_attr($service['category_id']); ?>">
                            <div class="connection-i selector-trigger">
                                <h3 class="connection-name"><?php echo esc_html($service['name']); ?> • </h3>
                                <div class="connection-details">
                                    <?php echo esc_html($service['duration']); ?>                 <?php esc_html_e('min', 'latepoint-plus'); ?> •
                                    <?php echo esc_html($service['price_formatted']); ?>
                                    <?php if (!empty($service['category_name'])): ?>
                                        <span class="service-category"> • <?php echo esc_html($service['category_name']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <input type="hidden" class="connection-child-is-connected" value="no">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="bulk-services-actions">
                    <button type="button" class="latepoint-btn latepoint-btn-primary bulk-add-selected-services-btn">
                        <i class="latepoint-icon latepoint-icon-plus"></i>
                        <span><?php esc_html_e('Add Selected Services', 'latepoint-plus'); ?></span>
                    </button>
                </div>
            </div>

            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                    // Find the Order Items section and add our bulk add button to the header actions
                    var $orderItemsHeader = $('.order-items-info-w .os-form-sub-header-actions');
                    var $addItemBtn = $('.order-form-add-item-btn');

                    if ($orderItemsHeader.length && $addItemBtn.length && !$('.bulk-services-trigger-btn').length) {
                        var bulkButton = '<a href="#" class="latepoint-btn latepoint-btn-sm latepoint-btn-link bulk-services-trigger-btn">' +
                            '<i class="latepoint-icon latepoint-icon-layers"></i><span><?php esc_html_e('Bulk Add', 'latepoint-plus'); ?></span>' +
                            '</a>';

                        // Insert bulk button right after the "Add Another Item" button in the header actions
                        $addItemBtn.after(' ' + bulkButton);

                        // Move the bulk interface to be right after the order items list
                        var $bulkWrapper = $('.bulk-services-wrapper');
                        var $orderItemsList = $('.order-items-list');

                        if ($bulkWrapper.length && $orderItemsList.length) {
                            // Insert right after the order items list, within the order-items-info-w container
                            $orderItemsList.after($bulkWrapper);
                        }
                    }

                    // Handle bulk add button click
                    $(document).on('click', '.bulk-services-trigger-btn', function (e) {
                        e.preventDefault();
                        $('.bulk-services-wrapper').slideDown();
                        // Disable the button temporarily
                        $(this).addClass('disabled');
                    });

                    // Handle cancel button
                    $(document).on('click', '.bulk-services-cancel-btn', function (e) {
                        e.preventDefault();
                        $('.bulk-services-wrapper').slideUp();
                        $('.bulk-services-trigger-btn').removeClass('disabled');
                        // Reset selections and filters
                        $('.bulk-service-connection').removeClass('active').show();
                        $('.connection-child-is-connected').val('no');
                        $('.bulk-services-category-select').val('all');
                    });

                    // Handle category filter change
                    $(document).on('change', '.bulk-services-category-select', function () {
                        var selectedCategory = $(this).val();
                        var $connections = $('.bulk-service-connection');

                        if (selectedCategory === 'all') {
                            // Show all services
                            $connections.show();
                        } else {
                            // Hide all first, then show matching category
                            $connections.hide();
                            $connections.filter('[data-category-id="' + selectedCategory + '"]').show();
                        }
                    });

                    // Handle service selection (same as bundles form)
                    $(document).on('click', '.bulk-service-connection .selector-trigger', function () {
                        var $connection = $(this).closest('.connection');
                        var $hiddenField = $connection.find('.connection-child-is-connected');

                        if ($connection.hasClass('active')) {
                            $connection.removeClass('active');
                            $hiddenField.val('no');
                        } else {
                            $connection.addClass('active');
                            $hiddenField.val('yes');
                        }
                    });

                    // Handle add selected services
                    $(document).on('click', '.bulk-add-selected-services-btn', function (e) {
                        e.preventDefault();

                        var selectedServices = [];
                        $('.bulk-service-connection.active').each(function () {
                            selectedServices.push($(this).data('service-id'));
                        });

                        if (selectedServices.length === 0) {
                            alert('<?php esc_html_e('Please select at least one service', 'latepoint-plus'); ?>');
                            return;
                        }

                        var $submitBtn = $(this);
                        var originalText = $submitBtn.find('span').text();

                        $submitBtn.prop('disabled', true).find('span').text('<?php esc_html_e('Adding...', 'latepoint-plus'); ?>');

                        // Add each service individually using LatePoint's native method
                        var servicesAdded = 0;
                        var totalServices = selectedServices.length;

                        selectedServices.forEach(function (serviceId) {
                            var data = {
                                action: latepoint_helper.route_action,
                                route_name: '<?php echo esc_js(OsRouterHelper::build_route_name('orders', 'generate_booking_order_item_block')); ?>',
                                params: {
                                    service_id: serviceId,
                                    order_id: '<?php echo esc_js($order->id); ?>',
                                    customer_id: '<?php echo esc_js($order->customer_id); ?>'
                                },
                                return_format: 'json'
                            };

                            $.post(latepoint_helper.ajaxurl, data, function (response) {
                                if (response.status === 'success') {
                                    $('.order-items-list').prepend(response.message);
                                    $('.order-items-list .no-results').remove();

                                    servicesAdded++;

                                    if (servicesAdded === totalServices) {
                                        // Hide bulk services interface and re-enable trigger button
                                        $('.bulk-services-wrapper').slideUp();
                                        $('.bulk-services-trigger-btn').removeClass('disabled');

                                        // Reset selections
                                        $('.bulk-service-connection').removeClass('active');
                                        $('.connection-child-is-connected').val('no');

                                        // Trigger LatePoint events
                                        if (typeof latepoint_init_booking_form_fields === 'function') {
                                            latepoint_init_booking_form_fields();
                                        }
                                        if (typeof latepoint_reload_price_breakdown === 'function') {
                                            latepoint_reload_price_breakdown();
                                        }
                                    }
                                } else {
                                    alert(response.message || '<?php esc_html_e('Error adding service', 'latepoint-plus'); ?>');
                                }
                            }).fail(function () {
                                alert('<?php esc_html_e('Error adding service', 'latepoint-plus'); ?>');
                            }).always(function () {
                                if (servicesAdded === totalServices) {
                                    $submitBtn.prop('disabled', false).find('span').text(originalText);
                                }
                            });
                        });
                    });
                });
            </script>
            <?php
        }
    }

endif;
