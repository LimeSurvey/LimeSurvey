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
$backgroundImageFile           = '';
$backgroundfileInheritPreview  = '';
$optgroup                      = '';
foreach ($aTemplateConfiguration['imageFileList'] as $image) {
    if ($image['group'] != $optgroup) {
        if ($optgroup != '') {
            $backgroundImageFile .= '</optgroup>';
        }
        $backgroundImageFile .= '<optgroup label="' . $image['group'] . '">';
        $optgroup            = $image['group'];
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
$brandlogo               = '';
$logofileInheritPreview  = '';
$optgroup                = '';
foreach ($aTemplateConfiguration['imageFileList'] as $image) {
    if ($image['group'] != $optgroup) {
        if ($optgroup != '') {
            $brandlogo .= '</optgroup>';
        }
        $brandlogo .= '<optgroup label="' . $image['group'] . '">';
        $optgroup  = $image['group'];
    }

    $brandlogo .= '</optgroup>';

    if (isset($oParentOptions['brandlogofile']) && ($oParentOptions['brandlogofile'] == $image['filepath'] || $oParentOptions['brandlogofile'] == $image['filepathOptions'])) {
        $logofileInheritPreview  = $image['preview'];
    }

    $brandlogo .= '<option data-lightbox-src="' . $image['preview'] . '" value="' . $image['filepath'] . '">' . $image['filename'] . '</option>';
}

$aOptionAttributes['optionAttributes']['brandlogofile']['dropdownoptions'] = $brandlogo;

foreach ($aOptionAttributes['categories'] as $key => $category) { ?>
    <div role="tabpanel" class="CoreThemeOptions--settingsTab tab-pane <?php echo $key == 0 ? 'active' : ''; ?>" id="category-<?php echo $key; ?>">
        <?php if ($key == 0) { ?>
            <?php /* Small loading animation to give the scripts time to parse and render the correct values */ ?>
            <div class="" style="display:none;height:100%;width:100%;position:absolute;left:0;top:0;background:rgb(255,255,255);background:rgba(235,235,235,0.8);z-index:2000;">
                <div style="position:absolute; left:49%;top:35%;" class="text-center">
                    <i class="ri-loader-2-fill remix-pulse remix-3x fa-fw"></i>
                </div>
            </div>
            <?php /* If this is a surveyspecific settings page, offer the possibility to do a full inheritance of the parent template */
            if ($bInherit) { ?>
                <div class='row' id="general_inherit_active">
                    <label for='simple_edit_options_general_inherit' class='form-label'><?php echo gT("Inherit everything"); ?></label>
                    <div class='col-12'>
                        <div class="btn-group" role="group">
                            <input id="general_inherit_on" name='general_inherit' type='radio' value='on' class='btn-check selector_option_general_inherit ' data-id='simple_edit_options_general_inherit' />
                            <label for="general_inherit_on" class="btn btn-outline-secondary">
                                <?php echo gT("Inherited"); ?>
                            </label>
                            <input id="general_inherit_off" name='general_inherit' type='radio' value='off' class='btn-check selector_option_general_inherit ' data-id='simple_edit_options_general_inherit' />
                            <label for="general_inherit_off" class="btn btn-outline-secondary">
                                <?php echo gT("Customize theme"); ?>
                            </label>
                        </div>
                    </div>
                </div>
            <?php } ?>

        <?php } ?>

        <?php
        // options
        $iMaxColumnSize = 12;
        $iTotalWidth    = 0;
        $iCount         = 0;

        echo '<div class="position-relative">';

        if (strpos($_SERVER['REQUEST_URI'], 'updateSurvey') !== false) {
            echo '<div class="action_hide_on_inherit_wrapper ls-option-disabled">';
            echo '</div>';
        }

        foreach ($aOptionAttributes['optionAttributes'] as $attributeKey => $attribute) {
            $sParentOption = array_key_exists($attributeKey, $oParentOptions) ? $oParentOptions[$attributeKey] : '';
            if ($attributeKey === 'ajaxmode') {
                continue;
            }
            if (array_key_exists('category', $attribute) && $category == $attribute['category']) {
                $width = $attribute['width'];

                if (($iTotalWidth + $width) > $iMaxColumnSize) {
                    $iTotalWidth = 0;
                }

                if ($iTotalWidth == 0) {
                    if ($iCount > 0) {
                        echo '</div>';
                    }
                    echo '<div class="row action_hide_on_inherit p-1">';
                }

                echo '<div class="col-' . $attribute['width'] . '">
                                <label for="simple_edit_options_' . $attributeKey . '" class="form-label">' . gT($attribute['title']) . '</label>';
                if ($attribute['type'] == 'buttons') {
                    $optionsValues = !empty($attribute['options']) ? explode('|', $attribute['options']) : array();
                    $optionLabels  = !empty($attribute['optionlabels']) ? explode('|', $attribute['optionlabels']) : array();
                    $options       = array_combine($optionsValues, $optionLabels);
                    if ($bInherit && isset($sParentOption)) {
                        $options['inherit'] = $sParentOption . " ᴵ";
                    }
                    if ($bInherit && isset($sParentOption)) {
                        if (is_numeric($sParentOption) && array_key_exists($sParentOption, $options)) {
                            $sParentLabelOption = $options[$sParentOption];
                            $options['inherit'] = gT($sParentLabelOption) . " ᴵ";
                        } else {
                            $sParentOption = ! empty($options[$sParentOption]) ? gT($options[$sParentOption]) : '';
                            $options['inherit'] = $sParentOption . " ᴵ";
                        }
                    }

                    echo '<div class="col-12">
                                        <div class="btn-group">';
                    foreach ($options as $optionKey => $optionValue) {
                        $id = $attributeKey . "_" . $optionKey;
                        echo '<input id="' . $id . '" type="radio" name="' . $attributeKey . '" value="' . $optionKey . '" class="btn-check selector_option_radio_field simple_edit_options_' . $attributeKey . ' " id="' . $attributeKey . '"/>';
                        echo '<label for="' . $id . '" class="btn btn-outline-secondary">'
                            . gT($optionValue) . '
                                            </label>';
                    }
                    echo '</div>
                                </div>';
                } elseif ($attribute['type'] == 'colorpicker') {
                    echo '<div class="input-group">
                                    <div class="input-group-text style__colorpicker">
                                        <input type="color" name="' . $attributeKey . '_picker" data-value="' . $sParentOption . '" class="selector__colorpicker-inherit-value"/>
                                    </div>
                                    <input type="text" name="' . $attributeKey . '" data-inheritvalue="' . $sParentOption . '" value="inherit" class="selector_option_value_field selector__color-picker form-control simple_edit_options_' . $attributeKey . '" id="' . $attributeKey . '" />';
                    if ($bInherit && isset($sParentOption)) {
                        echo '<div class="input-group-text">
                                            <button class="btn btn-outline-secondary btn-xs selector__reset-colorfield-to-inherit"><i class="ri-refresh-line"></i></button>
                                        </div>';
                    }
                    echo '</div>';
                } elseif ($attribute['type'] == 'dropdown') {
                    if (!is_string($sParentOption)) {
                        // TODO: $aParentOptions is not loaded properly, it seems.
                        $sParentOption = 'N/A';
                    }
                    $classes = [
                        'form-select',
                        'selector_option_value_field',
                        'selector_radio_childfield',
                    ];
                    if ($category === 'Images') {
                        $classes[] = 'selector_image_selector';
                    }
                    $classValue = implode(' ', $classes);
                    echo ' <div class="col-12">
                                <select class="' . $classValue . '" data-parent="' . $attribute['parent'] . '" data-inheritvalue=\'' . ($attributeKey == 'font' && isset($sPackagesToLoad) ? $sPackagesToLoad : $sParentOption) . '\' id="simple_edit_options_' . $attributeKey . '" name="' . $attributeKey . '"  >';
                    if ($bInherit) {

                        $dataAttributes = '';
                        $lightboxSrc = '';
                        $inheritedValue = isset($sParentOption) ? $sParentOption : '';

                        if ($attributeKey == 'backgroundimagefile' && !empty($backgroundfileInheritPreview)) {
                            $lightboxSrc = $backgroundfileInheritPreview;
                        } elseif ($attributeKey == 'brandlogofile' && !empty($logofileInheritPreview)) {
                            $lightboxSrc =  $logofileInheritPreview;
                        }

                        if ($category === 'Images') {
                            $dataAttributes = 'data-lightbox-src="' . $lightboxSrc . '"';
                        }

                        echo '<option ' . $dataAttributes . ' value="inherit">' . gT("Inherit") . ' [' . gT("inherited value:") . ' ' . $inheritedValue . ']</option>';
                    }
                    // dropdown options from config.xml file
                    echo $aOptionAttributes['optionAttributes'][$attributeKey]['dropdownoptions'];
                    echo '</select>
                                    </div>';
                } elseif ($attribute['type'] == 'imagefile') {
                    if (!is_string($sParentOption)) {
                        // TODO: $aParentOptions is not loaded properly, it seems.
                        $sParentOption = 'N/A';
                    }
                    echo '<div class="col-12">';
                    // Fields linked to a parent option (Yes/No switch) need a class and data-parent attribute
                    if (!empty($attribute['parent'])) {
                        echo '<select class="form-select selector_option_value_field selector_radio_childfield selector_image_selector" data-parent="' . $attribute['parent'] . '" data-inheritvalue=\'' . $sParentOption . '\' id="simple_edit_options_' . $attributeKey . '" name="' . $attributeKey . '"  >';
                    } else {
                        echo '<select class="form-select selector_option_value_field selector_image_selector" data-inheritvalue=\'' . $sParentOption . '\' id="simple_edit_options_' . $attributeKey . '" name="' . $attributeKey . '"  >';
                    }
                    if ($bInherit) {
                        if (isset($attribute['preview'])) {
                            $inheritedValue = $attribute['preview'];
                        } else {
                            $inheritedValue = isset($sParentOption) ? $sParentOption : '';
                        }
                        echo '<option value="inherit">' . gT("Inherit") . ' [' . gT("inherited value:") . ' ' . $inheritedValue . ']</option>';
                    }
                    // Dropdown options for image files
                    echo $imageOptions;
                    echo '</select>';
                    echo '</div>';
                } elseif ($attribute['type'] == 'icon') {
                    echo ' <div class="col-12 input-group">
                                <select class="selector_option_value_field form-select simple_edit_options_checkicon" data-parent="' . $attribute['parent'] . '" id="simple_edit_options_' . $attributeKey . '" name="' . $attributeKey . '" >';
                    if ($bInherit) {
                        echo '<option value="inherit">' . gT("Inherit") . ' [' . gT("inherited value:") . ' ' . (isset($sParentOption) ? $sParentOption : '') . ']</option>';
                    }
                    // dropdown options from config.xml file
                    echo $aOptionAttributes['optionAttributes'][$attributeKey]['dropdownoptions'];
                    echo '</select>
                                        <div class="input-group-text selector__' . $attributeKey . '-preview">
                                        ( <i class="fa" data-inheritvalue="' . $sParentOption . '" style=" background-color: #328637; color: white; width: 16px; height: 16px;  padding: 3px; font-size: 11px; ">
                                            &#x' . $sParentOption . ';
                                        </i> )
                                    </div>
                                    </div>';
                } elseif ($attribute['type'] == 'text') {
                    echo '<div class="col-12">
                            <input type="text" class="form-control selector-text-input selector_text_option_value_field" data-parent="' . $attribute['parent'] . '" id="simple_edit_options_' . $attributeKey . '" name="' . $attributeKey . '" title="' . gT("inherited value:") . ' ' . $sParentOption . '" />
                        </div>';
                } elseif ($attribute['type'] == 'duration') {
                    echo '<div class="col-12">
                               <input type="text" class="form-control selector-numerical-input selector_text_option_value_field selector_radio_childfield" data-parent="' . $attribute['parent'] . '" id="simple_edit_options_' . $attributeKey . '" name="' . $attributeKey . '" title="' . gT("inherited value:") . ' ' . $sParentOption . '" />
                                        </div>';
                }

                echo '</div>';

                if ($attribute['type'] == 'imagefile' || ($category == 'Images' && $attribute['type'] == 'dropdown')) {
                    echo '<div class="col-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="col-12">
                                    <button 
                                        class="btn btn-outline-secondary selector__open_lightbox" 
                                        data-bs-target="#simple_edit_options_' . $attributeKey .'"> ' . gT('Preview image') . '
                                    </button>
                                </div>
                            </div>';
                }

                $iTotalWidth += $width;
                $iCount      += 1;
            }
        }
        echo '</div>';
        echo '</div>';

        if ($category == 'Images') {
        ?>
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
                                <div id="upload_progress_frontend" class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                                    <span class="visually-hidden">0%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php } ?>

    </div>

<?php } ?>

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
                        <img class="selector__image img-fluid" src="" alt="title" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
