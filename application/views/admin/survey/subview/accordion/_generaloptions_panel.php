<?php
/**
 * General options
 */
?>
<!-- General options -->

<?php if($action=='editsurveysettings'):?>
    <?php
        $yii = Yii::app();
        $controller = $yii->getController();
        $sConfirmLanguage="$(document).on('submit','#addnewsurvey',function(){\n"
                        . "  if(!UpdateLanguageIDs(mylangs,'".gT("All questions, answers, etc for removed languages will be lost. Are you sure?", "js")."')){\n"
                        . "    return false;\n"
                        . "  }\n"
                        . "});\n";
        Yii::app()->getClientScript()->registerScript('confirmLanguage',$sConfirmLanguage,CClientScript::POS_BEGIN);
    ?>

    <!-- Base language -->
    <div class="form-group">
        <label class="col-sm-3 control-label" ><?php  eT("Base language:") ; ?></label>
        <div class="col-sm-9" style="padding-top: 7px;">
            <?php echo getLanguageNameFromCode($esrow['language'],false) ?>
        </div>
    </div>

    <!-- Additional Languages -->
    <div class="form-group">
        <label class="col-sm-3 text-right"  for='additional_languages'><?php  eT("Additional Languages"); ?>:</label>
        <div class="col-sm-9">
            <table>
                <tr>
                    <td style='text-align:left'>
                        <select class="form-control " style='' size='5' id='additional_languages' name='additional_languages'>
                            <?php $jsX=0;
                                $jsRemLang ="<script type=\"text/javascript\">
                                var mylangs = new Array();
                                standardtemplaterooturl='".$yii->getConfig('standardtemplaterooturl')."';
                                templaterooturl='".$yii->getConfig('usertemplaterooturl')."';\n";

                                foreach (Survey::model()->findByPk($surveyid)->additionalLanguages as $langname) {
                                    if ($langname && $langname != $esrow['language']) {
                                        $jsRemLang .=" mylangs[$jsX] = \"$langname\"\n"; ?>
                                    <option id='<?php echo $langname; ?>' value='<?php echo $langname; ?>'><?php echo getLanguageNameFromCode($langname,false); ?>
                                    </option>
                                    <?php $jsX++; ?>
                                    <?php }
                                }
                                $jsRemLang .= "</script>";
                            ?>

                        </select>
                        <input type='hidden' name='languageids' id='languageids' value="<?php echo $esrow['additional_languages'];?>" />
                    </td>

                    <!-- Arrows -->
                    <td style='text-align:left'>
                        <div class="col-sm-4">
                            <button class="btn btn-default btn-xs" onclick="DoAdd()" id="AddBtn" type="button"  data-toggle="tooltip" data-placement="top" title="<?php eT("Add"); ?>">
                                <span class="fa fa-backward"></span>  <?php eT("Add"); ?>
                            </button>
                            <br /><br />
                            <button class="btn btn-default btn-xs" type="button" onclick="DoRemove(0,'')" id="RemoveBtn"  data-toggle="tooltip" data-placement="bottom" title="<?php eT("Remove"); ?>" >
                                <?php eT("Remove"); ?>  <span class="fa fa-forward"></span>
                            </button>
                        </div>
                    </td>


                    <td style='text-align:left'>
                        <select class="form-control input-xlarge" size='5'  id='available_languages' name='available_languages'>
                            <?php $tempLang=Survey::model()->findByPk($surveyid)->additionalLanguages;
                                foreach (getLanguageDataRestricted (false, Yii::app()->session['adminlang']) as $langkey2 => $langname) {
                                    if ($langkey2 != $esrow['language'] && in_array($langkey2, $tempLang) == false) {  // base languag must not be shown here ?>
                                    <option id='<?php echo $langkey2 ; ?>' value='<?php echo $langkey2; ?>'>
                                    <?php echo $langname['description']; ?></option>
                                    <?php }
                            } ?>
                        </select>
                    </td>
                </tr>
            </table>
            <br/>
        </div>
    </div>

    <!-- Survey owner -->
    <div class="form-group">
        <label class="col-sm-3 control-label"  for='owner_id'><?php  eT("Survey owner:"); ?></label>
        <div class="col-sm-9">
            <select class="form-control" id='owner_id' name='owner_id'>
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo $user['uid']; ?>" <?php if ($user['uid'] === $esrow['owner_id']) { echo "selected"; } ?>><?php echo $user['username']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Administrator -->
    <div class="form-group">
        <label class="col-sm-3 control-label"  for='admin'><?php  eT("Administrator:"); ?></label>
        <div class="col-sm-9">
            <input class="form-control" type='text' size='50' id='admin' name='admin' value="<?php echo htmlspecialchars($esrow['admin']); ?>" />
        </div>
    </div>

    <!-- Admin email -->
    <div class="form-group">
        <label class="col-sm-3 control-label"  for='adminemail'><?php  eT("Admin email:"); ?></label>
        <div class="col-sm-9">
            <input class="form-control" type='email' size='50' id='adminemail' name='adminemail' value="<?php echo htmlspecialchars($esrow['adminemail']); ?>" />
        </div>
    </div>

    <!-- Bounce email -->
    <div class="form-group">
        <label class="col-sm-3 control-label"  for='bounce_email'><?php  eT("Bounce email:"); ?></label>
        <div class="col-sm-9">
            <input class="form-control" type='email' size='50' id='bounce_email' name='bounce_email' value="<?php echo htmlspecialchars($esrow['bounce_email']); ?>" />
        </div>
    </div>

    <!-- Fax to -->
    <div class="form-group">
        <label class="col-sm-3 control-label"  for='faxto'><?php  eT("Fax to:"); ?></label>
        <div class="col-sm-9">
            <input class="form-control" type='text' size='50' id='faxto' name='faxto' value="<?php echo htmlspecialchars($esrow['faxto']); ?>" />
        </div>
    </div>

<?php else: ?>
    <!-- End URL -->
    <div class="form-group">
        <label class="col-sm-3 control-label" for='url'><?php  eT("End URL:"); ?></label>
        <div class="col-sm-9">
            <input type='text' class="form-control"  id='url' name='url' placeholder="http://example.com" />
        </div>
    </div>

    <!-- URL description -->
    <div class="form-group">
        <label class="col-sm-3 control-label" for='urldescrip'><?php  eT("URL description:") ; ?></label>
        <div class="col-sm-9">
            <input type='text' maxlength='255' size='50' id='urldescrip' name='urldescrip' value=''  class="form-control"  placeholder="<?php eT('Some description text');?>" />
        </div>
    </div>

    <!-- Date format -->
    <div class="form-group">
        <label class="col-sm-3 control-label" for='dateformat'><?php  eT("Date format:") ; ?></label>
        <div class="col-sm-9">
            <?php echo CHtml::listBox('dateformat',$sDateFormatDefault, $aDateFormatData, array('id'=>'dateformat','size'=>'1', 'class'=>'form-control')); ?>
        </div>
    </div>

    <!-- Decimal mark -->
    <div class="form-group">
        <label class="col-sm-3 control-label" for='numberformat'><?php  eT("Decimal mark:"); ?></label>
        <div class="col-sm-9">
            <?php echo CHtml::listBox('numberformat',$sRadixDefault, $aRadixPointData, array('id'=>'numberformat','size'=>'1', 'class'=>'form-control')); ?>
        </div>
    </div>

    <!-- Administrator -->
    <div class="form-group">
        <label class="col-sm-3 control-label" for='admin'><?php  eT("Administrator:") ; ?></label>
        <div class="col-sm-9">
            <input type='text' size='50' id='admin' name='admin'   class="form-control"  value='<?php echo $owner['full_name'] ; ?>' />
        </div>
    </div>

    <!-- Admin email -->
    <div class="form-group">
        <label class="col-sm-3 control-label" for='adminemail'><?php  eT("Admin email:") ; ?></label>
        <div class="col-sm-9">
            <input type='email' size='30'   class="form-control"   id='adminemail' name='adminemail' value='<?php echo $owner['email'] ; ?>' />
        </div>
    </div>

    <!-- Bounce Email -->
    <div class="form-group">
        <label class="col-sm-3 control-label" for='bounce_email'><?php  eT("Bounce Email:") ; ?></label>
        <div class="col-sm-9">
            <input type='email' size='50'  class="form-control"  id='bounce_email' name='bounce_email' value='<?php echo $owner['bounce_email'] ; ?>' />
        </div>
    </div>

    <!-- Fax to -->
    <div class="form-group">
        <label class="col-sm-3 control-label" for='faxto'><?php  eT("Fax to:") ; ?></label>
        <div class="col-sm-9">
            <input type='text' size='50' id='faxto' name='faxto'  class="form-control" />
        </div>
    </div>
<?php endif;?>
