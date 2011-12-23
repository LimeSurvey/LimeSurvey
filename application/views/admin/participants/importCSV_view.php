<div class='header ui-widget-header'>
    <strong><?php $clang->eT("Import CSV"); ?> </strong>
</div>
<form action="<?php echo $this->createURL('admin/participants/sa/attributeMapCSV'); ?>" method="post" accept-charset="utf-8" class="form44" enctype="multipart/form-data">
    <ul>
        <li>
            <label for="the_file" id="fileupload">
                <?php $clang->eT("Choose the file to upload:"); ?>
            </label>
            <input type="file" name="the_file" size="50" />
        </li>
        <li>
            <label for="characterset" id="characterset">
                <?php $clang->eT("Character set of file:"); ?>
            </label>
            <?php
            $encodingsarray = array("armscii8" => $clang->gT("ARMSCII-8 Armenian")
                , "ascii" => $clang->gT("US ASCII")
                , "auto" => $clang->gT("Automatic")
                , "big5" => $clang->gT("Big5 Traditional Chinese")
                , "binary" => $clang->gT("Binary pseudo charset")
                , "cp1250" => $clang->gT("Windows Central European")
                , "cp1251" => $clang->gT("Windows Cyrillic")
                , "cp1256" => $clang->gT("Windows Arabic")
                , "cp1257" => $clang->gT("Windows Baltic")
                , "cp850" => $clang->gT("DOS West European")
                , "cp852" => $clang->gT("DOS Central European")
                , "cp866" => $clang->gT("DOS Russian")
                , "cp932" => $clang->gT("SJIS for Windows Japanese")
                , "dec8" => $clang->gT("DEC West European")
                , "eucjpms" => $clang->gT("UJIS for Windows Japanese")
                , "euckr" => $clang->gT("EUC-KR Korean")
                , "gb2312" => $clang->gT("GB2312 Simplified Chinese")
                , "gbk" => $clang->gT("GBK Simplified Chinese")
                , "geostd8" => $clang->gT("GEOSTD8 Georgian")
                , "greek" => $clang->gT("ISO 8859-7 Greek")
                , "hebrew" => $clang->gT("ISO 8859-8 Hebrew")
                , "hp8" => $clang->gT("HP West European")
                , "keybcs2" => $clang->gT("DOS Kamenicky Czech-Slovak")
                , "koi8r" => $clang->gT("KOI8-R Relcom Russian")
                , "koi8u" => $clang->gT("KOI8-U Ukrainian")
                , "latin1" => $clang->gT("cp1252 West European")
                , "latin2" => $clang->gT("ISO 8859-2 Central European")
                , "latin5" => $clang->gT("ISO 8859-9 Turkish")
                , "latin7" => $clang->gT("ISO 8859-13 Baltic")
                , "macce" => $clang->gT("Mac Central European")
                , "macroman" => $clang->gT("Mac West European")
                , "sjis" => $clang->gT("Shift-JIS Japanese")
                , "swe7" => $clang->gT("7bit Swedish")
                , "tis620" => $clang->gT("TIS620 Thai")
                , "ucs2" => $clang->gT("UCS-2 Unicode")
                , "ujis" => $clang->gT("EUC-JP Japanese")
                , "utf8" => $clang->gT("UTF-8 Unicode"));
            ?>
            <select name="characterset">
                <option value="auto" selected="selected">Automatic</option>
                <?php
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
            <label for="seperatorused" id="seperatorused">
                <?php $clang->eT("Seperator used:"); ?>
            </label>
            <?php
            $seperatorused = array("comma" => $clang->gT("Comma")
                , "semicolon" => $clang->gT("Semicolon"));
            ?>

            <select name="seperatorused">
                <option value="auto" selected="selected"><?php $clang->eT("(Autodetect)"); ?></option>
                <?php
                $seperatorused_keys = array_keys($seperatorused);
                $i = 0;
                foreach ($seperatorused as $seperator):
                    ?>
                    <option value="<?php echo ($seperatorused_keys[$i++]); ?>"><?php echo $seperator; ?></option>
                    <?php
                endforeach;
                ?>
            </select>
        </li>
        <li>
            <label for ="filter" id="filter">
                <?php
                $clang->eT("Filter blank email addresses:");
                ?>
            </label>
            <input type="checkbox" name="filterbea" value="accept" checked="checked"/></li>
        </li>
        <li>
            <p><input type="submit" value="upload" /></p>
        </li>
    </ul>
</form>
<div class="messagebox ui-corner-all">
    <div class="header ui-widget-header">
        <?php $clang->gT("CSV input format") ?>
    </div>
    <p>
        <?php $clang->eT("File should be a standard CSV (comma delimited) file with optional double quotes around values (default for OpenOffice and Excel). The first line must contain the field names. The fields can be in any order."); ?>
    </p>
    <span style="font-weight:bold;">Mandatory fields:</span><?php $clang->eT("firstname, lastname, email"); ?>
    <br/>
    <span style="font-weight:bold;">Optional fields:</span><?php $clang->eT("blacklist,language"); ?>
</div>