<?php
    $yii = Yii::app();
    $controller = $yii->getController();
?>
<div id='general'>
    <ul>
        <li>
            <label><?php eT("Base language:") ; ?></label>
            <?php echo getLanguageNameFromCode($esrow['language'],false) ?>
        </li>
        <li><label for='additional_languages'><?php eT("Additional Languages"); ?>:</label>
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
                    <td style='text-align:left'><input type="button" value="<< <?php eT("Add"); ?>" onclick="DoAdd()" id="AddBtn" /><br /> <input type="button" value="<?php eT("Remove"); ?> >>" onclick="DoRemove(0,'')" id="RemoveBtn"  /></td>


                    <td style='text-align:left'><select size='5' style='min-width:220px;' id='available_languages' name='available_languages'>
                            <?php

                                $tempLang=Survey::model()->findByPk($surveyid)->additionalLanguages;
                                foreach (App()->locale->getLocaleIDs() as $locale)
                                {
                                    if ($locale != $esrow['language'])
                                    {
                                        $language = $locale . ': '. App()->locale->getLanguage($locale) . ' - ' . App()->locale->getLocaleDisplayName($locale);
                                        echo CHtml::tag('option', array(
                                            'id' => $locale,
                                            'value' => $locale
                                        ), $language);
                                    }
                                }
                            ?>
                        </select></td>
                </tr></table></li>


        <li><label for='admin'><?php eT("Administrator:"); ?></label>
            <input type='text' size='50' id='admin' name='admin' value="<?php echo htmlspecialchars($esrow['admin']); ?>" /></li>
        <li><label for='adminemail'><?php eT("Admin email:"); ?></label>
            <input type='email' size='50' id='adminemail' name='adminemail' value="<?php echo htmlspecialchars($esrow['adminemail']); ?>" /></li>
        <li><label for='bounce_email'><?php eT("Bounce email:"); ?></label>
            <input type='email' size='50' id='bounce_email' name='bounce_email' value="<?php echo htmlspecialchars($esrow['bounce_email']); ?>" /></li>
        <li><label for='faxto'><?php eT("Fax to:"); ?></label>
            <input type='text' size='50' id='faxto' name='faxto' value="<?php echo htmlspecialchars($esrow['faxto']); ?>" />
        </li>
 </ul>
</div>
