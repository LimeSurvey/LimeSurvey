<?php
/* @var $this AdminController */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('importParticipants');

?>
<div id="pjax-content">
    <div class="col-lg-12 list-surveys">
        <h3><?php eT("Import CSV"); ?></h3>

        <div class="row">
            <div class="container">

                <?php echo TbHtml::form(array("admin/participants/sa/attributeMapCSV"), 'post', array('id'=>'addsurvey','enctype'=>'multipart/form-data', 'accept-charset'=>'utf-8')); ?>

                <div class="row ls-space margin top-25 bottom-25">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="the_file" id="fileupload" class='control-label '>
                                <?php eT("Choose the file to upload:"); ?>
                            </label>
                            <div class="col-sm-12">
                                <input type="file" name="the_file" accept='.csv' />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row ls-space margin top-25 bottom-25">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="characterset" id="characterset" class='control-label '>
                                <?php eT("Character set of file:"); ?>
                            </label>
                            <div class="col-sm-12">
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
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="separatorused" id="separatorused" class='control-label '>
                                <?php eT("Separator used:"); ?>
                            </label>
                            <div class="col-sm-12">
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
                    </div>
                </div>

                <div class="row  ls-space margin top-25 bottom-25">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="filter" id="filter" class='control-label '>
                                <?php
                                eT("Filter blank email addresses:");
                                ?>
                                <input class="ls-space margin left-15" type="checkbox" name="filterbea" value="accept" checked="checked">
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row  ls-space margin top-25 bottom-25">            
                    <div class="form-group">
                        <div class="col-sm-12 ">
                            <input type="submit" value="<?php eT("Upload") ?>" class="btn btn-default col-md-offset-4 col-md-4 col-sm-offest-3 col-sm-6">
                        </div>
                    </div>
                </div>
                <?php echo CHtml::endForm();?>
    

                <div class="col-sm-12  ls-space margin top-25 bottom-25">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <?php eT("CSV input format") ?>
                        </div>
                        <div class='panel-body'>

                            <p>
                                <?php eT("File should be a standard CSV (comma delimited) file with optional double quotes around values (default for most spreadsheet tools). The first line must contain the field names. The fields can be in any order."); ?>
                            </p>
                            <span style="font-weight:bold;"><?php eT("Mandatory field:") ?></span> email <br/>
                            <span style="font-weight:bold;"><?php eT("Optional fields:") ?></span> firstname, lastname,blacklisted,language
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <span id="locator" data-location="import">&nbsp;</span>
</div>
