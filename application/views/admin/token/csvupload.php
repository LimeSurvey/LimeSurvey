<?php if (!empty($sError)) { ?>
    <?php echo $sError; ?><br /><br />
<?php } ?>

<form id='tokenimport' enctype='multipart/form-data' action='<?php echo $this->createUrl("admin/tokens/sa/import/surveyid/$iSurveyId"); ?>' method='post'>
    <ul>
        <li>
            <label for='the_file'><?php $clang->eT("Choose the CSV file to upload:"); ?></label>
            <input type='file' id='the_file' name='the_file' size='35' /></li>
        <li>
            <label for='csvcharset'><?php $clang->eT("Character set of the file:"); ?></label>
            <?php echo CHtml::dropDownList('csvcharset', 'auto', $aEncodings, array('size' => '1')); ?>
        </li>
        <li>
            <label for='separator'><?php $clang->eT("Separator used:"); ?> </label>
            <?php
            $aSeparator = array('auto' => $clang->gT("(Autodetect)"), 'comma' => $clang->gT("Comma"), 'semicolon' => $clang->gT("Semicolon"));
            echo CHtml::dropDownList('separator', returnglobal('separator'), $aSeparator, array('size' => '1'));
            ?>
        </li>
        <li>
            <label for='filterblankemail'><?php $clang->eT("Filter blank email addresses:"); ?></label>
            <input type='checkbox' id='filterblankemail' name='filterblankemail' checked='checked' />
        </li>
        <li>
            <label for='filterduplicatetoken'><?php $clang->eT("Filter duplicate records:"); ?></label>
            <input type='checkbox' id='filterduplicatetoken' name='filterduplicatetoken' checked='checked' />
        </li>
        <li id='lifilterduplicatefields'>
            <label for='filterduplicatefields[]'><?php $clang->eT("Duplicates are determined by:"); ?></label>
            <?php
            $aFilterDuplicateFields = array('firstname' => 'firstname', 'lastname' => 'lastname', 'email' => 'email', 'token' => 'token', 'language' => 'language');
            array_merge($aFilterDuplicateFields, GetAttributeFieldNames($iSurveyId));
            echo CHtml::listBox('filterduplicatefields', array('firstname', 'lastname', 'email'), $aFilterDuplicateFields, array('multiple' => 'multiple', 'size' => '5'));
            ?>
        </li>
    </ul>
    <p>
        <input class='submit' type='submit' name='submit' value='<?php $clang->eT("Upload"); ?>' />
        <input type='hidden' name='subaction' value='upload' />
        <input type='hidden' name='sid' value='$iSurveyId' />
    </p>
</form>
<div class='messagebox ui-corner-all'>
    <div class='header ui-widget-header'><?php $clang->eT("CSV input format"); ?></div>
    <p><?php $clang->eT("File should be a standard CSV (comma delimited) file with optional double quotes around values (default for OpenOffice and Excel). The first line must contain the field names. The fields can be in any order."); ?></p>
    <span style="font-weight:bold;"><?php $clang->eT("Mandatory fields:"); ?></span> firstname, lastname, email<br />
    <span style="font-weight:bold;"><?php $clang->eT('Optional fields:'); ?></span> emailstatus, token, language, validfrom, validuntil, attribute_1, attribute_2, attribute_3, usesleft, ... .
</div>
