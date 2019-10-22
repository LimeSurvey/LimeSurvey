<?php 
$bInherit = (!empty($aTemplateConfiguration['sid']) || !empty($aTemplateConfiguration['gsid']));



    $dropdown_options['font'] = ($bInherit ? '<option value="inherit">' . gT("Inherit") . ' [' . gT("inherited value:") . ' ' . (isset($oParentOptions['font']) ? $oParentOptions['font'] : '') . ']</option>' : '');
    
    
    // background file
    $backgroundImageFile = '';
    $backgroundfileOptionsInherit = '';
    $backgroundfileInheritPreview = '';
    $backgroundfileInheritFilename = '';
    $optgroup = '';
    foreach($aTemplateConfiguration['imageFileList'] as $image){
        if ($image['group'] != $optgroup){
            if ($optgroup != ''){
                $backgroundImageFile .=  '</optgroup>';
            }
            $backgroundImageFile .= '<optgroup label="' . $image['group'] . '">';
            $optgroup = $image['group'];
        }

        $backgroundImageFile .= '</optgroup>';
        if (isset($oParentOptions['backgroundimagefile']) && $oParentOptions['backgroundimagefile'] == $image['filepath']){ 
            $backgroundfileInheritPreview = $backgroundimagefileInheritPreview . $image['preview'];
            $backgroundfileInheritFilename = $backgroundimagefileInheritFilename . $image['filename']; 
        }
        $backgroundImageFile .=  '<option data-lightbox-src="' . $image['preview'] . '" value="' . $image['filepath'] . '">' . $image['filename'] . '</option>';
    }

    $aOptionAttributes['optionAttributes']['backgroundimagefile']['dropdownoptions'] = $backgroundImageFile;

    // brand logo file
    $brandlogo = '';
    $logofileOptionsInherit = '';
    $logofileInheritPreview = '';
    $logofileInheritFilename = '';
    $optgroup = '';
    foreach($aTemplateConfiguration['imageFileList'] as $image){
        if ($image['group'] != $optgroup){
            if ($optgroup != ''){
                $brandlogo .=  '</optgroup>';
            }
            $brandlogo .= '<optgroup label="' . $image['group'] . '">';
            $optgroup = $image['group'];
        }

        $brandlogo .= '</optgroup>';
        if ($oParentOptions['brandlogo'] == $image['filepath']){ 
            $logofileInheritPreview = $logofileInheritPreview . $image['preview'];
            $logofileInheritFilename = $logofileInheritFilename . $image['filename']; 
        }
        $brandlogo .=  '<option data-lightbox-src="' . $image['preview'] . '" value="' . $image['filepath'] . '">' . $image['filename'] . '</option>';
    }

    $aOptionAttributes['optionAttributes']['brandlogofile']['dropdownoptions'] = $brandlogo;

    foreach($aOptionAttributes['categories'] as $key => $category){ ?>
        <div role="tabpanel" class="CoreThemeOptions--settingsTab tab-pane  <?php echo $key == 0 ? 'active' : ''; ?>" id="category-<?php echo $key; ?>">
            <div class="container-fluid" style="position:relative">
                <?php if ($key == 0){ ?>
                    <?php /* Small loading animation to give the scripts time to parse and render the correct values */ ?>
                    <div class="" style="display:none;height:100%;width:100%;position:absolute;left:0;top:0;background:rgb(255,255,255);background:rgba(235,235,235,0.8);z-index:2000;">
                        <div style="position:absolute; left:49%;top:35%;" class="text-center">
                            <i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>
                        </div>
                    </div>
                    <?php /* If this is a surveyspecific settings page, offer the possibility to do a full inheritance of the parent template */
                    if ($bInherit){ ?>
                    <div class='row' id="general_inherit_active">
                        <div class='form-group row'>
                            <label for='simple_edit_options_general_inherit' class='control-label'><?php echo gT("Inherit everything" ); ?></label>
                            <div class='col-sm-12'>
                                <div class="btn-group" data-toggle="buttons">
                                    <label class="btn btn-default">
                                        <input id="general_inherit_on" name='general_inherit' type='radio' value='on' class='selector_option_general_inherit ' data-id='simple_edit_options_general_inherit'/>
                                        <?php echo gT("Yes"); ?>
                                    </label>
                                    <label class="btn btn-default">
                                        <input id="general_inherit_off" name='general_inherit' type='radio' value='off' class='selector_option_general_inherit ' data-id='simple_edit_options_general_inherit'/>
                                        <?php echo gT("No"); ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <?php } ?>                 
                    
                <?php } ?>

                <?php
                    // options
                    $iMaxColumnSize = 12;
                    $iTotalWidth = 0;
                    $iCount = 0;
                    foreach($aOptionAttributes['optionAttributes'] as $attributeKey => $attribute){
                        $sParentOption =  array_key_exists($attributeKey, $oParentOptions) ? $oParentOptions[$attributeKey] : '';
                        if ($attributeKey === 'ajaxmode') {
                            continue;
                        }
                        if (array_key_exists('category', $attribute) &&  $category == $attribute['category']){
                            $width = $attribute['width'];

                            if (($iTotalWidth + $width) > $iMaxColumnSize){
                                $iTotalWidth = 0;
                            }
                            
                            if ($iTotalWidth == 0){
                                if ($iCount > 0) {
                                    echo '</div>';
                                }
                                echo '<div class="row action_hide_on_inherit">';
                            }

                            echo '<div class="col-xs-12 col-sm-6 col-md-4 col-lg-' . $attribute['width'] . '">
                            <div class="form-group row">
                                <label for="simple_edit_options_' . $attributeKey . '" class="control-label">' . gT($attribute['title']) . '</label>';
                            if ($attribute['type'] == 'buttons'){
                                $optionsValues = !empty($attribute['options']) ? explode('|', $attribute['options']) : array();
                                $optionLabels = !empty($attribute['optionlabels']) ? explode('|', $attribute['optionlabels']) : array();
                                $options = array_combine($optionsValues, $optionLabels);
                                if ($bInherit && isset($sParentOption)){
                                    $options['inherit'] = gT("Inherit") . ' [' . $sParentOption . ']';
                                }

                                echo '<div class="col-sm-12">
                                        <div class="btn-group" data-toggle="buttons">';
                                        foreach($options as $optionKey =>$optionValue){
                                            echo '<label class="btn btn-default">
                                                <input type="radio" name="' . $attributeKey .'" value="' . $optionKey .'" class="selector_option_radio_field simple_edit_options_' . $attributeKey .' " id="' . $attributeKey .'"/>'
                                                . gT($optionValue) . '
                                            </label>';
                                            }
                                echo '</div>
                                </div>';
                            } elseif ($attribute['type'] == 'colorpicker'){
                                echo '<div class="input-group">
                                    <div class="input-group-addon style__colorpicker">
                                        <input type="color" name="' . $attributeKey . '_picker" data-value="' . $sParentOption . '" class="selector__colorpicker-inherit-value"/>
                                    </div>
                                    <input type="text" name="' . $attributeKey . '" data-inheritvalue="' . $sParentOption . '" value="inherit" class="selector_option_value_field selector__color-picker form-control simple_edit_options_' . $attributeKey . '" id="' . $attributeKey . '" />';
                                    if ($bInherit && isset($sParentOption)){
                                        echo '<div class="input-group-addon">
                                            <button class="btn btn-default btn-xs selector__reset-colorfield-to-inherit"><i class="fa fa-refresh"></i></button>
                                        </div>';
                                    }
                                echo '</div>';
                            } elseif ($attribute['type'] == 'dropdown'){
                                echo ' <div class="col-sm-12">
                                <select class="form-control selector_option_value_field selector_radio_childfield selector_image_selector" data-parent="' . $attribute['parent'] . '" data-inheritvalue=\'' . ($attributeKey == 'font' && isset($sPackagesToLoad) ? $sPackagesToLoad : $sParentOption) . '\' id="simple_edit_options_' . $attributeKey . '" name="' . $attributeKey . '"  >';
                                if ($bInherit){
                                    if ($attributeKey == 'backgroundimagefile'){
                                        $inheritedValue = isset($backgroundfileInheritPreview) ? $backgroundfileInheritPreview : '';
                                    } elseif ($attributeKey == 'backgroundimagefile'){
                                        $inheritedValue = isset($logofileInheritPreview) ? $logofileInheritPreview : '';
                                    } else {
                                        $inheritedValue = isset($sParentOption) ? $sParentOption : '';
                                    }
                                    echo '<option value="inherit">' . gT("Inherit") . ' [' . gT("inherited value:") . ' ' . $inheritedValue . ']</option>';
                                }
                                // dropdown options from config.xml file
                                echo $aOptionAttributes['optionAttributes'][$attributeKey]['dropdownoptions'];
                                echo '</select>
                                    </div>';

                            } elseif ($attribute['type'] == 'icon'){
                                echo ' <div class="col-sm-12 input-group">
                                <select class="selector_option_value_field form-control simple_edit_options_checkicon" data-parent="' . $attribute['parent'] . '" id="simple_edit_options_' . $attributeKey . '" name="' . $attributeKey . '" >';
                                if ($bInherit){
                                    echo '<option value="inherit">' . gT("Inherit") . ' [' . gT("inherited value:") . ' ' . (isset($sParentOption) ? $sParentOption : '') . ']</option>';
                                }
                                // dropdown options from config.xml file
                                echo $aOptionAttributes['optionAttributes'][$attributeKey]['dropdownoptions'];
                                echo '</select>
                                        <div class="input-group-addon selector__' . $attributeKey . '-preview">
                                        ( <i class="fa" data-inheritvalue="' . $sParentOption . '" style=" background-color: #328637; color: white; width: 16px; height: 16px;  padding: 3px; font-size: 11px; ">
                                            &#x' . $sParentOption . ';
                                        </i> )
                                    </div>
                                    </div>';

                            } elseif ($attribute['type'] == 'input'){

                            } elseif ($attribute['type'] == 'duration'){
                                echo '<div class="col-sm-12">
                                            <input type="text" class="form-control selector-numerical-input selector_option_value_field selector_radio_childfield" data-parent="' . $attribute['parent'] . '" id="simple_edit_options_' . $attributeKey . '" name="' . $optionKey .'" title="' . gT("inherited value:") . ' ' . $sParentOption . '" />
                                        </div>';
                            }
                            
                            echo '</div>
                            </div>';

                            if ($category == 'Images' && $attribute['type'] == 'dropdown'){
                                echo '<div class="col-sm-4 col-md-2">
                                <br/>
                                <button class="btn btn-default selector__open_lightbox" data-target="#simple_edit_options_' . $attributeKey .'"> ' . gT('Preview image') . '</button>
                            </div>';
                            }

                            $iTotalWidth += $width;
                            $iCount += 1;
                        }
                    }
                    echo '</div>';
                ?>

                </div>
                
            

        </div>

    <?php } ?>

<div class="modal fade" tabindex="-1" role="dialog" id="lightbox-modal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title selector__title"> </h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-xs-12">
                            <img class="selector__image img-responsive" src="" alt="title"  />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
