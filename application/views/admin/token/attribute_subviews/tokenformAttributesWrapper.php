<?php
/** @var array $attrDescription */
/** @var string $attrName */
/**  @var $inputValue */
/** @var string $jsDate */
?>


    <div class="ex-form-group mb-3 col-6">
        <label class="form-label" for='<?php echo $attrName; ?>'>
            <?php echo $attrDescription['description'] . ($attrDescription['mandatory'] == 'Y' ? '*' : '') ?>:
        </label>
        <div class="">
            <?php
            switch ($attrDescription['type']) {
                case 'DD':
                    // Drop down
                    $this->renderPartial(
                        '/admin/token/attribute_subviews/tokenformSelect',
                        [
                            'attrDescription' => $attrDescription,
                            'attrName' => $attrName,
                            'inputValue' => $inputValue
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
                            'inputValue' => $inputValue
                        ]
                    );
                    break;
            }
            ?>
        </div>
    </div>
