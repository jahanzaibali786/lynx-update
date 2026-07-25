/**
 * CustomSelect - A searchable select dropdown component
 * Usage: Add class 'custom-select' to any select element
 * Version: 1.1.0
 */

(function () {
    'use strict';

    // CSS Styles
    const css = `
        .custom-select-wrapper {
            position: relative;
            width: 100%;
            font-family: Arial, sans-serif;
        }

        .custom-select-display {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid var(--primary);
            border-radius: 10px;
            background: white;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 14px;
            min-height: 20px;
            box-sizing: border-box;
        }

        .custom-select-display:hover {
            border-color: var(--primary);
        }

        .custom-select-display.active {
            border-color: var(--primary);
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
        }

        .custom-select-arrow {
            width: 0;
            height: 0;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-top: 5px solid #666;
            transition: transform 0.2s;
        }

        .custom-select-arrow.open {
            transform: rotate(180deg);
        }

        .custom-select-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid var(--primary);
            border-top: none;
            border-radius: 0 0 4px 4px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            box-sizing: border-box;
        }

        .custom-select-dropdown.show {
            display: block;
        }

        .custom-select-search {
            width: 100%;
            padding: 8px 12px;
            border: none;
            border-bottom: 1px solid #eee;
            font-size: 14px;
            outline: none;
            box-sizing: border-box;
        }

        .custom-select-search:focus {
            background-color: #f8f9fa;
        }

        .custom-select-option {
            padding: 8px 12px;
            cursor: pointer;
            font-size: 14px;
            border-bottom: 1px solid #f0f0f0;
        }

        .custom-select-option:hover {
            background-color: #f8f9fa;
        }

        .custom-select-option.selected {
            background-color: #007bff;
            color: white;
        }

        .custom-select-option:last-child {
            border-bottom: none;
        }

        .custom-select-no-results {
            padding: 8px 12px;
            color: #999;
            font-style: italic;
            text-align: center;
        }

        /* Disabled state */
        .custom-select-wrapper.disabled .custom-select-display {
            background-color: #f5f5f5;
            color: #666;
            cursor: not-allowed;
        }

        .custom-select-wrapper.disabled .custom-select-arrow {
            border-top-color: var(--primary);
        }
    `;

    function injectCSS() {
        if (!document.getElementById('custom-select-styles')) {
            const style = document.createElement('style');
            style.id = 'custom-select-styles';
            style.textContent = css;
            document.head.appendChild(style);
        }
    }

    // Helper function to find the nearest label for a select element
    function findNearestLabel(selectElement) {
        // First check if the select has an id and if there's a label with matching 'for' attribute
        if (selectElement.id) {
            const labelForSelect = document.querySelector(`label[for="${selectElement.id}"]`);
            if (labelForSelect) {
                return labelForSelect.textContent.trim();
            }
        }

        // Check if select is inside a label
        let parent = selectElement.parentElement;
        while (parent) {
            if (parent.tagName === 'LABEL') {
                return parent.textContent.trim();
            }

            // Look for a label in the same parent container (common pattern in forms)
            if (parent.querySelector('label')) {
                const labels = parent.querySelectorAll('label');
                // Get the first label in the same container
                if (labels.length > 0) {
                    return labels[0].textContent.trim();
                }
            }

            // Move up the DOM tree
            parent = parent.parentElement;

            
            // Limit how far we look up (stop at table row, form, or body)
            if (parent && (parent.tagName === 'TR' || parent.tagName === 'TABLE' || parent.tagName === 'FORM' || parent.tagName === 'BODY')) {
                break;
            }
        }

        // If no label found, return null
        return null;
    }

    // CustomSelect Class
    class CustomSelect {
        constructor(selectElement) {
            this.originalSelect = selectElement;
            this.options = [];
            this.filteredOptions = [];
            this.selectedValue = '';
            this.selectedText = '';
            this.isOpen = false;

            this.init();
        }

        init() {
            // Hide original select
            this.originalSelect.style.display = 'none';

            // Get options from original select
            this.extractOptions();

            // Create custom select structure
            this.createCustomSelect();

            // Set initial value
            this.setInitialValue();

            // Bind events
            this.bindEvents();
        }

        extractOptions() {
            this.options = [];
            const selectOptions = this.originalSelect.querySelectorAll('option');

            selectOptions.forEach(option => {
                if (option.value !== '') { // Skip empty placeholder options
                    this.options.push({
                        value: option.value,
                        text: option.textContent.trim(),
                        selected: option.selected
                    });
                }
            });

            this.filteredOptions = [...this.options];
        }

        createCustomSelect() {
            // Create wrapper
            this.wrapper = document.createElement('div');
            this.wrapper.className = 'custom-select-wrapper';

            if (this.originalSelect.disabled) {
                this.wrapper.classList.add('disabled');
            }

            // Create display element
            this.display = document.createElement('div');
            this.display.className = 'custom-select-display';

            this.displayText = document.createElement('span');

           // Get placeholder text - explicit placeholder attribute takes priority
            let placeholderText = this.originalSelect.getAttribute('placeholder') || 'Select .....';
            if (!this.originalSelect.getAttribute('placeholder')) {
                const labelText = findNearestLabel(this.originalSelect);
                if (labelText) {
                    placeholderText = `Select ${labelText}`;
                }
            }

            this.displayText.textContent = placeholderText;

            this.arrow = document.createElement('div');
            this.arrow.className = 'custom-select-arrow';

            this.display.appendChild(this.displayText);
            this.display.appendChild(this.arrow);

            // Create dropdown
            this.dropdown = document.createElement('div');
            this.dropdown.className = 'custom-select-dropdown';

            // Create search input
            this.searchInput = document.createElement('input');
            this.searchInput.className = 'custom-select-search';
            this.searchInput.type = 'text';
            this.searchInput.placeholder = 'Search ......';

            // Create options container
            this.optionsContainer = document.createElement('div');

            this.dropdown.appendChild(this.searchInput);
            this.dropdown.appendChild(this.optionsContainer);

            // Assemble wrapper
            this.wrapper.appendChild(this.display);
            this.wrapper.appendChild(this.dropdown);

            // Insert after original select
            this.originalSelect.parentNode.insertBefore(this.wrapper, this.originalSelect.nextSibling);

            // Render options
            this.renderOptions();
        }

        setInitialValue() {
            const selectedOption = this.options.find(opt => opt.selected);
            if (selectedOption) {
                this.selectedValue = selectedOption.value;
                this.selectedText = selectedOption.text;
                this.displayText.textContent = selectedOption.text;
            }
        }

        renderOptions() {
            this.optionsContainer.innerHTML = '';

            if (this.filteredOptions.length === 0) {
                const noResults = document.createElement('div');
                noResults.className = 'custom-select-no-results';
                noResults.textContent = 'No options found';
                this.optionsContainer.appendChild(noResults);
                return;
            }

            this.filteredOptions.forEach(option => {
                const optionElement = document.createElement('div');
                optionElement.className = 'custom-select-option';
                optionElement.textContent = option.text;
                optionElement.dataset.value = option.value;

                if (option.value === this.selectedValue) {
                    optionElement.classList.add('selected');
                }

                optionElement.addEventListener('click', () => {
                    this.selectOption(option);
                });

                this.optionsContainer.appendChild(optionElement);
            });
        }

        bindEvents() {
            // Toggle dropdown
            this.display.addEventListener('click', (e) => {
                if (!this.originalSelect.disabled) {
                    this.toggle();
                }
            });

            // Search functionality
            this.searchInput.addEventListener('input', (e) => {
                this.search(e.target.value);
            });

            // Prevent dropdown close when clicking search input
            this.searchInput.addEventListener('click', (e) => {
                e.stopPropagation();
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!this.wrapper.contains(e.target)) {
                    this.close();
                }
            });

            // Keyboard navigation
            this.searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.close();
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    const firstOption = this.filteredOptions[0];
                    if (firstOption) {
                        this.selectOption(firstOption);
                    }
                } else if (e.key === 'Tab') {
                    e.preventDefault();
                    const firstOption = this.filteredOptions[0];
                    if (firstOption) {
                        this.selectOption(firstOption);
                    }
                }
            });
        }

        search(query) {
            const searchTerm = query.toLowerCase().trim();

            if (searchTerm === '') {
                this.filteredOptions = [...this.options];
            } else {
                this.filteredOptions = this.options.filter(option =>
                    option.text.toLowerCase().includes(searchTerm) ||
                    option.value.toLowerCase().includes(searchTerm)
                );
            }

            this.renderOptions();
        }

        selectOption(option) {
            this.selectedValue = option.value;
            this.selectedText = option.text;
            this.displayText.textContent = option.text;

            // Update original select
            this.originalSelect.value = option.value;

            // Trigger change event on original select
            const changeEvent = new Event('change', { bubbles: true });
            this.originalSelect.dispatchEvent(changeEvent);

            // Clear search and close dropdown
            this.searchInput.value = '';
            this.filteredOptions = [...this.options];
            this.close();
        }

        open() {
            if (this.originalSelect.disabled) return;

            this.isOpen = true;
            this.dropdown.classList.add('show');
            this.display.classList.add('active');
            this.arrow.classList.add('open');
            this.searchInput.focus();
        }

        close() {
            this.isOpen = false;
            this.dropdown.classList.remove('show');
            this.display.classList.remove('active');
            this.arrow.classList.remove('open');
            this.searchInput.value = '';
            this.filteredOptions = [...this.options];
            this.renderOptions();
        }

        toggle() {
            if (this.isOpen) {
                this.close();
            } else {
                this.open();
            }
        }

        // Public method to update options dynamically
        updateOptions() {
            this.extractOptions();
            this.renderOptions();
        }

        // Public method to set value
        setValue(value) {
            const option = this.options.find(opt => String(opt.value) === String(value));
            if (option) {
                this.selectOption(option);
            } else {
                console.warn("Value not found:", value, this.options);
            }
        }

        // Public method to destroy the custom select
        destroy() {
            this.wrapper.remove();
            this.originalSelect.style.display = '';
        }
    }

    // Initialize CustomSelect for all elements with 'custom-select' class
    function initCustomSelects(container = document) {
        const selects = container.querySelectorAll('select.custom-select');
        selects.forEach(select => {
            if (!select.customSelectInstance) {
                select.customSelectInstance = new CustomSelect(select);
            }
        });
    }

    // Observer to watch for dynamically added elements
    function setupMutationObserver() {
        const observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (node) {
                    if (node.nodeType === 1) { // Element node
                        // Check if the added node itself is a select with custom-select class
                        if (node.matches && node.matches('select.custom-select')) {
                            if (!node.customSelectInstance) {
                                node.customSelectInstance = new CustomSelect(node);
                            }
                        }
                        // Check for select elements within the added node
                        const selectsInNode = node.querySelectorAll && node.querySelectorAll('select.custom-select');
                        if (selectsInNode && selectsInNode.length > 0) {
                            selectsInNode.forEach(select => {
                                if (!select.customSelectInstance) {
                                    select.customSelectInstance = new CustomSelect(select);
                                }
                            });
                        }
                    }
                });
            });
        });

        // Start observing
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    // Auto-initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            injectCSS();
            initCustomSelects();
            setupMutationObserver();
        });
    } else {
        injectCSS();
        initCustomSelects();
        setupMutationObserver();
    }

    // Expose global functions
    window.CustomSelect = {
        init: initCustomSelects,
        create: (selectElement) => {
            const instance = new CustomSelect(selectElement);
            selectElement.customSelectInstance = instance;
            return instance;
        },
        initContainer: (container) => initCustomSelects(container)
    };
})();