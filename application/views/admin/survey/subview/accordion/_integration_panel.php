<?php
/**
* Right accordion, integration panel
* Use datatables, needs surveysettings.js
*/
$yii = Yii::app();
$controller = $yii->getController();
?>
  <!-- Datatable translation-data -->
  <script type="text/javascript">
    var jsonUrl = "<?php echo App()->createUrl('admin/survey/sa/getUrlParamsJson', array('surveyid' => $surveyid))?>";
    var imageUrl = "<?php echo $yii->getConfig("
    adminimageurl ");?>";
    var sProgress = "<?php  eT('Showing _START_ to _END_ of _TOTAL_ entries','js');?>";
    var sAction = "<?php  eT('Action','js');?>";
    var sParameter = "<?php  eT('Parameter','js');?>";
    var sTargetQuestion = "<?php  eT('Target question','js');?>";
    var sURLParameters = "<?php  eT('URL parameters','js');?>";
    var sNoParametersDefined = "<?php  eT('No parameters defined','js');?>";
    var sSearchPrompt = "<?php  eT('Search:','js');?>";
    var sSureDelete = "<?php  eT('Are you sure you want to delete this URL parameter?','js');?>";
    var sEnterValidParam = "<?php  eT('You have to enter a valid parameter name.','js');?>";
    var sAddParam = "<?php  eT('Add URL parameter','js');?>";
    var sEditParam = "<?php  eT('Edit URL parameter','js');?>";
    var iSurveyId = "<?php  echo $surveyid; ?>";
    var questionArray = JSON.parse('<?php echo json_encode($questions); ?>');
  </script>
  <!-- Container -->
  <lspanelparametertable :sid="<?php  echo $surveyid; ?>" json-url="<?php echo App()->createUrl('admin/survey/sa/getUrlParamsJson', array('surveyid' => $surveyid))?>" :translate="{
    table: {
        idColumn : '<?php eT('ID');?>',
        actionColumn : '<?php eT('Action');?>',
        parameterColumn : '<?php eT('Parameter');?>',
        questionColumn : '<?php eT('Target question');?>',
        sidColumn : '<?php eT('Survey id');?>',
        qidColumn : '<?php eT('Question id');?>',
        sqidColumn : '<?php eT('SubQuestion ID');?>',
        addParameter : '<?php eT('Add URL parameter');?>'
    },
    popup: {
        editParam : '<?php eT('Edit URL parameter');?>',
        newParam : '<?php eT('Add URL parameter');?>',
        paramName : '<?php eT('Parameter');?>',
        targetQuestion : '<?php eT('Target question');?>',
        noTargetQuestion : '<?php eT('(No target question)');?>',
        sureToDelete : '<?php eT('Are you sure you want to delete this URL parameter?'); ?>',
        deleteCancel : '<?php eT('No, cancel'); ?>',
        deleteConfirm : '<?php eT('Yes, delete'); ?>',
        save : '<?php eT('Save');?>',
        cancel : '<?php eT('Cancel');?>'
    }
}"></lspanelparametertable>

  <?php /*
<div class="container-fluid">
<div class="row">
<div class="col-sm-12">
<table id="urlparams" class='table dataTable table-striped table-borders' >
<thead>
<tr>
<th></th><th><?php eT('Action');?>
    </th>
    <th>
      <?php eT('Parameter');?>
    </th>
    <th>
      <?php eT('Target question');?>
    </th>
    <th></th>
    <th></th>
    <th></th>
    </tr>
    </thead>
    </table>
    <input type='hidden' id='allurlparams' name='allurlparams' value='' />
    </div>
    </div>
    </div>

    <!-- Modal box to add a parameter -->
    <div data-copy="submitsurveybutton"></div>
    <?php $this->renderPartial('survey/subview/addPanelIntegrationParameter_view', array('questions' => $questions)); ?>
      */?>
