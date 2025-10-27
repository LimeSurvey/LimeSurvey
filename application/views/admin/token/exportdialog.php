<!-- Token export options -->
<div class='side-body'>
    <h3 aria-level="2"><?php eT("Survey participant export options"); ?></h3>
    <div class="row">
        <div class="col-12 content-right">
            <?php echo CHtml::form(array("admin/tokens/sa/exportdialog/surveyid/$surveyid"), 'post',array('class'=>'form-core settingswidget ','id'=>'bouncesettings','name'=>'frmeditquestion')); ?>
            <div class="row">
                <div class="settings-list col-12 col-lg-6">
                    <!--Survey status -->
                    <div class=" mb-3 control-group" data-name="tokenstatus">
                        <label class="default form-label" for="tokenstatus">
                            <?php eT('Survey status:'); ?>
                        </label>
                        <div class="default controls">
                            <select id="tokenstatus" name="tokenstatus" class="form-select">
                                <option value="0"><?php eT('All participants'); ?></option>
                                <option value="1"><?php eT('Completed'); ?></option>
                                <option value="2"><?php eT('Not completed'); ?></option>
                                <option value="3"><?php eT('Not started'); ?></option>
                                <option value="4"><?php eT('Started but not yet completed'); ?></option>
                            </select>
                        </div>
                    </div>

                    <!--Invitation status -->
                    <div class=" mb-3 control-group" data-name="invitationstatus">
                        <label class="default form-label" for="invitationstatus">
                            <?php eT('Invitation status:'); ?>
                        </label>
                        <div class="default controls">
                            <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', array(
                                'name' => 'invitationstatus',
                                'checkedOption'=> 0 ,
                                'ariaLabel'=>gT('Invitation status'),
                                'selectOptions'=>array(
                                    "0"=>gT("All",'unescaped'),
                                    "1"=>gT("Invited",'unescaped'),
                                    "2"=>gT("Not invited",'unescaped')
                                )
                            ));?>
                        </div>
                    </div>

                    <!--Reminder status -->
                    <div class=" mb-3 control-group" data-name="reminderstatus">
                        <label class="default form-label" for="reminderstatus">
                            <?php eT('Reminder status:'); ?>
                        </label>
                        <div class="default controls">
                            <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', array(
                                'name' => 'reminderstatus',
                                'checkedOption'=> 0 ,
                                'ariaLabel'=>gT('Reminder status'),
                                'selectOptions'=>array(
                                    "0"=>gT("All",'unescaped'),
                                    "1"=>gT("Reminder(s) sent",'unescaped'),
                                    "2"=>gT("No reminder(s) sent",'unescaped')
                                )
                            ));?>
                        </div>
                    </div>

                    <!--Filter by language -->
                    <div class=" mb-3 control-group" data-name="tokenlanguage">
                        <label class="default form-label" for="tokenlanguage">
                            <?php eT('Filter by language:'); ?>
                        </label>
                        <div class="default controls">
                            <select id="tokenlanguage" name="tokenlanguage" class="form-select">
                                <option value="" selected="selected"><?php eT('All'); ?>
                                <option value="de"><?php eT('German'); ?></option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-6">

                    <!--Filter by email address -->
                    <div class=" mb-3 control-group" data-name="filteremail">

                        <label class="default form-label" for="filteremail">
                            <?php eT('Filter by email address:'); ?>
                        </label>
                        <div class="default controls">
                            <input type="text" class="form-control" value="" name="filteremail" id="filteremail" aria-describedby="filteremailhelp" />
                        </div>
                        <?php
                        $this->widget('ext.AlertWidget.AlertWidget', [
                            'text' => gT('Only export entries which contain this string in the email address.'),
                            'type' => 'info',
                            'htmlOptions' => ['class' => 'mt-1' ,'id' => 'filteremailhelp'],
                        ]);
                        ?>

                    </div>

                    <!--Delete exported tokens -->
                    <?php if (Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'delete')) { ?>

                    <div class="mb-3 control-group " data-name="tokendeleteexported">
                        <label class="default form-label" for="tokendeleteexported">
                            <?php eT('Delete exported participants:'); ?>
                        </label>
                        <div class="default controls">
                            <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                'name' => 'tokendeleteexported',
                                'ariaLabel' => gT('Delete exported participants'),
                                'checkedOption' => 0,
                                'selectOptions' => [
                                    '1' => gT('On'),
                                    '0' => gT('Off'),
                                ],
                            ]); ?>
                        </div>
                        <?php
                        $this->widget('ext.AlertWidget.AlertWidget', [
                            'text' => gT('Warning: Deleted participants entries cannot be recovered.'),
                            'type' => 'warning',
                            'htmlOptions' => ['class' => 'mt-1'],
                        ]);
                        ?>
                    </div>
                    <?php } ?>
                    <div class="mb-3 control-group " data-name="maskequations">
                        <label class="default form-label" for="maskequations">
                            <?php eT('Quote equations:'); ?>
                        </label>
                        <div class="default controls">
                            <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                'name' => 'maskequations',
                                'checkedOption' => 1,
                                'ariaLabel' => gT('Quote equations'),
                                'selectOptions' => [
                                    '1' => gT('On'),
                                    '0' => gT('Off'),
                                ],
                            ]); ?>
                        </div>
                        <?php
                        $this->widget('ext.AlertWidget.AlertWidget', [
                            'text' => gT('Important: Quote all content that starts with an equal sign to prevent CSV injections.'),
                            'type' => 'warning',
                            'htmlOptions' => ['class' => 'mt-1'],
                        ]);
                        ?>
                    </div>
                </div>
                <button role="button" class="btn btn-primary btn-block d-none" type="submit" name="submit">
                    <i class="ri-download-fill"></i>
                    <?php eT('Export participants'); ?>
                </button>
            </div>
            </form>
        </div>
    </div>
</div>
</div>