    <br /><div class='messagebox ui-corner-all'>
        <div class='header ui-widget-header'><?php eT("queXML PDF export"); echo " ($surveyid)" ;?></div>
             <?php echo CHtml::form(array("admin/survey/sa/quexml/surveyid/{$surveyid}/"), 'post', array('class'=>'form44')); ?>
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
    </div><br />&nbsp;
