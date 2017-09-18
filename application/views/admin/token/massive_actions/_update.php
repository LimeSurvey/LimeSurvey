<?php
/**
 * Edit multiple tokens
 */
 $iSurveyId = Yii::app()->request->getParam('surveyid');
 $attrfieldnames = Survey::model()->findByPk($iSurveyId)->tokenAttributes;
 $aCoreTokenFields = array('validfrom', 'validuntil', 'firstname', 'lastname', 'emailstatus', 'token', 'language', 'sent', 'remindersent', 'completed', 'usesleft' );
?>
<form class="custom-modal-datas">
    <div id='updateTokens' >
        <div class="alert alert-warning" role="alert">
            Work in progress.
            Fields with value "lskeep" will not be updated
        </div>
            <!-- Tabs -->
            <?php if( count($attrfieldnames) > 0 ):?>
                <ul class="nav nav-tabs" id="edit-survey-text-element-language-selection">

                    <!-- Common  -->
                    <li role="presentation" class="active">
                        <a data-toggle="tab" href="#general" aria-expanded="true">
                            <?php eT('General'); ?>
                        </a>
                        </li>

                        <!-- Custom attibutes -->
                        <li role="presentation" class="">
                            <a data-toggle="tab" href="#custom" aria-expanded="false">
                                <?php eT('Additional attributes'); ?>
                            </a>
                        </li>
                    </ul>
            <?php endif; ?>

            <!-- Tabs content-->
            <div class="tab-content">
                <!-- General -->
                <div id="general" class="tab-pane fade in  active">
                    <?php foreach($aCoreTokenFields as $sCoreTokenField): ?>
                        <div class="row">
                            <div class="form-group">
                                <label class="col-sm-2 control-label"  for='<?php echo $sCoreTokenField; ?>'><?php echo $sCoreTokenField;  ?>:</label>
                                <div class="col-sm-8">
                                    <input type="text" class="custom-data" name="<?php echo $sCoreTokenField;?>" id="<?php echo $sCoreTokenField;?>" value="lskeep" />
                                </div>
                            </div>
                        </div>
                    <?php endforeach;?>
                </div>

                <!-- Custom attibutes -->
                <div id="custom" class="tab-pane fade in">
                    <!-- Attributes -->
                    <?php foreach ($attrfieldnames as $attr_name => $attr_description): ?>
                        <div class="row">
                            <div class="form-group">
                                <label class="col-sm-2 control-label"  for='<?php echo $attr_name; ?>'><?php echo $attr_description['description'] . ($attr_description['mandatory'] == 'Y' ? '*' : '') ?>:</label>
                                <div class="col-sm-10">
                                    <input type='text' class="custom-data" size='55' id='<?php echo $attr_name; ?>' name='<?php echo $attr_name; ?>' value='lskeep' />
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <input type="hidden" id="sid" name="sid" class="custom-data" value="<?php echo $_GET['surveyid']; ?>" />

    </div>
</form>
