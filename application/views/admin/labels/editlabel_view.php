<?php
/**
 * @var $tgis AdminController
 */
?>

<script type="text/javascript">
    var sImageURL = '';
    var duplicatelabelcode='<?php eT('Error: You are trying to use duplicate label codes.','js'); ?>';
    var otherisreserved='<?php eT("Error: 'other' is a reserved keyword.",'js'); ?>';
    var quickaddtitle='<?php eT('Quick-add subquestion or answer items','js'); ?>';
</script>

<div class="col-lg-12 list-surveys">
    <h3><?php if ($action == "newlabelset") { eT("Create or import new label set(s)");} else {eT("Edit label set"); } ?></h3>
    <?=// DO NOT REMOVE This is for automated testing to validate we see that page
    viewHelper::getViewTestTag('createLabelSets');?>


    <div class="row">
        <div class="col-lg-12 content-right">

            <!-- Tabs -->
            <ul class="nav nav-tabs" id="edit-survey-text-element-language-selection">
                <li role="presentation" class="active">
                    <a data-toggle="tab" href='#neweditlblset0'>
                        <?php echo $tabitem; ?>
                    </a>
                </li>
                <?php if ($action == "newlabelset" && Permission::model()->hasGlobalPermission('labelsets','import')): ?>
                    <li>
                        <a data-toggle="tab"  href='#neweditlblset1'><?php eT("Import label set(s)"); ?></a>
                    </li>
                <?php endif; ?>
            </ul>

            <!-- Tabs content -->
            <div class="tab-content">
                <div id='neweditlblset0' class="tab-pane fade in active">

                    <!-- Form -->
                    <?php echo CHtml::form(array("admin/labels/sa/process"), 'post',array('class'=>'form form30 ','id'=>'labelsetform','onsubmit'=>"return isEmpty(document.getElementById('label_name'), '".gT("Error: You have to enter a name for this label set.","js")."')")); ?>


                            <!-- Set name -->
                            <div class="form-group col-md-6">
                                <label  class="control-label" for='label_name'><?php eT("Set name:"); ?></label>
                                <div class="">
                                <?php echo CHtml::textField('label_name',isset($lbname)?$lbname:"",array('maxlength'=>100,'size'=>50, 'class' => 'form-control')); ?>
                                </div>
                            </div>

                            <!-- Languages -->
                            <div class="form-group col-md-6">
                                <label class=" control-label"><?php eT("Languages:"); ?></label>
                                <div class=""><?php
                                $aAllLanguages=getLanguageDataRestricted (false,'short');
                                if (isset($esrow)) {
                                    unset($aAllLanguages[$esrow['language']]);
                                }
                                Yii::app()->getController()->widget('yiiwheels.widgets.select2.WhSelect2', array(
                                    'asDropDownList' => true,
                                    'htmlOptions'=>array('multiple'=>'multiple','style'=>"width: 80%",'required'=>'required'),
                                    'data' => $aAllLanguages,
                                    'value' => $langidsarray,
                                    'name' => 'languageids',
                                    'pluginOptions' => array(
                                        'placeholder' => gt('Select languages','unescaped'),
                                )));
                                ?>
                                <input type='hidden' name='oldlanguageids' id='oldlanguageids' value='<?php echo $langids; ?>' />
                                </div>
                            </div>


                        <p>
                            <input type='submit' class="hidden" value='<?php if ($action == "newlabelset") {eT("Save");}else {eT("Update");} ?>' />
                            <input type='hidden' name='action' value='<?php if ($action == "newlabelset") {echo "insertlabelset";} else {echo "updateset";} ?>' />

                            <?php if ($action == "editlabelset") { ?>
                                <input type='hidden' name='lid' value='<?php echo $lblid; ?>' />
                            <?php } ?>
                        </p>
                    </form>
                </div>


                <!-- Import -->
                <?php if ($action == "newlabelset" && Permission::model()->hasGlobalPermission('labelsets','import')): ?>
                    <div id='neweditlblset1' class="tab-pane fade in" >
                        <?php echo CHtml::form(array("admin/labels/sa/import"), 'post',array('enctype'=>'multipart/form-data', 'class'=>'form','id'=>'importlabels','name'=>"importlabels")); ?>
                                <div class="form-group">
                                    <label  class="control-label" for='the_file'>
                                    <?php echo gT("Select label set file (*.lsl):").'<br>'.sprintf(gT("(Maximum file size: %01.2f MB)"),getMaximumFileUploadSize()/1024/1024); ?>
                                    </label>
                                    <input id='the_file' name='the_file' type='file'/>
                                </div>
                                <div class="form-group">
                                    <label  class=" control-label" for='checkforduplicates'>
                                        <?php eT("Don't import if label set already exists:"); ?>
                                    </label>
                                    <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                        'name' => 'checkforduplicates',
                                        'id'=>'checkforduplicates',
                                        'value' => 1,
                                        'onLabel'=>gT('On'),
                                        'offLabel' => gT('Off')));
                                    ?>
                                </div>

                                <div class="form-group">
                                    <div class="">
                                        <input type='submit' class='btn btn-default' value='<?php eT("Import label set(s)"); ?>' />
                                        <input type='hidden' name='action' value='importlabels' />
                                    </div>
                                </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
