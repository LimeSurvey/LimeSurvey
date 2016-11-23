<div class="side-body <?php echo getSideBodyClass(false); ?>">
    <?php if($tableExists):?>
    <h3><?php eT("Import a VV survey file"); ?></h3>
    <?php endif;?>

        <div class="row">
            <div class="col-lg-12 content-right">

<?php
    if ($tableExists) {
    ?>
    <?php echo CHtml::form(array('admin/dataentry/sa/vvimport/surveyid/'.$surveyid), 'post', array('enctype'=>'multipart/form-data', 'id'=>'vvexport',  'class'=>'form-horizontal'));?>

    <div class="panel panel-primary" id="pannel-1" style="opacity: 1; top: 0px;">
        <div class="panel-heading">
            <h4 class="panel-title">
                <?php eT("General");?>
            </h4>
        </div>

        <div class="panel-body">
            <div class="form-group">
                <label for="csv_vv_file" class="col-sm-2 control-label">
                    <?php eT("File:");?>
                </label>
                <div class="col-sm-6">
                    <input type="file" value="" name="csv_vv_file" id="csv_vv_file" class="form-control">
                </div>
            </div>

            <div class="form-group">
                <label for="noid" class="col-sm-2 control-label">
                    <?php eT("Exclude record IDs?"); ?>
                </label>
                <div class="col-sm-4">
                    <?php  $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                        'name' => 'noid',
                        'value'=> 'noid',
                        'onLabel'=>gT('Yes'),
                        'offLabel'=>gT('No')
                        ));
                    ?>
                </div>
            </div>

            <div class="form-group" id="insertmethod-container">
                <label for="insertmethod" class="col-sm-2 control-label">
                    <?php eT("When an imported record matches an existing record ID:"); ?>
                </label>
                <div class="col-sm-4">
                    <?php  echo CHtml::dropDownList('insertmethod', 'ignore', array(
                            'skip' => gT("Report and skip the new record."),
                            'renumber' => gT("Renumber the new record."),
                            'replace' => gT("Replace the existing record."),
                            'replaceanswers' => gT("Replace answers in file in the existing record."),
                            ),array('disabled'=>'disabled','class'=>'form-control'));
                     ?>
                </div>
            </div>

            <div class="form-group">
                <label for="notfinalized" class="col-sm-2 control-label">
                    <?php eT("Import as not finalized answers?"); ?>
                </label>
                <div class="col-sm-4">
                    <?php  $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                        'name' => 'notfinalized',
                        'value'=> false,
                        'onLabel'=>gT('Yes'),
                        'offLabel'=>gT('No')
                        ));
                    ?>
                </div>
            </div>

            <div class="form-group">
                <label for="vvcharset" class="col-sm-2 control-label">
                    <?php eT("Character set of the file:"); ?>
                </label>
                <div class="col-sm-4">
                    <?php  echo CHtml::dropDownList('vvcharset',false,$aEncodings,array('class'=>'form-control', 'empty' => gT('Automatic (UTF-8)'))); ?>
                </div>
            </div>

            <div class="form-group">
                <label for="dontdeletefirstline" class="col-sm-2 control-label" title='<?php eT("With real vv file : questions code are in second line"); ?>' data-toggle="tooltip" data-placement="right">
                    <?php eT("First line contains the code of questions:"); ?>
                </label>
                <div class="col-sm-4">
                    <?php  $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                        'name' => 'dontdeletefirstline',
                        'value'=> false,
                        'onLabel'=>gT('Yes'),
                        'offLabel'=>gT('No')
                        ));
                    ?>
                </div>
            </div>

            <div class="form-group">
                <label for="forceimport" class="col-sm-2 control-label" title='<?php eT("Try to import even if question codes don't match"); ?>' data-toggle="tooltip" data-placement="right">
                    <?php eT("Force import:"); ?>
                </label>
                <div class="col-sm-4">                    
                    <?php  $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                        'name' => 'forceimport',
                        'value'=> false,
                        'onLabel'=>gT('Yes'),
                        'offLabel'=>gT('No')
                        ));
                    ?>
                </div>
            </div>


        </div>
    </div>

        <p>
            <input type='submit' class="hidden" value='<?php eT("Import"); ?>' />
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
            <a class="btn btn-lg btn-default" href='<?php echo $this->createUrl('admin/survey/sa/view/'.$surveyid); ?>'><?php eT("Return to survey administration"); ?></a>
        </p>
    </div>

        <?php } ?>

</div></div></div>

<script>
$(document).ready(function() {
    $('#noid').on('switchChange.bootstrapSwitch', function(event, state) {
        if (!state){
            $('#insertmethod').removeAttr('disabled');
            $('#insertmethod-container').show('slow');
        }else{
            $('#insertmethod').attr('disabled','disabled');
            $('#insertmethod-container').hide('slow');
        }
    });
});
</script>
