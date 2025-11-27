/**
 * GLOBAL EVENT DELEGATION SYSTEM
 * ==============================
 *
 * A centralized event delegation system for the entire application.
 * This ensures that event listeners work with dynamically added/removed DOM elements
 * without needing to reattach listeners after DOM updates.
 *
 * BENEFITS:
 * - Works with dynamically added/removed elements
 * - Better performance (fewer event listeners)
 * - No need to reattach listeners after DOM updates
 * - Consistent pattern across the entire application
 *
 * @package    TIJA_PMS
 * @subpackage Core_JavaScript
 * @author     TIJA Development Team
 * @version    1.0.0
 * @since      2024
 */

(function() {
    'use strict';

    /**
     * Global Event Delegation Manager
     * Handles all event delegation across the application
     */
    const EventDelegation = {
        /**
         * Registered delegation handlers
         * @type {Map<string, Array>}
         */
        handlers: new Map(),

        /**
         * Register an event delegation handler
         *
         * @param {string} selector - CSS selector for target elements
         * @param {string} eventType - Event type (e.g., 'click', 'change', 'submit')
         * @param {Function} handler - Handler function
         * @param {Object} options - Optional configuration
         * @param {HTMLElement|Document} container - Container element (default: document)
         * @returns {Function} Unregister function
         */
        on: function(selector, eventType, handler, options = {}, container = document) {
            const key = `${eventType}:${selector}`;

            // Create delegation handler
            const delegationHandler = function(e) {
                const target = e.target.closest(selector);
                if (target && container.contains(target)) {
                    // Call the handler with the target element and event
                    handler.call(target, e, target);
                }
            };

            // Store handler info
            if (!this.handlers.has(key)) {
                this.handlers.set(key, []);
            }

            const handlerInfo = {
                selector: selector,
                eventType: eventType,
                handler: handler,
                delegationHandler: delegationHandler,
                container: container,
                options: options
            };

            this.handlers.get(key).push(handlerInfo);

            // Attach event listener
            container.addEventListener(eventType, delegationHandler, options);

            // Return unregister function
            return () => {
                this.off(selector, eventType, handler, container);
            };
        },

        /**
         * Unregister an event delegation handler
         *
         * @param {string} selector - CSS selector for target elements
         * @param {string} eventType - Event type
         * @param {Function} handler - Handler function (optional, removes all if not provided)
         * @param {HTMLElement|Document} container - Container element (default: document)
         */
        off: function(selector, eventType, handler, container = document) {
            const key = `${eventType}:${selector}`;
            const handlers = this.handlers.get(key);

            if (!handlers) return;

            const remainingHandlers = handlers.filter(handlerInfo => {
                const shouldRemove = handlerInfo.container === container &&
                                   (!handler || handlerInfo.handler === handler);

                if (shouldRemove) {
                    handlerInfo.container.removeEventListener(
                        handlerInfo.eventType,
                        handlerInfo.delegationHandler,
                        handlerInfo.options
                    );
                }

                return !shouldRemove;
            });

            if (remainingHandlers.length === 0) {
                this.handlers.delete(key);
            } else {
                this.handlers.set(key, remainingHandlers);
            }
        },

        /**
         * Register multiple delegation handlers at once
         *
         * @param {Array} configs - Array of configuration objects
         * @param {HTMLElement|Document} container - Container element (default: document)
         * @returns {Array} Array of unregister functions
         */
        register: function(configs, container = document) {
            const unregisters = [];

            configs.forEach(config => {
                const unregister = this.on(
                    config.selector,
                    config.event || 'click',
                    config.handler,
                    config.options || {},
                    config.container || container
                );
                unregisters.push(unregister);
            });

            return unregisters;
        }
    };

    // Make EventDelegation available globally
    window.EventDelegation = EventDelegation;

    /**
     * Convenience function for common click delegation patterns
     *
     * @param {string} selector - CSS selector
     * @param {Function} handler - Handler function
     * @param {HTMLElement|Document} container - Container element
     * @returns {Function} Unregister function
     */
    window.delegateClick = function(selector, handler, container = document) {
        return EventDelegation.on(selector, 'click', handler, {}, container);
    };

    /**
     * Convenience function for common change delegation patterns
     *
     * @param {string} selector - CSS selector
     * @param {Function} handler - Handler function
     * @param {HTMLElement|Document} container - Container element
     * @returns {Function} Unregister function
     */
    window.delegateChange = function(selector, handler, container = document) {
        return EventDelegation.on(selector, 'change', handler, {}, container);
    };

    /**
     * Convenience function for common submit delegation patterns
     *
     * @param {string} selector - CSS selector
     * @param {Function} handler - Handler function
     * @param {HTMLElement|Document} container - Container element
     * @returns {Function} Unregister function
     */
    window.delegateSubmit = function(selector, handler, container = document) {
        return EventDelegation.on(selector, 'submit', handler, {}, container);
    };

    /**
     * Initialize common event delegations for the application
     * This sets up common patterns used across the application
     */
    function initializeCommonDelegations() {
        // Common delete button pattern
        EventDelegation.on('.deleteProject, .delete-item, .btn-delete', 'click', function(e, target) {
            e.preventDefault();
            // Handler will be set by individual pages
            if (target.onDelete && typeof target.onDelete === 'function') {
                target.onDelete.call(target, e);
            }
        });

        // Common edit button pattern
        EventDelegation.on('.editProjectCase, .edit-item, .btn-edit', 'click', function(e, target) {
            e.preventDefault();
            // Handler will be set by individual pages
            if (target.onEdit && typeof target.onEdit === 'function') {
                target.onEdit.call(target, e);
            }
        });

        // Common view button pattern
        EventDelegation.on('.view-item, .btn-view', 'click', function(e, target) {
            e.preventDefault();
            // Handler will be set by individual pages
            if (target.onView && typeof target.onView === 'function') {
                target.onView.call(target, e);
            }
        });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeCommonDelegations);
    } else {
        initializeCommonDelegations();
    }

    // Export for module systems (if needed)
    if (typeof module !== 'undefined' && module.exports) {
        module.exports = EventDelegation;
    }
})();

