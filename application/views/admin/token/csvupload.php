<?php echo CHtml::form(array("admin/tokens/sa/import/surveyid/{$iSurveyId}"), 'post', array('id'=>'tokenimport', 'name'=>'tokenimport', 'enctype'=>'multipart/form-data')); ?>

    <ul>
        <li>
            <label for='the_file'><?php eT("Choose the CSV file to upload:"); ?></label>
            <?php echo CHtml::fileField('the_file','',array('required'=>'required','accept'=>'.csv')); ?>
        </li>
        <li>
            <label for='csvcharset'><?php eT("Character set of the file:"); ?></label>
            <?php echo CHtml::dropDownList('csvcharset', 'auto', $aEncodings, array('size' => '1')); ?>
        </li>
        <li>
            <label for='separator'><?php eT("Separator used:"); ?> </label>
            <?php
                $aSeparator = array('auto' => gT("(Autodetect)"), 'comma' => gT("Comma"), 'semicolon' => gT("Semicolon"));
                echo CHtml::dropDownList('separator', returnGlobal('separator'), $aSeparator, array('size' => '1'));
            ?>
        </li>
        <li>
            <label for='filterblankemail'><?php eT("Filter blank email addresses:"); ?></label>
            <?php echo CHtml::checkBox('filterblankemail', true); ?>
        </li>
        <li>
            <label for='allowinvalidemail'><?php eT("Allow invalid email addresses:"); ?></label>
            <?php echo CHtml::checkBox('allowinvalidemail', false); ?>
        </li>
        <li>
            <label for='filterduplicatetoken'><?php eT("Filter duplicate records:"); ?></label>
            <?php echo CHtml::checkBox('filterduplicatetoken', true); ?>
        </li>
        <li id='lifilterduplicatefields'>
            <label for='filterduplicatefields'><?php eT("Duplicates are determined by:"); ?></label>
            <?php
                echo CHtml::listBox('filterduplicatefields', array('firstname', 'lastname', 'email'), $aTokenTableFields, array('multiple' => 'multiple', 'size' => '7'));
            ?>
        </li>
    </ul>
    <p>
        <?php echo CHtml::htmlButton(gT("Upload"),array('type'=>'submit','name'=>'upload','value'=>'import')); ?>
    </p>
</form>
<div class='messagebox ui-corner-all'>
    <div class='header ui-widget-header'><?php eT("CSV input format"); ?></div>
    <p><?php eT("File should be a standard CSV (comma delimited) file with optional double quotes around values (default for OpenOffice and Excel). The first line must contain the field names. The fields can be in any order."); ?></p>
    <span style="font-weight:bold;"><?php eT("Mandatory fields:"); ?></span> firstname, lastname, email<br />
    <span style="font-weight:bold;"><?php eT('Optional fields:'); ?></span> emailstatus, token, language, validfrom, validuntil, attribute_1, attribute_2, attribute_3, usesleft, ... .
</div>
