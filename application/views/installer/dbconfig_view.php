<?php $this->render("/installer/header_view", compact('progressValue', 'clang')); ?>
/*
 * LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 *	$Id: Admin_Controller.php 11256 2011-10-25 13:52:18Z c_schmitz $
 */
<?php echo CHtml::beginForm($this->createUrl('installer/database')); ?>

<div class="container_6">

<?php $this->render('/installer/sidebar_view', compact('progressValue', 'classesForStep', 'clang')); ?>

<div class="grid_4 table">

<p class="title"><?php echo $title; ?></p>

<div style="-moz-border-radius:15px; border-radius:15px; " >
<p><?php echo $descp; ?></p>
<hr />
<div style="color:red; font-size:12px;">
	<?php echo CHtml::errorSummary($model, null, null, array('class' => 'errors')); ?>
</div>
<br /><?php echo $clang->gT("Note: All fields marked with (*) are required."); ?>
<br />


 <fieldset class="content-table">
  <legend class="content-table-heading"><?php echo $clang->gT("Database configuration"); ?></legend>
  <table style="width: 672px; font-size:14px;">
   <tr>
    <td style="width: 428px;">
	 <b><?php echo CHtml::activeLabelEx($model, 'dbtype', array('label' => $clang->gT("Database type"))); ?></b><br />
     <div class="description-field"><?php echo $clang->gT("This is the database type."); ?> </div>
    </td>
    <td style="width: 224px;" align="right">
	 <?php echo CHtml::activeDropDownList($model, 'dbtype', $model->supported_db_types, array('style' => 'width: 147px')); ?>
    </td>
    </tr>
    <tr>
     <td style="width: 428px;">
	 <b><?php echo CHtml::activeLabelEx($model, 'dblocation', array('label' => $clang->gT("Database location"))); ?></b><br />
      <div class="description-field"><?php echo $clang->gT('Set this to the IP/net location of your database server. In most cases "localhost" will work.'); ?> </div>
     </td>
     <td style="width: 224px;" align="right"><?php echo CHtml::activeTextField($model,'dblocation', array('value' => 'localhost')) ?></td>
    </tr>
    <tr>
     <td style="width: 428px;">
	 <b><?php echo CHtml::activeLabelEx($model, 'dbname', array('label' => $clang->gT("Database name"))); ?></b><br />
      <div class="description-field"><?php echo $clang->gT("If you provide an existing database name make sure the database does not contain old tables of LimeSurvey."); ?></div>
     </td>
     <td style="width: 224px;" align="right"><?php echo CHtml::activeTextField($model,'dbname') ?></td>
    </tr>
    <tr>
     <td style="width: 428px;">
	 <b><?php echo CHtml::activeLabelEx($model, 'dbuser', array('label' => $clang->gT("Database user"))); ?></b><br />
      <div class="description-field"><?php echo $clang->gT('Your database server user name. In most cases "root" will work.'); ?></div>
    </td>
    <td style="width: 224px;" align="right"><?php echo CHtml::activeTextField($model,'dbuser') ?></td>
   </tr>
   <tr>
    <td style="width: 428px;">
	 <b><?php echo CHtml::activeLabelEx($model, 'dbpwd', array('label' => $clang->gT("Database password"))); ?></b><br />
     <div class="description-field"><?php echo $clang->gT("Your database server password."); ?></div>
    </td>
    <td style="width: 224px;" align="right"><?php echo CHtml::activePasswordField($model,'dbpwd') ?></td>
   </tr>
   <tr>
    <td style="width: 428px;">
	 <b><?php echo CHtml::activeLabelEx($model, 'dbprefix', array('label' => $clang->gT("Database prefix"))); ?></b><br />
     <div class="description-field"><?php echo $clang->gT('If your database is shared, recommended prefix is "lime_" else you can leave this setting blank.'); ?></div>
    </td>
    <td style="width: 224px;" align="right"><?php echo CHtml::activeTextField($model,'dbprefix', array('value' => 'lime_')) ?></td>
   </tr>
   <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
   </tr>
  </table>
 </fieldset>

</div>
</div>
</div>

<div class="container_6">
<div class="grid_2">&nbsp;</div>
<div class="grid_4 demo">
 <br/>
 <table style="width: 694px; background: #ffffff;">
  <tbody>
   <tr>
    <td align="left" style="width: 33%;"><input class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" type="button" value="<?php echo $clang->gT("Previous"); ?>" onclick="javascript: window.open('<?php echo $this->createUrl("installer/precheck"); ?>', '_top')" /></td>
    <td align="center" style="width: 34%;"></td>
    <td align="right" style="width: 33%;"><?php echo CHtml::submitButton($clang->gT("Next"), array('class' => 'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only')); ?></td>
   </tr>
  </tbody>
 </table>
</div>
</div>

<?php echo CHtml::endForm(); ?>

<?php $this->render("/installer/footer_view"); ?>
