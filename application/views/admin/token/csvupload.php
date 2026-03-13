<?php
/**
 * Import tokens from CSV file
 *
 */
?>

<div class='side-body'>
    <h3 aria-level="1"><?php eT("Import survey participants from CSV file"); ?></h3>

    <div class="row">
        <div class="col-12 content-right">
            <?php echo CHtml::form(["admin/tokens/sa/import/surveyid/{$iSurveyId}"],
                'post',
                ['id' => 'tokenimport', 'name' => 'tokenimport', 'class' => '', 'enctype' => 'multipart/form-data']); ?>

            <!-- Choose the CSV file to upload -->
            <div class="mb-3">
                <label class=" form-label" for='the_file'><?php eT("Choose the CSV file to upload:"); ?></label>
                <?php echo CHtml::fileField('the_file', '', ['required' => 'required', 'accept' => '.csv', 'class' => 'form-control']); ?>
            </div>

            <!-- "Character set of the file -->
            <div class="mb-3">
                <label class=" form-label" for='csvcharset'><?php eT("Character set of the file:"); ?></label>
                <div class="">
                    <?php
                    echo CHtml::dropDownList('csvcharset', $thischaracterset, $aEncodings, ['size' => '1', 'class' => 'form-select']);
                    ?>
                </div>
            </div>

            <!-- Separator used -->
            <div class="mb-3">
                <label class=" form-label" for='separator'><?php eT("Separator used:"); ?> </label>
                <div class="">
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'separator',
                        'checkedOption'         => 'auto',
                        'ariaLabel'    => gT("Separator used:"),
                        'selectOptions' => [
                            "auto"      => gT("Automatic", 'unescaped'),
                            "comma"     => gT("Comma", 'unescaped'),
                            "semicolon" => gT("Semicolon", 'unescaped')
                        ]
                    ]); ?>
                </div>
            </div>

            <!-- Filter blank email addresses -->
            <div class="mb-3">
                <label class="form-label" for='filterblankemail'><?php eT("Filter blank email addresses:"); ?></label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'filterblankemail',
                        'ariaLabel'    => gT("Filter blank email addresses:"),
                        'checkedOption' => '1',
                        'selectOptions' => [
                            "1" => gT('On'),
                            "0" => gT('Off')
                        ]
                    ]); ?>
                </div>
            </div>

            <!-- Allow invalid email addresses -->
            <div class="mb-3">
                <label class=" form-label" for='allowinvalidemail'><?php eT("Allow invalid email addresses:"); ?></label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'allowinvalidemail',
                        'checkedOption' => '0',
                        'ariaLabel'    => gT("Allow invalid email addresses:"),
                        'selectOptions' => [
                            "1" => gT('On'),
                            "0" => gT('Off')
                        ]
                    ]); ?>
                </div>
            </div>

            <!-- show invalid attributes -->
            <div class="mb-3">
                <label class=" form-label" for='showwarningtoken'><?php eT("Display attribute warnings:"); ?></label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'showwarningtoken',
                        'checkedOption' => '0',
                        'ariaLabel'    => gT("Display attribute warnings:"),
                        'selectOptions' => [
                            "1" => gT('On'),
                            "0" => gT('Off')
                        ]
                    ]); ?>
                </div>
            </div>

            <!-- Filter duplicate records -->
            <div class="mb-3">
                <label class=" form-label" for='filterduplicatetoken'><?php eT("Filter duplicate records:"); ?></label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name'          => 'filterduplicatetoken',
                        'checkedOption' => '1',
                        'ariaLabel'    => gT("Filter duplicate records:"),
                        'selectOptions' => [
                            "1" => gT('On'),
                            "0" => gT('Off')
                        ]
                    ]); ?>
                </div>
                <?php
                $this->widget('ext.AlertWidget.AlertWidget', [
                    'text' => gT("The access code field is always checked for duplicates."),
                    'type' => 'info',
                    'htmlOptions' => ['class' => 'mt-1'],
                ]);
                ?>
            </div>

            <!-- Duplicates are determined by -->
            <div class="mb-3" id='lifilterduplicatefields'>
                <label class=" form-label" for='filterduplicatefields'><?php eT("Duplicates are determined by:"); ?></label>
                <div class="">
                    <?php
                    unset($aTokenTableFields['token']); // token are already duplicate forbidden mantis #14334, remove it
                    echo CHtml::listBox('filterduplicatefields',
                        ['firstname', 'lastname', 'email'],
                        $aTokenTableFields,
                        ['multiple' => 'multiple', 'size' => '7', 'class' => 'form-control']);
                    ?>
                </div>
            </div>

            <!-- Buttons -->
            <div class="mb-3">
                <?php $this->widget(
                    'ext.ButtonWidget.ButtonWidget',
                    [
                        'name' => 'upload',
                        'value' => 'import',
                        'text' => gT('Upload'),
                        'icon' => 'icon-import',
                        'htmlOptions' => [
                            'class' => 'btn btn-primary',
                            'type' => 'submit'
                        ]
                    ]
                ); ?>
            </div>
            </form>

            <!-- Infos -->
            <?php
            $message = '<div><strong>' . gT("CSV input format") . '</strong><br/>' .
                '<p>' . gT("File should be a standard CSV (comma delimited) file with optional double quotes around values (default for most spreadsheet tools). The first line must contain the field names. The fields can be in any order.") . '</p>' .
                '<span class="fw-bold">' . gT("Mandatory fields:") . '</span> firstname, lastname, email<br/>' .
                '<span class="fw-bold">' . gT('Optional fields:') .
                '</span> emailstatus, token, language, validfrom, validuntil, attribute_1, attribute_2, attribute_3, usesleft, ... .</div>';
            $this->widget('ext.AlertWidget.AlertWidget', [
                'text' => $message,
                'type' => 'info',
            ]);
            ?>
        </div>
    </div>
</div>