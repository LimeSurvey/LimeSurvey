<?php
/** @var array $attrDescription */
/** @var string $attrName */
/**  @var $inputValue */
/** @var string $jsDate */
/** @var string $addClass */
/** @var boolean $batchEdit */
$batchEdit = isset($batchEdit) && $batchEdit;
?>


    <div class="ex-form-group mb-3 <?= $addClass ?>">
        <?php if ($batchEdit): ?>
            <div class="col-md-1">
                <label class="">

                    <input type="checkbox" class="action_check_to_keep_old_value"></input>
                </label>
            </div>
                <label class="col-md-3 form-label" for='massedit_<?php echo $attrName; ?>'><?php echo $attrDescription['description'] . ($attrDescription['mandatory'] == 'Y' ? '*' : '') ?>:</label>
            <div class="col-md-8">
        <?php else: ?>
            <label class="form-label" for='<?php echo $attrName; ?>'>
                <?php echo $attrDescription['description'] . ($attrDescription['mandatory'] == 'Y' ? '*' : '') ?>:
            </label>
            <div class="">
        <?php endif;?>
        <?php
            switch ($attrDescription['type']) {
                case 'DD':
                    // Drop down
                    $this->renderPartial(
                        '/admin/token/attribute_subviews/tokenformSelect',
                        [
                            'attrDescription' => $attrDescription,
                            'attrName' => $attrName,
                            'inputValue' => $inputValue,
                            'batchEdit' => $batchEdit,
                        ]
                    );
                    break;

                case 'DP':
                    // Date
                    $this->renderPartial(
                        '/admin/token/attribute_subviews/tokenformDateInput',
                        [
                            'attrDescription' => $attrDescription,
                            'attrName' => $attrName,
                            'inputValue' => $inputValue,
                            'jsDate' => $jsDate,
                            'batchEdit' => $batchEdit,
                        ]
                    );
                    break;

                default:
                    // Text
                    $this->renderPartial(
                        '/admin/token/attribute_subviews/tokenformTextInput',
                        [
                            'attrDescription' => $attrDescription,
                            'attrName' => $attrName,
                            'inputValue' => $inputValue,
                            'batchEdit' => $batchEdit,
                        ]
                    );
                    break;
            }
        ?>
        </div>
    </div>
