<div class="side-body">
<?php echo CHtml::form(array('admin/dataentry/sa/vvimport/surveyid/'.$surveyid), 'post', array('enctype'=>'multipart/form-data', 'id'=>'vvexport',  'class'=>''));?>
    <?php if($tableExists):?>
        <div class="row">
        <div class="col-12">
            <div class="col-lg-6 text-start">
                <h4>
                    <?php  eT("Import a VV response data file"); ?>
                </h4>
            </div>
        </div>
        <h3></h3>
    </div>
    <?php endif;?>
    

        <div class="row">
            <div class="col-12 content-right">

<?php
    if ($tableExists) {
    ?>
    
    
    <div class="card card-primary" id="panel-1">
        <div class="card-header ">
            <div class="">
                <?php eT("General");?>
            </div>
        </div>

        <div class="card-body">
            <div class="mb-3">
                <label for="csv_vv_file" class=" form-label">
                    <?php printf(gT("Response data file (*.csv,*.vv,*.txt) (maximum size: %d MB):"),getMaximumFileUploadSize()/1024/1024); ?>
                </label>
                <div class="">
                    <input type="file" value="" name="csv_vv_file" id="csv_vv_file" class="form-control"  accept='.csv,.vv,.txt' required>
                </div>
            </div>

            <div class="mb-3">
                <label for="noid" class=" form-label">
                    <?php eT("Exclude record IDs?"); ?>
                </label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name' => 'noid',
                        'checkedOption' => '1',
                        'selectOptions' => [
                            '1' => gT('Yes'),
                            '0' => gT('No'),
                        ]
                    ]); ?>
                </div>
            </div>

            <div class="mb-3 d-none" id="insertmethod-container">
                <label for="insertmethod" class=" form-label">
                    <?php eT("When an imported record matches an existing record ID:"); ?>
                </label>
                <div class="">
                    <?php  echo CHtml::dropDownList('insertmethod', 'ignore', array(
                            'skip' => gT("Report and skip the new record."),
                            'renumber' => gT("Renumber the new record."),
                            'replace' => gT("Replace the existing record."),
                            'replaceanswers' => gT("Replace answers in file in the existing record."),
                            ),array('class'=>'form-control'));
                     ?>
                </div>
            </div>

            <div class="mb-3">
                <label for="notfinalized" class=" form-label">
                    <?php eT("Import as not finalized answers?"); ?>
                </label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name' => 'notfinalized',
                        'checkedOption' => '0',
                        'selectOptions' => [
                            '1' => gT('Yes'),
                            '0' => gT('No'),
                        ]
                    ]); ?>
                </div>
            </div>

            <div class="mb-3">
                <label for="vvcharset" class=" form-label">
                    <?php eT("Character set of the file:"); ?>
                </label>
                <div class="">
                    <?php  echo CHtml::dropDownList('vvcharset',false,$aEncodings,array('class'=>'form-select', 'empty' => gT('Automatic (UTF-8)'))); ?>
                </div>
            </div>

            <div class="mb-3">
                <label for="dontdeletefirstline" class=" form-label" title='<?php eT("With real vv file : questions code are in second line"); ?>' data-bs-toggle="tooltip" data-bs-placement="right">
                    <?php eT("First line contains the code of questions:"); ?>
                </label>
                <div class="">
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name' => 'dontdeletefirstline',
                        'checkedOption' => '0',
                        'selectOptions' => [
                            '1' => gT('Yes'),
                            '0' => gT('No'),
                        ]
                    ]); ?>
                </div>
            </div>

            <div class="mb-3">
                <label for="forceimport" class=" form-label" title='<?php eT("Try to import even if question codes don't match"); ?>' data-bs-toggle="tooltip" data-bs-placement="right">
                    <?php eT("Force import:"); ?>
                </label>
                <div>
                    <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                        'name' => 'forceimport',
                        'checkedOption' => '0',
                        'selectOptions' => [
                            '1' => gT('Yes'),
                            '0' => gT('No'),
                        ]
                    ]); ?>
                </div>
            </div>


        </div>
    </div>

        <p>
            <input type='submit' class="d-none" value='<?php eT("Import"); ?>' />
            <input type='hidden' name='action' value='vvimport' />
            <input type='hidden' name='subaction' value='upload' />
            <input type='hidden' name='sid' value='<?php echo $surveyid; ?>' />
        </p>
    </form>
    <br />

    <?php } else { ?>

    <div class="jumbotron message-box message-box-error">
        <h2 class="danger"><?php eT("Import a VV response data file"); ?>:</h2>
        <p class="lead text-danger">
            <?php eT("Cannot import the VVExport file."); ?>
        </p>
        <p>
            <?php eT("This survey is not active. You must activate the survey before attempting to import a VVexport file."); ?>
        </p>
        <p>
            <a class="btn btn-lg btn-outline-secondary" href='<?php echo $this->createUrl('surveyAdministration/view/'.$surveyid); ?>'><?php eT("Return to survey administration"); ?></a>
        </p>
    </div>

        <?php } ?>

</div></div></div>
<?php
$excludeRecordActive = <<<JAVASCRIPT
$('#noid_1').on('change', function (evt) {
    $('#insertmethod-container').addClass('d-none');
    $('#insertmethod').attr('disabled','disabled');
});

$('#noid_2').on('change', function (evt) {
    $('#insertmethod-container').removeClass('d-none');
    $('#insertmethod').removeAttr('disabled');
});
JAVASCRIPT;

App()->getClientScript()->registerScript('VVImportBS5Switcher', $excludeRecordActive, LSYii_ClientScript::POS_POSTSCRIPT);
?>
