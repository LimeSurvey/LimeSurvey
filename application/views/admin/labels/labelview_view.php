<script type="text/javascript"><!--
   var ci_path = '<?php echo Yii::app()->getConfig('imageurl') ?>'; //-->
</script>
<div class='header ui-widget-header'><?php echo $clang->gT("Labels") ?></div>
<div id='tabs'>
    <ul>
<?php
    foreach ($lslanguages as $i => $language)
        echo "
        <li><a href='#neweditlblset$i'>" . getLanguageNameFromCode($language, false) . "</a></li>";
    echo "
        <li><a href='#up_resmgmt'>" . $clang->gT("Uploaded Resources Management") . "</a></li>";
?>
    </ul>
    <form method='post' id='mainform' action='<?php echo $this->createUrl('admin/labels/sa/process') ?>' onsubmit="return codeCheck('code_', <?php echo $maxsortorder ?>, '<?php echo $clang->gT("Error: You are trying to use duplicate label codes.", 'js') ?>', '<?php echo $clang->gT("Error: 'other' is a reserved keyword.", 'js') ?>');">
        <input type='hidden' name='sortorder' value='<?php echo $msorow['sortorder'] ?>' />
        <input type='hidden' name='lid' value='<?php echo $lid ?>' />
        <input type='hidden' name= 'action' value='modlabelsetanswers' />

<?php
    $i = 0;
    $first = true;
    $sortorderids = '';
    $codeids = '';
    foreach ($lslanguages as $lslanguage)
    {
?>
        <div id='neweditlblset<?php echo $i ?>'>
            <input type='hidden' class='lslanguage' value='<?php echo $lslanguage ?>' />
            <table class='answertable' align='center'>
                <thead align='center'>
                    <tr>
<?php
    if ($first)
        echo '
                        <th>&nbsp;</th>';
?>
                        <th><?php echo $clang->gT("Code") ?></th>
                        <th><?php echo $clang->gT("Assessment value") ?></th>
                        <th><?php echo $clang->gT("Title") ?></th>
                        <th><?php echo $clang->gT("Action") ?></th>
                    </tr>
                </thead>
                <tbody align='center'>
<?php
    $position = 0;
    $alternate = false;
    foreach ($results[$i] as $row)
    {
        $sortorderids = $sortorderids . ' ' . $row['language'] . '_' . $row['sortorder'];
        if ($first)
        {
            $codeids = $codeids . ' ' . $row['sortorder'];
        }
?>
                    <tr style='white-space: nowrap;' name='<?php echo $row['sortorder'] ?>'
<?php
            if ($alternate == true) {
                ?> class="highlight" <?php
            }
            else
                $alternate = true;
?>
                >
<?php
            if (!$first) {
?>                      <td><?php echo $row['code'] ?></td><td><?php echo $row['assessment_value'] ?></td>
<?php
            }
            else
            {
?>
                        <td><img src='<?php echo Yii::app()->getConfig('imageurl') ?>/handle.png' /></td>
                        <td>
                            <input type='hidden' class='hiddencode' value='<?php echo $row['code'] ?>' />
                            <input type='text'  class='codeval'id='code_<?php echo $row['sortorder'] ?>' name='code_<?php echo $row['sortorder'] ?>' maxlength='5' size='6' value='<?php echo $row['code'] ?>'/>
                        </td>

                        <td>
                            <input type='text' class='assessmentval' id='assessmentvalue_<?php echo $row['sortorder'] ?>' style='text-align: right;' name='assessmentvalue_<?php echo $row['sortorder'] ?>' maxlength='5' size='6' value='<?php echo $row['assessment_value'] ?>' />
                        </td>
<?php
        }
?>
                        <td>
                            <input type='text' name='title_<?php echo $row['language'] ?>_<?php echo $row['sortorder'] ?>' maxlength='3000' size='80' value="<?php echo html_escape($row['title']) ?>" />
<?php
                            echo getEditor("editlabel", "title_{$row['language']}_{$row['sortorder']}", "[" . $clang->gT("Label:", "js") . "](" . $row['language'] . ")", '', '', '', $action);
?>
                        </td>
<?php
            if ($first)
            {
?>
                        <td style='text-align:center;'>
                            <img src='<?php echo Yii::app()->getConfig('imageurl') ?>/addanswer.png' class='btnaddanswer' />
                            <img src='<?php echo Yii::app()->getConfig('imageurl') ?>/deleteanswer.png' class='btndelanswer' />
                        </td>
<?php
            }
?>
                    </tr>
<?php
            $position++;
        }
        $i++;
 ?>
                </tbody>
            </table>
            <button class='btnquickadd' id='btnquickadd' type='button'><?php echo $clang->gT('Quick add...') ?></button>
            <p><input type='submit' name='method' value='<?php echo $clang->gT("Save Changes") ?>'  id='saveallbtn_<?php echo $lslanguage ?>' /></p>
        </div>
<?php
}
?>
    </form>
    <div id='up_resmgmt'>
        <div>
            <form class='form30' enctype='multipart/form-data' id='importlabelresources' name='importlabelresources'
                  action='<?php echo $this->createUrl('admin/labels/sa/importlabelresources') ?>' method='post'
                  onsubmit='return validatefilename(this, "<?php echo $clang->gT('Please select a file to import!', 'js') ?>");'>

                <input type='hidden' name='lid' value='$lid' />
                <input type='hidden' name='action' value='importlabelresources' />
                <ul style='list-style-type:none; text-align:center'>
                    <li>
                        <label>&nbsp;</label>
                        <input type='button'<?php hasResources($lid, 'label') === false ? ' disabled="disabled"' : '' ?>
                               onclick='window.open("<?php echo Yii::app()->getConfig('sCKEditorURL') ?>/editor/filemanager/browser/default/browser.html?Connector=../../connectors/php/connector.php?", "_blank")'
                               value="<?php echo $clang->gT("Browse Uploaded Resources") ?>" />
                    </li>
                    <li>
                        <label>&nbsp;</label>
                        <input type='button'<?php hasResources($lid, 'label') === false ? ' disabled="disabled"' : '' ?>
                               onclick='window.open("scriptname?action=exportlabelresources&amp;lid=<?php echo $lid ?>", "_blank")'
                               value="<?php echo $clang->gT("Export Resources As ZIP Archive") ?>"  />
                    </li>
                    <li>
                        <label for='the_file'><?php echo $clang->gT("Select ZIP File:") ?></label>
                        <input id='the_file' name="the_file" type="file" size="50" />
                    </li>
                    <li>
                        <label>&nbsp;</label>
                        <input type='button' value='<?php echo $clang->gT("Import Resources ZIP Archive") ?>'
                               <?php echo !function_exists("zip_open") ? "onclick='alert(\"" . $clang->gT("zip library not supported by PHP, Import ZIP Disabled", "js") . "\");'" : "onclick='if (validatefilename(this.form,\"" . $clang->gT('Please select a file to import!', 'js') . "\")) { this.form.submit();}'" ?>/>
                    </li>
                </ul>
            </form>
        </div>
    </div>
    <div id='quickadd' name='<?php echo $clang->gT('Quick add') ?>' style='display:none;'>
        <div style='float:left;'>
            <label for='quickadd'><?php echo $clang->gT('Enter your labels:') ?></label>
            <br />
            <textarea id='quickaddarea' class='tipme' title='<?php echo $clang->gT('Enter one label per line. You can provide a code by separating code and label text with a semikolon or tab. For multilingual surveys you add the translation(s) on the same line separated with a semikolon or space.') ?>' rows='30' cols='100' style='width:570px;'></textarea>
            <br /><button id='btnqareplace' type='button'><?php echo $clang->gT('Replace') ?></button>
            <button id='btnqainsert' type='button'><?php echo $clang->gT('Add') ?></button>
            <button id='btnqacancel' type='button'><?php echo $clang->gT('Cancel') ?></button>
        </div>
    </div>
</div>