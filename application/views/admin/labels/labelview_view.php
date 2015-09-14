    <script type='text/javascript'>
        var duplicatelabelcode='<?php eT('Error: You are trying to use duplicate label codes.','js'); ?>';
        var otherisreserved='<?php eT("Error: 'other' is a reserved keyword.",'js'); ?>';
        var quickaddtitle='<?php eT('Quick-add subquestion or answer items','js'); ?>';
    </script>
<div class="col-lg-8 labels">
    <h3 class="pagetitle"><?php eT("Labels") ?></h3>

    <div class="row">
        <div class="col-lg-12 content-right text-center">


<ul class="nav nav-tabs">
    <?php  foreach ($lslanguages as $i => $language): ?>
        <li role="presentation" <?php if($i==0){ echo 'class="active"';}?>>
            <a data-toggle="tab" href='#neweditlblset<?php echo $i; ?>' >
                <?php echo getLanguageNameFromCode($language, false); ?>
            </a>
        </li>
    <?php endforeach;?>
</ul>            
            
    <?php echo CHtml::form(array("admin/labels/sa/process"), 'post', array('id'=>'mainform')); ?>
        <input type='hidden' name='lid' value='<?php echo $lid ?>' />
        <input type='hidden' name='action' value='modlabelsetanswers' />            
<div class="tab-content">
        <?php
            $i = 0;
            $first = true;
            $sortorderids = '';
            $codeids = '';
            foreach ($lslanguages as $lslanguage)
            {
            ?>
            <div id='neweditlblset<?php echo $i ?>' class="tab-pane fade in <?php if($i==0){ echo 'active first';} else {echo "not_first";}?>">
                <input type='hidden' class='lslanguage' value='<?php echo $lslanguage ?>' <?php if($i==0){ echo 'id="lslanguagemain"';}?> />
                <table class='answertable'>
                    <thead>
                        <tr>
                            <?php
                                if ($first)
                                    echo '
                                    <th>&nbsp;</th>';
                            ?>
                            <th><?php eT("Code") ?></th>
                            <th><?php eT("Assessment value") ?></th>
                            <th><?php eT("Title") ?></th>
                            <th><?php eT("Action") ?></th>
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
                            <tr class="labelDatas" style='white-space: nowrap;' id='row_<?php echo $row['language']; ?>_<?php echo $row['sortorder'] ?>'<?php
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
                                        echo getEditor("editlabel", "title_{$row['language']}_{$row['sortorder']}", "[" . gT("Label:", "js") . "](" . $row['language'] . ")", '', '', '', $action);
                                    ?>
                                </td>
                                <td style='text-align:center;'>
                                <?php
                                    if ($first)
                                    {
                                    ?>
                                        <img src='<?php echo $sImageURL; ?>addanswer.png' class='btnaddanswer' alt='<?php eT("Insert a new label after this one") ?>' />
                                        <img src='<?php echo $sImageURL; ?>deleteanswer.png' class='btndelanswer' alt='<?php eT("Delete this label") ?>' />
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
                    <br/>
                    <br/>
                    <button class='btnquickadd btn btn-default' id='btnquickadd_<?php echo $i ?>' type='button' ><?php eT('Quick add...') ?></button>
                </div>
                <p><input type='submit' class='hidden' name='method' value='<?php eT("Save changes") ?>'  id='saveallbtn_<?php echo $lslanguage ?>' /></p>
            </div>
            <?php
                $first=false;
            }
        ?>
    </div>
</form>          
    
    
          <div id='quickadd' style='display:none;'>
        <div style='float:left;'>
            <label for='quickaddarea'><?php eT('Enter your labels:') ?></label>
            <br />
            <textarea id='quickaddarea' name='quickaddarea' class='tipme' title='<?php eT('Enter one label per line. You can provide a code by separating code and label text with a semikolon or tab. For multilingual surveys you add the translation(s) on the same line separated with a semikolon or tab.') ?>' rows='30' cols='100' style='width:570px;'></textarea>
            <p class='button-list'>
                <button id='btnqareplace' type='button'><?php eT('Replace') ?></button>
                <button id='btnqainsert' type='button'><?php eT('Add') ?></button>
                <button id='btnqacancel' type='button'><?php eT('Cancel') ?></button>
            </p>
        </div>
    </div>
    
    
    
    
    
    
    

    
    
    
      
        </div>
    </div>
</div>    


<div class="col-lg-4">
    <div class="row">
        <div class="col-lg-12 content-right text-center">    
            <div class="panel-group" id="accordion" role="tablist" style="margin-top: 20px">
                <div class="panel panel-default" id="up_resmgmt">
                    
                    <div class="panel-heading" role="tab" id="headingOne">
                        <h4 class="panel-title">
                            <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                <?php eT("Uploaded resources management"); ?>
                            </a>
                        </h4>
                    </div>            
                    
                    <div id="collapseOne" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
                        <div class="panel-body">
                            <div>
                                <?php echo CHtml::form('third_party/kcfinder/browse.php?language='.sTranslateLangCode2CK(App()->language), 'get', array('id'=>'browselabelresources','class'=>'form30','name'=>'browselabelresources','target'=>'_blank')); ?>
                                    <ul class="list-unstyled">
                                        <li>
                                            <label>&nbsp;</label>
                                            <?php echo CHtml::dropDownList('type', 'files', array('files' => gT('Files'), 'flash' => gT('Flash'), 'images' => gT('Images'))); ?>
                                            <input type='submit' value="<?php eT("Browse uploaded resources") ?>" />
                                        </li>
                                        <li>
                                            <label>&nbsp;</label>
                                            <input class="btn btn-default" type='button'<?php echo hasResources($lid, 'label') === false ? ' disabled="disabled"' : '' ?>
                                                onclick='window.open("<?php echo $this->createUrl("/admin/export/sa/resources/export/label/lid/$lid"); ?>", "_blank")'
                                                value="<?php eT("Export resources as ZIP archive") ?>"  />
                                        </li>
                                    </ul>
                                    <input type='hidden' name='lid' value='<?php echo $lid; ?>' />
                                </form>
                                <?php echo CHtml::form(array('admin/labels/sa/importlabelresources'), 'post', array('id'=>'importlabelresources',
                                                                                                          'class'=>'form30',
                                                                                                          'name'=>'importlabelresources',
                                                                                                          'enctype'=>'multipart/form-data',
                                                                                                          'onsubmit'=>'return validatefilename(this, "'.gT('Please select a file to import!', 'js').'");')); ?>
                                    <ul class="list-unstyled">
                                        <li>
                                            <label for='the_file'><?php eT("Select ZIP file:") ?></label>
                                            <input id='the_file' name="the_file" type="file" />
                                        </li>
                                        <li>
                                            <label>&nbsp;</label>
                                            <input type='button' value='<?php eT("Import resources ZIP archive") ?>'
                                                <?php echo !function_exists("zip_open") ? "onclick='alert(\"" . gT("zip library not supported by PHP, Import ZIP Disabled", "js") . "\");'" : "onclick='if (validatefilename(this.form,\"" . gT('Please select a file to import!', 'js') . "\")) { this.form.submit();}'" ?>/>
                                        </li>
                                    </ul>
                                    <input type='hidden' name='lid' value='<?php echo $lid; ?>' />
                                    <input type='hidden' name='action' value='importlabelresources' />
                                </form>
                            </div>
                        </div>
                    </div>
                                
                </div>
            </div>
        </div>            
</div>   
</div>   

