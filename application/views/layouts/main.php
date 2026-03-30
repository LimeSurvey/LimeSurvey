<?php

Yii::import('application.helpers.common_helper', true);
Yii::import('application.helpers.globalsettings_helper', true);


$aData = Yii::app()->getController()->aData;

$layoutHelper = new LayoutHelper();

// ###################################################  HEADER #####################################################
$layoutHelper->showHeaders($aData);
//################################################# END HEADER #######################################################


//################################################## ADMIN MENU #####################################################
$layoutHelper->showadminmenu($aData);

echo "<!-- BEGIN LAYOUT MAIN (refactored controllers-->";
echo "<div id='layout_sidebar'>";

App()->getController()->widget('ext.SideBarWidget.SideBarWidget');

echo "<div class='container-40'>";
echo $layoutHelper->renderTopbarTemplate($aData);

echo "<div class='container-fluid'>";
$layoutHelper->updatenotification();
echo "</div>";

$layoutHelper->notifications();

//The load indicator for pjax
echo ' <div id="pjax-file-load-container" class="ls-flex-row col-12"><div style="height:2px;width:0px;"></div></div>';

echo '<!-- Full page, started in SurveyCommonAction::renderWrappedTemplate() -->
      <div class="container-fluid" id="in_survey_common_action">';

echo $content;

echo '</div>';

// close container-40 and layout_sidebar
echo '</div>';
echo '</div>';

// Footer
if (!isset($aData['display']['endscripts']) || $aData['display']['endscripts'] !== false) {
    $layoutHelper->loadEndScripts();
}

App()->getClientScript()->registerScript(
    'tabs-a11y-roving-and-arrows',
    <<<'JS'
(function() {
    var tabSelectors = '[role="tab"], [data-bs-toggle="tab"], [data-bs-toggle="pill"]';

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
            .filter(function() {
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
        $(tabSelectors).each(function() {
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
            .on('keydown.ls-tabs-a11y', tabSelectors, function(event) {
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

    function initTabsA11y() {
        bindTabArrowNavigation();
        refreshAllTablistsRovingTabindex();
    }

    $(document).on('shown.bs.tab', tabSelectors, function() {
        updateRovingTabindex(getTabList($(this)));
    });

    $(document).on('ready pjax:scriptcomplete', initTabsA11y);
    initTabsA11y();
})();
JS
    ,
    LSYii_ClientScript::POS_END
);

if (!Yii::app()->user->isGuest) {
    if (!isset($aData['display']['footer']) || $aData['display']['footer'] !== false) {
        $layoutHelper->getAdminFooter('http://manual.limesurvey.org');
    }
} else {
    echo '</body>
    </html>';
}
