<?php

$attrfieldnames = getTokenFieldsAndNames($surveyid, true);
?>

<div class="card mb-4" id="panel-7">
    <div class="card-header ">
        <div class="">
            <?php eT("Participant control"); ?>
        </div>
    </div>
    <div class="card-body">
        <?php
        $this->widget('ext.AlertWidget.AlertWidget', [
            'text' => gT('Your survey can export associated participant data with each response. Select any additional fields you would like to export.'),
            'type' => 'info',
        ]);
        ?>
        <label for='attribute_select' class="form-label">
            <?php eT("Choose participant fields:"); ?>
        </label>
        <select name='attribute_select[]' multiple size='20' class="form-select" id="attribute_select">
            <option value='first_name' id='first_name'>
                <?php eT("First name"); ?>
            </option>
            <option value='last_name' id='last_name'>
                <?php eT("Last name"); ?>
            </option>
            <option value='email_address' id='email_address'>
                <?php eT("Email address"); ?>
            </option>

            <?php
            foreach ($attrfieldnames as $attr_name => $attr_desc) {
                echo "<option value='$attr_name' id='$attr_name' />" . $attr_desc['description'] . "</option>\n";
            }
            ?>
        </select>
    </div>
</div>
