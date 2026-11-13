<?php

/**
 * This view generate the language tab inside global settings.
 *
 *
 */

?>
<div class="container">
    <div class="row">
        <div class="col-6">
            <div class="mb-3">
                <label class="form-label" for='defaultlang'>
                    <?php eT("Default site language:");
                    echo ((Yii::app()->getConfig("demoMode") == true) ? '*' : ''); ?>
                </label>
                <select class="form-select"  name='defaultlang' id='defaultlang'>
                <?php
                    $actuallang = Yii::app()->getConfig('defaultlang');
                foreach (getLanguageData(true) as $langkey2 => $langname) {
                    ?>
                    <option value='<?php echo $langkey2; ?>'
                    <?php
                    if ($actuallang == $langkey2) {
                        ?> selected='selected' <?php
                    } ?>
                        ><?php echo $langname['nativedescription'] . " - " . $langname['description']; ?></option>
                    <?php
                }
                ?>
                </select>
            </div>

            <div class="mb-3">
                <?php // The two lists are a group "Available languages", each list is labelled "Visible" / "Hidden" ?>
                <span class="form-label d-block" id="languageSelection-label"><?php eT("Available languages:"); ?></span>
                    <div role="group" aria-labelledby="languageSelection-label">
                    <table id='languageSelection' role="presentation">
                    <tr>
                        <td>
                            <label for="includedLanguages"><?php eT("Visible:"); ?></label>
                            <br>
                            <select class="form-select"  style='min-width:220px;min-height:300px;' size='10' id='includedLanguages' name='includedLanguages' multiple='multiple'>
                                <?php foreach ($restrictToLanguages as $sLanguageCode) : ?>
                                    <option value='<?php echo $sLanguageCode; ?>'>
                                        <?php echo $allLanguages[$sLanguageCode]['description']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td class="p-1">
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-secondary" id="btnAdd" type="button">
                                    <span class="ri-arrow-<?php if (getLanguageRTL($_SESSION['adminlang'])) {
                                        echo 'right';
                                                          } else {
                                                              echo 'left';
                                                          } ?>-fill"></span>
                                    <?php eT("Add"); ?>
                                </button>
                                <button class="btn btn-outline-secondary" type="button" id="btnRemove">
                                    <?php eT("Remove"); ?>
                                    <span class="ri-arrow-<?php if (getLanguageRTL($_SESSION['adminlang'])) {
                                        echo 'left';
                                                          } else {
                                                              echo 'right';
                                                          } ?>-fill"></span>
                                </button>
                            </div>
                        </td>
                        <td>
                            <label for="excludedLanguages"><?php eT("Hidden:"); ?></label>
                            <br>
                            <select class="form-select"  size='10' style='min-width:220px;min-height:300px;' id='excludedLanguages' name='excludedLanguages' multiple='multiple'>
                                <?php foreach ($excludedLanguages as $sLanguageCode) : ?>
                                    <option value='<?php echo $sLanguageCode; ?>'>
                                        <?php echo $allLanguages[$sLanguageCode]['description']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                </table>
                    </div>
                <?php // Announces the languages moved with "Add" / "Remove" (globalsettings.js) ?>
                <div id="languageSelection-status" class="visually-hidden" role="status" aria-live="polite" aria-atomic="true"
                     data-added="<?= CHtml::encode(gT('Added to the visible languages: %s', 'unescaped')) ?>"
                     data-removed="<?= CHtml::encode(gT('Moved to the hidden languages: %s', 'unescaped')) ?>"></div>
            </div>

            <?php if (Yii::app()->getConfig("demoMode") == true) :?>
                <p><?php eT("Note: Demo mode is activated. Marked (*) settings can't be changed."); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>
