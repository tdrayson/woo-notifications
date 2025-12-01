/**
 * WooCommerce Order Notifications
 * Displays random order notifications at configured intervals
 */

(function () {
  'use strict';

  // Check if configuration is available
  if (
    typeof wooNotifications === 'undefined' ||
    !wooNotifications.orders ||
    wooNotifications.orders.length === 0
  ) {
    return;
  }

  const config = wooNotifications;
  let notificationInterval = null;
  let currentNotification = null;

  /**
   * Apply CSS variables to the document
   */
  function applyCSSVariables() {
    if (!config.cssVariables) {
      return;
    }

    const root = document.documentElement;
    Object.keys(config.cssVariables).forEach(function (key) {
      root.style.setProperty(key, config.cssVariables[key]);
    });
  }

  /**
   * Get position classes based on configuration
   */
  function getPositionClasses() {
    const position = config.position || 'bottom-right';
    const classes = {
      'bottom-right': 'woo-notif-bottom-right',
      'bottom-left': 'woo-notif-bottom-left',
      'top-right': 'woo-notif-top-right',
      'top-left': 'woo-notif-top-left',
    };
    return classes[position] || classes['bottom-right'];
  }

  /**
   * Get random action word from variations
   */
  function getRandomAction() {
    if (!config.actionVariations || config.actionVariations.length === 0) {
      return 'purchased';
    }
    const randomIndex = Math.floor(
      Math.random() * config.actionVariations.length,
    );
    return config.actionVariations[randomIndex];
  }

  /**
   * Format additional items text
   */
  function formatAdditionalItems(count) {
    if (!config.showAdditionalItems || !count || count <= 0) {
      return '';
    }

    const type = ['other', 'more'];
    const product = ['item', 'product'];
    const randomIndexType = Math.floor(Math.random() * type.length);
    const randomIndexProduct = Math.floor(Math.random() * product.length);
    const typeText = type[randomIndexType];
    const productText = product[randomIndexProduct];

    if (count === 1) {
      return ` and 1 ${typeText} ${productText}`;
    }
    return ` and ${count} ${typeText} ${productText}s`;
  }

  /**
   * Create notification element
   */
  function createNotification(name, product, additionalItems) {
    const notification = document.createElement('div');
    notification.className = 'woo-notification ' + getPositionClasses();

    // Add accessibility attributes
    notification.setAttribute('role', 'status');
    notification.setAttribute('aria-live', 'polite');
    notification.setAttribute('aria-atomic', 'true');

    // Create notification content wrapper
    const contentWrapper = document.createElement('div');
    contentWrapper.className = 'woo-notif-content';

    // Add product image if enabled and available
    if (config.showProductImage && product.image_url) {
      const imageWrapper = document.createElement('div');
      imageWrapper.className = 'woo-notif-image-wrapper';
      const productImage = document.createElement('img');
      productImage.src = product.image_url;
      productImage.alt = product.title;
      productImage.className = 'woo-notif-product-image';
      const imageLink = document.createElement('a');
      imageLink.href = product.url;
      imageLink.className = 'woo-notif-image-link';
      imageLink.appendChild(productImage);
      imageWrapper.appendChild(imageLink);
      notification.appendChild(imageWrapper);
    }

    // Get template and replace placeholders
    let template =
      config.template || '{name} just {action} {product}{additional_items}';

    // Get random action word
    const action = getRandomAction();

    // Format additional items text
    const additionalItemsText = formatAdditionalItems(additionalItems || 0);

    // Create product link
    const productLink = document.createElement('a');
    productLink.href = product.url;
    productLink.textContent = product.title;
    productLink.className = 'woo-notif-product-link';

    // Replace placeholders
    template = template.replace('{name}', name);
    template = template.replace('{action}', action);
    template = template.replace('{additional_items}', additionalItemsText);

    // Create full text version for screen readers (replace product link with text)
    const screenReaderText = template.replace('{product}', product.title);

    // Replace {product} placeholder with a temporary marker
    const productMarker = '__PRODUCT_LINK_MARKER__';
    template = template.replace('{product}', productMarker);

    // Split by marker and build the notification content
    const parts = template.split(productMarker);
    if (parts.length > 0 && parts[0]) {
      contentWrapper.appendChild(document.createTextNode(parts[0]));
    }
    contentWrapper.appendChild(productLink);
    if (parts.length > 1 && parts[1]) {
      contentWrapper.appendChild(document.createTextNode(parts[1]));
    }

    notification.appendChild(contentWrapper);

    // Add screen reader only text for better announcement
    const screenReaderOnly = document.createElement('span');
    screenReaderOnly.className = 'woo-notif-sr-only';
    screenReaderOnly.textContent = screenReaderText;
    notification.appendChild(screenReaderOnly);

    return notification;
  }

  /**
   * Show notification
   */
  function showNotification() {
    // Remove existing notification if present
    if (currentNotification) {
      removeNotification();
    }

    // Get random order
    const randomIndex = Math.floor(Math.random() * config.orders.length);
    const order = config.orders[randomIndex];

    if (!order || !order.name || !order.product) {
      return;
    }

    // Create and show notification
    currentNotification = createNotification(
      order.name,
      order.product,
      order.additional_items || 0,
    );
    document.body.appendChild(currentNotification);

    // Trigger slide-in animation
    // The aria-live="polite" attribute will automatically announce to screen readers
    setTimeout(function () {
      if (currentNotification) {
        currentNotification.classList.add('woo-notif-visible');
      }
    }, 10);

    // Remove notification after duration
    setTimeout(function () {
      removeNotification();
    }, config.duration);
  }

  /**
   * Remove notification with slide-out animation
   */
  function removeNotification() {
    if (!currentNotification) {
      return;
    }

    currentNotification.classList.remove('woo-notif-visible');
    currentNotification.classList.add('woo-notif-hiding');

    setTimeout(function () {
      if (currentNotification && currentNotification.parentNode) {
        currentNotification.parentNode.removeChild(currentNotification);
      }
      currentNotification = null;
    }, 300); // Match animation duration
  }

  /**
   * Initialize notification system
   */
  function init() {
    // Apply CSS variables
    applyCSSVariables();

    // Start showing notifications at intervals
    notificationInterval = setInterval(function () {
      showNotification();
    }, config.interval);

    // Show first notification after a short delay
    setTimeout(function () {
      showNotification();
    }, 1000);
  }

  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // Clean up on page unload
  window.addEventListener('beforeunload', function () {
    if (notificationInterval) {
      clearInterval(notificationInterval);
    }
    if (currentNotification) {
      removeNotification();
    }
  });
})();
