<?php
/**
 *
 * Manage token attribute fields/ Add or delete token attributes
 * @var AdminController $this
 * @var Survey $oSurvey
 */
?>
<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <?php if( count($tokenfieldlist)) : ?>
        <h3><?php eT("Manage attribute fields"); ?></h3>

        <div class="row">
            <div class="col-lg-12 content-right">
                <?php echo CHtml::form(array("admin/tokens/sa/updatetokenattributedescriptions/surveyid/{$surveyid}"), 'post'); ?>
                <div>
                    <ul class="nav nav-tabs">
                        <?php $c=true; ?>
                        <?php foreach ($oSurvey->allLanguages as $sLanguage) {
                            $sTabTitle = getLanguageNameFromCode($sLanguage, false);
                            if ($sLanguage == $oSurvey->language) {
                                $sTabTitle .= ' (' . gT("Base language") . ')';
                            }
                            ?>
                            <li <?php if ($c){$c=false; echo 'class="active"'; }?> >
                                <a data-toggle="tab" href="#language_<?php echo $sLanguage ?>"><?php echo $sTabTitle; ?></a>
                            </li>
                            <?php } ?>
                    </ul>

                    <div class="tab-content">
                        <?php $c=true;?>
                        <?php foreach ($oSurvey->allLanguages as $sLanguage) { ?>
                            <div id="language_<?php echo $sLanguage ?>"  class="tab-pane fade in <?php if ($c){$c=false; echo 'active'; }?>">
                                <table class='listtokenattributes table'>
                                    <thead> <tr>
                                        <th><?php eT("Attribute field"); ?></th>
                                        <th><?php eT("Field description"); ?></th>
                                        <th><?php eT("Mandatory?"); ?></th>
                                        <th><?php eT("Show during registration?") ?></th>
                                        <th><?php eT("Field caption"); ?></th>
                                        <th><?php eT("CPDB mapping"); ?></th>
                                        <th><?php eT("Example data"); ?></th>
                                    </tr> </thead>
                                    <tbody>
                                    <?php $nrofattributes = 0;
                                    foreach ($tokenfields as $sTokenField) {
                                        if (isset($tokenfielddata[$sTokenField]))
                                            $tokenvalues = $tokenfielddata[$sTokenField];
                                        else
                                            $tokenvalues = array('description' => '','mandatory' => 'N','show_register' => 'N','cpdbmap'=>'');
                                        $nrofattributes++;
                                        echo "
                                        <tr>
                                        <td>{$sTokenField}</td>";
                                        if ($sLanguage == $oSurvey->language)
                                        { ?>
                                            <td><input type='text' name='description_<?php echo $sTokenField; ?>' class='form-control' value='<?php echo htmlspecialchars($tokenvalues['description'], ENT_QUOTES, 'UTF-8'); ?>' /></td>
                                            <td>
                                                <?php
                                                $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                                    'name' => "mandatory_{$sTokenField}",
                                                    'id'=>"mandatory_{$sTokenField}",
                                                    'value' => $tokenvalues['mandatory']=='Y'?'1':'0',
                                                    'onLabel'=>gT('On'),
                                                    'offLabel' => gT('Off')));
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                                    'name' => "show_register_{$sTokenField}",
                                                    'id'=>"show_register_{$sTokenField}",
                                                    'value' => $tokenvalues['show_register']=='Y'?'1':'0',
                                                    'onLabel'=>gT('On'),
                                                    'offLabel' => gT('Off')));
                                                ?>
                                            </td>
                                            <?php
                                        }
                                        else
                                        {
                                            echo "
                                            <td>", htmlspecialchars($tokenvalues['description'], ENT_QUOTES, 'UTF-8'), "</td>
                                            <td>", $tokenvalues['mandatory'] == 'Y' ? eT('Yes') : eT('No'), "</td>
                                            <td>", $tokenvalues['show_register'] == 'Y' ? eT('Yes') : eT('No'), "</td>";
                                        }; ?>
                                        <td><input type='text' name='caption_<?php echo $sTokenField; ?>_<?php echo $sLanguage; ?>' class='form-control' value='<?php echo htmlspecialchars(!empty($tokencaptions[$sLanguage][$sTokenField]) ? $tokencaptions[$sLanguage][$sTokenField] : '', ENT_QUOTES, 'UTF-8'); ?>' /></td>
                                        <td><?php
                                            if ($sLanguage == $oSurvey->language)
                                            {
                                                echo CHtml::dropDownList('cpdbmap_'.$sTokenField,$tokenvalues['cpdbmap'],$aCPDBAttributes, array('class' => 'form-control'));
                                            }
                                            else
                                            {
                                                echo $aCPDBAttributes[$tokenvalues['cpdbmap']];
                                            }
                                        ?></td>
                                        <td>
                                        <?php
                                        if (!empty($examplerow))
                                        {
                                            echo htmlspecialchars($examplerow[$sTokenField]);
                                        }
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                    <tbody></table></div>
                            <?php } ?>
                    </div>
                </div>
                <p>
                    <input type="submit" class="btn btn-success" value="<?php eT('Save'); ?>" />
                    <input type='hidden' name='action' value='tokens' />
                    <input type='hidden' name='subaction' value='updatetokenattributedescriptions' />
                </p>
                <?php echo CHtml::endForm() ?>

            </div>
        </div>

        <?php endif; ?>
    <h3><?php eT("Add/delete survey participant attributes"); ?></h3>

    <div class="row">
        <div class="col-lg-12 content-right">
            <p><?php neT('There is {n} user attribute field in this survey participants table.|There are {n} user attribute fields in this survey participants table.', $nrofattributes); ?></p>
            <?php echo CHtml::form(array("admin/tokens/sa/updatetokenattributes/surveyid/{$surveyid}"), 'post',array('id'=>'addattribute')); ?>
            <p>
                <label for="addnumber"><?php eT('Number of attribute fields to add:'); ?></label>
                <div class=''>
                    <input class='form-control' type="text" id="addnumber" name="addnumber" size="3" maxlength="3" value="1" />
                </div>
            </p>
            <p>
                <?php echo CHtml::submitButton(gT('Add fields','unescaped'), array('class'=>'btn btn-warning')); ?>
                <?php echo CHtml::hiddenField('action','tokens'); ?>
                <?php echo CHtml::hiddenField('subaction','updatetokenattributes'); ?>
                <?php echo CHtml::hiddenField('sid',$surveyid); ?>
            </p>
            <?php echo CHtml::endForm() ?>
            <?php if( count($tokenfieldlist)) { ?>
                <?php echo CHtml::form(array("admin/tokens/sa/deletetokenattributes/surveyid/{$surveyid}"), 'post',array('id'=>'attributenumber')); ?>
                <p>
                    <label for="deleteattribute"><?php eT('Delete this attribute:'); ?></label>
                    <div class=''>
                        <?php  echo CHtml::dropDownList('deleteattribute',"",CHtml::listData($tokenfieldlist,'id','description'),array('empty' => gT('(None)','unescaped'), 'class'=>'form-control')); ?>
                    </div>
                </p>
                <p>
                    <?php echo CHtml::submitButton(gT('Delete attribute','unescaped'), array('class'=>'btn btn-danger')); ?>
                    <?php echo CHtml::hiddenField('action','tokens'); ?>
                    <?php echo CHtml::hiddenField('subaction','deletetokenattributes'); ?>
                    <?php echo CHtml::hiddenField('sid',$surveyid); ?>
                </p>
                <?php echo CHtml::endForm() ?>
                <?php } ?>
        </div>
    </div>
</div>
<?php
App()->getClientScript()->registerScript('ManageTokenAttributesViewBSSwitcher', "
LS.renderBootstrapSwitch();
", LSYii_ClientScript::POS_POSTSCRIPT);
?>
