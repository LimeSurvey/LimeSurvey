<?php
    $yii = Yii::app();
    $controller = $yii->getController();
    $sConfirmLanguage="$(document).on('submit','#addnewsurvey',function(){\n"
                    . "  if(!UpdateLanguageIDs(mylangs,'".gT("All questions, answers, etc for removed languages will be lost. Are you sure?", "js")."')){\n"
                    . "    return false;\n"
                    . "  }\n"
                    . "});\n";
    Yii::app()->getClientScript()->registerScript('confirmLanguage',$sConfirmLanguage,CClientScript::POS_BEGIN);
?>
<div id='general'>
    <ul>
        <li>
            <label><?php $clang->eT("Base language:") ; ?></label>
            <?php echo getLanguageNameFromCode($esrow['language'],false) ?>
        </li>
        <li><label for='additional_languages'><?php $clang->eT("Additional Languages"); ?>:</label>
            <table><tr><td style='text-align:left'><select style='min-width:220px;' size='5' id='additional_languages' name='additional_languages'>
                            <?php $jsX=0;
                                $jsRemLang ="<script type=\"text/javascript\">
                                var mylangs = new Array();
                                standardtemplaterooturl='".$yii->getConfig('standardtemplaterooturl')."';
                                templaterooturl='".$yii->getConfig('usertemplaterooturl')."';\n";

                                foreach (Survey::model()->findByPk($surveyid)->additionalLanguages as $langname) {
                                    if ($langname && $langname != $esrow['language']) {
                                        $jsRemLang .=" mylangs[$jsX] = \"$langname\"\n"; ?>
                                    <option id='<?php echo $langname; ?>' value='<?php echo $langname; ?>'><?php echo getLanguageNameFromCode($langname,false); ?>
                                    </option>
                                    <?php $jsX++; ?>
                                    <?php }
                                }
                                $jsRemLang .= "</script>";
                            ?>

                        </select>
                        <?php echo $jsRemLang; ?>
                    </td>
                    <td style='text-align:left'><input type="button" value="<< <?php $clang->eT("Add"); ?>" onclick="DoAdd()" id="AddBtn" /><br /> <input type="button" value="<?php $clang->eT("Remove"); ?> >>" onclick="DoRemove(0,'')" id="RemoveBtn"  /></td>


                    <td style='text-align:left'><select size='5' style='min-width:220px;' id='available_languages' name='available_languages'>
                            <?php $tempLang=Survey::model()->findByPk($surveyid)->additionalLanguages;
                                foreach (getLanguageDataRestricted (false, Yii::app()->session['adminlang']) as $langkey2 => $langname) {
                                    if ($langkey2 != $esrow['language'] && in_array($langkey2, $tempLang) == false) {  // base languag must not be shown here ?>
                                    <option id='<?php echo $langkey2 ; ?>' value='<?php echo $langkey2; ?>'>
                                    <?php echo $langname['description']; ?></option>
                                    <?php }
                            } ?>
                        </select></td>
                </tr></table></li>


        <li><label for='admin'><?php $clang->eT("Administrator:"); ?></label>
            <input type='text' size='50' id='admin' name='admin' value="<?php echo htmlspecialchars($esrow['admin']); ?>" /></li>
        <li><label for='adminemail'><?php $clang->eT("Admin email:"); ?></label>
            <input type='email' size='50' id='adminemail' name='adminemail' value="<?php echo htmlspecialchars($esrow['adminemail']); ?>" /></li>
        <li><label for='bounce_email'><?php $clang->eT("Bounce email:"); ?></label>
            <input type='email' size='50' id='bounce_email' name='bounce_email' value="<?php echo htmlspecialchars($esrow['bounce_email']); ?>" /></li>
        <li><label for='faxto'><?php $clang->eT("Fax to:"); ?></label>
            <input type='text' size='50' id='faxto' name='faxto' value="<?php echo htmlspecialchars($esrow['faxto']); ?>" />
        </li>
 </ul>
</div>
