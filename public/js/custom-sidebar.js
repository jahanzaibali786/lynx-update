(() => {
    class LynxMultiLevelSidebar {
        constructor(root) {
            this.root = root;
            this.panel = root?.querySelector('[data-lynx-sidebar-panel]');
            this.backdrop = root?.querySelector('[data-lynx-sidebar-backdrop]');
            this.toggleButton = root?.querySelector('[data-lynx-sidebar-toggle-drawer]');
            this.scroll = root?.querySelector('[data-lynx-sidebar-scroll]');
            this.desktopQuery = window.matchMedia('(min-width: 992px)');
            this.bound = false;
            this.hoverTimers = new Map();
            this.closeTimers = new Map();
            this.pinButton = null;
            this.isPinned = false;
        }

        init() {
            if (!this.root || this.root.dataset.lynxSidebarReady === 'true') {
                return;
            }

            this.root.dataset.lynxSidebarReady = 'true';
            this.bind();
            this.restorePinnedState();
            this.syncState();
        }

        bind() {
            if (this.bound) {
                return;
            }

            this.bound = true;

            if (this.toggleButton) {
                this.toggleButton.addEventListener('click', () => this.toggleDrawer());
            }

            if (this.backdrop) {
                this.backdrop.addEventListener('click', () => this.closeDrawer());
            }

            this.root.addEventListener('mouseleave', () => {
                if (this.isDesktop() && !this.isPinned) {
                    this.scheduleCloseAll();
                }
            });

            this.root.addEventListener('mouseenter', () => this.cancelCloseAll());
            this.scroll?.addEventListener('scroll', () => this.positionVisibleSubmenus(), { passive: true });

            this.root.querySelectorAll('[data-lynx-sidebar-item]').forEach((item) => {
                const trigger = item.querySelector('[data-lynx-sidebar-trigger]');
                const submenu = item.querySelector(':scope > [data-lynx-sidebar-submenu]');

                if (!trigger || !submenu) {
                    return;
                }

                trigger.addEventListener('click', (event) => this.onTriggerClick(event, item));
                trigger.addEventListener('keydown', (event) => this.onTriggerKeydown(event, item));
                trigger.addEventListener('focus', () => this.openItem(item));
                trigger.addEventListener('mouseenter', () => this.onItemHover(item));
                trigger.addEventListener('mouseleave', () => this.onItemLeave(item));
                submenu.addEventListener('mouseenter', () => { this.cancelItemClose(item); this.cancelCloseAll(); });
                submenu.addEventListener('mouseleave', () => this.onItemLeave(item));
            });

            window.addEventListener('resize', () => this.onResize(), { passive: true });
            document.addEventListener('keydown', (event) => this.onGlobalKeydown(event));
            document.addEventListener('click', (event) => this.onDocumentClick(event));
        }

        isDesktop() {
            return this.desktopQuery.matches;
        }

        restorePinnedState() {
            try {
                this.isPinned = window.localStorage.getItem('lynx-sidebar-pinned') === 'true';
            } catch (error) {
                this.isPinned = false;
            }

            this.applyPinnedState(this.isPinned);
        }

        applyPinnedState(isPinned) {
            this.isPinned = Boolean(isPinned);
            this.root.classList.toggle('is-pinned', this.isPinned);

            if (this.pinButton) {
                this.pinButton.setAttribute('aria-pressed', this.isPinned ? 'true' : 'false');
            }

            try {
                window.localStorage.setItem('lynx-sidebar-pinned', this.isPinned ? 'true' : 'false');
            } catch (error) {
                // ignore storage failures
            }
        }

        togglePinned(event) {
            event.preventDefault();
            this.applyPinnedState(! this.isPinned);
        }

        onResize() {
            if (this.isDesktop()) {
                this.closeDrawer();
            } else {
                this.syncMobileOpenState();
            }

            this.positionVisibleSubmenus();
        }

        onDocumentClick(event) {
            if (!this.root.contains(event.target) && !this.isPinned) {
                this.closeAll();
            }
        }

        onGlobalKeydown(event) {
            if (event.key === 'Escape') {
                this.closeAll();
                return;
            }

            const trigger = event.target.closest?.('[data-lynx-sidebar-trigger]');
            if (!trigger) {
                return;
            }

            const item = trigger.closest('[data-lynx-sidebar-item]');
            if (!item) {
                return;
            }

            if (event.key === 'ArrowRight') {
                event.preventDefault();
                this.openItem(item, true);
                return;
            }

            if (event.key === 'ArrowLeft') {
                event.preventDefault();
                this.closeItem(item);
                trigger.focus();
            }
        }

        onTriggerClick(event, item) {
            const hasChildren = item.classList.contains('lynx-sidebar__item--parent');

            if (!hasChildren) {
                return;
            }

            event.preventDefault();
            if (this.isDesktop() && item.classList.contains('is-open')) {
                this.closeItem(item);
            } else {
                this.openItem(item, true);
            }
        }

        onTriggerKeydown(event, item) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                this.openItem(item, true);
            }
        }

        onItemHover(item) {
            if (!this.isDesktop() || this.isPinned) {
                return;
            }

            this.cancelItemClose(item);
            this.clearHoverTimer(item);

            const timer = window.setTimeout(() => {
                this.openItem(item);
            }, 120);

            this.hoverTimers.set(item, timer);
        }

        onItemLeave(item) {
            if (!this.isDesktop() || this.isPinned) {
                return;
            }

            this.clearHoverTimer(item);
            this.scheduleItemClose(item);
        }

        toggleDrawer() {
            if (this.root.classList.contains('is-open')) {
                this.closeDrawer();
            } else {
                this.openDrawer();
            }
        }

        openDrawer() {
            this.root.classList.add('is-open');
            document.documentElement.classList.add('lynx-sidebar-drawer-open');
        }

        closeDrawer() {
            this.root.classList.remove('is-open');
            document.documentElement.classList.remove('lynx-sidebar-drawer-open');
        }

        syncMobileOpenState() {
            const shouldBeOpen = this.root.classList.contains('is-open');
            if (shouldBeOpen) {
                document.documentElement.classList.add('lynx-sidebar-drawer-open');
            }
        }

        syncState() {
            this.root.querySelectorAll('.is-open').forEach((item) => {
                const trigger = item.querySelector(':scope > [data-lynx-sidebar-trigger]');
                if (trigger) {
                    trigger.setAttribute('aria-expanded', 'true');
                }
            });

            this.positionVisibleSubmenus();
        }

        openItem(item, focusFirst = false) {
            if (!item) {
                return;
            }

            const trigger = item.querySelector(':scope > [data-lynx-sidebar-trigger]');
            const submenu = item.querySelector(':scope > [data-lynx-sidebar-submenu]');
            if (!trigger || !submenu) {
                return;
            }

            const siblings = item.parentElement?.querySelectorAll(':scope > .lynx-sidebar__item.is-open') || [];
            siblings.forEach((sibling) => {
                if (sibling !== item) {
                    this.closeItem(sibling);
                }
            });

            item.classList.add('is-open');
            trigger.setAttribute('aria-expanded', 'true');
            window.requestAnimationFrame(() => this.positionSubmenu(item, submenu));

            if (focusFirst) {
                const firstFocusable = submenu.querySelector('a, button');
                if (firstFocusable) {
                    firstFocusable.focus({ preventScroll: true });
                }
            }
        }

        closeItem(item) {
            if (!item) {
                return;
            }

            const trigger = item.querySelector(':scope > [data-lynx-sidebar-trigger]');
            const submenu = item.querySelector(':scope > [data-lynx-sidebar-submenu]');
            if (submenu) {
                submenu.querySelectorAll('.lynx-sidebar__item.is-open').forEach((child) => this.closeItem(child));
                this.resetSubmenuPosition(submenu);
            }

            item.classList.remove('is-open');
            if (trigger) {
                trigger.setAttribute('aria-expanded', 'false');
            }

            this.clearHoverTimer(item);
            this.cancelItemClose(item);
        }

        closeAll() {
            this.cancelCloseAll();
            this.root.querySelectorAll('.lynx-sidebar__item.is-open').forEach((item) => this.closeItem(item));
            this.closeDrawer();
        }

        scheduleItemClose(item) {
            this.cancelItemClose(item);
            const timer = window.setTimeout(() => this.closeItem(item), 160);
            this.closeTimers.set(item, timer);
        }

        cancelItemClose(item) {
            const timer = this.closeTimers.get(item);
            if (timer) {
                window.clearTimeout(timer);
                this.closeTimers.delete(item);
            }
        }

        clearHoverTimer(item) {
            const timer = this.hoverTimers.get(item);
            if (timer) {
                window.clearTimeout(timer);
                this.hoverTimers.delete(item);
            }
        }

        scheduleCloseAll() {
            if (this.isPinned) {
                return;
            }

            this.cancelCloseAll();
            this.closeAllTimer = window.setTimeout(() => this.closeAll(), 180);
        }

        cancelCloseAll() {
            if (this.closeAllTimer) {
                window.clearTimeout(this.closeAllTimer);
                this.closeAllTimer = null;
            }
        }

        resetSubmenuPosition(submenu) {
            submenu.style.position = '';
            submenu.style.top = '';
            submenu.style.left = '';
            submenu.style.right = '';
            submenu.style.zIndex = '';
            submenu.classList.remove('is-left');
        }

        positionVisibleSubmenus() {
            if (!this.isDesktop() || this.isPinned) {
                return;
            }

            this.root.querySelectorAll('.lynx-sidebar__item.is-open > [data-lynx-sidebar-submenu]').forEach((submenu) => {
                const item = submenu.parentElement;
                this.positionSubmenu(item, submenu);
            });
        }

        positionSubmenu(item, submenu) {
            if (!this.isDesktop()) {
                this.resetSubmenuPosition(submenu);
                return;
            }

            const padding = 12;
            const gap = 12;
            const parentFlyout = item.closest('[data-lynx-sidebar-submenu]');
            const anchorRect = parentFlyout ? parentFlyout.getBoundingClientRect() : item.getBoundingClientRect();
            const itemRect = item.getBoundingClientRect();
            const computedStyle = window.getComputedStyle(submenu);
            const width = submenu.getBoundingClientRect().width || submenu.offsetWidth || parseFloat(computedStyle.width) || 286;
            const height = submenu.getBoundingClientRect().height || submenu.offsetHeight || parseFloat(computedStyle.maxHeight) || 560;
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;

            let left = anchorRect.right + gap;
            let top = itemRect.top;

            if (left + width > viewportWidth - padding) {
                left = anchorRect.left - width - gap;
                submenu.classList.add('is-left');
            } else {
                submenu.classList.remove('is-left');
            }

            if (left < padding) {
                left = Math.max(padding, viewportWidth - width - padding);
                submenu.classList.remove('is-left');
            }

            if (top + height > viewportHeight - padding) {
                top = Math.max(padding, viewportHeight - height - padding);
            }

            if (top < padding) {
                top = padding;
            }

            submenu.style.position = 'fixed';
            submenu.style.left = `${Math.round(left)}px`;
            submenu.style.top = `${Math.round(top)}px`;
            submenu.style.right = 'auto';
            submenu.style.zIndex = String(1060 + Number(submenu.dataset.lynxSidebarDepth || 0));
        }
    }

    const init = () => {
        const root = document.querySelector('[data-lynx-sidebar]');
        if (!root) {
            return;
        }

        if (window.__lynxMultiLevelSidebar instanceof LynxMultiLevelSidebar) {
            return;
        }

        window.__lynxMultiLevelSidebar = new LynxMultiLevelSidebar(root);
        window.__lynxMultiLevelSidebar.init();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, { once: true });
    } else {
        init();
    }

    window.LynxMultiLevelSidebar = LynxMultiLevelSidebar;
})();







