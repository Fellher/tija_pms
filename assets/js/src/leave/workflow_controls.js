/**
 * leave/workflow_controls.js
 *
 * Centralised helpers for binding delegated UI behaviours within the leave
 * experience. The module exposes a `leaveUI` namespace on `window` so that
 * feature pages (dashboards, modals, full-page workflows) can opt-in to the
 * shared wiring logic instead of sprinkling inline handlers throughout the
 * markup.
 *
 * Key capabilities:
 *  - Wizard navigation (next/previous/submit) driven by `data-action` hooks.
 *  - Debug trigger binding (used by the modal visibility debugger).
 *  - File upload removal buttons (delegated so dynamically added files work).
 */
;(function (window, document) {
    const leaveUI = window.leaveUI || (window.leaveUI = {});

    /**
     * Attach a handler to a list of buttons while avoiding duplicate listeners.
     * Each button is marked with `data-leave-ui-bound="true"` once processed so
     * subsequent initialisation passes (e.g. when partials re-run) do not stack
     * handlers.
     *
     * @param {HTMLElement[]} buttons - collection of interactive elements.
     * @param {(ctx: { event: Event, button: HTMLElement, dataset: DOMStringMap }) => void} handler
     */
    function bindActionButtons(buttons, handler) {
        if (!buttons || !handler) {
            return;
        }

        buttons.forEach(button => {
            if (!button || button.dataset.leaveUiBound === 'true') {
                return;
            }

            button.addEventListener('click', event => {
                handler({
                    event,
                    button,
                    dataset: button.dataset
                });
            });

            button.dataset.leaveUiBound = 'true';
        });
    }

    /**
     * Derive the wizard step number from a button's dataset. Returns `undefined`
     * when the attribute is absent or not a number so callers can fall back to
     * their own defaults.
     *
     * @param {HTMLElement} button
     * @returns {number|undefined}
     */
    function parseStep(button) {
        if (!button) {
            return undefined;
        }

        const { step } = button.dataset || {};
        if (typeof step === 'string' && step.trim() !== '') {
            const parsed = Number(step);
            return Number.isNaN(parsed) ? undefined : parsed;
        }

        return undefined;
    }

    /**
     * Bind wizard navigation controls. Consumers provide callbacks for the
    * next/previous/submit actions and the helper attaches listeners to any
     * elements matching the standard `data-action` attributes within `root`.
     *
     * @param {Object} options
     * @param {ParentNode} [options.root=document] - scope to search within.
     * @param {(step:number|undefined, button:HTMLElement)=>void} [options.onNext]
     * @param {(step:number|undefined, button:HTMLElement)=>void} [options.onPrev]
     * @param {(button:HTMLElement)=>void} [options.onSubmit]
     */
    leaveUI.bindWizardNavigation = function bindWizardNavigation(options = {}) {
        const {
            root = document,
            onNext,
            onPrev,
            onSubmit
        } = options;

        bindActionButtons(
            Array.from(root.querySelectorAll('[data-action="workflow-next-step"]')),
            ({ button }) => {
                if (typeof onNext === 'function') {
                    onNext(parseStep(button), button);
                }
            }
        );

        bindActionButtons(
            Array.from(root.querySelectorAll('[data-action="workflow-prev-step"]')),
            ({ button }) => {
                if (typeof onPrev === 'function') {
                    onPrev(parseStep(button), button);
                }
            }
        );

        bindActionButtons(
            Array.from(root.querySelectorAll('[data-action="workflow-submit"]')),
            ({ button }) => {
                if (typeof onSubmit === 'function') {
                    onSubmit(button);
                }
            }
        );
    };

    /**
     * Register handlers for debug controls (e.g. the modal visibility checker).
     *
     * @param {Object} options
     * @param {ParentNode} [options.root=document]
     * @param {(button:HTMLElement)=>void} options.handler
     */
    leaveUI.bindDebugButtons = function bindDebugButtons(options = {}) {
        const {
            root = document,
            handler
        } = options;

        if (typeof handler !== 'function') {
            return;
        }

        bindActionButtons(
            Array.from(root.querySelectorAll('[data-action="workflow-debug"]')),
            ({ button }) => handler(button)
        );
    };

    /**
     * Delegate click handling for file removal buttons generated during uploads.
     * The default selector targets the shared `data-action="remove-uploaded-file"`
     * attribute, but callers can override it for custom widgets.
     *
     * @param {Object} options
     * @param {ParentNode} [options.root=document]
     * @param {string} [options.buttonSelector]
     * @param {(button:HTMLElement)=>void} options.onRemove
     */
    leaveUI.bindFileRemovalButtons = function bindFileRemovalButtons(options = {}) {
        const {
            root = document,
            buttonSelector = '[data-action="remove-uploaded-file"]',
            onRemove
        } = options;

        if (typeof onRemove !== 'function') {
            return;
        }

        bindActionButtons(
            Array.from(root.querySelectorAll(buttonSelector)),
            ({ button }) => onRemove(button)
        );
    };
})(window, document);

