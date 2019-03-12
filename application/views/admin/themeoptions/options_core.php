<?php 
$bInherit = (!empty($aTemplateConfiguration['sid']) || !empty($aTemplateConfiguration['gsid']));
$animationOptions = 
    '<optgroup label="Attention Seekers">
        <option value="bounce">bounce</option>
        <option value="flash">
            flash</option>
        <option value="pulse">
            pulse</option>
        <option value="rubberBand">
            rubberBand</option>
        <option value="shake">
            shake</option>
        <option value="swing">
            swing</option>
        <option value="tada">
            tada</option>
        <option value="wobble">
            wobble</option>
        <option value="jello">
            jello</option>
    </optgroup>

    <optgroup label="Bouncing Entrances">
        <option value="bounceIn">bounceIn</option >
        <option value="bounceInDown">bounceInDown</option>
        <option value="bounceInLeft">
            bounceInLeft</option>
        <option value="bounceInRight">
            bounceInRight</option>
        <option value="bounceInUp">
            bounceInUp</option>
    </optgroup>

    <optgroup label="Bouncing Exits">
        <option value="bounceOut">bounceOut</option >
        <option value="bounceOutDown">bounceOutDown</option>
        <option value="bounceOutLeft">
            bounceOutLeft</option>
        <option value="bounceOutRight">
            bounceOutRight</option>
        <option value="bounceOutUp">
            bounceOutUp</option>
    </optgroup>

    <optgroup label="Fading Entrances">
        <option value="fadeIn">fadeIn</option >
        <option value="fadeInDown">fadeInDown</option>
        <option value="fadeInDownBig">
            fadeInDownBig</option>
        <option value="fadeInLeft">
            fadeInLeft</option>
        <option value="fadeInLeftBig">
            fadeInLeftBig</option>
        <option value="fadeInRight">
            fadeInRight</option>
        <option value="fadeInRightBig">
            fadeInRightBig</option>
        <option value="fadeInUp">
            fadeInUp</option>
        <option value="fadeInUpBig">
            fadeInUpBig</option>
    </optgroup>

    <optgroup label="Fading Exits">
        <option value="fadeOut">fadeOut</option >
        <option value="fadeOutDown">fadeOutDown</option>
        <option value="fadeOutDownBig">
            fadeOutDownBig</option>
        <option value="fadeOutLeft">
            fadeOutLeft</option>
        <option value="fadeOutLeftBig">
            fadeOutLeftBig</option>
        <option value="fadeOutRight">
            fadeOutRight</option>
        <option value="fadeOutRightBig">
            fadeOutRightBig</option>
        <option value="fadeOutUp">
            fadeOutUp</option>
        <option value="fadeOutUpBig">
            fadeOutUpBig</option>
    </optgroup>

    <optgroup label="Flippers">
        <option value="flip">flip</option >
        <option value="flipInX">flipInX</option>
        <option value="flipInY">
            flipInY</option>
        <option value="flipOutX">
            flipOutX</option>
        <option value="flipOutY">
            flipOutY</option>
    </optgroup>

    <optgroup label="Lightspeed">
        <option value="lightSpeedIn">lightSpeedIn</option >
        <option value="lightSpeedOut">lightSpeedOut</option>
    </optgroup>

    <optgroup label="Rotating Entrances">
        <option value="rotateIn">rotateIn</option >
        <option value="rotateInDownLeft">rotateInDownLeft</option>
        <option value="rotateInDownRight">
            rotateInDownRight</option>
        <option value="rotateInUpLeft">
            rotateInUpLeft</option>
        <option value="rotateInUpRight">
            rotateInUpRight</option>
    </optgroup>

    <optgroup label="Rotating Exits">
        <option value="rotateOut">rotateOut</option >
        <option value="rotateOutDownLeft">rotateOutDownLeft</option>
        <option value="rotateOutDownRight">
            rotateOutDownRight</option>
        <option value="rotateOutUpLeft">
            rotateOutUpLeft</option>
        <option value="rotateOutUpRight">
            rotateOutUpRight</option>
    </optgroup>

    <optgroup label="Sliding Entrances">
        <option value="slideInUp">slideInUp</option >
        <option value="slideInDown">slideInDown</option>
        <option value="slideInLeft">
            slideInLeft</option>
        <option value="slideInRight">
            slideInRight</option>
    </optgroup>

    <optgroup label="Sliding Exits">
        <option value="slideOutUp">slideOutUp</option >
        <option value="slideOutDown">slideOutDown</option>
        <option value="slideOutLeft">
            slideOutLeft</option>
        <option value="slideOutRight">
            slideOutRight</option>
    </optgroup>

    <optgroup label="Zoom Entrances">
        <option value="zoomIn">zoomIn</option >
        <option value="zoomInDown">zoomInDown</option>
        <option value="zoomInLeft">
            zoomInLeft</option>
        <option value="zoomInRight">
            zoomInRight</option>
        <option value="zoomInUp">
            zoomInUp</option>
    </optgroup>

    <optgroup label="Zoom Exits">
        <option value="zoomOut">zoomOut</option >
        <option value="zoomOutDown">zoomOutDown</option>
        <option value="zoomOutLeft">
            zoomOutLeft</option>
        <option value="zoomOutRight">
            zoomOutRight</option>
        <option value="zoomOutUp">
            zoomOutUp</option>
    </optgroup>

    <optgroup label="Specials">
        <option value="hinge">hinge</option >
        <option value="jackInTheBox">jackInTheBox</option>
        <option value="rollIn">
            rollIn</option>
        <option value="rollOut">
            rollOut</option>
    </optgroup>';

    $dropdown_options['cssframework'] = ($bInherit ? '<option value="inherit">' . gT("Inherit") . ' [' . gT("inherited value:") . ' ' . (isset($oParentOptions['cssframework']) ? $oParentOptions['cssframework'] : '') . ']</option>' : '') . '
        <option value="css/variations/sea_green.css">Sea Green</option>
        <option value="css/variations/apple_blossom.css">Apple Blossom</option>
        <option value="css/variations/bay_of_many.css">Bay of Many</option>
        <option value="css/variations/black_pearl.css">Black Pearl</option>
        <option value="css/variations/free_magenta.css">Free Magenta</option>
        <option value="css/variations/purple_tentacle.css">Purple Tentacle</option>
        <option value="css/variations/sunset_orange.css">Sunset Orange</option>
        <option value="css/variations/skyline_blue.css">Skyline Blue</option>';

    $dropdown_options['checkicon'] = ($bInherit ? '<option value = "inherit" > Inherit</option>' : '') . '
        <option value="f00c"> <i class="fa fa-check"></i> Check </option>
        <option value="f058"> <i class="fa fa-check-circle"></i> Check circle </option>
        <option value="f14a"> <i class="fa fa-check-square"></i> Check square </option>
        <option value="f111"> <i class="fa fa-circle"></i> Circle </option>
        <option value="f067"> <i class="fa fa-plus"></i> Plus </option>
        <option value="f0c8"> <i class="fa fa-square"></i> Square </option>
        <option value="f005"> <i class="fa fa-star"></i> Star </option>
        <option value="f00d"> <i class="fa fa-times"></i> Times </option>
        <option value="f069"> <i class="fa fa-asterisk"></i> Asterisk </option>
        <option value="f061"> <i class="fa fa-arrow-right"></i> Arrow right </option>
        <option value="f138"> <i class="fa fa-chevron-circle-right"></i> Chevron circle right </option>
        <option value="f1d0"> <i class="fa fa-resistance"></i> Resistance </option>';

    $dropdown_options['font'] = ($bInherit ? '<option value="inherit">' . gT("Inherit") . ' [' . gT("inherited value:") . ' ' . (isset($oParentOptions['font']) ? $oParentOptions['font'] : '') . ']</option>' : '');
    
    $fontPackages = App()->getClientScript()->fontPackages;
    $coreFontPackages = $fontPackages['core'];
    $userFontPackages = $fontPackages['user'];

    // generate CORE fonts package list
    $i = 0;
    foreach($coreFontPackages as $coreKey => $corePackage){
        $i+=1;
        if ($i === 1){
            $dropdown_options['font'] .='<optgroup  label="' . gT("Local Server") . ' - ' . gT("Core") . '">';
        }
        $dropdown_options['font'] .='<option class="font-' . $coreKey . '"     value="' . $coreKey . '"     data-font-package="' . $coreKey . '"      >' . $corePackage['title'] . '</option>';
    }
    if ($i > 0){
        $dropdown_options['font'] .='</optgroup>';
    }

    // generate USER fonts package list
    $i = 0;
    foreach($userFontPackages as $userKey => $userPackage){
        $i+=1;
        if ($i === 1){
            $dropdown_options['font'] .='<optgroup  label="' . gT("Local Server") . ' - ' . gT("User") . '">';
        }
        $dropdown_options['font'] .='<option class="font-' . $userKey . '"     value="' . $userKey . '"     data-font-package="' . $userKey . '"      >' . $userPackage['title'] . '</option>';
    }
    if ($i > 0){
        $dropdown_options['font'] .='</optgroup>';
    }

    $dropdown_options['font'] .='<optgroup  label="' . gT("User browser") . '">
        <option class="font-georgia         " value="georgia"         data-font-package="websafe" >Georgia</option>
        <option class="font-palatino        " value="palatino"        data-font-package="websafe" >Palatino Linotype</option>
        <option class="font-times_new_roman " value="times_new_roman" data-font-package="websafe" >Times New Roman</option>
        <option class="font-arial           " value="arial"           data-font-package="websafe" >Arial</option>
        <option class="font-arial_black     " value="arial_black"     data-font-package="websafe" >Arial Black</option>
        <option class="font-comic_sans      " value="comic_sans"      data-font-package="websafe" >Comic Sans</option>
        <option class="font-impact          " value="impact"          data-font-package="websafe" >Impact</option>
        <option class="font-lucida_sans     " value="lucida_sans"     data-font-package="websafe" >Lucida Sans</option>
        <option class="font-trebuchet       " value="trebuchet"       data-font-package="websafe" >Trebuchet</option>
        <option class="font-courier         " value="courier"         data-font-package="websafe" >Courier New</option>
        <option class="font-lucida_console  " value="lucida_console"  data-font-package="websafe" >Lucida Console</option>
    </optgroup>';

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

    if ($bInherit){
        $backgroundfileOptionsInherit = '<option data-lightbox-src="' . $backgroundfileInheritPreview . '" value="inherit">' . gT("Inherit") . ' [' . gT("inherited value:") . ' ' . ']</option>';
    }
    $dropdown_options['backgroundimagefile'] = $backgroundfileOptionsInherit . $backgroundImageFile;


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

    if ($bInherit){
        $logofileOptionsInherit = '<option data-lightbox-src="' . $logofileInheritPreview . '" value="inherit">' . gT("Inherit") . ' [' . gT("inherited value:") . ' ' . ']</option>';
    }
    $dropdown_options['brandlogofile'] = $logofileOptionsInherit . $brandlogo;
    $dropdown_options['bodyanimation'] = ($bInherit ? '<option value = "inherit" > Inherit</option>' : '') . $animationOptions;
    $dropdown_options['questionanimation'] = ($bInherit ? '<option value = "inherit" > Inherit</option>' : '') . $animationOptions;
    $dropdown_options['alertanimation'] = ($bInherit ? '<option value = "inherit" > Inherit</option>' : '') . $animationOptions;
    $dropdown_options['checkboxanimation'] = ($bInherit ? '<option value = "inherit" > Inherit</option>' : '') . $animationOptions;
    $dropdown_options['radioanimation'] = ($bInherit ? '<option value = "inherit" > Inherit</option>' : '') . $animationOptions;


    foreach($aOptionAttributes['categories'] as $key => $category){ ?>
        <div role="tabpanel" class="tab-pane  <?php echo $key == 0 ? 'active' : ''; ?>" id="category-<?php echo $key; ?>">
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
                        if ($category == $attribute['category']){
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
                                $test = 1;
                                echo ' <div class="col-sm-12">
                                <select class="form-control selector_option_value_field selector_radio_childfield selector_image_selector" data-parent="' . $attribute['parent'] . '" data-inheritvalue=\'' . ($attributeKey == 'font' && isset($sPackagesToLoad) ? $sPackagesToLoad : $sParentOption) . '\' id="simple_edit_options_' . $attributeKey . '" name="' . $attributeKey . '"  >';
                                    echo $dropdown_options[$attributeKey];
                                echo '</select>
                                    </div>';

                            } elseif ($attribute['type'] == 'icon'){
                                echo ' <div class="col-sm-12 input-group">
                                <select class="selector_option_value_field form-control simple_edit_options_checkicon" data-parent="' . $attribute['parent'] . '" id="simple_edit_options_' . $attributeKey . '" name="' . $attributeKey . '" >';
                                    echo $dropdown_options[$attributeKey];
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
