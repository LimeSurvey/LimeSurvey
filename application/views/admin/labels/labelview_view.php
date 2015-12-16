<script type='text/javascript'>
    var duplicatelabelcode='<?php eT('Error: You are trying to use duplicate label codes.','js'); ?>';
    var otherisreserved='<?php eT("Error: 'other' is a reserved keyword.",'js'); ?>';
    var quickaddtitle='<?php eT('Quick-add subquestion or answer items','js'); ?>';
</script>
<div class="col-lg-12 labels">
    <h3 class="pagetitle"><?php eT("Labels") ?></h3>
    <div class="row">

        <!-- Left content -->
        <div class="col-lg-8 content-right text-center">

            <!-- tabs -->
            <ul class="nav nav-tabs">
                <?php  foreach ($lslanguages as $i => $language): ?>
                    <li role="presentation" <?php if($i==0){ echo 'class="active"';}?>>
                        <a data-toggle="tab" href='#neweditlblset<?php echo $i; ?>' >
                            <?php echo getLanguageNameFromCode($language, false); ?>
                        </a>
                    </li>
                <?php endforeach;?>
            </ul>

            <!-- FORM -->
            <?php echo CHtml::form(array("admin/labels/sa/process"), 'post', array('id'=>'mainform')); ?>
                <input type='hidden' name='lid' value='<?php echo $lid ?>' />
                <input type='hidden' name='action' value='modlabelsetanswers' />

                <!-- tab content -->
                <?php $this->renderPartial("./labels/_labelviewtabcontent_view", array('lslanguages'=>$lslanguages, 'results'=>$results)); ?>

            </form>

<div id='quickadd' style='display:none;'>
    <div style='float:left;'>
            <label for='quickaddarea'><?php eT('Enter your labels:') ?></label>
            <br />
            <textarea id='quickaddarea' name='quickaddarea' class='tipme' title='<?php eT('Enter one label per line. You can provide a code by separating code and label text with a semikolon or tab. For multilingual surveys you add the translation(s) on the same line separated with a semikolon or tab.') ?>' rows='30' cols='100' style='width:570px;'></textarea>
            <p class='button-list'>
                <button id='btnqareplace' type='button'><?php eT('Replace') ?></button>
                <button id='btnqainsert' type='button'><?php eT('Add') ?></button>
                <button id='btnqacancel' type='button'><?php eT('Cancel') ?></button>
            </p>
        </div>
</div>




</div>



<div class="col-lg-4">
    <div class="row">
        <div class="col-lg-12 content-right text-center">
            <div class="panel-group" id="accordion" role="tablist" style="margin-top: 20px">
                <div class="panel panel-default" id="up_resmgmt">

                    <div class="panel-heading" role="tab" id="headingOne">
                        <h4 class="panel-title">
                            <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                <?php eT("Uploaded resources management"); ?>
                            </a>
                        </h4>
                    </div>

                    <div id="collapseOne" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
                        <div class="panel-body">
                            <div>
                                <?php echo CHtml::form('third_party/kcfinder/browse.php?language='.sTranslateLangCode2CK(App()->language), 'get', array('id'=>'browselabelresources','class'=>'form30','name'=>'browselabelresources','target'=>'_blank')); ?>
                                    <ul class="list-unstyled">
                                        <li>
                                            <label>&nbsp;</label>
                                            <div class="col-sm-6">
                                                <?php echo CHtml::dropDownList('type', 'files', array('files' => gT('Files'), 'flash' => gT('Flash'), 'images' => gT('Images')), array('class'=>' form-control input-lg ') ); ?>
                                            </div>
                                            <div class="col-sm-5">
                                                <input type='submit' class="btn btn-default" value="<?php eT("Browse uploaded resources") ?>" />
                                            </div>
                                        </li>
                                        <li>
                                            <label>&nbsp;</label>
                                            <input class="btn btn-default" type='button'<?php echo hasResources($lid, 'label') === false ? ' disabled="disabled"' : '' ?>
                                                onclick='window.open("<?php echo $this->createUrl("/admin/export/sa/resources/export/label/lid/$lid"); ?>", "_blank")'
                                                value="<?php eT("Export resources as ZIP archive") ?>"  />
                                        </li>
                                    </ul>
                                    <input type='hidden' name='lid' value='<?php echo $lid; ?>' />
                                </form>
                                <?php echo CHtml::form(array('admin/labels/sa/importlabelresources'), 'post', array('id'=>'importlabelresources',
                                                                                                          'class'=>'form30',
                                                                                                          'name'=>'importlabelresources',
                                                                                                          'enctype'=>'multipart/form-data',
                                                                                                          'onsubmit'=>'return validatefilename(this, "'.gT('Please select a file to import!', 'js').'");')); ?>
                                    <ul class="list-unstyled">
                                        <li>
                                            <label for='the_file'><?php eT("Select ZIP file:") ?></label>
                                            <input id='the_file' name="the_file" type="file" />
                                        </li>
                                        <li>
                                            <label>&nbsp;</label>
                                            <input class="btn btn-default" type='button' value='<?php eT("Import resources ZIP archive") ?>'
                                                <?php echo !function_exists("zip_open") ? "onclick='alert(\"" . gT("zip library not supported by PHP, Import ZIP Disabled", "js") . "\");'" : "onclick='if (validatefilename(this.form,\"" . gT('Please select a file to import!', 'js') . "\")) { this.form.submit();}'" ?>/>
                                        </li>
                                    </ul>
                                    <input type='hidden' name='lid' value='<?php echo $lid; ?>' />
                                    <input type='hidden' name='action' value='importlabelresources' />
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
</div>
</div>




</div>
</div>
