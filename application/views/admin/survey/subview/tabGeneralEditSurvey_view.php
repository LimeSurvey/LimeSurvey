<div id='general'>
<ul>
    <li>
    <label><?php echo $clang->gT("Base language:") ; ?></label>
    <?php echo GetLanguageNameFromCode($esrow['language'],false) ?>
    </li>
    <li><label for='additional_languages'><?php echo $clang->gT("Additional Languages"); ?>:</label>
            <table><tr><td align='left'><select style='min-width:220px;' size='5' id='additional_languages' name='additional_languages'>
            <?php $jsX=0;
            $jsRemLang ="<script type=\"text/javascript\">
                        var mylangs = new Array();
                        standardtemplaterooturl='".$this->config->item('standardtemplaterooturl')."';
                        templaterooturl='".$this->config->item('usertemplaterooturl')."';\n";

            foreach (GetAdditionalLanguagesFromSurveyID($surveyid) as $langname) {
                if ($langname && $langname != $esrow['language']) {
                $jsRemLang .=" mylangs[$jsX] = \"$langname\"\n"; ?>
                <option id='<?php echo $langname; ?>' value='<?php echo $langname; ?>'><?php echo getLanguageNameFromCode($langname,false); ?>
                </option>
                <?php $jsX++; ?>
               <?php }
            }
            $jsRemLang .= "</script>";
            echo $jsRemLang; ?>

            </select></td>
            <td align='left'><input type="button" value="<< <?php echo $clang->gT("Add"); ?>" onclick="DoAdd()" id="AddBtn" /><br /> <input type="button" value="<?php echo $clang->gT("Remove"); ?> >>" onclick="DoRemove(0,'')" id="RemoveBtn"  /></td>


            <td align='left'><select size='5' style='min-width:220px;' id='available_languages' name='available_languages'>
            <?php $tempLang=GetAdditionalLanguagesFromSurveyID($surveyid);
            foreach (getLanguageData () as $langkey2 => $langname) {
                if ($langkey2 != $esrow['language'] && in_array($langkey2, $tempLang) == false) {  // base languag must not be shown here ?>
                    <option id='<?php echo $langkey2 ; ?>' value='<?php echo $langkey2; ?>'>
                    <?php echo $langname['description']; ?></option>
                <?php }
            } ?>
            </select></td>
             </tr></table></li>


            <li><label for='admin'><?php echo $clang->gT("Administrator:"); ?></label>
            <input type='text' size='50' id='admin' name='admin' value="<?php echo $esrow['admin']; ?>" /></li>
            <li><label for='adminemail'><?php echo $clang->gT("Admin email:"); ?></label>
            <input type='text' size='50' id='adminemail' name='adminemail' value="<?php echo $esrow['adminemail']; ?>" /></li>
            <li><label for='bounce_email'><?php echo $clang->gT("Bounce email:"); ?></label>
            <input type='text' size='50' id='bounce_email' name='bounce_email' value="<?php echo $esrow['bounce_email']; ?>" /></li>
            <li><label for='faxto'><?php echo $clang->gT("Fax to:"); ?></label>
            <input type='text' size='50' id='faxto' name='faxto' value="<?php echo $esrow['faxto']; ?>" />
        </li>
    </ul>
</div>
