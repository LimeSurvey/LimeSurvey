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
  <lspanelparametertable :sid="<?php  echo $surveyid; ?>" json-url="<?php echo App()->createUrl('admin/survey/sa/getUrlParamsJson', array('surveyid' => $surveyid))?>" :translate="{
    table: {
        idColumn : '<?php eT('ID');?>',
        actionColumn : '<?php eT('Action');?>',
        parameterColumn : '<?php eT('Parameter');?>',
        questionColumn : '<?php eT('Target question');?>',
        sidColumn : '<?php eT('Survey id');?>',
        qidColumn : '<?php eT('Question id');?>',
        sqidColumn : '<?php eT('Subquestion ID');?>',
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
