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
<div class="simpleContainer" id="vue-parameter-table-container">
  <lspanelparametertable />
</div>

<?php  
    App()->getClientScript()->registerScript('IntegrationPanel-variables', " 
    window.PanelIntegrationData = ".json_encode($jsData).";
    ", LSYii_ClientScript::POS_BEGIN ); 
?> 
