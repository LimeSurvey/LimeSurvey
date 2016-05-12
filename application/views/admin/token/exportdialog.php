<!-- Token export options -->
<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <?php $this->renderPartial('/admin/survey/breadcrumb', array('oSurvey'=>$oSurvey, 'token'=>true, 'active'=>gT("Token export options"))); ?>
    <h3><?php eT("Token export options"); ?></h3>
    <div class="row">
        <div class="col-lg-12 content-right">
            <?php echo CHtml::form(array("admin/tokens/sa/exportdialog/surveyid/$surveyid"), 'post',array('class'=>'form-core settingswidget form-horizontal','id'=>'bouncesettings','name'=>'frmeditquestion')); ?>
                <div class="settings-list">

                    <!--Survey status -->
                    <div class=" form-group control-group setting-select" data-name="tokenstatus">
                        <label class="default control-label col-lg-2 col-sm-5 col-md-7" for="tokenstatus">
                            <?php eT('Survey status:'); ?>
                        </label>
                        <div class="default col-lg-4 col-sm-5 col-md-7 controls">
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
                    <div class=" form-group control-group setting-select" data-name="invitationstatus">
                        <label class="default control-label col-lg-2 col-sm-5 col-md-7" for="invitationstatus">
                            <?php eT('Invitation status:'); ?>
                        </label>
                        <div class="default col-lg-4 col-sm-5 col-md-7 controls">
                            <select id="invitationstatus" name="invitationstatus" class="form-control">
                                <option value="0"><?php eT('All'); ?></option>
                                <option value="1"><?php eT('Invited'); ?></option>
                                <option value="2"><?php eT('Not invited'); ?></option>
                            </select>
                        </div>
                    </div>

                    <!--Reminder status -->
                    <div class=" form-group control-group setting-select" data-name="reminderstatus">
                        <label class="default control-label col-lg-2 col-sm-5 col-md-7" for="reminderstatus">
                            <?php eT('Reminder status:'); ?>
                        </label>
                        <div class="default col-lg-4 col-sm-5 col-md-7 controls">
                            <select id="reminderstatus" name="reminderstatus" class="form-control">
                            <option value="0"><?php eT('All'); ?></option>
                            <option value="1"><?php eT('Reminder(s) sent'); ?></option>
                            <option value="2"><?php eT('No reminder(s) sent'); ?></option>
                            </select>
                        </div>
                    </div>

                    <!--Filter by language -->
                    <div class=" form-group control-group setting-select" data-name="tokenlanguage">
                        <label class="default control-label col-lg-2 col-sm-5 col-md-7" for="tokenlanguage">
                            <?php eT('Filter by language:'); ?>
                        </label>
                        <div class="default col-lg-4 col-sm-5 col-md-7 controls">
                            <select id="tokenlanguage" name="tokenlanguage" class="form-control">
                                <option value="" selected="selected"><?php eT('All'); ?>
                                <option value="de"><?php eT('German'); ?></option>
                            </select>
                        </div>
                    </div>

                    <!--Filter by email address -->
                    <div class=" form-group control-group setting-select" data-name="filteremail">

                        <label class="default control-label col-lg-2 col-sm-5 col-md-7" for="filteremail">
                            <?php eT('Filter by email address:'); ?>
                        </label>
                        <div class="default col-lg-4 col-sm-5 col-md-7 controls">
                            <input size="50" type="text" value="" name="filteremail" id="filteremail" />
                        </div>
                        <div class="alert alert-info col-lg-3 col-sm-2 col-md-12 controls" role="alert">
                            <?php eT('Only export entries which contain this string in email address.'); ?>
                        </div>

                    </div>

                    <!--Delete exported tokens -->
                    <div class=" form-group control-group setting-select" data-name="tokendeleteexported">
                        <label class="default control-label col-lg-2 col-sm-5 col-md-7" for="tokendeleteexported">
                            <?php eT('Delete exported tokens:'); ?>
                        </label>
                        <div class="default col-lg-4 col-sm-5 col-md-7 controls">
                            <input type="checkbox" value="1" name="tokendeleteexported" id="tokendeleteexported" />
                        </div>
                        <div class="alert alert-warning col-lg-3 col-sm-2 col-md-12 controls" role="alert">
                            <?php eT('Warning: Deleted token entries cannot be recovered.'); ?>
                        </div>
                    </div>
                </div>
                <div class="buttons control-group hidden"><button class="btn" type="submit" name="submit"><?php eT('Export tokens'); ?></button></div>
            </form>
        </div>
    </div>
</div>


</div>


    <?php /*
    $this->widget('ext.SettingsWidget.SettingsWidget', array(
        'settings' => $aSettings,
        'action'=>$sAction,
        'form' => true,
        'title' => gT("Token export options"),
        'buttons' => $aButtons,
    ));*/
    ?>
