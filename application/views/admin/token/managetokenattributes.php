<?php
/**
 *
 * Manage token attribute fields/ Add or delete token attributes
 * @var AdminController $this
 * @var Survey $oSurvey
 * @var array $tokenFields
 * @var array $attributeTypeDropdownArray
 */
?>
<div class='side-body'>
    <h3><?php eT("Manage attribute fields"); ?></h3>

    <div class="row">
        <div class="col-12 content-right">
            <?php echo CHtml::form(
                    array("admin/tokens/sa/updatetokenattributedescriptions/surveyid/{$surveyid}"),
                    'post',
                    ['id' => 'manage_token_attributes_form']
            ); ?>
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
                                    <th><?php eT("Field type"); ?></th>
                                    <th><?php eT("CPDB mapping"); ?></th>
                                    <th><?php eT("Example data"); ?></th>
                                </tr> </thead>
                                <tbody>
                                <?php
                                $nrofattributes = 0;
                                $defaultTokenValues = [
                                        'description' => '',
                                        'mandatory' => 'N',
                                        'encrypted' => 'N',
                                        'show_register' => 'N',
                                        'type' => 'TB',
                                        'type_options' => '[]',
                                        'cpdbmap' => ''
                                ];

                                foreach ($tokenFields as $sTokenField) {
                                    $tokenValues = array_merge(
                                        $defaultTokenValues,
                                        $tokenfielddata[$sTokenField] ?? []
                                );
                                    $customAttribute = empty($tokenValues['coreattribute']);
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
                                            <?php if ($customAttribute){ ?>
                                                <?php
                                                    echo CHtml::textField(
                                                        "description_{$sTokenField}",
                                                        $tokenValues['description'],
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
                                                'checkedOption' => $tokenValues['show_register'] === 'Y' ? '1' : '0',
                                                'selectOptions' => [
                                                    '1' => gT('On'),
                                                    '0' => gT('Off'),
                                                ],
                                                'htmlOptions'   => [
                                                    'disabled' => !$customAttribute,
                                                ]
                                            ]); ?>
                                        </td>
                                        <td>
                                            <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                                'name'          => "mandatory_{$sTokenField}",
                                                'checkedOption' => $tokenValues['mandatory'] === 'Y' ? '1' : '0',
                                                'selectOptions' => [
                                                    '1' => gT('On'),
                                                    '0' => gT('Off'),
                                                ],
                                                'htmlOptions'   => [
                                                    'disabled' => !$customAttribute,
                                                ]
                                            ]); ?>
                                        </td>
                                        <td>
                                            <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                                'name'          => "encrypted_{$sTokenField}",
                                                'checkedOption' => $tokenValues['encrypted'] === 'Y' ? '1' : '0',
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
                                        <td>", htmlspecialchars((string) $tokenValues['description'], ENT_QUOTES, 'UTF-8'), "</td>
                                        <td>", $tokenValues['mandatory'] == 'Y' ? eT('Yes') : eT('No'), "</td>
                                        <td>", $tokenValues['encrypted'] == 'Y' ? eT('Yes') : eT('No'), "</td>
                                        <td>", $tokenValues['show_register'] == 'Y' ? eT('Yes') : eT('No'), "</td>";
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
                                    <td class="text-nowrap">
                                        <?php
                                        $attributeType = $tokenValues['type'];
                                        $attributeTypeOptions = $tokenValues['type_options'];
                                        if ($sLanguage == $oSurvey->language) :
                                            echo CHtml::hiddenField(
                                                    "type_{$sTokenField}",
                                                    $attributeType,
                                                    array('id' => "type_{$sTokenField}")
                                            );
                                            echo CHtml::hiddenField(
                                                    "type_options_{$sTokenField}",
                                                    $attributeTypeOptions
                                            );
                                            ?>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="type_display_<?php echo $sTokenField; ?> me-1">
                                                    <?php echo $attributeTypeDropdownArray[$attributeType]; ?>
                                                </span>
                                                <?php if($customAttribute): ?>
                                                    <a href='#' class='btn btn-sm btn-outline-secondary edit-attribute-type ms-2'
                                                       data-token-field='<?php echo $sTokenField; ?>'
                                                       data-bs-toggle='modal'
                                                       data-bs-target='#attributeTypeModal'
                                                       title='<?php eT("Edit attribute type"); ?>'>
                                                        <i class='ri-pencil-fill'></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        <?php else :?>
                                            <span class="type_display_<?php echo $sTokenField;?> text-muted">
                                                <?php echo $attributeTypeDropdownArray[$attributeType]; ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php
                                        if ($sLanguage == $oSurvey->language){
                                            if ($customAttribute){
                                                echo CHtml::dropDownList('cpdbmap_'.$sTokenField,$tokenValues['cpdbmap'],$aCPDBAttributes, array('class' => 'form-select'));
                                            }
                                        }
                                        else
                                        {
                                            echo $aCPDBAttributes[$tokenValues['cpdbmap']];
                                        }
                                    ?></td>
                                    <td>
                                    <?php
                                    if ($customAttribute && !empty($examplerow))
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
                <input type="submit" class="btn btn-primary mt-2" value="<?php eT('Save'); ?>" />
                <input type='hidden' name='action' value='tokens' />
                <input type='hidden' name='subaction' value='updatetokenattributedescriptions' />
            </p>
            <?php echo CHtml::endForm() ?>
        </div>
    </div>

    <h3><?php eT("Add/delete survey participant attributes"); ?></h3>

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
<?php App()->getController()->renderPartial(
        'token/_attributeTypeModal',
        ['attributeTypeDropdownArray' => $attributeTypeDropdownArray]
); ?>