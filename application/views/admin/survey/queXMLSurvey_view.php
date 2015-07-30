    <br /><div class='messagebox ui-corner-all' style="width: 100%;">
        <div class='header ui-widget-header'><?php eT("queXML PDF export"); echo " ($surveyid)" ;?></div>
             <?php echo CHtml::form(array("admin/export/sa/quexml/surveyid/{$surveyid}/"), 'post', array('class'=>'form44')); ?>
            <ul>
                <li><label for='save_language'><?php eT("Language selection"); ?></label>
                    <select name='save_language'>
                    <?php foreach ($slangs as $lang)
                    {
                        if ($lang == $baselang) { ?>
                           <option value='<?php echo $lang; ?>' selected='selected'><?php echo getLanguageNameFromCode($lang,false); ?></option>
                        <?php }
                        else { ?>
                            <option value='<?php echo $lang; ?>'><?php echo getLanguageNameFromCode($lang,false); ?></option>
                            <?php }
                    } ?>
                    </select>
                </li>
                
                 <li><label for='queXMLStyle'><?php eT("Style:"); ?></label>
            
            <textarea rows="10" cols="80" id='queXMLStyle' name='queXMLStyle'><?php echo $queXMLStyle; ?> </textarea>
        </li>


        <li><label for='queXMLAllowSplittingSingleChoiceHorizontal'><?php eT("Allow array style questions to be split over multiple pages"); ?></label>
            <select id='queXMLAllowSplittingSingleChoiceHorizontal' name='queXMLAllowSplittingSingleChoiceHorizontal'>
                <option value=1
                <?php if ($queXMLAllowSplittingSingleChoiceHorizontal == 1) { ?>
                            selected='selected'
                        <?php } ?>
                        ><?php eT("Yes"); ?></option>
                <option value=0
                <?php if ($queXMLAllowSplittingSingleChoiceHorizontal == 0) { ?>
                            selected='selected'
                        <?php } ?>
                        ><?php eT("No"); ?></option>
            </select>           

        </li>

        <li><label for='queXMLAllowSplittingSingleChoiceVertical'><?php eT("Allow single choice questions to be split over multiple pages"); ?></label>

            <select id='queXMLAllowSplittingSingleChoiceVertical' name='queXMLAllowSplittingSingleChoiceVertical'>
                <option value=1
                <?php if ($queXMLAllowSplittingSingleChoiceVertical == 1) { ?>
                            selected='selected'
                        <?php } ?>
                        ><?php eT("Yes"); ?></option>
                <option value=0
                <?php if ($queXMLAllowSplittingSingleChoiceVertical == 0) { ?>
                            selected='selected'
                        <?php } ?>
                        ><?php eT("No"); ?></option>
            </select> 

        </li>

        <li><label for='queXMLAllowSplittingMatrixText'><?php eT("Allow multiple short text / numeric questions to be split over multiple pages"); ?></label>

            <select id='queXMLAllowSplittingMatrixText' name='queXMLAllowSplittingMatrixText'>
                <option value=1
                <?php if ($queXMLAllowSplittingMatrixText == 1) { ?>
                            selected='selected'
                        <?php } ?>
                        ><?php eT("Yes"); ?></option>
                <option value=0
                <?php if ($queXMLAllowSplittingMatrixText == 0) { ?>
                            selected='selected'
                        <?php } ?>
                        ><?php eT("No"); ?></option>
            </select> 


        </li>


        <li><label for='queXMLAllowSplittingVas'><?php eT("Allow slider questions to be split over multiple pages"); ?></label>

            <select id='queXMLAllowSplittingVas' name='queXMLAllowSplittingVas'>
                <option value=1
                <?php if ($queXMLAllowSplittingVas == 1) { ?>
                            selected='selected'
                        <?php } ?>
                        ><?php eT("Yes"); ?></option>
                <option value=0
                <?php if ($queXMLAllowSplittingVas == 0) { ?>
                            selected='selected'
                        <?php } ?>
                        ><?php eT("No"); ?></option>
            </select>  


        </li>

        <li><label for='queXMLSingleResponseAreaHeight'><?php eT("Minimum height of single choice answer boxes"); ?></label>
            <input type='text' size='10' id='queXMLSingleResponseAreaHeight' name='queXMLSingleResponseAreaHeight' value="<?php echo $queXMLSingleResponseAreaHeight; ?>" />
        </li>

        <li><label for='queXMLSingleResponseHorizontalHeight'><?php eT("Minimum height of subquestion items"); ?></label>
            <input type='text' size='10' id='queXMLSingleResponseHorizontalHeight' name='queXMLSingleResponseHorizontalHeight' value="<?php echo $queXMLSingleResponseHorizontalHeight; ?>" />
        </li>

        <li><label for='queXMLQuestionnaireInfoMargin'><?php eT("Margin before questionnaireInfo element (mm)"); ?></label>
            <input type='text' size='10' id='queXMLQuestionnaireInfoMargin' name='queXMLQuestionnaireInfoMargin' value="<?php echo $queXMLQuestionnaireInfoMargin; ?>" />
        </li>

        <li><label for='queXMLResponseTextFontSize'><?php eT("Answer option / subquestion font size"); ?></label>
            <input type='text' size='10' id='queXMLResponseTextFontSize' name='queXMLResponseTextFontSize' value="<?php echo $queXMLResponseTextFontSize; ?>" />
        </li>

        <li><label for='queXMLResponseLabelFontSize'><?php eT("Answer label font size (normal)"); ?></label>
            <input type='text' size='10' id='queXMLResponseLabelFontSize' name='queXMLResponseLabelFontSize' value="<?php echo $queXMLResponseLabelFontSize; ?>" />
        </li>

        <li><label for='queXMLResponseLabelFontSizeSmall'><?php eT("Answer label font size (small)"); ?></label>
            <input type='text' size='10' id='queXMLResponseLabelFontSizeSmall' name='queXMLResponseLabelFontSizeSmall' value="<?php echo $queXMLResponseLabelFontSizeSmall; ?>" />
        </li>

        <li><label for='queXMLSectionHeight'><?php eT("Minimum section height (mm)"); ?></label>
            <input type='text' size='10' id='queXMLSectionHeight' name='queXMLSectionHeight' value="<?php echo $queXMLSectionHeight; ?>" />
        </li>


        <li><label for='queXMLBackgroundColourSection'><?php eT("Background colour for sections (0 black - 255 white)"); ?></label>
            <input type='text' size='10' id='queXMLBackgroundColourSection' name='queXMLBackgroundColourSection' value="<?php echo $queXMLBackgroundColourSection; ?>" />
        </li>                

                <li><label for='queXMLBackgroundColourQuestion'><?php eT("Background colour for questions (0 black - 255 white)"); ?></label>
                    <input type='text' size='10' id='queXMLBackgroundColourQuestion' name='queXMLBackgroundColourQuestion' value="<?php echo $queXMLBackgroundColourQuestion; ?>" />
                </li>

                <li><label for='queXMLPageOrientation'><?php eT("Page orientation"); ?></label>
                    <select id='queXMLPageOrientation' name='queXMLPageOrientation'>
                        <option value='P'
                            <?php if ($queXMLPageOrientation == "P") { ?>
                                selected='selected'
                                <?php } ?>
                            ><?php eT("Portrait"); ?></option>
                        <option value='L'
                            <?php if ($queXMLPageOrientation == "L") { ?>
                                selected='selected'
                                <?php } ?>
                            ><?php eT("Landscape"); ?></option>
                    </select>
                </li>


               <li><label for='queXMLPageFormat'><?php eT("Page format"); ?></label>
                    <select id='queXMLPageFormat' name='queXMLPageFormat'>
                        <option value='A4'
                            <?php if ($queXMLPageFormat == "A4") { ?>
                                selected='selected'
                                <?php } ?>
                            ><?php eT("A4"); ?></option>
                        <option value='USLETTER'
                            <?php if ($queXMLPageFormat == "USLETTER") { ?>
                                selected='selected'
                                <?php } ?>
                            ><?php eT("US Letter"); ?></option>
                        <option value='A3'
                            <?php if ($queXMLPageFormat == "A3") { ?>
                                selected='selected'
                                <?php } ?>
                            ><?php eT("A3"); ?></option>
                    </select>
                </li>

              <li><label for='queXMLEdgeDetectionFormat'><?php eT("Edge detection format"); ?></label>
                    <select id='queXMLEdgeDetectionFormat' name='queXMLEdgeDetectionFormat'>
                        <option value='lines'
                            <?php if ($queXMLEdgeDetectionFormat == "lines") { ?>
                                selected='selected'
                                <?php } ?>
                            ><?php eT("Corner lines"); ?></option>
                        <option value='boxes'
                            <?php if ($queXMLEdgeDetectionFormat == "boxes") { ?>
                                selected='selected'
                                <?php } ?>
                            ><?php eT("Corner boxes"); ?></option>
                   </select>
                </li>

            <input type='hidden' name='ok' value='Y' />
            <input type='submit' value="<?php eT("queXML PDF export"); ?>" />
        </form>
             <?php echo CHtml::form(array("admin/export/sa/quexmlclear/surveyid/{$surveyid}/"), 'post', array('class'=>'form44'));
            echo CHtml::htmlButton(gT('Reset to default settings'),array('type'=>'submit'));?>
        </form>
    </div><br />&nbsp;
