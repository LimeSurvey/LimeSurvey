<?php
/**
 * This view generate the language tab inside global settings.
 *
 *
 */
?>
    <div class="form-group">
            <label class=" control-label"  for='defaultlang'><?php eT("Default site language:"); echo ((Yii::app()->getConfig("demoMode")==true)?'*':''); ?></label>
            <div class="">
                    <select class="form-control"  name='defaultlang' id='defaultlang'>
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

        </div>
    </div>

    <div class="form-group">
            <label class=" control-label"  for='includedLanguages'><?php eT("Available languages:"); ?></label>
            <div class="">
            <table id='languageSelection'>
            <tr>
                <td>
                <?php eT("Visible:"); ?><br>
                <select class="form-control"  style='min-width:220px;' size='10' id='includedLanguages' name='includedLanguages' multiple='multiple'><?php
                            foreach ($restrictToLanguages as $sLanguageCode) {?>
                            <option value='<?php echo $sLanguageCode; ?>'><?php echo $allLanguages[$sLanguageCode]['description']; ?></option>
                            <?php
                        }?>

                    </select>
                </td>
                <td style="padding: 10px;">
                    <button class="btn btn-default" id="btnAdd" type="button">
                        <span class="ui-icon ui-icon-carat-1-<?php if (getLanguageRTL($_SESSION['adminlang'])) { echo 'e'; } else { echo 'w'; } ?>"></span>
                        <?php eT("Add"); ?>
                    </button>
                    <br /><br />
                    <button class="btn btn-default" type="button" id="btnRemove">
                        <?php eT("Remove"); ?>
                        <span class="ui-icon ui-icon-carat-1-<?php if (getLanguageRTL($_SESSION['adminlang'])) { echo 'w'; } else { echo 'e'; } ?>"></span>
                    </button>
                </td>
                <td >
                <?php eT("Hidden:"); ?><br>
                <select class="form-control"  size='10' style='min-width:220px;' id='excludedLanguages' name='excludedLanguages' multiple='multiple'>
                        <?php foreach ($excludedLanguages as $sLanguageCode) {
                            ?><option value='<?php echo $sLanguageCode; ?>'><?php echo $allLanguages[$sLanguageCode]['description']; ?></option><?php
                        } ?>
                    </select>
                </td>
            </tr>
        </table>

        </div>
    </div>


<?php if (Yii::app()->getConfig("demoMode")==true):?>
    <p><?php eT("Note: Demo mode is activated. Marked (*) settings can't be changed."); ?></p>
<?php endif; ?>
