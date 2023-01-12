<?php
/**
 * Import tokens from CSV file
 *
 */
?>

<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <h3><?php eT("Import survey participants from CSV file"); ?></h3>

    <div class="row">
        <div class="col-12 content-right">
            <?php echo CHtml::form(["admin/tokens/sa/import/surveyid/{$iSurveyId}"],
                'post',
                ['id' => 'tokenimport', 'name' => 'tokenimport', 'class' => '', 'enctype' => 'multipart/form-data']); ?>

            <!-- Choose the CSV file to upload -->
            <div class="mb-3">
                <label class=" form-label" for='the_file'><?php eT("Choose the CSV file to upload:"); ?></label>
                <div class="">
                    <?php echo CHtml::fileField('the_file', '', ['required' => 'required', 'accept' => '.csv']); ?>
                </div>
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
                        'selectOptions' => [
                            "1" => gT('On'),
                            "0" => gT('Off')
                        ]
                    ]); ?>
                </div>
                <div class="help-block"><?php eT("The access code field is always checked for duplicates."); ?></div>
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
                <div class="">
                    <?php echo CHtml::htmlButton(gT("Upload"),
                        ['type' => 'submit', 'name' => 'upload', 'value' => 'import', 'class' => 'btn btn-outline-secondary']); ?>
                </div>
            </div>
            </form>

            <!-- Infos -->
            <div class="alert alert-info" role="alert">
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <strong><?php eT("CSV input format"); ?></strong><br/>
                <p><?php eT("File should be a standard CSV (comma delimited) file with optional double quotes around values (default for most spreadsheet tools). The first line must contain the field names. The fields can be in any order."); ?></p>
                <span style="font-weight:bold;"><?php eT("Mandatory fields:"); ?></span> firstname, lastname, email<br/>
                <span style="font-weight:bold;"><?php eT('Optional fields:'); ?></span> emailstatus, token, language, validfrom, validuntil,
                attribute_1, attribute_2, attribute_3, usesleft, ... .
            </div>
        </div>
    </div>
</div>
<?php
App()->getClientScript()->registerScript('CSVUploadViewBSSwitcher',
    "
LS.renderBootstrapSwitch();
",
    LSYii_ClientScript::POS_POSTSCRIPT);
?>
