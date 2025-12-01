<?php

/**
 * Plugin Name: WooCommerce Order Notifications
 * Plugin URI: https://thecreativetinker.com
 * Description: Displays random order notifications in the bottom corner of the screen using real WooCommerce orders.
 * Version: 1.0.0
 * Author: Taylor Drayson
 * Author URI: https://thecreativetinker.com
 * Text Domain: woo-notifications
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * WC requires at least: 3.0
 * WC tested up to: 8.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WOO_NOTIFICATIONS_VERSION', '1.0.0');
define('WOO_NOTIFICATIONS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WOO_NOTIFICATIONS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WOO_NOTIFICATIONS_PLUGIN_BASE', plugin_basename(__FILE__));
define('WOO_NOTIFICATIONS_DEV_MODE', (strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false));

/*
 * Declare HPOS and Blocks compatibility.
 */
function woo_notifications_declare_wc_compatibility()
{
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', WOO_NOTIFICATIONS_PLUGIN_BASE, true);
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', WOO_NOTIFICATIONS_PLUGIN_BASE, true);
    }
}
add_action('before_woocommerce_init', 'woo_notifications_declare_wc_compatibility');

/**
 * Main plugin class
 */
class WooNotifications
{
    /**
     * Plugin instance
     *
     * @var Woo_Notifications
     */
    private static $instance = null;

    /**
     * Configuration array
     *
     * @var array
     */
    private $config = null;

    /**
     * Get plugin instance
     *
     * @return Woo_Notifications
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct()
    {
        $this->initHooks();
    }

    /**
     * Initialize WordPress hooks
     */
    private function initHooks()
    {
        add_action('wp_enqueue_scripts', array($this, 'enqueueAssets'));
    }

    /**
     * Get plugin configuration array
     * Modify these settings to customize the notification behavior
     *
     * @return array
     */
    public function getConfig()
    {
        if (null !== $this->config) {
            return $this->config;
        }

        $this->config = array(
            // Number of latest orders to fetch
            'order_limit' => 20,

            // Interval between notifications in seconds
            'notification_interval' => 10,

            // Duration each notification is visible in seconds
            'notification_duration' => 5,

            // Position of notifications: 'bottom-right', 'bottom-left', 'top-right', 'top-left'
            'position' => 'bottom-right',

            // Notification text template
            // Available placeholders: {name}, {product}, {action}, {additional_items}
            'notification_template' => '{name} just {action} {product}{additional_items}',

            // Show product image in notifications
            'show_product_image' => true,

            // Show additional items count (e.g., "and 2 other items")
            'show_additional_items' => true,

            // Action word variations (randomly selected)
            'action_variations' => array(
                'purchased',
                'ordered',
                'bought',
                'got',
            ),

            // CSS variable overrides
            'css_variables' => array(
                '--woo-notif-bg-color' => '#ffffff',
                '--woo-notif-text-color' => '#333333',
                '--woo-notif-border-radius' => '8px',
                '--woo-notif-padding' => '16px 20px',
                '--woo-notif-font-size' => '16px',
                '--woo-notif-box-shadow' => '0 4px 12px rgba(0, 0, 0, 0.15)',
                '--woo-notif-animation-duration' => '0.3s',
                '--woo-notif-link-color' => '#266431',
            ),

            // Dev mode: Set to true to use test data, or use constant WOO_NOTIFICATIONS_DEV_MODE
            'dev_mode' => defined('WOO_NOTIFICATIONS_DEV_MODE') ? WOO_NOTIFICATIONS_DEV_MODE : false,
        );

        return $this->config;
    }

    /**
     * Fetch orders from WooCommerce or test file
     *
     * @return array Array of formatted order data
     */
    public function getOrders()
    {
        $config = $this->getConfig();

        // Dev mode: Load test data
        if ($config['dev_mode']) {
            return $this->getTestOrders();
        }

        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            return array();
        }

        // Fetch latest orders with status 'wc-processing' or 'wc-completed'
        $args = array(
            'limit' => $config['order_limit'],
            'status' => array('wc-processing', 'wc-completed'), // Only processing or completed orders
            'orderby' => 'date',
            'order' => 'DESC',
        );

        $orders = wc_get_orders($args);
        $formatted_orders = array();

        foreach ($orders as $order) {
            // Get customer first name
            $first_name = $order->get_billing_first_name();

            // Skip if no first name
            if (empty($first_name)) {
                continue;
            }

            // Get order items
            $items = $order->get_items();

            if (empty($items)) {
                continue;
            }

            // Get total item count
            $total_items = count($items);

            // Randomly select one product from the order
            $random_item = array_rand($items);
            $item = $items[$random_item];

            $product_id = $item->get_product_id();
            $product = wc_get_product($product_id);

            if (!$product) {
                continue;
            }

            $product_data = array(
                'title' => $product->get_name(),
                'url' => $product->get_permalink(),
            );

            // Add product image if enabled
            if ($config['show_product_image']) {
                $image_url = $this->getProductImageUrl($product);
                if ($image_url) {
                    $product_data['image_url'] = $image_url;
                }
            }

            // Calculate additional items count
            $additional_items_count = 0;
            if ($config['show_additional_items'] && $total_items > 1) {
                $additional_items_count = $total_items - 1;
            }

            $formatted_orders[] = array(
                'name' => $first_name,
                'product' => $product_data,
                'additional_items' => $additional_items_count,
            );
        }

        return $formatted_orders;
    }

    /**
     * Get product image URL if available
     *
     * @param WC_Product $product Product object
     * @return string|null Image URL or null
     */
    private function getProductImageUrl($product)
    {
        if (!$product) {
            return null;
        }

        $image_id = $product->get_image_id();
        if (!$image_id) {
            return null;
        }

        $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
        if (!$image_url) {
            return null;
        }

        return $image_url;
    }

    /**
     * Get test orders from JSON file
     *
     * @return array Array of formatted test order data
     */
    private function getTestOrders()
    {
        $test_file = WOO_NOTIFICATIONS_PLUGIN_DIR . 'test-orders.json';

        if (!file_exists($test_file)) {
            return array();
        }

        $json_content = file_get_contents($test_file);
        $test_orders = json_decode($json_content, true);

        if (!is_array($test_orders)) {
            return array();
        }

        return $test_orders;
    }

    /**
     * Enqueue scripts and styles
     */
    public function enqueueAssets()
    {
        // Only load on frontend
        if (is_admin()) {
            return;
        }

        $config = $this->getConfig();
        $orders = $this->getOrders();

        // Don't enqueue if no orders
        if (empty($orders)) {
            return;
        }

        // Get file modification times for cache busting
        $css_file = WOO_NOTIFICATIONS_PLUGIN_DIR . 'assets/css/woo-notifications.css';
        $js_file = WOO_NOTIFICATIONS_PLUGIN_DIR . 'assets/js/woo-notifications.js';
        $css_version = file_exists($css_file) ? filemtime($css_file) : WOO_NOTIFICATIONS_VERSION;
        $js_version = file_exists($js_file) ? filemtime($js_file) : WOO_NOTIFICATIONS_VERSION;

        // Enqueue CSS
        wp_enqueue_style(
            'woo-notifications',
            WOO_NOTIFICATIONS_PLUGIN_URL . 'assets/css/woo-notifications.css',
            array(),
            $css_version
        );

        // Enqueue JavaScript
        wp_enqueue_script(
            'woo-notifications',
            WOO_NOTIFICATIONS_PLUGIN_URL . 'assets/js/woo-notifications.js',
            array(),
            $js_version,
            true
        );

        // Localize script with order data and configuration
        wp_localize_script(
            'woo-notifications',
            'wooNotifications',
            array(
                'orders' => $orders,
                'interval' => $config['notification_interval'] * 1000, // Convert to milliseconds
                'duration' => $config['notification_duration'] * 1000, // Convert to milliseconds
                'position' => $config['position'],
                'template' => $config['notification_template'],
                'showProductImage' => $config['show_product_image'],
                'showAdditionalItems' => $config['show_additional_items'],
                'actionVariations' => $config['action_variations'],
                'cssVariables' => $config['css_variables'],
            )
        );
    }
}

// Initialize the plugin
WooNotifications::getInstance();
