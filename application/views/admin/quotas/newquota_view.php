<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <div class='col-lg-8'>
    <div class="row">
        <div class="col-lg-12 content-right">
            <?php $this->renderPartial('/admin/survey/breadcrumb', array('oSurvey'=>$oSurvey, 'active'=> gT("New quota"))); ?>
            <h3>
                <?php eT("New quota");?>
            </h3>
            <?php echo CHtml::form(array("admin/quotas/sa/insertquota/surveyid/{$iSurveyId}"), 'post', array('class'=>'form-horizontal', 'id'=>'addnewquotaform', 'name'=>'addnewquotaform')); ?>
                <!-- quota name -->
                <div class="form-group">
                    <label class="col-sm-2 control-label" for="quota_name"><?php eT("Quota name:");?></label>
                    <div class="col-sm-5">
                        <input class="form-control" id="quota_name" name="quota_name" type="text" size="30" maxlength="255" />
                    </div>
                </div>

                <!-- quota limit -->
                <div class="form-group">
                    <label class="col-sm-2 control-label" for="quota_limit"><?php eT("Quota limit:");?></label>
                    <div class="col-sm-2">
                        <input class="form-control" id="quota_limit" name="quota_limit" type="number" size="12" maxlength="8" />
                    </div>
                </div>

                <!-- quota actions -->
                <div class="form-group">
                    <label class="col-sm-2 control-label" for="quota_action"><?php eT("Quota action:");?></label>
                    <div class="col-sm-5">
                        <select id="quota_action" name="quota_action" class="form-control">
                            <option value ="1"><?php eT("Terminate survey");?></option>
                            <option value ="2"><?php eT("Terminate survey with warning");?></option>
                        </select>
                    </div>
                </div>

                <!-- -->
                <div class="form-group">
                    <label class="col-sm-2 control-label" for="autoload_url"><?php eT("Autoload URL:");?></label>
                    <div class="col-sm-10">
                        <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                            'name' => 'autoload_url',
                            'id'=>'autoload_url',
                            'value' => 1,
                            'onLabel'=>gT('Yes'),
                            'offLabel' => gT('No')));
                        ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label" for="active"><?php eT("Active:");?></label>
                    <div class="col-sm-10">
                        <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                            'name' => 'active',
                            'id'=>'active',
                            'value' => 1,
                            'onLabel'=>gT('Yes'),
                            'offLabel' => gT('No')));
                        ?>
                    </div>
                </div>
                <!--
                <div class="form-group">
                    <div class="col-sm-10">
                    </div>
                </div>
                -->

                <!-- Language tabs -->
                <ul class="nav nav-tabs">
                    <?php foreach ($langs as $lang): ?>
                        <li role="presentation" <?php if ($lang==$baselang){echo 'class="active"';}?>>
                            <a data-toggle="tab" href="#tabpage_<?php echo $lang ?>">
                                <?php echo getLanguageNameFromCode($lang,false); ?>
                                <?php if ($lang==$baselang) {echo '('.gT("Base language").')';} ;?>
                            </a>
                        </li>
                    <?php endforeach?>
                </ul>
                <div class="tab-content">
                    <?php foreach ($langs as $lang): ?>
                        <div id="tabpage_<?php echo $lang ?>" class="tab-pane fade in <?php if ($lang==$baselang){echo ' active ';}?>>">

                            <!-- Quota message -->
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="quotals_message_<?php echo $lang;?>"><?php eT("Quota message:");?></label>
                                <div class="col-sm-5">
                                    <textarea class="form-control" id="quotals_message_<?php echo $lang;?>" name="quotals_message_<?php echo $lang;?>" cols="60" rows="6"><?php eT("Sorry your responses have exceeded a quota on this survey.");?></textarea>
                                </div>
                            </div>

                            <!-- URL -->
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="quotals_url_<?php echo $lang;?>"><?php eT("URL:");?></label>
                                <div class="col-sm-5">
                                    <input class="form-control" id="quotals_url_<?php echo $lang;?>" name="quotals_url_<?php echo $lang;?>" type="text" size="50" maxlength="255" value="<?php echo $thissurvey['url'];?>" />
                                </div>
                            </div>

                            <!-- URL Description -->
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="quotals_urldescrip_<?php echo $lang;?>"><?php eT("URL description:");?></label>
                                <div class="col-sm-5">
                                    <input class="form-control" id="quotals_urldescrip_<?php echo $lang;?>" name="quotals_urldescrip_<?php echo $lang;?>" type="text" size="50" maxlength="255" value="<?php echo $thissurvey['urldescrip'];?>" />
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <input type="submit" name="submit" class="hidden" />
                <input type="hidden" name="sid" value="<?php echo $surveyid;?>" />
                <input type="hidden" name="action" value="quotas" />
                <input type="hidden" name="subaction" value="insertquota" />
            </form>
        </div>
    </div>
</div></div></div>
