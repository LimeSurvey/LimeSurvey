<?php
/**
 * Right accordion, integration pannel
 * Use datatables, needs surveysettings.js
 */
    $yii = Yii::app();
    $controller = $yii->getController();
?>

<!-- Datatable translation-data -->
<script type="text/javascript">
    var jsonUrl = "<?php echo App()->createUrl('admin/survey', array('sa' => 'getUrlParamsJson', 'surveyid' => $surveyid))?>";
    var imageUrl = "<?php echo $yii->getConfig("adminimageurl");?>";
    var sAction = "<?php  eT('Action','js');?>";
    var sParameter = "<?php  eT('Parameter','js');?>";
    var sTargetQuestion = "<?php  eT('Target question','js');?>";
    var sURLParameters = "<?php  eT('URL parameters','js');?>";
    var sNoParametersDefined = "<?php  eT('No parameters defined','js');?>";
    var sSureDelete = "<?php  eT('Are you sure you want to delete this URL parameter?','js');?>";
    var sEnterValidParam = "<?php  eT('You have to enter a valid parameter name.','js');?>";
    var sAddParam = "<?php  eT('Add URL parameter','js');?>";
    var sEditParam = "<?php  eT('Edit URL parameter','js');?>";
    var iSurveyId = "<?php  echo $surveyid; ?>";
</script>

<!-- datatable container -->
<div id='panelintegration' class=" tab-pane fade in text-center" >
    <div class="container-center">
        <div class="row">
            <table id="urlparams" class='table dataTable table-striped table-borders' ></table>
            <input type='hidden' id='allurlparams' name='allurlparams' value='' />
        </div>
    </div>
</div>

<!-- Modal box to add a parameter -->
<div data-copy="submitsurveybutton"></div>
<?php $this->renderPartial('survey/subview/addPanelIntegrationParameter_view', array('questions' => $questions)); ?>
