<?php
/**
 * This view generate the language tab inside global settings.
 * 
 *  
 */
?>

<ul>
    <li><label for='defaultlang'><?php eT("Default site language:"); echo ((Yii::app()->getConfig("demoMode")==true)?'*':''); ?></label>
        <select name='defaultlang' id='defaultlang'>
            <?php
                $actuallang=getGlobalSetting('defaultlang');
                foreach (getLanguageData(true) as  $langkey2=>$langname)
                {
                ?>
                <option value='<?php echo $langkey2; ?>'
                    <?php
                        if ($actuallang == $langkey2) { ?> selected='selected' <?php } ?>
                    ><?php echo $langname['nativedescription']." - ".$langname['description']; ?></option>
                <?php
                }
            ?>
        </select>
    </li>
    <li><label for='includedLanguages'><?php eT("Available languages:"); ?></label>
        <table id='languageSelection'>
            <tr>
                <td>
                    <select style='min-width:220px;' size='5' id='includedLanguages' name='includedLanguages' multiple='multiple'><?php
                            foreach ($restrictToLanguages as $sLanguageCode) {?>
                            <option value='<?php echo $sLanguageCode; ?>'><?php echo $allLanguages[$sLanguageCode]['description']; ?></option>
                            <?php
                        }?>

                    </select>
                </td>
                <td >
                    <button id="btnAdd" type="button"><span class="ui-icon ui-icon-carat-1-w" style="float:left"></span><?php eT("Add"); ?></button><br /><button type="button" id="btnRemove"><span class="ui-icon ui-icon-carat-1-e" style="float:right"></span><?php eT("Remove"); ?></button>
                </td>
                <td >
                    <select size='5' style='min-width:220px;' id='excludedLanguages' name='excludedLanguages' multiple='multiple'>
                        <?php foreach ($excludedLanguages as $sLanguageCode) {
                            ?><option value='<?php echo $sLanguageCode; ?>'><?php echo $allLanguages[$sLanguageCode]['description']; ?></option><?php
                        } ?>
                    </select>
                </td>
            </tr>
        </table>
    </li>
</ul>

<p><br/><input type='button' onclick='$("#frmglobalsettings").submit();' class='standardbtn' value='<?php eT("Save settings"); ?>' /><br /></p>
<?php if (Yii::app()->getConfig("demoMode")==true):?>
    <p><?php eT("Note: Demo mode is activated. Marked (*) settings can't be changed."); ?></p>
<?php endif; ?>            
