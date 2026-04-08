<?php
/**
 *
 * Manage token attribute fields/ Add or delete token attributes
 * @var AdminController $this
 * @var Survey $oSurvey
 */
?>
<div class='side-body'>
    <h1 class="h3"><?php eT("Manage attribute fields"); ?></h1>

    <div class="row">
        <div class="col-12 content-right">
            <?php echo CHtml::form(array("admin/tokens/sa/updatetokenattributedescriptions/surveyid/{$surveyid}"), 'post'); ?>
            <div>
                <ul class="nav nav-tabs">
                    <?php $c = true; ?>
                    <?php foreach ($oSurvey->allLanguages as $sLanguage): ?>
                        <?php $sTabTitle = getLanguageNameFromCode($sLanguage, false) . " " . (($sLanguage == $oSurvey->language) ? "(" . gT("Base language") . ")" : "") ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $c ? "active" : "" ?>" data-bs-toggle="tab" href="#language_<?php echo $sLanguage ?>">
                                <?php $c = false; ?>
                                <?php echo $sTabTitle; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="tab-content">
                    <?php $c=true;?>
                    <?php foreach ($oSurvey->allLanguages as $sLanguage) { ?>
                        <div id="language_<?php echo $sLanguage ?>"  class="table-responsive tab-pane fade <?= $c ? "show active" : "" ?>">
                            <?php $c=false; ?>
                            <table class='listtokenattributes table table-hover'>
                                <thead> <tr>
                                    <th><?php eT("Attribute field"); ?></th>
                                    <th><?php eT("Field description"); ?></th>
                                    <th><?php eT("Show during registration?") ?></th>
                                    <th><?php eT("Mandatory during registration?"); ?></th>
                                    <th title="<?php !$bEncrypted ? eT("Encryption is disabled because Sodium library isn't installed") : ''; ?>"><?php eT("Encrypted?"); ?></th>
                                    <th><?php eT("Field caption"); ?></th>
                                    <th><?php eT("CPDB mapping"); ?></th>
                                    <th><?php eT("Example data"); ?></th>
                                </tr> </thead>
                                <tbody>
                                <?php $nrofattributes = 0;
                                foreach ($tokenfields as $sTokenField) {
                                    if (isset($tokenfielddata[$sTokenField])) {
                                        $tokenvalues = $tokenfielddata[$sTokenField];
                                        if (!array_key_exists('encrypted', $tokenvalues)){
                                            $tokenvalues['encrypted'] = 'N';   
                                        }
                                        if (!array_key_exists('cpdbmap', $tokenvalues)){
                                            $tokenvalues['cpdbmap'] = '';   
                                        }
                                    } else {
                                        $tokenvalues = array('description' => '','mandatory' => 'N','encrypted' => 'N','show_register' => 'N','cpdbmap'=>'');
                                    }
                                    // add count only if not core token attribute
                                    if (!in_array($sTokenField, array('firstname', 'lastname', 'email'))){
                                        $nrofattributes++;
                                    }
                                    echo "
                                    <tr>
                                    <td>{$sTokenField}</td>";
                                    if ($sLanguage == $oSurvey->language)
                                    { ?>
                                        <td>
                                            <?php if (empty($tokenvalues['coreattribute'])){ ?>
                                                <?php
                                                    echo CHtml::textField(
                                                        "description_{$sTokenField}",
                                                        $tokenvalues['description'],
                                                        array('class' => 'form-control')
                                                    );
                                                ?>
                                            <?php } else { ?>
                                                <span><?php echo gT('Core attribute'); ?></span>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                                'name'          => "show_register_{$sTokenField}",
                                                'checkedOption' => $tokenvalues['show_register'] === 'Y' ? '1' : '0',
                                                'selectOptions' => [
                                                    '1' => gT('On'),
                                                    '0' => gT('Off'),
                                                ],
                                                'htmlOptions'   => [
                                                    'disabled' => !empty($tokenvalues['coreattribute']),
                                                ]
                                            ]); ?>
                                        </td>
                                        <td>
                                            <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                                'name'          => "mandatory_{$sTokenField}",
                                                'checkedOption' => $tokenvalues['mandatory'] === 'Y' ? '1' : '0',
                                                'selectOptions' => [
                                                    '1' => gT('On'),
                                                    '0' => gT('Off'),
                                                ],
                                                'htmlOptions'   => [
                                                    'disabled' => !empty($tokenvalues['coreattribute']),
                                                ]
                                            ]); ?>
                                        </td>
                                        <td>
                                            <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                                'name'          => "encrypted_{$sTokenField}",
                                                'checkedOption' => $tokenvalues['encrypted'] === 'Y' ? '1' : '0',
                                                'selectOptions' => [
                                                    '1' => gT('On'),
                                                    '0' => gT('Off'),
                                                ],
                                                'htmlOptions'   => [
                                                    'disabled' => !$bEncrypted,
                                                ]
                                            ]); ?>
                                        </td>
                                        <?php
                                    }
                                    else
                                    {
                                        echo "
                                        <td>", htmlspecialchars((string) $tokenvalues['description'], ENT_QUOTES, 'UTF-8'), "</td>
                                        <td>", $tokenvalues['mandatory'] == 'Y' ? eT('Yes') : eT('No'), "</td>
                                        <td>", $tokenvalues['encrypted'] == 'Y' ? eT('Yes') : eT('No'), "</td>
                                        <td>", $tokenvalues['show_register'] == 'Y' ? eT('Yes') : eT('No'), "</td>";
                                    }; ?>
                                    <td>
                                        <?php
                                            echo CHtml::textField(
                                                "caption_{$sTokenField}_{$sLanguage}",
                                                $tokencaptions[$sLanguage][$sTokenField] ?? '',
                                                array('class' => 'form-control')
                                            );
                                        ?>
                                    </td>
                                    <td><?php
                                        if ($sLanguage == $oSurvey->language){
                                            if (empty($tokenvalues['coreattribute'])){
                                                echo CHtml::dropDownList('cpdbmap_'.$sTokenField,$tokenvalues['cpdbmap'],$aCPDBAttributes, array('class' => 'form-select'));
                                            }
                                        }
                                        else
                                        {
                                            echo $aCPDBAttributes[$tokenvalues['cpdbmap']];
                                        }
                                    ?></td>
                                    <td>
                                    <?php
                                    if (empty($tokenvalues['coreattribute']) && !empty($examplerow))
                                    {
                                        echo CHTml::encode($examplerow[$sTokenField]);
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
                <input type="submit" class="btn btn-primary" value="<?php eT('Save'); ?>" />
                <input type='hidden' name='action' value='tokens' />
                <input type='hidden' name='subaction' value='updatetokenattributedescriptions' />
            </p>
            <?php echo CHtml::endForm() ?>

        </div>
    </div>

    <h2 class="h3"><?php eT("Add/delete survey participant attributes"); ?></h2>

    <div class="row">
        <div class="col-12 content-right">
            <p><?php neT('There is {n} user attribute field in this survey participant list.|There are {n} user attribute fields in this survey participant list.', $nrofattributes); ?></p>
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
                        <?php  echo CHtml::dropDownList('deleteattribute',"",CHtml::listData($tokenfieldlist,'id','description'),array('empty' => gT('(None)','unescaped'), 'class'=>'form-select')); ?>
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