<div class='header ui-widget-header'><?php $clang->eT("Labels") ?></div>
<div id='tabs' class='ui-tabs ui-widget ui-widget-content ui-corner-all'>
    <ul class='ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all'>
        <?php
            foreach ($lslanguages as $i => $language)
                echo "
                <li><a href='#neweditlblset$i'>" . getLanguageNameFromCode($language, false) . "</a></li>";
            echo "
            <li><a href='#up_resmgmt'>" . $clang->gT("Uploaded resources management") . "</a></li>";
        ?>
    </ul>

    <?php echo CHtml::form(array("admin/labels/sa/process"), 'post', array('id'=>'mainform')); ?>
        <input type='hidden' name='lid' value='<?php echo $lid ?>' />
        <input type='hidden' name='action' value='modlabelsetanswers' />
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
                <table class='answertable'>
                    <thead>
                        <tr>
                            <?php
                                if ($first)
                                    echo '
                                    <th>&nbsp;</th>';
                            ?>
                            <th><?php $clang->eT("Code") ?></th>
                            <th><?php $clang->eT("Assessment value") ?></th>
                            <th><?php $clang->eT("Title") ?></th>
                            <th><?php $clang->eT("Action") ?></th>
                        </tr>
                    </thead>
                    <tbody>
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
                            <tr style='white-space: nowrap;' id='row_<?php echo $row['language']; ?>_<?php echo $row['sortorder'] ?>'<?php
                                    if ($alternate == true) {
                                    ?> class="highlight" <?php
                                    }
                                    else
                                        $alternate = true;
                                ?>>
                                <?php
                                    if (!$first) {
                                    ?>                      <td><?php echo $row['code'] ?></td><td><?php echo $row['assessment_value'] ?></td>
                                    <?php
                                    }
                                    else
                                    {
                                    ?>
                                    <td><img class='handle' src='<?php echo $sImageURL; ?>handle.png' alt=''/></td>
                                    <td>
                                        <input type='hidden' class='hiddencode' value='<?php echo $row['code'] ?>' />
                                        <input type='text'  class='codeval' id='code_<?php echo $row['sortorder'] ?>' name='code_<?php echo $row['sortorder'] ?>' maxlength='5' size='6' value='<?php echo $row['code'] ?>'/>
                                    </td>

                                    <td>
                                        <input type='text' class='assessmentval' id='assessmentvalue_<?php echo $row['sortorder'] ?>' style='text-align: right;' name='assessmentvalue_<?php echo $row['sortorder'] ?>' maxlength='5' size='6' value='<?php echo $row['assessment_value'] ?>' />
                                    </td>
                                    <?php
                                    }
                                ?>
                                <td>
                                    <input type='text' name='title_<?php echo $row['language'] ?>_<?php echo $row['sortorder'] ?>' maxlength='3000' size='80' value="<?php echo HTMLEscape($row['title']) ?>" />
                                    <?php
                                        echo getEditor("editlabel", "title_{$row['language']}_{$row['sortorder']}", "[" . $clang->gT("Label:", "js") . "](" . $row['language'] . ")", '', '', '', $action);
                                    ?>
                                </td>
                                <td style='text-align:center;'>
                                <?php
                                    if ($first)
                                    {
                                    ?>
                                        <img src='<?php echo $sImageURL; ?>addanswer.png' class='btnaddanswer' alt='<?php $clang->eT("Insert a new label after this one") ?>' />
                                        <img src='<?php echo $sImageURL; ?>deleteanswer.png' class='btndelanswer' alt='<?php $clang->eT("Delete this label") ?>' />
                                    <?php
                                    }
                                ?>
                                    </td>
                            </tr>
                            <?php
                                $position++;
                            }
                            $i++;
                        ?>
                    </tbody>
                </table>
                <div class="action-buttons">
                    <button class='btnquickadd' id='btnquickadd_<?php echo $i ?>' type='button'><?php $clang->eT('Quick add...') ?></button>
                </div>
                <p><input type='submit' name='method' value='<?php $clang->eT("Save changes") ?>'  id='saveallbtn_<?php echo $lslanguage ?>' /></p>
            </div>
            <?php
                $first=false;
            }
        ?>
    </form>
    <div id='up_resmgmt'>                              
        <div>
            <?php echo CHtml::form('third_party/kcfinder/browse.php?language='.sTranslateLangCode2CK($clang->getlangcode()), 'get', array('id'=>'browselabelresources','class'=>'form30','name'=>'browselabelresources','target'=>'_blank')); ?>
                <ul>
                    <li>
                        <label>&nbsp;</label>
                        <?php echo CHtml::dropDownList('type', 'files', array('files' => $clang->gT('Files'), 'flash' => $clang->gT('Flash'), 'images' => $clang->gT('Images'))); ?>
                        <input type='submit' value="<?php $clang->eT("Browse uploaded resources") ?>" />
                    </li>
                    <li>
                        <label>&nbsp;</label>
                        <input type='button'<?php echo hasResources($lid, 'label') === false ? ' disabled="disabled"' : '' ?>
                            onclick='window.open("<?php echo $this->createUrl("/admin/export/sa/resources/export/label/lid/$lid"); ?>", "_blank")'
                            value="<?php $clang->eT("Export resources as ZIP archive") ?>"  />
                    </li>
                </ul>
                <input type='hidden' name='lid' value='<?php echo $lid; ?>' />
            </form>
            <?php echo CHtml::form(array('admin/labels/sa/importlabelresources'), 'post', array('id'=>'importlabelresources',
                                                                                      'class'=>'form30', 
                                                                                      'name'=>'importlabelresources', 
                                                                                      'enctype'=>'multipart/form-data',
                                                                                      'onsubmit'=>'return validatefilename(this, "'.$clang->gT('Please select a file to import!', 'js').'");')); ?>
                <ul>
                    <li>
                        <label for='the_file'><?php $clang->eT("Select ZIP file:") ?></label>
                        <input id='the_file' name="the_file" type="file" />
                    </li>
                    <li>
                        <label>&nbsp;</label>
                        <input type='button' value='<?php $clang->eT("Import resources ZIP archive") ?>'
                            <?php echo !function_exists("zip_open") ? "onclick='alert(\"" . $clang->gT("zip library not supported by PHP, Import ZIP Disabled", "js") . "\");'" : "onclick='if (validatefilename(this.form,\"" . $clang->gT('Please select a file to import!', 'js') . "\")) { this.form.submit();}'" ?>/>
                    </li>
                </ul>
                <input type='hidden' name='lid' value='<?php echo $lid; ?>' />
                <input type='hidden' name='action' value='importlabelresources' />
            </form>
        </div>
    </div>
    <div id='quickadd' style='display:none;'>
        <div style='float:left;'>
            <label for='quickaddarea'><?php $clang->eT('Enter your labels:') ?></label>
            <br />
            <textarea id='quickaddarea' name='quickaddarea' class='tipme' title='<?php $clang->eT('Enter one label per line. You can provide a code by separating code and label text with a semikolon or tab. For multilingual surveys you add the translation(s) on the same line separated with a semikolon or tab.') ?>' rows='30' cols='100' style='width:570px;'></textarea>
            <p class='button-list'>
                <button id='btnqareplace' type='button'><?php $clang->eT('Replace') ?></button>
                <button id='btnqainsert' type='button'><?php $clang->eT('Add') ?></button>
                <button id='btnqacancel' type='button'><?php $clang->eT('Cancel') ?></button>
            </p>
        </div>
    </div>
    </div>
