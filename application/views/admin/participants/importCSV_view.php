<div class='header ui-widget-header'>
    <strong><?php eT("Import CSV"); ?> </strong>
</div>
<?php echo CHtml::form(array("admin/participants/sa/attributeMapCSV"), 'post', array('id'=>'addsurvey','class'=>'form44', 'enctype'=>'multipart/form-data', 'accept-charset'=>'utf-8')); ?>
    <ul>
        <li>
            <label for="the_file" id="fileupload">
                <?php eT("Choose the file to upload:"); ?>
            </label>
            <input type="file" name="the_file" required>
        </li>
        <li>
            <label for="characterset" id="characterset">
                <?php eT("Character set of file:"); ?>
            </label>
            <select name="characterset">
                <option value="auto" selected="selected">Automatic</option>
                <?php
                $encodingsarray =aEncodingsArray();
                $encodingsarray_keys = array_keys($encodingsarray);
                $i = 0;
                foreach ($encodingsarray as $encoding):
                    ?>
                    <option value="<?php echo ($encodingsarray_keys[$i++]); ?>"><?php echo $encoding; ?></option>
                    <?php
                endforeach;
                ?>
            </select>
        </li>
        <li>
            <label for="separatorused" id="separatorused">
                <?php eT("Separator used:"); ?>
            </label>
            <?php
            $separatorused = array("comma" => gT("Comma")
                , "semicolon" => gT("Semicolon"));
            ?>

            <select name="separatorused">
                <option value="auto" selected="selected"><?php eT("(Autodetect)"); ?></option>
                <?php
                $separatorused_keys = array_keys($separatorused);
                $i = 0;
                foreach ($separatorused as $separator):
                    ?>
                    <option value="<?php echo ($separatorused_keys[$i++]); ?>"><?php echo $separator; ?></option>
                    <?php
                endforeach;
                ?>
            </select>
        </li>
        <li>
            <label for ="filter" id="filter">
                <?php
                eT("Filter blank email addresses:");
                ?>
            </label>
            <input type="checkbox" name="filterbea" value="accept" checked="checked"/></li>
        </li>
        <li>
            <p><input type="submit" value="<?php eT("Upload") ?>" /></p>
        </li>
    </ul>
</form>
<div class="messagebox ui-corner-all">
    <div class="header ui-widget-header">
        <?php gT("CSV input format") ?>
    </div>
    <p>
        <?php eT("File should be a standard CSV (comma delimited) file with optional double quotes around values (default for OpenOffice and Excel). The first line must contain the field names. The fields can be in any order."); ?>
    </p>
    <span style="font-weight:bold;"><?php eT("Mandatory field:") ?></span> email <br/>
    <span style="font-weight:bold;"><?php eT("Optional fields:") ?></span> firstname, lastname,blacklisted,language
</div>
