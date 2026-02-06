<?php

$bInherit = (!empty($aTemplateConfiguration['sid']) || !empty($aTemplateConfiguration['gsid']));

$dropdown_options['font'] = ($bInherit ? '<option value="inherit">' . gT("Inherit") . ' [' . gT(
        "inherited value:"
    ) . ' ' . (isset($oParentOptions['font']) ? $oParentOptions['font'] : '') . ']</option>' : '');

/** @var string The html for image file dropdown options */
$imageOptions = '';
$optgroup = '';
foreach ($aTemplateConfiguration['imageFileList'] as $image) {
    // If group is different than the previous one, close the previous optgroup and open a new one
    if ($image['group'] != $optgroup) {
        if ($optgroup != '') {
            $imageOptions .= '</optgroup>';
        }
        $imageOptions .= '<optgroup label="' . $image['group'] . '">';
        $optgroup = $image['group'];
    }
    $imageOptions .= '<option data-lightbox-src="' . $image['preview'] . '" value="' . $image['filepath'] . '">' . $image['filename'] . '</option>';
}

// Add extra info needed for theme options of type "imagefile" (preview path and filename)
foreach ($aOptionAttributes['optionAttributes'] as $attributeName => &$attribute) {
    if ($attribute['type'] == 'imagefile') {
        if (!isset($oParentOptions[$attributeName])) {
            continue;
        }
        if (isset($aTemplateConfiguration['imageFileList'][$oParentOptions[$attributeName]])) {
            $image = $aTemplateConfiguration['imageFileList'][$oParentOptions[$attributeName]];
            $attribute['preview'] = $image['preview'];
            $attribute['filename'] = $image['filename'];
        } else {
            $attribute['preview'] = '';
            $attribute['filename'] = '';
        }
    }
}
unset($attribute);

/**
 * @todo: Convert backgroundimagefile and brandlogofile to 'imagefile' type
 */
// background file
$backgroundImageFile = '';
$backgroundfileInheritPreview = '';
$optgroup = '';
foreach ($aTemplateConfiguration['imageFileList'] as $image) {
    if ($image['group'] != $optgroup) {
        if ($optgroup != '') {
            $backgroundImageFile .= '</optgroup>';
        }
        $backgroundImageFile .= '<optgroup label="' . $image['group'] . '">';
        $optgroup = $image['group'];
    }

    $backgroundImageFile .= '</optgroup>';

    if (isset($oParentOptions['backgroundimagefile']) && ($oParentOptions['backgroundimagefile'] == $image['filepath'] || $oParentOptions['backgroundimagefile'] == $image['filepathOptions'])) {
        $backgroundfileInheritPreview = $image['preview'];
        $backgroundfileInheritFilename = $image['filename'];
    }

    $backgroundImageFile .= '<option data-lightbox-src="' . $image['preview'] . '" value="' . $image['filepath'] . '">' . $image['filename'] . '</option>';
}

$aOptionAttributes['optionAttributes']['backgroundimagefile']['dropdownoptions'] = $backgroundImageFile;

// brand logo file
$brandlogo = '';
$logofileInheritPreview = '';
$optgroup = '';
foreach ($aTemplateConfiguration['imageFileList'] as $image) {
    if ($image['group'] != $optgroup) {
        if ($optgroup != '') {
            $brandlogo .= '</optgroup>';
        }
        $brandlogo .= '<optgroup label="' . $image['group'] . '">';
        $optgroup = $image['group'];
    }

    $brandlogo .= '</optgroup>';

    if (isset($oParentOptions['brandlogofile']) && ($oParentOptions['brandlogofile'] == $image['filepath'] || $oParentOptions['brandlogofile'] == $image['filepathOptions'])) {
        $logofileInheritPreview = $image['preview'];
    }

    $brandlogo .= '<option data-lightbox-src="' . $image['preview'] . '" value="' . $image['filepath'] . '">' . $image['filename'] . '</option>';
}
$aOptionAttributes['optionAttributes']['brandlogofile']['dropdownoptions'] = $brandlogo;
?>

<?php foreach ($aOptionAttributes['categories'] as $key => $category) : ?>
    <div role="tabpanel" class="CoreThemeOptions--settingsTab tab-pane <?php echo $key == 0 ? 'active' : ''; ?>" id="category-<?php echo $key; ?>">
        <?php if ($key === 0) : ?>
            <?php // If this is a surveyspecific settings page, offer the possibility to do a full inheritance of the parent template ?>
            <?php if ($bInherit) : ?>
                <div class='row' id="general_inherit_active">
                    <label for='simple_edit_options_general_inherit' class='form-label'><?php echo gT("Inherit everything"); ?></label>
                    <div class='col-12'>
                        <div class="btn-group" role="group">
                            <input id="general_inherit_on" name='general_inherit' type='radio' value='on' class='btn-check selector_option_general_inherit '
                                   data-id='simple_edit_options_general_inherit'/>
                            <label for="general_inherit_on" class="btn btn-outline-secondary">
                                <?php echo gT("Inherited"); ?>
                            </label>
                            <input id="general_inherit_off" name='general_inherit' type='radio' value='off' class='btn-check selector_option_general_inherit '
                                   data-id='simple_edit_options_general_inherit'/>
                            <label for="general_inherit_off" class="btn btn-outline-secondary">
                                <?php echo gT("Customize theme"); ?>
                            </label>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        <div class="position-relative">
            <div class="row action_hide_on_inherit p-1">
                <?php if (strpos($_SERVER['REQUEST_URI'], 'updateSurvey') !== false) : ?>
                    <div class="action_hide_on_inherit_wrapper ls-option-disabled">
                    </div>
                <?php endif; ?>
                <?php foreach ($aOptionAttributes['optionAttributes'] as $attributeKey => $attribute) : ?>
                    <?php
                    $sParentOption = array_key_exists($attributeKey, $oParentOptions) ? $oParentOptions[$attributeKey] : '';
                    if ($attributeKey === 'ajaxmode') {
                        continue;
                    }
                    // Check if the option has a parent option. For example, the 'brandlogofile' option (dropdown)
                    // has 'brandlogo' (Yes/No) as parent option, because it is only enabled when the parent option
                    // is set to 'Yes'.
                    $hasParent = !empty($attribute['parent']);
                    $parentDataAttribute = "";
                    if ($hasParent) {
                        $parentDataAttribute = 'data-parent="' . $attribute['parent'] . '"';
                    }
                    ?>
                    <?php if (array_key_exists('category', $attribute) && $category === $attribute['category']) : ?>
                        <div class="col-<?= $attribute['width'] ?>">
                            <label for="simple_edit_options_<?= $attributeKey ?>" class="form-label">
                                <?= gT($attribute['title']) ?>
                            </label>
                            <?php if ($attribute['type'] === 'buttons') : ?>
                                <?php
                                $optionsValues = !empty($attribute['options']) ? explode('|', $attribute['options']) : [];
                                $optionLabels = !empty($attribute['optionlabels']) ? explode('|', $attribute['optionlabels']) : [];
                                // images are loaded through a css class injection
                                $optionImages = !empty($attribute['optionimages']) ? explode('|', $attribute['optionimages']) : [];
                                $options = array_combine($optionsValues, $optionLabels);
                                foreach ($optionsValues as $optionKey => $optionSettings) {
                                    $imageClass = $optionImages[$optionKey] ?? '';
                                    $options[$optionSettings] = [
                                        'value' => $optionLabels[$optionKey],
                                        'image' => $imageClass,
                                    ];
                                }
                                if ($bInherit && isset($sParentOption)) {
                                    $options['inherit']['value'] = $sParentOption . " ᴵ";
                                    if (!empty($options[$sParentOption]['image'])) {
                                        $options['inherit']['image'] = $options[$sParentOption]['image'];
                                    }
                                }
                                if ($bInherit && isset($sParentOption)) {
                                    if (is_numeric($sParentOption) && array_key_exists($sParentOption, $options)) {
                                        $sParentLabelOption = $options[$sParentOption]['value'];
                                        $options['inherit']['value'] = gT($sParentLabelOption) . " ᴵ";
                                    } else {
                                        $sParentOption = !empty($options[$sParentOption]['value']) ? gT($options[$sParentOption]['value']) : '';
                                        $options['inherit']['value'] = $sParentOption . " ᴵ";
                                    }
                                }
                                ?>
                                <!-- buttons type -->
                                <div class="col-12">
                                    <div class="btn-group" role="group">
                                        <?php foreach ($options as $optionKey => $optionSettings) : ?>
                                            <?php $id = $attributeKey . "_" . $optionKey; ?>
                                            <input id="<?= $id ?>" type="radio" name="<?= $attributeKey ?>" value="<?= $optionKey ?>"
                                                   class="btn-check selector_option_radio_field simple_edit_options_<?= $attributeKey ?>"/>
                                            <label for="<?= $id ?>" class="btn btn-outline-secondary">
                                                <?php if (!empty($optionSettings['image'])) : ?>
                                                    <?php $imageFilePath = App()->getConfig('standardthemerootdir') . DIRECTORY_SEPARATOR . $aTemplateConfiguration['template_name'] . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . $optionSettings['image'] ?>
                                                    <?php if (file_exists($imageFilePath)) : ?>
                                                        <?= file_get_contents($imageFilePath) ?>
                                                        <?= $optionKey === 'inherit' ? ' ᴵ' : '' ?>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                <?php if (empty($optionSettings['image'])) : ?>
                                                    <?= gT($optionSettings['value']) ?>
                                                <?php endif; ?>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php elseif ($attribute['type'] === 'colorpicker') : ?>
                                <!-- colorpicker type -->
                                <div class="input-group">
                                    <div class="input-group-text style__colorpicker">
                                        <input type="color" name="<?= $attributeKey ?>_picker" data-value="<?= $sParentOption ?>" class="selector__colorpicker-inherit-value"/>
                                    </div>
                                    <input id="<?= $attributeKey ?>" type="text" name="<?= $attributeKey ?>" data-inheritvalue="<?= $sParentOption ?>" value="inherit"
                                           class="selector_option_value_field selector__color-picker form-control simple_edit_options_<?= $attributeKey ?>"/>
                                    <?php if ($bInherit && isset($sParentOption)) : ?>
                                        <div class="input-group-text">
                                            <button class="btn btn-outline-secondary btn-xs selector__reset-colorfield-to-inherit"><i class="ri-refresh-line"></i></button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php elseif ($attribute['type'] === 'dropdown') : ?>
                                <?php if (!is_string($sParentOption)) {
                                    // TODO: $aParentOptions is not loaded properly, it seems.
                                    $sParentOption = 'N/A';
                                }
                                $classes = [
                                    'form-select',
                                    'selector_option_value_field',
                                ];
                                if ($hasParent) {
                                    $classes[] = 'selector_radio_childfield';
                                }
                                if ($category === 'Images') {
                                    $classes[] = 'selector_image_selector';
                                }
                                $classValue = implode(' ', $classes);

                                if ($attributeKey === 'font') {
                                    // Register font packages
                                    // All fonts are displayed in the dropdowns, so we need to register the packages for font preview to work.
                                    // Packages are separated in two groups: core and user.
                                    foreach (Yii::app()->getClientScript()->fontPackages as $fontPackages) {
                                        foreach (array_keys($fontPackages) as $fontKey) {
                                            Yii::app()->getClientScript()->registerPackage('font-' . $fontKey);
                                        }
                                    }
                                    // Websafe fonts are on a separate package
                                    Yii::app()->getClientScript()->registerPackage('font-websafe');
                                }
                                ?>
                                <!-- dropdown type -->
                                <div class="col-12">
                                    <select class="<?= $classValue ?>" <?= $parentDataAttribute ?>
                                            data-inheritvalue="<?= ($attributeKey === 'font' && isset($sPackagesToLoad) ? htmlspecialchars($sPackagesToLoad) : $sParentOption) ?>"
                                            id="simple_edit_options_<?= $attributeKey ?>" name="<?= $attributeKey ?>">
                                        <?php if ($bInherit) : ?>
                                            <?php
                                            $dataAttributes = '';
                                            $lightboxSrc = '';
                                            $inheritedValue = isset($sParentOption) ? $sParentOption : '';
                                            if ($attributeKey === 'backgroundimagefile' && !empty($backgroundfileInheritPreview)) {
                                                $lightboxSrc = $backgroundfileInheritPreview;
                                            } elseif ($attributeKey === 'brandlogofile' && !empty($logofileInheritPreview)) {
                                                $lightboxSrc = $logofileInheritPreview;
                                            }
                                            if ($category === 'Images') {
                                                $dataAttributes = 'data-lightbox-src="' . $lightboxSrc . '"';
                                            }
                                            ?>

                                            <option <?= $dataAttributes ?> value="inherit"><?= gT("Inherit") ?>[<?= gT("inherited value:") ?> <?= $inheritedValue ?>]</option>
                                        <?php endif; ?>
                                        <?php // dropdown options from config.xml file ?>
                                        <?= $aOptionAttributes['optionAttributes'][$attributeKey]['dropdownoptions'] ?>
                                    </select>
                                </div>
                            <?php elseif ($attribute['type'] === 'imagefile') : ?>
                                <?php
                                if (!is_string($sParentOption)) {
                                    // TODO: $aParentOptions is not loaded properly, it seems.
                                    $sParentOption = 'N/A';
                                }
                                ?>
                                <!-- imagefile type -->
                                <div class="col-12">
                                    <?php // Fields linked to a parent option (Yes/No switch) need a class and data-parent attribute ?>
                                    <select class="form-select selector_option_value_field selector_option_value_field selector_image_selector"
                                            <?= $parentDataAttribute ?>
                                            data-inheritvalue="<?= $sParentOption ?>"
                                            id="simple_edit_options_<?= $attributeKey ?>"
                                            name="<?= $attributeKey ?>">
                                        <?php if ($bInherit) : ?>
                                            <?php
                                            if (isset($attribute['preview'])) {
                                                $inheritedValue = $attribute['preview'];
                                            } else {
                                                $inheritedValue = isset($sParentOption) ? $sParentOption : '';
                                            }
                                            ?>
                                            <option value="inherit"><?= gT("Inherit") ?>[<?= gT("inherited value:") ?> <?= $inheritedValue ?>]</option>
                                        <?php endif; ?>
                                        <?php // Dropdown options for image files ?>
                                        <?= $imageOptions ?>
                                    </select>
                                </div>
                            <?php elseif ($attribute['type'] === 'icon') : ?>
                                <!-- icon type -->
                                <div class="col-12 input-group">
                                    <select class="selector_option_value_field form-select simple_edit_options_checkicon" <?= $parentDataAttribute ?>
                                            id="simple_edit_options_<?= $attributeKey ?>" name="<?= $attributeKey ?>">
                                        <?php if ($bInherit) : ?>
                                            <option value="inherit"><?= gT("Inherit") ?>[<?= gT("inherited value:") ?> <?= ($sParentOption ?? '') ?>]</option>
                                        <?php endif; ?>
                                        <?php // dropdown options from config.xml file ?>
                                        <?= $aOptionAttributes['optionAttributes'][$attributeKey]['dropdownoptions'] ?>
                                    </select>
                                    <div class="input-group-text selector__<?= $attributeKey ?>-preview">
                                        ( <i class="fa" data-inheritvalue="<?= $sParentOption ?>"
                                             style=" background-color: #328637; color: white; width: 16px; height: 16px;  padding: 3px; font-size: 11px; ">
                                            &#x<?= $sParentOption ?>
                                        </i> )
                                    </div>
                                </div>
                            <?php elseif ($attribute['type'] === 'text') : ?>
                                <!-- text type -->
                                <div class="col-12">
                                    <input type="text" class="form-control selector-text-input selector_text_option_value_field" <?= $parentDataAttribute ?>
                                           id="simple_edit_options_<?= $attributeKey ?>" name="<?= $attributeKey ?>"
                                           title="<?= gT("inherited value:") ?> <?= CHtml::encode($sParentOption) ?>"/>
                                </div>
                            <?php elseif ($attribute['type'] === 'textarea') : ?>
                                <!-- textarea type -->
                                <div class="col-12">
                                    <textarea
                                        class="form-control selector-text-input selector_text_option_value_field" <?= $parentDataAttribute ?>
                                        id="simple_edit_options_<?= $attributeKey ?>" name="<?= $attributeKey ?>"
                                        rows="<?= (int)$attribute['rows'] ?>"
                                        title="<?= gT("inherited value:") . CHtml::encode($sParentOption) ?>"
                                    >
                                    </textarea>
                                </div>
                            <?php elseif ($attribute['type'] === 'duration') : ?>
                                <!-- duration type -->
                                <div class="col-12">
                                    <input type="text" class="form-control selector-numerical-input selector_text_option_value_field selector_radio_childfield"
                                           <?= $parentDataAttribute ?> id="simple_edit_options_<?= $attributeKey ?>" name="<?= $attributeKey ?>"
                                           title="<?= gT("inherited value:") ?> <?= $sParentOption ?>"/>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if ($attribute['type'] === 'imagefile' || ($category == 'Images' && $attribute['type'] == 'dropdown')) : ?>
                            <!-- imagefile, dropdown type -->
                            <div class="col-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="col-12">
                                    <button
                                        class="btn btn-outline-secondary selector__open_lightbox"
                                        data-bs-target="#simple_edit_options_<?= $attributeKey ?>"> <?= gT('Preview image') ?>
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php if ($category === 'Images') : ?>
                <!-- Images category -->
                <div class="row action_hide_on_inherit">
                    <div class="ls-space margin bottom-15 top-15">
                        <div class="row ls-space margin bottom-15">
                            <div class="col-4">
                                <label>
                                    <?php printf(gT("Upload an image (maximum size: %d MB):"), getMaximumFileUploadSize() / 1024 / 1024); ?>
                                </label>
                            </div>
                            <div class="col-8">
                                <span id="fileselector_frontend">
                                    <label class="btn btn-outline-secondary" for="upload_image_frontend">
                                    <input class="d-none" id="upload_image_frontend" name="upload_image_frontend" type="file">
                                        <i class="ri-upload-fill ls-space margin right-10"></i>
                                        <?php eT("Upload"); ?>
                                    </label>
                                </span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="progress">
                                    <div id="upload_progress_frontend" class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
                                         style="width: 0%;">
                                        <span class="visually-hidden">0%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif ?>
        </div>
    </div>
<?php endforeach; ?>

<div class="modal fade" tabindex="-1" role="dialog" id="lightbox-modal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title selector__title"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <img class="selector__image img-fluid" src="" alt="title"/>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
