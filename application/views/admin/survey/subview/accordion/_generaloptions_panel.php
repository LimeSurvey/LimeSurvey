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
        $sConfirmLanguage="$(document).on('submit','#globalsetting',function(){\n"
                        . "  if(!ConfirmLanguageChange('".gT("All questions, answers, etc for removed languages will be lost. Are you sure?", "js")."')){\n"
                        . "    return false;\n"
                        . "  }\n"
                        . "});
                            function ConfirmLanguageChange(confirmtxt)
                        {
                           if ($('#oldlanguages').val().trim()=='')
                           {
                                mylangs= []
                           }
                           else{
                                mylangs=$('#oldlanguages').val().split(' ');
                           }
                            if (checkSelect2Languages(mylangs))
                                {
                                return true;
                            } else
                                {
                                return confirm(confirmtxt);
                            }
                        };
    function checkSelect2Languages(mylangs)
    {
        newLanguages=$('#additional_languages').val();
        for (x = 0; x < mylangs.length; x++)
        {
            if ($.inArray(mylangs[x],newLanguages)==-1)
            {
                return false;
            }
        }
        return true;
    };";
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
        <label class="col-sm-3 control-label"  for='additional_languages'><?php  eT("Additional Languages"); ?>:</label>
        <div class="col-sm-9">
            <?php
            $aAllLanguages=getLanguageDataRestricted (false,'short');
            unset($aAllLanguages[$esrow['language']]);
            Yii::app()->getController()->widget('yiiwheels.widgets.select2.WhSelect2', array(
                'asDropDownList' => true,
                'htmlOptions'=>array('multiple'=>'multiple','style'=>"width: 100%"),
                'data' => $aAllLanguages,
                'value' => Survey::model()->findByPk($surveyid)->additionalLanguages,
                'name' => 'additional_languages',
                'pluginOptions' => array(
                    'placeholder' => gt('Select additional languages','unescaped'),
            )));
            ?>
            <input type='hidden' name='oldlanguages' id='oldlanguages' value='<?php echo implode(' ',Survey::model()->findByPk($surveyid)->additionalLanguages); ?>'>
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
