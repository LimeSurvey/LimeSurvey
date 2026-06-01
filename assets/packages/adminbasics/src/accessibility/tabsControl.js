/**
 * Accessible keyboard navigation for Bootstrap tab components (ARIA APG pattern).
 *
 * - Roving tabindex: only the active tab is reachable via the Tab key;
 *   all others have tabindex="-1" and are navigated with arrow keys.
 * - Arrow keys (Left/Right/Up/Down), Home and End move focus and activate tabs.
 * - Safe to call multiple times — all listeners are re-bound with .off()/.on()
 *   to prevent duplicate handlers.
 * - Re-initialization after AJAX is handled externally via appendToLoad with
 *   the 'ajaxStop' event (see adminbasicsmain.js).
 */
const tabsControl = () => {
    const tabSelectors = '[role="tab"], [data-bs-toggle="tab"], [data-bs-toggle="pill"]';

    function getTabList($tab) {
        var $list = $tab.closest('[role="tablist"]');
        if ($list.length) {
            return $list;
        }
        $list = $tab.closest('ul.nav.nav-tabs, ul.nav.nav-pills');
        if ($list.length) {
            return $list;
        }
        $list = $tab.closest('.nav-tabs, .nav-pills');
        if ($list.length) {
            return $list.first();
        }
        return $tab.closest('.nav').first();
    }

    function listTabs($tabList) {
        return $tabList
            .find(tabSelectors)
            .filter(':visible')
            .filter(function () {
                return !$(this).hasClass('disabled')
                    && !$(this).is('[disabled]')
                    && $(this).attr('aria-disabled') !== 'true';
            });
    }

    /** Roving tabindex: only the selected tab is in Tab order; others use arrow keys only (APG tabs). */
    function updateRovingTabindex($tabList) {
        if (!$tabList || !$tabList.length) {
            return;
        }
        var $tabs = listTabs($tabList);
        if ($tabs.length === 0) {
            return;
        }
        var $active = $tabs.filter('.active').first();
        if (!$active.length) {
            $active = $tabs.filter('[aria-selected="true"]').first();
        }
        if (!$active.length) {
            $active = $tabs.first();
        }
        $tabs.attr('tabindex', '-1');
        $active.attr('tabindex', '0');
    }

    function refreshAllTablistsRovingTabindex() {
        var seen = [];
        $(tabSelectors).each(function () {
            var $list = getTabList($(this));
            if (!$list.length) {
                return;
            }
            var el = $list[0];
            if (seen.indexOf(el) !== -1) {
                return;
            }
            seen.push(el);
            updateRovingTabindex($list);
        });
    }

    function bindTabArrowNavigation() {
        $(document)
            .off('keydown.ls-tabs-a11y', tabSelectors)
            .on('keydown.ls-tabs-a11y', tabSelectors, function (event) {
                var key = event.key;
                if (['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'].indexOf(key) === -1) {
                    return;
                }

                var $currentTab = $(this);
                var $tabList = getTabList($currentTab);
                if ($tabList.length === 0) {
                    return;
                }

                var $tabs = listTabs($tabList);
                if ($tabs.length < 2) {
                    return;
                }

                var currentIndex = $tabs.index($currentTab);
                if (currentIndex < 0) {
                    return;
                }

                event.preventDefault();

                var nextIndex = currentIndex;
                if (key === 'ArrowLeft' || key === 'ArrowUp') {
                    nextIndex = (currentIndex - 1 + $tabs.length) % $tabs.length;
                } else if (key === 'ArrowRight' || key === 'ArrowDown') {
                    nextIndex = (currentIndex + 1) % $tabs.length;
                } else if (key === 'Home') {
                    nextIndex = 0;
                } else if (key === 'End') {
                    nextIndex = $tabs.length - 1;
                }

                var $nextTab = $tabs.eq(nextIndex);
                $nextTab.trigger('focus');

                if (window.bootstrap && window.bootstrap.Tab) {
                    window.bootstrap.Tab.getOrCreateInstance($nextTab[0]).show();
                } else if (typeof $nextTab.tab === 'function') {
                    $nextTab.tab('show');
                } else {
                    $nextTab.trigger('click');
                }
            });
    }

    bindTabArrowNavigation();
    refreshAllTablistsRovingTabindex();

    // Update roving tabindex after every tab switch
    $(document).off('shown.bs.tab.ls-tabs-a11y', tabSelectors)
        .on('shown.bs.tab.ls-tabs-a11y', tabSelectors, function () {
            updateRovingTabindex(getTabList($(this)));
        });
};

export default tabsControl;

