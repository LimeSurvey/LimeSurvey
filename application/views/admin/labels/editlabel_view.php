<?php
/**
 * @var $tgis AdminController
 */
?>

<script type="text/javascript">
    var sImageURL = '';
    var duplicatelabelcode = '<?php eT('Error: You are trying to use duplicate label codes.', 'js'); ?>';
    var otherisreserved = '<?php eT("Error: 'other' is a reserved keyword.", 'js'); ?>';
    var quickaddtitle = '<?php eT('Quick-add subquestion or answer items', 'js'); ?>';
</script>

<div class="col-12 list-surveys">
    <?= // DO NOT REMOVE This is for automated testing to validate we see that page
    viewHelper::getViewTestTag('createLabelSets') ?>

    <div class="row">
        <div class="col-12 content-right">
            <!-- Tabs -->
            <ul class="nav nav-tabs" id="edit-survey-text-element-language-selection">
                <li class="nav-item">
                    <a class="nav-link active" href='#neweditlblset0' data-bs-toggle="tab">
                        <?php echo $tabitem; ?>
                    </a>
                </li>
                <?php if ($action === "newlabelset" && Permission::model()->hasGlobalPermission('labelsets', 'import')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href='#neweditlblset1' data-bs-toggle="tab">
                            <?php eT("Import label set(s)"); ?>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>


            <!-- Tabs content -->
            <div class="tab-content">
                <div id='neweditlblset0' class="tab-pane fade show active">
                    <!-- Form -->
                    <?php echo CHtml::form(["admin/labels/sa/process"], 'post', ['class' => 'form form30 ', 'id' => 'labelsetform', 'onsubmit' => "return isEmpty(document.getElementById('label_name'), '" . gT("Error: You have to enter a name for this label set.", "js") . "')"]); ?>
                    <!-- Set name -->
                    <div class="row">
                        <div class="mb-3 col-lg-6">
                            <label class="form-label" for='label_name'><?php eT("Set name:"); ?></label>
                            <div class="">
                                <?php echo CHtml::textField('label_name', $lbname ?? "", ['maxlength' => 100, 'size' => 50, 'class' => 'form-control']); ?>
                            </div>
                        </div>

                        <!-- Languages -->
                        <div class="mb-3 col-lg-6">
                            <label class=" form-label"><?php eT("Languages:"); ?></label>
                            <div class="">
                                <?php
                                $aAllLanguages = getLanguageDataRestricted(false, 'short');
                                if (isset($esrow)) {
                                    unset($aAllLanguages[$esrow['language']]);
                                }
                                Yii::app()->getController()->widget('yiiwheels.widgets.select2.WhSelect2',
                                    [
                                        'asDropDownList' => true,
                                        'htmlOptions'    => ['multiple' => 'multiple', 'style' => "width: 80%", 'required' => 'required'],
                                        'data'           => $aAllLanguages,
                                        'value'          => $langidsarray,
                                        'name'           => 'languageids',
                                        'pluginOptions'  => [
                                            'placeholder' => gT('Select languages', 'unescaped'),
                                        ]
                                    ]); ?>
                                <input type='hidden' name='oldlanguageids' id='oldlanguageids' value='<?php echo $langids; ?>'/>
                            </div>
                        </div>
                    </div>


                    <p>
                    <input type='submit' class="d-none" value='<?php if ($action === "newlabelset") {
                            eT("Save");
                        } else {
                            eT("Update");
                        } ?>'/>
                        <input type='hidden' name='action' value='<?php if ($action === "newlabelset") {
                            echo "insertlabelset";
                        } else {
                            echo "updateset";
                        } ?>'/>

                        <?php if ($action === "editlabelset") { ?>
                            <input type='hidden' name='lid' value='<?php echo $lblid; ?>'/>
                        <?php } ?>
                    </p>
                    <?php echo CHtml::endForm() ?>
                </div>
                <!-- Import -->
                <?php if ($action === "newlabelset" && Permission::model()->hasGlobalPermission('labelsets', 'import')): ?>
                    <div id='neweditlblset1' class="tab-pane fade">
                        <?php echo CHtml::form(["admin/labels/sa/import"], 'post', ['enctype' => 'multipart/form-data', 'class' => 'form', 'id' => 'importlabels', 'name' => "importlabels"]); ?>
                        <div class="mb-3 col-6">
                            <label class="form-label" for='the_file'>
                                <?php echo gT("Select label set file (*.lsl):") . '<br>' . sprintf(gT("(Maximum file size: %01.2f MB)"), getMaximumFileUploadSize() / 1024 / 1024); ?>
                            </label>
                            <input id='the_file' class="form-control col" name='the_file' type='file' accept=".lsl"/>
                            <div class="col"></div>
                            <div class="col"></div>
                        </div>
                        <div class="mb-3">
                            <label class=" form-label" for='checkforduplicates'>
                                <?php eT("Don't import if label set already exists:"); ?>
                            </label>
                            <input id="ytcheckforduplicates" name="checkforduplicates" type="hidden" value="0" >
                            <input id="checkforduplicates" name="checkforduplicates" type="checkbox" value="1" checked>
                        </div>

                        <div class="mb-3">
                            <div class="">
                            <input type='submit' class='btn btn-outline-secondary' value='<?php eT("Import label set(s)"); ?>'/>
                                <input type='hidden' name='action' value='importlabels'/>
                            </div>
                        </div>
                        <?php echo CHtml::endForm() ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
