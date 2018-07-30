<!-- Token export options -->
<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <h3><?php eT("Survey participant export options"); ?></h3>
    <div class="row">
        <div class="col-lg-12 content-right">
            <?php echo CHtml::form(array("admin/tokens/sa/exportdialog/surveyid/$surveyid"), 'post',array('class'=>'form-core settingswidget ','id'=>'bouncesettings','name'=>'frmeditquestion')); ?>
                <div class="settings-list">

                    <!--Survey status -->
                    <div class=" form-group control-group" data-name="tokenstatus">
                        <label class="default control-label" for="tokenstatus">
                            <?php eT('Survey status:'); ?>
                        </label>
                        <div class="default controls">
                            <select id="tokenstatus" name="tokenstatus" class="form-control">
                                <option value="0"><?php eT('All tokens'); ?></option>
                                <option value="1"><?php eT('Completed'); ?></option>
                                <option value="2"><?php eT('Not completed'); ?></option>
                                <option value="3"><?php eT('Not started'); ?></option>
                                <option value="4"><?php eT('Started but not yet completed'); ?></option>
                            </select>
                        </div>
                    </div>

                    <!--Invitation status -->
                    <div class=" form-group control-group" data-name="invitationstatus">
                        <label class="default control-label" for="invitationstatus">
                            <?php eT('Invitation status:'); ?>
                        </label>
                        <div class="default controls">
                            <?php $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                                'name' => 'invitationstatus',
                                'value'=> 0 ,
                                'selectOptions'=>array(
                                    "0"=>gT("All",'unescaped'),
                                    "1"=>gT("Invited",'unescaped'),
                                    "2"=>gT("Not invited",'unescaped')
                                )
                            ));?>
                        </div>
                    </div>

                    <!--Reminder status -->
                    <div class=" form-group control-group" data-name="reminderstatus">
                        <label class="default control-label" for="reminderstatus">
                            <?php eT('Reminder status:'); ?>
                        </label>
                        <div class="default controls">
                            <?php $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                                'name' => 'reminderstatus',
                                'value'=> 0 ,
                                'selectOptions'=>array(
                                    "0"=>gT("All",'unescaped'),
                                    "1"=>gT("Reminder(s) sent",'unescaped'),
                                    "2"=>gT("No reminder(s) sent",'unescaped')
                                )
                            ));?>
                        </div>
                    </div>

                    <!--Filter by language -->
                    <div class=" form-group control-group" data-name="tokenlanguage">
                        <label class="default control-label" for="tokenlanguage">
                            <?php eT('Filter by language:'); ?>
                        </label>
                        <div class="default controls">
                            <select id="tokenlanguage" name="tokenlanguage" class="form-control">
                                <option value="" selected="selected"><?php eT('All'); ?>
                                <option value="de"><?php eT('German'); ?></option>
                            </select>
                        </div>
                    </div>

                    <!--Filter by email address -->
                    <div class=" form-group control-group" data-name="filteremail">

                        <label class="default control-label" for="filteremail">
                            <?php eT('Filter by email address:'); ?>
                        </label>
                        <div class="default controls">
                            <input type="text" class="form-control" value="" name="filteremail" id="filteremail" />
                        </div>
                        <div class="alert alert-info controls" role="alert">
                            <?php eT('Only export entries which contain this string in the email address.'); ?>
                        </div>

                    </div>

                    <!--Delete exported tokens -->
                    <?php if (Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'delete')) { ?>

                    <div class="form-group control-group " data-name="tokendeleteexported">
                        <label class="default control-label" for="tokendeleteexported">
                            <?php eT('Delete exported participants:'); ?>
                        </label>
                        <div class="default controls">
                            <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                'name' => 'tokendeleteexported',
                                'id'=>'tokendeleteexported',
                                'value' => 0,
                                'onLabel'=>gT('On'),
                                'offLabel' => gT('Off')));
                            ?>
                        </div>
                        <div class="alert alert-warning controls" role="alert">
                            <?php eT('Warning: Deleted participants entries cannot be recovered.'); ?>
                        </div>
                    </div>
                    <?php } ?>
                </div>
                <div class="buttons control-group hidden"><button class="btn" type="submit" name="submit"><?php eT('Export tokens'); ?></button></div>
            </form>
        </div>
    </div>
</div>


</div>
<?php
App()->getClientScript()->registerScript('ExportDialogViewBSSwitcher', "
LS.renderBootstrapSwitch();
", LSYii_ClientScript::POS_POSTSCRIPT);
?>