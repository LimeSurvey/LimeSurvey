<?php


class SideBarWidget extends CWidget
{
    /** Initializes the widget */
    public function init(): void
    {
        $this->registerClientScript();
    }

    /** Executes the widget
     * @throws CException
     */
    public function run(): void
    {
        $this->renderActions();
    }

    /** Renders the actions for a row in CLSGridView tables
     * @throws CException
     */
    public function renderActions(): void
    {
        $this->render('side_bar', [
            'dropdownItems' => $this->dropdownItems,
            'id' => self::$id
        ]);
    }


    /** Registers required script files */
    public function registerClientScript(): void
    {
        App()->getClientScript()->registerScriptFile(
            App()->getConfig("extensionsurl") . 'admin/grid/GridActionsWidget/assets/action_dropdown.js',
            CClientScript::POS_END
        );
    }
}
