<div class="col-lg-12 list-surveys">
    <h3><?php eT("Import CSV"); ?></h3>

    <div class="row">
        <div class="col-lg-12 content-right">

            <?php echo CHtml::form(array("admin/participants/sa/attributeMapCSV"), 'post', array('id'=>'addsurvey','class'=>'col-md-6 col-md-offset-3', 'enctype'=>'multipart/form-data', 'accept-charset'=>'utf-8')); ?>

<div class="form-group">
    <label for="the_file" id="fileupload">
                <?php eT("Choose the file to upload:"); ?>
            </label>
    <input type="file" name="the_file" class="form-control" />
</div>
<div class="form-group">
            <label for="characterset" id="characterset">
                <?php eT("Character set of file:"); ?>
            </label>
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

<div class="form-group">
            <label for="separatorused" id="separatorused">
                <?php eT("Separator used:"); ?>
            </label>
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

<div class="form-group">
            <label for ="filter" id="filter">
                <?php
                eT("Filter blank email addresses:");
                ?>
            </label>

            <input type="checkbox" name="filterbea" value="accept" checked="checked"/>
</div>
<div class="form-group">
            <p><input type="submit" value="<?php eT("Upload") ?>" class="btn btn-default" /></p>
</div>
</div>




</form>

<div class="messagebox ui-corner-all col-md-6 col-md-offset-3">
    <div class="header ui-widget-header">
        <?php gT("CSV input format") ?>
    </div>
    <p>
        <?php eT("File should be a standard CSV (comma delimited) file with optional double quotes around values (default for OpenOffice and Excel). The first line must contain the field names. The fields can be in any order."); ?>
    </p>
    <span style="font-weight:bold;"><?php eT("Mandatory field:") ?></span> email <br/>
    <span style="font-weight:bold;"><?php eT("Optional fields:") ?></span> firstname, lastname,blacklisted,language
</div>

        </div>
    </div>
</div>
