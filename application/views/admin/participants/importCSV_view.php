<div class='header ui-widget-header'>
 <strong><?php echo $clang->gT("Import CSV"); ?> </strong>
</div>
 <?php
  $attribute = array('class' => 'form44');
  echo form_open_multipart('/admin/participants/attributeMapCSV',$attribute);
 ?>
<ul>
 <li>
  <label for="fileupload" id="fileupload">
   <?php echo $clang->gT("Choose the file to Upload:");?>
  </label>
  <input type="file" name="userfile" size="50" />
 </li>
 <li>
  <label for="characterset" id="characterset">
   <?php echo $clang->gT("Characterset of File:");?>
  </label>
   <?php
      $encodingsarray = array("armscii8"=>$clang->gT("ARMSCII-8 Armenian")
                             ,"ascii"=>$clang->gT("US ASCII")
                             ,"auto"=>$clang->gT("Automatic")
                             ,"big5"=>$clang->gT("Big5 Traditional Chinese")
                             ,"binary"=>$clang->gT("Binary pseudo charset")
                             ,"cp1250"=>$clang->gT("Windows Central European")
                             ,"cp1251"=>$clang->gT("Windows Cyrillic")
                             ,"cp1256"=>$clang->gT("Windows Arabic")
                             ,"cp1257"=>$clang->gT("Windows Baltic")
                             ,"cp850"=>$clang->gT("DOS West European")
                             ,"cp852"=>$clang->gT("DOS Central European")
                             ,"cp866"=>$clang->gT("DOS Russian")
                             ,"cp932"=>$clang->gT("SJIS for Windows Japanese")
                             ,"dec8"=>$clang->gT("DEC West European")
                             ,"eucjpms"=>$clang->gT("UJIS for Windows Japanese")
                             ,"euckr"=>$clang->gT("EUC-KR Korean")
                             ,"gb2312"=>$clang->gT("GB2312 Simplified Chinese")
                             ,"gbk"=>$clang->gT("GBK Simplified Chinese")
                             ,"geostd8"=>$clang->gT("GEOSTD8 Georgian")
                             ,"greek"=>$clang->gT("ISO 8859-7 Greek")
                             ,"hebrew"=>$clang->gT("ISO 8859-8 Hebrew")
                             ,"hp8"=>$clang->gT("HP West European")
                             ,"keybcs2"=>$clang->gT("DOS Kamenicky Czech-Slovak")
                             ,"koi8r"=>$clang->gT("KOI8-R Relcom Russian")
                             ,"koi8u"=>$clang->gT("KOI8-U Ukrainian")
                             ,"latin1"=>$clang->gT("cp1252 West European")
                             ,"latin2"=>$clang->gT("ISO 8859-2 Central European")
                             ,"latin5"=>$clang->gT("ISO 8859-9 Turkish")
                             ,"latin7"=>$clang->gT("ISO 8859-13 Baltic")
                             ,"macce"=>$clang->gT("Mac Central European")
                             ,"macroman"=>$clang->gT("Mac West European")
                             ,"sjis"=>$clang->gT("Shift-JIS Japanese")
                             ,"swe7"=>$clang->gT("7bit Swedish")
                             ,"tis620"=>$clang->gT("TIS620 Thai")
                             ,"ucs2"=>$clang->gT("UCS-2 Unicode")
                             ,"ujis"=>$clang->gT("EUC-JP Japanese")
                             ,"utf8"=>$clang->gT("UTF-8 Unicode"));
       echo form_dropdown('characterset', $encodingsarray, 'auto');
   ?>
 </li>
 <li>
  <label for="seperatorused" id="seperatorused">
   <?php  echo $clang->gT("Seperator Used:");?>
  </label>
   <?php
     $seperatorused = array( "auto"=>$clang->gT("Auto Detected")
                             ,"comma"=>$clang->gT("Comma")
                             ,"semicolon"=>$clang->gT("Semicolon"));
     echo form_dropdown('seperatorused', $seperatorused, 'auto');
   ?>
 </li>
 <li>
  <label for ="filter" id="filter">
   <?php
    echo $clang->gT("Filter blank email addresses:");
   ?>
  </label>
   <?php
    echo form_checkbox('filterbea','accept', TRUE); 
   ?>
 </li>
 <li>
     <p><input type="submit" value="upload" /></p>
 </li>
</ul>
 <?php 
  echo form_close();
 ?>
<div class="messagebox ui-corner-all">
 <div class="header ui-widget-header">
  <?php $clang->gT("CSV input format") ?>
 </div>
 <p>
  <?php echo $clang->gT("File should be a standard CSV (comma delimited) file with optional double quotes around values (default for OpenOffice and Excel). The first line must contain the field names. The fields can be in any order.");?>
 </p>
 <span style="font-weight:bold;">Mandatory fields:</span><?php echo $clang->gT("firstname, lastname, email"); ?>
  <br/>
 <span style="font-weight:bold;">Optional fields:</span><?php echo $clang->gT("blacklist,language"); ?>
</div>