<?php
/**
 * @var $this AdminController
 *
 * Right accordion, integration panel
 * Use datatables, needs surveysettings.js
 */
$yii = Yii::app();
$controller = $yii->getController();
// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveyPanelIntegration');

?>
<!-- Datatable translation-data -->
<!-- Container -->
<div id='panelintegration' class="tab-pane fade show active">
    <div class="row table-responsive">
        <table id="urlparams" class='table dataTable table-hover table-borders'>
            <thead>
            <tr>
                <th></th>
                <th><?php eT('Action'); ?></th>
                <th><?php eT('Parameter'); ?></th>
                <th><?php eT('Target question'); ?></th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
            </thead>
        </table>
        <input type='hidden' id='allurlparams' name='allurlparams' value=''/>
    </div>
</div>

<?php
App()->getClientScript()->registerScript(
    'IntegrationPanel-variables',
    "window.PanelIntegrationData = " . json_encode($jsData) . ";
     window.sEnterValidParam = '" . gT('You have to enter a valid parameter name.', 'js') . "';",
    LSYii_ClientScript::POS_BEGIN
);
?>

<!-- Modal box to add a parameter -->
<!--div data-copy="submitsurveybutton"></div-->
<?php $this->renderPartial('addPanelIntegrationParameter_view', ['questions' => $questions]); ?>
