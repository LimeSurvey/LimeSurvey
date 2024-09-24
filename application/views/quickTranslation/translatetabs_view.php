<?php
/* @var $type string */
/* @var $activeTab bool */
/* @var $baselangdesc string */
/* @var $tolangdesc string */

extract($tabData);

?>

<div id='tab-<?php echo $type; ?>' class='tab-pane fade <?php if ($activeTab) {
    echo "show active";
} ?>'>
    <?php
    Yii::app()->loadHelper('admin.htmleditor');
    echo PrepareEditorScript(true, Yii::app()->getController());
    ?>

    <div class='translate'>
        <?php if (App()->getConfig('googletranslateapikey')) { ?>
            <input type='button' class='auto-trans btn btn-outline-secondary' value='<?php eT("Auto Translate"); ?>'
                   id='auto-trans-tab-<?php echo $type; ?>'/>
            <img src='<?php echo Yii::app()->getConfig("adminimageurl"); ?>/ajax-loader.gif' style='display: none'
                 class='ajax-loader' alt='<?php eT("Loading..."); ?>'/>
        <?php } ?>

        <?php
        $threeRows = ($type == 'question' || $type == 'subquestion' || $type == 'answer');
        ?>
        <table class='table table-striped'>
            <thead>

            <?php
            if ($type == 'answer') { ?>
                <th class="col-lg-2 text-strong"> <?= gT('QCode / Answer Code / ID') ?> </th>
                <?php
            } elseif ($threeRows) { ?>
                <th class="col-lg-2 text-strong"> <?= gT('Question code / ID') ?> </th>
                <?php
            }
            $cssClass = $threeRows ? "col-md-5 text-strong" : "col-md-6";
            ?>
            <th class="<?= $cssClass ?>"> <?= $baselangdesc ?> </th>
            <th class="<?= $cssClass ?>"> <?= $tolangdesc ?> </th>
            </thead>

            <?php
            //table content should be rendered here translatefields_view
            //content of translatefields_view
            if (isset($singleTabFieldsData)) {
                $allFieldsEmpty = false;
                foreach ($singleTabFieldsData as $fieldData) {
                    // @todo: use all_fields_empty in this loop?
                    $allFieldsEmpty = $fieldData['all_fields_empty'] && $allFieldsEmpty;
                    $textfrom = $fieldData['fieldData']['textfrom'];
                    foreach ($fieldData['translateFields'] as $field) {
                        if (strlen(trim((string)$field['textfrom'])) > 0) {
                            $this->renderPartial('translateFieldData', $field);
                        } else { ?>
                            <input type='hidden' name='<?php echo $type; ?>_newvalue[<?php echo $field['j']; ?>]'
                                   value='<?php echo $field['textto']; ?>'/>
                        <?php }
                    }
                }
            } ?>
        </table>
    </div>
    <?php
    if (isset($singleTabFieldsData)) {
        if ($allFieldsEmpty) : ?>
            <p><?php eT("Nothing to translate on this page"); ?></p><br/>
        <?php endif; ?>
        <input type='hidden' name='<?php echo $type; ?>_size' value='<?php echo count($singleTabFieldsData) - 1; ?>'/>
        <?php if ($singleTabFieldsData[0]['fieldData']['associated']) : ?>
            <input type='hidden' name='<?php echo $singleTabFieldsData[0]['fieldData']['associatedName']; ?>_size'
                   value='<?= count($singleTabFieldsData) - 1; ?>'/>
        <?php endif;
    } else { ?>
        <p><?php eT("Nothing to translate on this page"); ?></p><br/>
    <?php } ?>
</div>


