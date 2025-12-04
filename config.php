<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Woo Notifications configuration.
 *
 * Modify these settings to customize the notification behavior.
 */
return array(
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
