<script type="text/javascript">
    var sImageURL = '';
    var duplicatelabelcode='<?php eT('Error: You are trying to use duplicate label codes.','js'); ?>';
    var otherisreserved='<?php eT("Error: 'other' is a reserved keyword.",'js'); ?>';
    var quickaddtitle='<?php eT('Quick-add subquestion or answer items','js'); ?>';
</script>

<div class="col-lg-12 list-surveys">
    <h3><?php if ($action == "newlabelset") { eT("Create or import new label set(s)");} else {eT("Edit label set"); } ?></h3>

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
                    <?php echo CHtml::form(array("admin/labels/sa/process"), 'post',array('class'=>'form30 form-horizontal','id'=>'labelsetform','onsubmit'=>"return isEmpty(document.getElementById('label_name'), '".gT("Error: You have to enter a name for this label set.","js")."')")); ?>


                            <!-- Set name -->
                            <div class="form-group">
                                <label  class="col-sm-1 control-label" for='label_name'><?php eT("Set name:"); ?></label>
                                <input type='hidden' name='languageids' id='languageids' value='<?php echo $langids; ?>' />
                                <?php echo CHtml::textField('label_name',isset($lbname)?$lbname:"",array('maxlength'=>100,'size'=>50)); ?>
                            </div>

                            <!-- Languages -->
                            <div class="form-group">
                                <label class="col-sm-1 control-label">
                                    <?php eT("Languages:"); ?>
                                </label>

                                <table>
                                    <tr>
                                        <!-- additional languages -->
                                        <td>
                                            <select multiple='multiple' style='min-width:420px;' size='5' id='additional_languages' name='additional_languages' class="form-control">
                                                <?php foreach ($langidsarray as $langid): ?>
                                                    <option id='<?php echo $langid; ?>' value='<?php echo $langid; ?>'>
                                                        <?php echo getLanguageNameFromCode($langid,false); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>

                                        <!-- arrows -->
                                        <td style="padding: 1em;">
                                            <button class="btn btn-default btn-xs" onclick="DoAdd()" id="AddBtn" type="button"  data-toggle="tooltip" data-placement="top" title="<?php eT("Add"); ?>">
                                                <span class="fa fa-backward"></span>  <?php eT("Add"); ?>
                                            </button>
                                            <br /><br />
                                            <button class="btn btn-default btn-xs" type="button" onclick="DoRemove(1,'<?php eT("You cannot remove this item since you need at least one language in a labelset.", "js"); ?>')" id="RemoveBtn"  data-toggle="tooltip" data-placement="bottom" title="<?php eT("Remove"); ?>" >
                                                <?php eT("Remove"); ?>  <span class="fa fa-forward"></span>
                                            </button>
                                        </td>

                                        <td>
                                            <select size='5' style='min-width:420px;' id='available_languages' name='available_languages'  class="form-control">
                                                <?php foreach (getLanguageDataRestricted(false) as  $langkey=>$langname)
                                                {
                                                    if (in_array($langkey,$langidsarray)==false)  // base languag must not be shown here
                                                    { ?>
                                                        <option id='<?php echo $langkey; ?>' value='<?php echo $langkey; ?>'>
                                                            <?php echo $langname['description']; ?>
                                                        </option>
                                                        <?php }
                                                } ?>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
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
                        <?php echo CHtml::form(array("admin/labels/sa/import"), 'post',array('enctype'=>'multipart/form-data', 'class'=>'form-horizontal','id'=>'importlabels','name'=>"importlabels")); ?>
                                <div class="form-group">
                                    <label  class="col-sm-2 control-label" for='the_file'>
                                        <?php eT("Select label set file (*.lsl):"); ?>
                                    </label>
                                    <input id='the_file' name='the_file' type='file'/>
                                </div>
                                <div class="form-group">
                                    <label  class="col-sm-2 control-label" for='checkforduplicates'>
                                        <?php eT("Don't import if label set already exists:"); ?>
                                    </label>
                                    <input name='checkforduplicates' id='checkforduplicates' type='checkbox' checked='checked' />
                                </div>

                                <div class="form-group">
                                    <label  class="col-sm-2 control-label" for='translinksfields'>
                                        <?php eT("Convert resources links?"); ?>
                                    </label>
                                    <input name='translinksfields' id='translinksfields' type='checkbox' checked='checked' />
                                </div>

                                <div class="form-group">
                                    <div class="col-sm-offset-1">
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
