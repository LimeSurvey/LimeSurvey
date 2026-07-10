<?php

/**
 * A floating action bar widget for CLSGridView grids.
 *
 * Renders a persistent floating bar that appears at the bottom of the viewport
 * whenever one or more grid rows are selected (including across pagination).
 *
 * Action definitions are supplied via the $aActions property and should be
 * maintained in a separate config file (e.g. floating_actions/_actions.php)
 * that returns a PHP array.
 *
 * Supported action types:
 *   'action'    – a single button (direct action or modal)
 *   'dropdown'  – a split button with sub-items (each sub-item is an action)
 *   'separator' – a thin vertical divider line
 *
 * Usage example:
 *   $actions = require(__DIR__ . '/floating_actions/_actions.php');
 *   $this->widget('ext.admin.grid.FloatingActionsWidget.FloatingActionsWidget', [
 *       'pk'       => 'sid',
 *       'gridId'   => 'survey-grid',
 *       'aActions' => $actions,
 *   ]);
 */
class FloatingActionsWidget extends CWidget
{
    /** @var string Primary key column name (e.g. 'sid', 'id') */
    public $pk;

    /** @var string ID attribute of the CLSGridView container element */
    public $gridId;

    /**
     * Alias for $gridId using lowercase property name, required by the 6.x
     * MassiveActionsWidget modal views which reference $this->gridid.
     * @var string
     */
    public $gridid;

    /** @var array Action definitions – see class docblock for structure */
    public $aActions = [];

    /** Modal view names available in MassiveActionsWidget/views/modals/ */
    private const MODAL_VIEW_NAMES = [
        'yes-no',
        'empty',
        'yes-no-lg',
        'empty-lg',
        'cancel-apply',
        'cancel-change',
        'cancel-resend',
        'cancel-add',
        'cancel-save',
        'cancel-delete',
        'cancel-export',
    ];

    /** Absolute path to the MassiveActionsWidget modals directory */
    private const MASSIVE_MODALS_DIR = __DIR__ . '/../MassiveActionsWidget/views/modals/';

    public function run(): void
    {
        // Sync lowercase alias so modal views ($this->gridid) work correctly.
        $this->gridid = $this->gridId;

        // 1. Render the floating bar HTML
        $this->render('floating_bar');

        // 2. Render Bootstrap modals for every modal-type action
        $this->renderAllModals();

        // 3. Register the widget JavaScript
        Yii::app()->getClientScript()->registerScriptFile(
            Yii::app()->getConfig('extensionsurl') . 'admin/grid/FloatingActionsWidget/assets/floatingActions.js',
            CClientScript::POS_END
        );

        // 4. Initialise this bar instance after the DOM is ready
        Yii::app()->getClientScript()->registerScript(
            'FloatingActionsWidget-init-' . $this->gridId,
            "$(function () { LS.floatingActions.init('"
                . $this->gridId . "', '"
                . $this->pk . "'); });",
            LSYii_ClientScript::POS_POSTSCRIPT
        );
    }

    // -------------------------------------------------------------------------
    // Modal rendering
    // -------------------------------------------------------------------------

    /**
     * Walk through $aActions and render a modal for every modal-type entry,
     * including sub-items inside 'dropdown' action groups.
     */
    private function renderAllModals(): void
    {
        foreach ($this->aActions as $key => $action) {
            if (!is_array($action) || empty($action['type'])) {
                continue;
            }

            if ($action['type'] === 'action'
                && isset($action['actionType'])
                && $action['actionType'] === 'modal'
            ) {
                $this->renderModal((string) $key, $action);

            } elseif ($action['type'] === 'dropdown' && !empty($action['items'])) {
                foreach ($action['items'] as $subKey => $subAction) {
                    if (isset($subAction['actionType']) && $subAction['actionType'] === 'modal') {
                        $this->renderModal('d' . $key . '_' . $subKey, $subAction);
                    }
                }
            }
        }
    }

    /**
     * Render a single modal view for one action.
     *
     * @param string $keyStr  Unique string key used to build the modal DOM ID
     * @param array  $action  Action definition array
     */
    private function renderModal(string $keyStr, array $action): void
    {
        $modalType = $action['modalType'] ?? '';
        if (!in_array($modalType, self::MODAL_VIEW_NAMES, true)) {
            return;
        }

        $modalDomId      = $this->getModalId($keyStr, $action['action']);
        $modalTitleId    = $modalDomId . '-title';
        $modalDialogSrId = $modalTitleId . '-dialogsr';

        $this->renderFile(
            self::MASSIVE_MODALS_DIR . $modalType . '.php',
            [
                'aAction'                => $action,
                'key'                    => $keyStr,
                'massiveModalDomId'      => $modalDomId,
                'massiveModalTitleId'    => $modalTitleId,
                'massiveModalDialogSrId' => $modalDialogSrId,
                'showSelected'           => $action['showSelected']  ?? 'no',
                'selectedUrl'            => $action['selectedUrl']   ?? '#',
                'largeModalView'         => !empty($action['largeModalView']) ? 'modal-lg' : '',
            ]
        );
    }

    // -------------------------------------------------------------------------
    // Public helpers (called from the view)
    // -------------------------------------------------------------------------

    /**
     * Build a unique modal DOM ID for the given action key and action name.
     * Uses the same pattern as MassiveActionsWidget so the 6.x modal views
     * (which hard-code "massive-actions-modal-{gridid}-{action}-{key}")
     * produce IDs that match the data-modal-id attributes on the bar buttons.
     *
     * @param string $keyStr  Key string (numeric index or 'd{n}_{m}' for dropdowns)
     * @param string $action  Action identifier (e.g. 'delete', 'updateTheme')
     * @return string
     */
    public function getModalId(string $keyStr, string $action): string
    {
        return 'massive-actions-modal-' . $this->gridId . '-' . $action . '-' . $keyStr;
    }
}

