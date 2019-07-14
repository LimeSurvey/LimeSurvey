<?php
/**
 * Import tokens from CSV file
 *
 */
?>

<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <h3><?php eT("Import survey participants from CSV file"); ?></h3>

    <div class="row">
        <div class="col-lg-12 content-right">
            <?php echo CHtml::form(array("admin/tokens/sa/import/surveyid/{$iSurveyId}"), 'post', array('id'=>'tokenimport', 'name'=>'tokenimport', 'class'=>'', 'enctype'=>'multipart/form-data')); ?>

                <!-- Choose the CSV file to upload -->
                <div class="form-group">
                    <label class=" control-label" for='the_file'><?php eT("Choose the CSV file to upload:"); ?></label>
                    <div class="">
                        <?php echo CHtml::fileField('the_file','',array('required'=>'required','accept'=>'.csv')); ?>
                    </div>
                </div>

                <!-- "Character set of the file -->
                <div class="form-group">
                    <label class=" control-label" for='csvcharset'><?php eT("Character set of the file:"); ?></label>
                    <div class="">
                        <?php
                            echo CHtml::dropDownList('csvcharset', $thischaracterset, $aEncodings, array('size' => '1', 'class'=>'form-control'));
                        ?>
                    </div>
                </div>

                <!-- Separator used -->
                <div class="form-group">
                    <label class=" control-label" for='separator'><?php eT("Separator used:"); ?> </label>
                    <div class="">
                        <?php $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                            'name' => 'separator',
                            'value'=> 'auto',
                            'selectOptions'=>array(
                                "auto"=>gT("Automatic",'unescaped'),
                                "comma"=>gT("Comma",'unescaped'),
                                "semicolon"=>gT("Semicolon",'unescaped')
                            )
                        ));?>
                    </div>
                </div>

                <!-- Filter blank email addresses -->
                <div class="form-group">
                    <label class=" control-label" for='filterblankemail'><?php eT("Filter blank email addresses:"); ?></label>
                    <div class="">
                            <?php
                            $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                'name' => "filterblankemail",
                                'id'=>"filterblankemail",
                                'value' => '1',
                                'onLabel'=>gT('On'),
                                'offLabel' => gT('Off')));
                            ?>
                    </div>
                </div>

                <!-- Allow invalid email addresses -->
                <div class="form-group">
                    <label class=" control-label" for='allowinvalidemail'><?php eT("Allow invalid email addresses:"); ?></label>
                    <div class="">
                        <?php
                        $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                            'name' => "allowinvalidemail",
                            'id'=>"allowinvalidemail",
                            'value' => '0',
                            'onLabel'=>gT('On'),
                            'offLabel' => gT('Off')));
                        ?>
                    </div>
                </div>

                <!-- show invalid attributes -->
                <div class="form-group">
                            <label class=" control-label" for='showwarningtoken'><?php eT("Display attribute warnings:"); ?></label>
                    <div class="">
                        <?php
                        $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                            'name' => "showwarningtoken",
                            'id'=>"showwarningtoken",
                            'value' => '0',
                            'onLabel'=>gT('On'),
                            'offLabel' => gT('Off')));
                        ?>
                    </div>
                </div>

                <!-- Filter duplicate records -->
                <div class="form-group">
                    <label class=" control-label" for='filterduplicatetoken'><?php eT("Filter duplicate records:"); ?></label>
                    <div class="">
                    <?php
                        $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                            'name' => "filterduplicatetoken",
                            'id'=>"filterduplicatetoken",
                            'value' => '1',
                            'onLabel'=>gT('On'),
                            'offLabel' => gT('Off')));
                        ?>
                    </div>
                    <div class="help-block"><?php eT("The token field is always checked for duplicates."); ?></div>
                </div>

                <!-- Duplicates are determined by -->
                <div class="form-group" id='lifilterduplicatefields'>
                    <label class=" control-label" for='filterduplicatefields'><?php eT("Duplicates are determined by:"); ?></label>
                    <div class="">
                        <?php
                            unset($aTokenTableFields['token']); // token are already duplicate forbidden mantis #14334, remove it
                            echo CHtml::listBox('filterduplicatefields', array('firstname', 'lastname', 'email'), $aTokenTableFields, array('multiple' => 'multiple', 'size' => '7','class'=>'form-control'));
                        ?>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="form-group">
                    <div class="">
                        <?php echo CHtml::htmlButton(gT("Upload"),array('type'=>'submit','name'=>'upload','value'=>'import', 'class'=>'btn btn-default')); ?>
                    </div>
                </div>
            </form>

            <!-- Infos -->
            <div class="alert alert-info" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <strong><?php eT("CSV input format"); ?></strong><br/>
                <p><?php eT("File should be a standard CSV (comma delimited) file with optional double quotes around values (default for most spreadsheet tools). The first line must contain the field names. The fields can be in any order."); ?></p>
                <span style="font-weight:bold;"><?php eT("Mandatory fields:"); ?></span> firstname, lastname, email<br />
                <span style="font-weight:bold;"><?php eT('Optional fields:'); ?></span> emailstatus, token, language, validfrom, validuntil, attribute_1, attribute_2, attribute_3, usesleft, ... .
            </div>
        </div>
    </div>
</div>
<?php
App()->getClientScript()->registerScript('CSVUploadViewBSSwitcher', "
LS.renderBootstrapSwitch();
", LSYii_ClientScript::POS_POSTSCRIPT);
?>
