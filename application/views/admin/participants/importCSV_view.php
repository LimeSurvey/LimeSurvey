<div class="col-lg-12 list-surveys">
    <h3><?php eT("Import CSV"); ?></h3>

    <div class="row">
        <div class="col-lg-8">

            <?php echo CHtml::form(array("admin/participants/sa/attributeMapCSV"), 'post', array('id'=>'addsurvey','class'=>'col-md-6 col-md-offset-3 form-horizontal ', 'enctype'=>'multipart/form-data', 'accept-charset'=>'utf-8')); ?>

            <div class="form-group">
                <label for="the_file" id="fileupload" class='control-label col-sm-5'>
                    <?php eT("Choose the file to upload:"); ?>
                </label>
                <div class="col-sm-7">
                    <input type="file" name="the_file" accept='.csv' />
                </div>
            </div>
            <div class="form-group">
                <label for="characterset" id="characterset" class='control-label col-sm-5'>
                    <?php eT("Character set of file:"); ?>
                </label>
                <div class="col-sm-7">
                    <select name="characterset"  class="form-control">
                        <?php
                        foreach (aEncodingsArray() as $key=>$encoding):
                            ?>
                            <option value="<?php echo $key;?>" <?php if($encoding==gT('Automatic')){echo 'selected="selected"';}?> ><?php echo $encoding; ?></option>
                            <?php
                            endforeach;
                        ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="separatorused" id="separatorused" class='control-label col-sm-5'>
                    <?php eT("Separator used:"); ?>
                </label>
                <div class="col-sm-7">
                    <?php
                    $separatorused = array("comma" => gT("Comma")
                        , "semicolon" => gT("Semicolon"));
                    ?>

                    <select name="separatorused"  class="form-control">
                        <option value="auto" selected="selected"><?php eT("(Autodetect)"); ?></option>
                        <?php
                        foreach ($separatorused as $key=>$separator):
                            ?>
                            <option value="<?php echo $key;?>"><?php echo $separator; ?></option>
                            <?php
                            endforeach;
                        ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="filter" id="filter" class='control-label col-sm-5'>
                    <?php
                    eT("Filter blank email addresses:");
                    ?>
                </label>
                <div class="col-sm-7">
                    <input type="checkbox" name="filterbea" value="accept" checked="checked">
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-7 col-sm-offset-5">
                    <input type="submit" value="<?php eT("Upload") ?>" class="btn btn-default">
                </div>
            </div>
            <?php echo CHtml::endForm();?>
        </div>
    </div>

    <div class="col-sm-6 col-sm-offset-2">
        <div class="panel panel-default">
            <div class="panel-heading">
                <?php eT("CSV input format") ?>
            </div>
            <div class='panel-body'>

                <p>
                    <?php eT("File should be a standard CSV (comma delimited) file with optional double quotes around values (default for OpenOffice and Excel). The first line must contain the field names. The fields can be in any order."); ?>
                </p>
                <span style="font-weight:bold;"><?php eT("Mandatory field:") ?></span> email <br/>
                <span style="font-weight:bold;"><?php eT("Optional fields:") ?></span> firstname, lastname,blacklisted,language
            </div>


        </div>
    </div>
</div>

