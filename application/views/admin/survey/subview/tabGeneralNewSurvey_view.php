<div id='general'>
    <ul>
        <li>
        <label for='language' title='<?php eT("This is the base language of your survey and it can't be changed later. You can add more languages after you have created the survey."); ?>'><span class='annotationasterisk'>*</span><?php eT("Base language:"); ?></label>
        <select id='language' name='language'>
                <?php foreach (getLanguageDataRestricted (false, Yii::app()->session['adminlang']) as $langkey2 => $langname) { ?>
                    <option value='<?php echo $langkey2; ?>'
                    <?php if (Yii::app()->getConfig('defaultlang') == $langkey2) { ?>
                         selected='selected'
                    <?php } ?>
                    ><?php echo $langname['description']; ?> </option>
                <?php } ?>


        </select>

        <span class='annotation'> <?php eT("*This setting cannot be changed later!"); ?></span></li>
        <li><label for='surveyls_title'><?php eT("Title"); ?> :</label>
        <input type='text' size='82' maxlength='200' id='surveyls_title' name='surveyls_title' required="required" autofocus="autofocus" /> <span class='annotation'><?php eT("Required"); ?> </span>
        </li>
        <li><label for='description'><?php eT("Description:"); ?> </label>
        <div class='htmleditor'>
        <textarea cols='80' rows='10' id='description' name='description'></textarea>
        </div>
        <?php echo getEditor("survey-desc", "description", "[" . gT("Description:", "js") . "]", '', '', '', $action); ?>
        </li>
        <li><label for='welcome'><?php eT("Welcome message:"); ?> </label>
        <div class='htmleditor'>
        <textarea cols='80' rows='10' id='welcome' name='welcome'></textarea>
        <?php echo getEditor("survey-welc", "welcome", "[" . gT("Welcome message:", "js") . "]", '', '', '', $action) ?>
        </div>
        </li>
        <li><label for='endtext'><?php eT("End message:") ;?> </label>
        <div class='htmleditor'>
        <textarea cols='80' id='endtext' rows='10' name='endtext'></textarea>
        </div>
        <?php echo getEditor("survey-endtext", "endtext", "[" . gT("End message:", "js") . "]", '', '', '', $action) ?>
        </li>

        <li><label for='url'><?php eT("End URL:"); ?></label>
        <input type='text' size='50' id='url' name='url' value='http://' /></li>
        <li><label for='urldescrip'><?php eT("URL description:") ; ?></label>
        <input type='text' maxlength='255' size='50' id='urldescrip' name='urldescrip' value='' /></li>
        <li><label for='dateformat'><?php eT("Date format:") ; ?></label>
        <?php
            echo CHtml::listBox('dateformat',$sDateFormatDefault, $aDateFormatData, array('id'=>'dateformat','size'=>'1'));
        ?>
        </li>
        <li><label for='numberformat'><?php eT("Decimal mark:"); ?></label>
        <?php
            echo CHtml::listBox('numberformat',$sRadixDefault, $aRadixPointData, array('id'=>'numberformat','size'=>'1'));
        ?>
        </li>


        <li><label for='admin'><?php eT("Administrator:") ; ?></label>
        <input type='text' size='50' id='admin' name='admin' value='<?php echo $owner['full_name'] ; ?>' /></li>
        <li><label for='adminemail'><?php eT("Admin email:") ; ?></label>
        <input type='email' size='50' id='adminemail' name='adminemail' value='<?php echo $owner['email'] ; ?>' /></li>
        <li><label for='bounce_email'><?php eT("Bounce Email:") ; ?></label>
        <input type='email' size='50' id='bounce_email' name='bounce_email' value='<?php echo $owner['bounce_email'] ; ?>' /></li>
        <li><label for='faxto'><?php eT("Fax to:") ; ?></label>
        <input type='text' size='50' id='faxto' name='faxto' /></li>
    </ul>
</div>
