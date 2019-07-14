<?php
/**
 * @var $aTabTitles
 * @var $aTabContents
 * @var $has_permissions
 * @var $surveyid
 * @var $surveyls_language
 */
if(isset($data)){
    extract($data);
}
 $count=0;
 if(isset($scripts))
    echo $scripts;

?>
<div class="container-center">
    <?php if($oSurvey->isNewRecord) { ?>
        <div class="row">
            <div class="col-sm-12 col-md-6">
                <!-- Base language -->
                <div class="form-group">
                    <label class=" control-label" ><?php  eT("Base language:") ; ?></label>
                    <div class="" style="padding-top: 7px;">
                        <?php if($oSurvey->isNewRecord):?>
                        <?php $this->widget('yiiwheels.widgets.select2.WhSelect2', array(
                            'asDropDownList' => true,
                            'data' => getLanguageDataRestricted (false,'short'),
                            'value' => $oSurvey->language,
                            'name' => 'language',
                            'pluginOptions' => array()
                        ));?>
                        <?php else:?>
                        <?php echo getLanguageNameFromCode($oSurvey->language,false); ?>
                        <?php endif;?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="createsample" class=" control-label"><?php eT("Create example question group and question?") ?></label>
                    <!--<input type="checkbox" name="createsample" id="createsample" />-->
                    <div class="">
                        <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                'name' => 'createsample',
                                'value' => 0,
                                'onLabel'=>gT('On'),
                                'offLabel'=>gT('Off')
                            )); ?>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
    <div id="advancedTextEditor"><lsnexttexteditor/></div>
    <div id="textEditLoader" class="ls-flex ls-flex-column align-content-center align-items-center">
        <div class="ls-flex align-content-center align-items-center">
            <div class="loader-advancedquestionsettings text-center">
                <div class="contain-pulse animate-pulse">
                    <div class="square"></div>
                    <div class="square"></div>
                    <div class="square"></div>
                    <div class="square"></div>
                </div>
            </div>
        </div>
    </div>
</div>

