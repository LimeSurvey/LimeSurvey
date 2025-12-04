/**
 * Global Sidemenu Component - Vanilla JS
 * Main sidebar with resizable functionality
 */

import ConsoleShim from '../../../meta/lib/ConsoleShim.js';

const LOG = new ConsoleShim('globalsidepanel');

class GlobalSidemenu {
    constructor(container, store, actions, components) {
        this.container = typeof container === 'string' ? document.querySelector(container) : container;
        this.store = store;
        this.actions = actions;
        this.components = components;

        // Component data
        this.activeMenuIndex = 0;
        this.initialPos = { x: 0, y: 0 };
        this.isMouseDown = false;
        this.isMouseDownTimeOut = null;

        // Bind methods
        this.mousedown = this.mousedown.bind(this);
        this.mouseup = this.mouseup.bind(this);
        this.mouseleave = this.mouseleave.bind(this);
        this.mousemove = this.mousemove.bind(this);

        this.init();
    }

    get sideBarWidth() {
        return this.store.get('sidebarwidth');
    }

    set sideBarWidth(value) {
        this.store.commit('setSidebarwidth', value);
    }

    get getWindowHeight() {
        return $(document).height();
    }

    get calculateSideBarMenuHeight() {
        const pjaxContent = document.getElementById('pjax-content');
        return pjaxContent ? pjaxContent.offsetHeight : 400;
    }

    get currentMenue() {
        return this.store.get('menu') || [];
    }

    translate(string) {
        return window.GlobalSideMenuData.i10n[string] || string;
    }

    init() {
        this.actions.getMenus().then(() => {
            this.controlActiveLink();
            this.render();
            this.attachEventListeners();
            this.mounted();
        });
    }

    render() {
        if (!this.container) return;

        this.container.innerHTML = `
            <div
                id="sidebar"
                class="d-flex col-lg-4 ls-ba position-relative transition-animate-width bg-white py-4 h-100"
                style="min-width: 250px; width: ${this.sideBarWidth}px;"
            >
                <div class="col-12">
                    <div class="mainMenu col-12">
                        <div id="sidemenu-container" style="min-height: ${this.calculateSideBarMenuHeight}px"></div>
                    </div>
                </div>
                <div class="resize-handle ls-flex-column" style="height: 100%; max-height: ${this.getWindowHeight}px">
                    <button
                        id="resize-handle-btn"
                        class="btn"
                        style="display: ${this.store.get('isCollapsed') ? 'none' : 'block'}"
                    >
                        <svg width="9" height="14" viewBox="0 0 9 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M0.4646 0.125H3.24762V2.625H0.4646V0.125ZM6.03064 0.125H8.81366V2.625H6.03064V0.125ZM0.4646 5.75H3.24762V8.25H0.4646V5.75ZM6.03064 5.75H8.81366V8.25H6.03064V5.75ZM0.4646 11.375H3.24762V13.875H0.4646V11.375ZM6.03064 11.375H8.81366V13.875H6.03064V11.375Z"
                                fill="currentColor" />
                        </svg>
                    </button>
                </div>
                ${this.isMouseDown ? '<div class="mouseup-support" style="position:fixed; inset: 0;"></div>' : ''}
            </div>
        `;

        this.sidebarEl = this.container.querySelector('#sidebar');
        this.renderSubComponents();
    }

    renderSubComponents() {
        const sidemenuContainer = this.container.querySelector('#sidemenu-container');
        if (sidemenuContainer && this.components.Sidemenu) {
            new this.components.Sidemenu(sidemenuContainer, this.store, this.currentMenue);
        }
    }

    attachEventListeners() {
        // Resize handle
        const resizeBtn = this.container.querySelector('#resize-handle-btn');
        if (resizeBtn) {
            resizeBtn.addEventListener('mousedown', this.mousedown);
            resizeBtn.addEventListener('click', (e) => e.preventDefault());
        }

        // Sidebar mouse events
        if (this.sidebarEl) {
            this.sidebarEl.addEventListener('mouseleave', this.mouseleave);
            this.sidebarEl.addEventListener('mouseup', this.mouseup);
        }

        // Body mousemove - only add once
        if (!this.mousemoveAttached) {
            document.body.addEventListener('mousemove', this.mousemove);
            this.mousemoveAttached = true;
        }

        // Subscribe to store changes - only subscribe once
        if (!this.storeSubscribed) {
            this.store.subscribe((key, newValue, oldValue) => {
                if (key === 'sidebarwidth' || key === 'menu') {
                    this.update();
                }
            });
            this.storeSubscribed = true;
        }
    }

    mounted() {
        $(document).on("vue-redraw", () => {
            this.update();
        });

        $(document).trigger("vue-reload-remote");
    }

    update() {
        this.render();
        this.attachEventListeners();
    }

    controlActiveLink() {
        const currentUrl = window.location.href;
        let lastMenuItemObject = false;

        if (this.currentMenue.entries) {
            LS.ld.each(this.currentMenue.entries, (itmm) => {
                lastMenuItemObject = LS.ld.endsWith(currentUrl, itmm.partial.split('/').pop())
                    ? itmm
                    : lastMenuItemObject;
            });
        }

        if (lastMenuItemObject === false) {
            lastMenuItemObject = { partial: 'redundant/_generaloptions_panel' };
        }

        this.store.commit('setLastMenuItemOpen', lastMenuItemObject.partial.split('/').pop());
    }

    mousedown(e) {
        this.isMouseDown = true;
        $("#sidebar").removeClass("transition-animate-width");
        $("#pjax-content").removeClass("transition-animate-width");
    }

    mouseup(e) {
        if (this.isMouseDown) {
            this.isMouseDown = false;
            this.store.commit('setIsCollapsed', false);
            if (parseInt(this.sideBarWidth) < 250) {
                this.sideBarWidth = 250;
            }
            $("#sidebar").addClass("transition-animate-width");
            $("#pjax-content").removeClass("transition-animate-width");
            this.update();
        }
    }

    mouseleave(e) {
        if (this.isMouseDown) {
            this.isMouseDownTimeOut = setTimeout(() => {
                this.mouseup(e);
            }, 1000);
        }
    }

    mousemove(e) {
        if (this.isMouseDown) {
            // Prevent unwanted value on dragend
            if (e.screenX === 0 && e.screenY === 0) {
                return;
            }
            if (e.clientX > screen.width / 2) {
                this.sideBarWidth = screen.width / 2;
                return;
            }
            this.sideBarWidth = e.pageX - 4;
            window.clearTimeout(this.isMouseDownTimeOut);
            this.isMouseDownTimeOut = null;
        }
    }

    updatePjaxLinks() {
        window.LS.doToolTip();
    }

    destroy() {
        const resizeBtn = this.container.querySelector('#resize-handle-btn');
        if (resizeBtn) {
            resizeBtn.removeEventListener('mousedown', this.mousedown);
        }

        document.body.removeEventListener('mousemove', this.mousemove);
        $(document).off("vue-redraw");
    }
}

export default GlobalSidemenu;
