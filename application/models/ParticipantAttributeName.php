<?php

/*
 * LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 */

/**
 * This is the model class for table "{{{{participant_attribute_names}}}}".
 *
 * The following are the available columns in table '{{{{participant_attribute_names}}}}':
 * @property integer $attribute_id
 * @property string $attribute_type
 * @property string $defaultname
 * @property string $visible
 * @property string $encrypted
 * @property string $core_attribute
 * @property ParticipantAttributeNameLang[] $participant_attribute_names_lang
 * @property ParticipantAttribute $participant_attribute
 * @property array $AttributeTypeDropdownArray
 *
 */
class ParticipantAttributeName extends LSActiveRecord
{
    /** @inheritdoc */
    public function primaryKey()
    {
        return 'attribute_id';
    }

    /**
     * @inheritdoc
     * @return ParticipantAttributeName
     */
    public static function model($className = __CLASS__)
    {
        /** @var self $model */
        $model = parent::model($className);
        return $model;
    }

    /** @inheritdoc */
    public function tableName()
    {
        return '{{participant_attribute_names}}';
    }

    /** @inheritdoc */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that will receive user inputs.
        return array(
            array('core_attribute', 'default', 'value' => 'N'),
            array('defaultname', 'LSYii_FilterValidator', 'filter' => 'strip_tags', 'skipOnEmpty' => true),
            array('attribute_type, visible, encrypted, core_attribute', 'required'),
            array('attribute_type', 'length', 'max' => 4),
            array('visible, encrypted, core_attribute', 'length', 'max' => 5),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('attribute_id, attribute_type, visible, encrypted, core_attribute', 'safe', 'on' => 'search'),
        );
    }

    /** @inheritdoc */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'participant_attribute_names_lang' => array(self::HAS_MANY, 'ParticipantAttributeNameLang', 'attribute_id'),
            'participant_attribute' => array(self::HAS_ONE, 'ParticipantAttribute', 'attribute_id')
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'attribute_id' => gT('Attribute'),
            'attribute_type' => gT('Attribute type'),
            'visible' => gT('Visible'),
            'encrypted' => gT('Encrypted'),
            'core_attribute' => gT('Core attribute'),
            'defaultname' => gT('Default attribute name:'),
        );
    }

    /**
     * Returns Buttons for Grid view
     * @return string html
     */
    public function getButtons()
    {
        $isNotCoreAttribute = $this->core_attribute !== 'Y';

        $dropdownItems = [];
        $dropdownItems[] = [
            'title'            => gT('Edit this attribute'),
            'linkClass'        => 'action_attributeNames_editModal',
            'iconClass'        => 'ri-pencil-fill',
            'enabledCondition' => $isNotCoreAttribute,
            'linkAttributes'   => [
                'data-attribute_id'   => $this->attribute_id,
            ]
        ];
        $dropdownItems[] = [
            'title'            => gT('Delete this attribute'),
            'linkClass'        => 'action_attributeNames_deleteModal',
            'iconClass'        => 'ri-delete-bin-fill text-danger',
            'enabledCondition' => $isNotCoreAttribute,
            'linkAttributes'   => [
                'data-bs-toggle' => "modal",
                'data-bs-target' => "#confirmation-modal",
                'data-title'     => gT("Delete this attribute"),
                'data-btnclass'  => "btn-danger",
                'data-btntext'   => gt("Delete"),
                'data-message'   => gt('Do you really want to delete this attribute') . "?",
                'data-onclick'   => 'deleteAttributeAjax(' . $this->attribute_id . ")",
            ]
        ];

        return App()->getController()->widget('ext.admin.grid.GridActionsWidget.GridActionsWidget', ['dropdownItems' => $dropdownItems], true);
    }

    /**
     * @return string
     */
    public function getMassiveActionCheckbox()
    {
        // don't show checkbox for core attributes
        if ($this->core_attribute == 'Y') {
            // BugFix: 317(op), whithout this hidden inputfields, massive action 'delete' is not working correctly
            // as we have only that special case in cpdb at the moment, it's not necessary to change it in the frontend part (listAction.js line 27)
            return "<input type='hidden' class='selector_attributeNamesCheckbox' name='selectedAttributeNames[]' value='" . $this->attribute_id . "' >";
        }
        return "<input type='checkbox' class='selector_attributeNamesCheckbox' name='selectedAttributeNames[]' value='" . $this->attribute_id . "' >";
    }

    /**
     * @return string ??
     */
    public function getAttributeTypeNice()
    {
        return $this->attributeTypeDropdownArray[$this->attribute_type];
    }

    /**
     * @return array
     */
    public function getAttributeTypeDropdownArray()
    {
        $realNames = array(
            'DD' => gT("Drop-down list"),
            'DP' => gT("Date"),
            'TB' => gT("Text box")
        );
        return $realNames;
    }

    /**
     * @return string
     */
    public function getNamePlusLanguageName()
    {
        $namesList = $this->participant_attribute_names_lang;
        $names = array();
        foreach ($namesList as $name) {
                $names[] = $name['attribute_name'];
        }
        $defaultname = $this->defaultname;
        $returnName = $defaultname . " (" . join(', ', $names) . ")";
        return $returnName;
    }

    /**
     * @return string
     */
    public function getVisibleSwitch()
    {
        $inputHtml = App()->getController()->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
            'name'          => 'visible_' . $this->attribute_id,
            'checkedOption' => $this->visible === "TRUE" ? "1" : "0",
            'selectOptions' => [
                '1' => gT('Yes'),
                '0' => gT('No'),
            ],
            'htmlOptions'   => [
                'class' => 'action_changeAttributeVisibility',
                'disabled' => $this->core_attribute === "Y" ? true : false
            ]
        ], true);
        return $inputHtml;
    }

    /**
     * @return string
     */
    public function getEncryptedSwitch()
    {
        $sodium = Yii::app()->sodium;
        $bEncrypted = $sodium->bLibraryExists;
        $inputHtml = App()->getController()->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
            'name'          => 'encrypted_' . $this->attribute_id,
            'checkedOption' => $this->encrypted === "Y" ? "1" : "0",
            'selectOptions' => [
                '1' => gT('Yes'),
                '0' => gT('No'),
            ],
            'htmlOptions'   => [
                'class' => 'action_changeAttributeEncrypted',
                'disabled' => !$bEncrypted
            ]
        ], true);
        return $inputHtml;
    }

    /**
     * @return string
     */
    public function getCoreAttributeSwitch()
    {
        $inputHtml = $this->core_attribute === "Y" ? gT("Yes") : gT("No");
        return $inputHtml;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        // load sodium library
        $sodium = Yii::app()->sodium;
        $bEncrypted = $sodium->bLibraryExists;
        $cols = [
            [
                "name"              => 'massiveActionCheckbox',
                "type"              => 'raw',
                "header"            => "<input type='checkbox' id='action_toggleAllAttributeNames' />",
                "filter"            => false,
                'headerHtmlOptions' => ['class' => 'ls-sticky-column'],
                'filterHtmlOptions' => ['class' => 'ls-sticky-column'],
                'htmlOptions'       => ['class' => 'ls-sticky-column']
            ],
            [
                "name"   => 'defaultname',
                "value"  => '$data->getNamePlusLanguageName()',
                "header" => gT("Name")
            ],
            [
                "name"   => 'attribute_type',
                "value"  => '$data->getAttributeTypeNice()',
                "filter" => $this->attributeTypeDropdownArray
            ],
            [
                "name"   => 'visible',
                "value"  => '$data->getVisibleSwitch()',
                "type"   => "raw",
                "filter" => ["TRUE" => gT("Yes"), "FALSE" => gT("No")]
            ],
            [
                "name"   => 'encrypted',
                "value"  => '$data->getEncryptedSwitch()',
                "type"   => "raw",
                "filter" => ["Y" => gT("Yes"), "N" => gT("No")],
                "header" => '<span ' . ($bEncrypted === true ? '' : 'title="' . gT("Encryption is disabled because Sodium library isn't installed") . '"') . '>' . gT("Encrypted") . '</span>',
            ],
            [
                "name"   => 'core_attribute',
                "value"  => '$data->getCoreAttributeSwitch()',
                "type"   => "raw",
                "filter" => ["Y" => gT("Yes"), "N" => gT("No")],
            ],
            [
                "name"              => 'buttons',
                "type"              => 'raw',
                "header"            => gT("Action"),
                "filter"            => false,
                'headerHtmlOptions' => ['class' => 'ls-sticky-column'],
                'filterHtmlOptions' => ['class' => 'ls-sticky-column'],
                'htmlOptions'       => ['class' => 'ls-sticky-column']
            ],
        ];
        return $cols;
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new LSDbCriteria();

        $criteria->compare('defaultname', $this->defaultname, true, 'AND', true);
        $criteria->compare('attribute_id', $this->attribute_id);
        $criteria->compare('attribute_type', $this->attribute_type);
        $criteria->compare('visible', $this->visible, true);
        $criteria->compare('encrypted', $this->encrypted, true);
        $criteria->compare('core_attribute', $this->core_attribute, true);

        $sort = new CSort();
        $sort->defaultOrder = array('core_attribute' => CSort::SORT_DESC, 'defaultname' => CSort::SORT_ASC);

        $pageSize = Yii::app()->user->getState('pageSizeAttributes', Yii::app()->params['defaultPageSize']);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
            'sort' => $sort,
            'pagination' => array(
                'pageSize' => $pageSize
            )
        ));
    }

    public function getAllAttributes()
    {
        $aResult = Yii::app()->db->createCommand()->select('{{participant_attribute_names}}.*')
                                                ->from('{{participant_attribute_names}}')
                                                ->where("{{participant_attribute_names}}.core_attribute <> 'Y'")
                                                ->order('{{participant_attribute_names}}.attribute_id')
                                                ->queryAll();
        return $aResult;
    }

    public function getAllAttributesValues()
    {
        return Yii::app()->db->createCommand()->select('*')->from('{{participant_attribute_values}}')->queryAll();
    }

    /**
     * Get an array of CPDB attributes
     *
     * @param mixed $sLanguageFilter
     * @return array
     */
    public function getVisibleAttributes($sLanguageFilter = null)
    {
        if ($sLanguageFilter == null) {
            $sLanguageFilter = Yii::app()->session['adminlang'];
        }
        $output = array();
        //First get all the distinct id's that are visible
        $ids = ParticipantAttributeName::model()->findAll("visible = 'TRUE' and core_attribute <> 'Y'");
        //Then find a language for each one - the current $lang, if possible, english second, otherwise, the first in the list
        foreach ($ids as $id) {
            $langs = ParticipantAttributeNameLang::model()->findAll(
                "attribute_id = :attribute_id",
                array(
                    ":attribute_id" => $id->attribute_id
                )
            );

            if ($langs) {
                $language = null;
                foreach ($langs as $lang) {
                    //If we can find a language match, set the language and exit
                    if ($lang->lang == $sLanguageFilter) {
                        $language = $lang->lang;
                        $attribute_name = $lang->attribute_name;
                        break;
                    }
                    if ($lang->lang == "en") {
                        $language = $lang->lang;
                        $attribute_name = $lang->attribute_name;
                    }
                }
                if ($language == null) {
                    $language = $langs[0]->lang;
                    $attribute_name = $langs[0]->attribute_name;
                }
            } else {
                $language = Yii::app()->session['adminlang'];
                $attribute_name = $id->defaultname;
            }

            $output[$id->attribute_id] = array(
                "attribute_id"      => $id->attribute_id,
                "attribute_type"    => $id->attribute_type,
                "visible"           => $id->visible,
                "attribute_name"    => $attribute_name,
                "lang"              => $language
            );
        }
        return $output;
    }

    /**
     * Returns a list of attributes, with name and value. Currently not working for alternate languages
     *
     * @param string $participant_id the id of the participant to return values/names for (if empty, returns all)
     * @return array
     */
    public function getParticipantVisibleAttribute($participant_id)
    {
        $output = array();

        if ($participant_id != '') {
            $findCriteria = new CDbCriteria();
            $findCriteria->addCondition('participant_id = :participant_id');
            $findCriteria->addCondition("core_attribute <> 'Y'");
            $findCriteria->params = array(':participant_id' => $participant_id);
            $records = ParticipantAttributeName::model()->with('participant_attribute_names_lang', 'participant_attribute')
                                                        ->findAll($findCriteria);
            foreach ($records as $row) {
//Iterate through each attribute
                $thisname = "";
                $thislang = "";
                foreach ($row->participant_attribute_names_lang as $names) {
//Iterate through each language version of this attribute
                    if ($thisname == "") {
                        $thisname = $names->attribute_name;
                        $thislang = $names->lang;
                    } //Choose the first item by default
                    if ($names->lang == Yii::app()->session['adminlang']) {
                        $thisname = $names->attribute_name;
                        $thislang = $names->lang;
                    } //Override the default with the admin language version if found
                }
                $output[] = array('participant_id' => $row->participant_attribute->participant_id,
                                'attribute_id' => $row->attribute_id,
                                'attribute_type' => $row->attribute_type,
                                'attribute_display' => $row->visible,
                                'attribute_name' => $thisname,
                                'value' => $row->participant_attribute->value,
                                'lang' => $thislang);
            }
            return $output;
        } else {
            $findCriteria = new CDbCriteria();
            $records = ParticipantAttributeName::model()->with('participant_attribute_names_lang', 'participant_attribute')->findAll($findCriteria);
            foreach ($records as $row) {
//Iterate through each attribute
                $thisname = "";
                $thislang = "";
                foreach ($row->participant_attribute_names_lang as $names) {
//Iterate through each language version of this attribute
                    if ($thisname == "") {
                        $thisname = $names->attribute_name;
                        $thislang = $names->lang;
                    } //Choose the first item by default
                    if ($names->lang == Yii::app()->session['adminlang']) {
                        $thisname = $names->attribute_name;
                        $thislang = $names->lang;
                    } //Override the default with the admin language version if found
                }
                $output[] = array('participant_id' => $row->participant_attribute->participant_id,
                                'attribute_id' => $row->attribute_id,
                                'attribute_type' => $row->attribute_type,
                                'attribute_display' => $row->visible,
                                'attribute_name' => $thisname,
                                'value' => $row->participant_attribute->value,
                                'lang' => $thislang);
            }
            return $output;
        }
    }

    public function getAttributeValue($participantid, $attributeid)
    {
        $data = Yii::app()->db->createCommand()
            ->select('*')
            ->from('{{participant_attribute}}')
            ->where('participant_id = :participant_id AND attribute_id = :attribute_id AND core_Attribute <> "Y"')
            ->bindValues(array(':participant_id' => $participantid, ':attribute_id' => $attributeid))
            ->queryRow();
        return $data;
    }

    /**
     * Returns an array with all participant attributes which are not core attributes.
     *
     *
     * @return array   will have the following structure
     *                  result['attribute_id']
     *                  result['attribute_type']
     *                  result['attribute_display'] --> visible
     *                  result['attribute_name']
     *                  result['lang']
     */
    public function getCPDBAttributes()
    {
        /**  @var $models ParticipantAttributeName[] */
        $models = ParticipantAttributeName::model()->findAll('core_attribute=:core_attribute', array(
            'core_attribute' => 'N'
        ));

        $output = array();
        //Iterate through each attribute to get language and language value
        foreach ($models as $row) {
            $thisname = '';
            $thislang = '';
            foreach ($row->participant_attribute_names_lang as $names) {
                //Iterate through each language version of this attribute
                if ($thisname == "") {
                    $thisname = $names->attribute_name;
                    $thislang = $names->lang;
                } //Choose the first item by default
                if ($names->lang == Yii::app()->session['adminlang']) {
                    $thisname = $names->attribute_name;
                    $thislang = $names->lang;
                } //Override the default with the admin language version if found
            }
            // user default attribute name if no other has been set
            if (!$thisname) {
                $thisname = $row->defaultname;
            }

            $output[] = array(
                'attribute_id' => $row->attribute_id,
                'attribute_type' => $row->attribute_type,
                'attribute_display' => $row->visible,
                'attribute_name' => $thisname,
                'lang' => $thislang
            );
        }

        return $output;
    }

    /**
     * @param int $attribute_id
     * @return array
     */
    public function getAttributesValues($attribute_id = null)
    {
        if (empty($attribute_id)) {
            return array();
        } else {
            return Yii::app()->db->createCommand()
                ->select('*')
                ->from('{{participant_attribute_values}} attr_val')
                ->join('{{participant_attribute_names}} attr_name', 'attr_val.attribute_id = attr_name.attribute_id')
                ->where("attr_val.attribute_id = :attribute_id AND core_attribute <> 'Y'")
                ->order('value_id ASC')
                ->bindParam(":attribute_id", $attribute_id, PDO::PARAM_INT)
                ->queryAll();
        }
    }

    /**
     * this is a very specific function used to get the attributes that are
     * not present for the participant
     * @param array $attributeIds
     * @return array
     */
    public function getNotAddedAttributes($attributeIds)
    {
        $output = array();
        $notin = array();
        foreach ($attributeIds as $row) {
            $notin[] = $row;
        }

        $criteria = new CDbCriteria();
        $alias = $this->getTableAlias();
        $criteria->addNotInCondition("$alias.attribute_id", $attributeIds);
        $records = ParticipantAttributeName::model()->with('participant_attribute_names_lang')->findAll($criteria);
        foreach ($records as $row) {
//Iterate through each attribute
            $thisname = "";
            $thislang = "";
            foreach ($row->participant_attribute_names_lang as $names) {
//Iterate through each language version of this attribute
                if ($thisname == "") {
                    $thisname = $names->attribute_name;
                    $thislang = $names->lang;
                } //Choose the first item by default
                if ($names->lang == Yii::app()->session['adminlang']) {
                    $thisname = $names->attribute_name;
                    $thislang = $names->lang;
                } //Override the default with the admin language version if found
            }
            $output[] = array('attribute_id' => $row->attribute_id,
                'attribute_type' => $row->attribute_type,
                'attribute_display' => $row->visible,
                'attribute_name' => $thisname,
                'lang' => $thislang);
        }
        return $output;
    }

    /**
     * Adds the data for a new attribute
     *
     * @param mixed $data
     * @return bool|int
     */
    public function storeAttribute($data)
    {
        // Do not allow more than 60 attributes because queries will break because of too many joins
        if (ParticipantAttributeName::model()->count() > 59) {
            return false;
        };
        $oParticipantAttributeName = new ParticipantAttributeName();
        $oParticipantAttributeName->attribute_type = $data['attribute_type'];
        $oParticipantAttributeName->defaultname = $data['defaultname'];
        $oParticipantAttributeName->visible = $data['visible'];
        $oParticipantAttributeName->encrypted = $data['encrypted'];
        $oParticipantAttributeName->core_attribute = $data['core_attribute'];
        $oParticipantAttributeName->save();
        $iAttributeID = $oParticipantAttributeName->attribute_id;
        $oParticipantAttributeNameLang = new ParticipantAttributeNameLang();
        $oParticipantAttributeNameLang->attribute_id = intval($iAttributeID);
        $oParticipantAttributeNameLang->attribute_name = $data['attribute_name'];
        $oParticipantAttributeNameLang->lang = Yii::app()->session['adminlang'];
        $oParticipantAttributeNameLang->save();
        return $iAttributeID;
    }

    public function editParticipantAttributeValue($data)
    {
        $query = ParticipantAttribute::model()
            ->find(
                'participant_id = :participant_id AND attribute_id=:attribute_id',
                array(':participant_id' => $data['participant_id'],
                    ':attribute_id' => $data['attribute_id'])
            );

        if (is_null($query)) {
            Yii::app()->db->createCommand()->insert('{{participant_attribute}}', $data);
        } else {
            Yii::app()->db->createCommand()
                ->update(
                    '{{participant_attribute}}',
                    $data,
                    'participant_id = :participant_id2 AND attribute_id = :attribute_id2',
                    array(':participant_id2' => $data['participant_id'], ':attribute_id2' => $data['attribute_id'])
                );
        }
    }

    /**
     * @param integer $attid
     * @return void
     */
    public function delAttribute($attid)
    {
        // never delete core-attributes !!!
        // first check if $attid is core attribute
        // if not attribute could be deleted
        $participantAttributeName = ParticipantAttributeName::model()->findByPk((int) $attid);
        if ($participantAttributeName->core_attribute !== 'Y') {
            Yii::app()->db->createCommand()->delete('{{participant_attribute_names_lang}}', 'attribute_id = ' . $attid);
            Yii::app()->db->createCommand()->delete('{{participant_attribute_names}}', 'attribute_id = ' . $attid);
            Yii::app()->db->createCommand()->delete('{{participant_attribute_values}}', 'attribute_id = ' . $attid);
            Yii::app()->db->createCommand()->delete('{{participant_attribute}}', 'attribute_id = ' . $attid);
        }
    }

    /**
     * @param int $attid
     * @param int $valid
     */
    public function delAttributeValues($attid, $valid)
    {
        Yii::app()->db
            ->createCommand()
            ->delete('{{participant_attribute_values}}', 'attribute_id = ' . $attid . ' AND value_id = ' . $valid);
    }

    /**
     * @param integer $attributeid
     * @return ParticipantAttributeName[]
     */
    public function getAttributeNames($attributeid)
    {
        return Yii::app()->db->createCommand()
            ->select('*')
            ->from('{{participant_attribute_names_lang}}')
            ->where("attribute_id = :attribute_id")
            ->bindParam(":attribute_id", $attributeid, PDO::PARAM_INT)
            ->queryAll();
    }

    /**
     * @param string $attributeid
     * @param string $lang
     * @return ParticipantAttributeNameLang
     */
    public function getAttributeName($attributeid, $lang = 'en')
    {
        return Yii::app()->db->createCommand()
            ->select('*')
            ->from('{{participant_attribute_names_lang}}')
            ->where("attribute_id = :attribute_id AND lang = :lang")
            ->bindParam(":attribute_id", $attributeid, PDO::PARAM_INT)
            ->bindParam(":lang", $lang, PDO::PARAM_STR)
            ->queryRow();
    }


    /**
     * @param string $attribute_id
     * @return mixed
     * @psalm-suppress ParamNameMismatch
     * TODO: Tonis: this is a bad name for this method - it overrides parent method doing totally different thing
     */
    public function getAttribute($attribute_id)
    {
        $data = Yii::app()->db->createCommand()
            ->select('*')
            ->from('{{participant_attribute_names}}')
            ->where('{{participant_attribute_names}}.attribute_id = ' . $attribute_id)
            ->queryRow();
        return $data;
    }

    public function saveAttribute($data)
    {
        if (empty($data['attribute_id'])) {
            return;
        }
        $insertnames = array();
        if (!empty($data['attribute_type'])) {
            $insertnames['attribute_type'] = $data['attribute_type'];
        }
        if (!empty($data['visible'])) {
            $insertnames['visible'] = $data['visible'];
        }
        if (!empty($data['encrypted'])) {
            $insertnames['encrypted'] = $data['encrypted'] == 'Y' ? 'Y' : 'N';
        } else {
            $insertnames['encrypted'] = 'N';
        }
        if (!empty($data['core_attribute'])) {
            $insertnames['core_attribute'] = $data['core_attribute'] == 'Y' ? 'Y' : 'N';
        } else {
            $insertnames['core_attribute'] = 'N';
        }
        if (!empty($data['defaultname'])) {
            $insertnames['defaultname'] = $data['defaultname'];
        }
        if (!empty($insertnames)) {
            $oParticipantAttributeName = ParticipantAttributeName::model()->findByPk($data['attribute_id']);
            foreach ($insertnames as $sFieldname => $sValue) {
                $oParticipantAttributeName->$sFieldname = $sValue;
            }
            $oParticipantAttributeName->save();
            $test = $oParticipantAttributeName->getErrors();
        }
        if (!empty($data['attribute_name'])) {
            $oParticipantAttributeNameLang = ParticipantAttributeNameLang::model()->findByPk(array('attribute_id' => $data['attribute_id'], 'lang' => Yii::app()->session['adminlang']));
            $oParticipantAttributeNameLang->attribute_name = $data['attribute_name'];
            $oParticipantAttributeNameLang->save();
        }
    }

    /**
     * @todo Doc
     * @param array $data
     */
    public function saveAttributeLanguages($data)
    {
        $query = Yii::app()->db
            ->createCommand()
            ->from('{{participant_attribute_names_lang}}')
            ->where('attribute_id = :attribute_id AND lang = :lang')
            ->select('*')
            ->bindParam(":attribute_id", $data['attribute_id'], PDO::PARAM_INT)
            ->bindParam(":lang", $data['lang'], PDO::PARAM_STR)
            ->queryAll();

        if (count($query) == 0) {
            // A record does not exist, insert one.
            $oParticipantAttributeNameLang = new ParticipantAttributeNameLang();
            $oParticipantAttributeNameLang->attribute_id = $data['attribute_id'];
            $oParticipantAttributeNameLang->attribute_name = $data['attribute_name'];
            $oParticipantAttributeNameLang->lang = $data['lang'];
            $oParticipantAttributeNameLang->save();
        } else {
            $oParticipantAttributeNameLang = ParticipantAttributeNameLang::model()->findByPk(array(
                'attribute_id' => $data['attribute_id'],
                'lang' => $data['lang']
            ));
            $oParticipantAttributeNameLang->attribute_name = $data['attribute_name'];
            $oParticipantAttributeNameLang->save();
        }
    }

    /**
     * @param array $data
     */
    public function storeAttributeValues($data)
    {
        foreach ($data as $record) {
            Yii::app()->db->createCommand()->insert('{{participant_attribute_values}}', $record);
        }
    }

    /**
     * @param array $data
     */
    public function storeAttributeValue($data)
    {
        Yii::app()->db->createCommand()->insert('{{participant_attribute_values}}', $data);
    }

    public function clearAttributeValues()
    {
        $deleteCommand = Yii::app()->db->createCommand();
        $deleteCommand->delete('{{participant_attribute_values}}', 'attribute_id=:attribute_id', array('attribute_id' => $this->attribute_id));
    }

    /**
     * @param array $data
     * @return int
     */
    public function storeAttributeCSV($data)
    {
        $oParticipantAttributeName = new ParticipantAttributeName();
        $oParticipantAttributeName->attribute_type = $data['attribute_type'];
        $oParticipantAttributeName->defaultname = $data['defaultname'];
        $oParticipantAttributeName->visible = $data['visible'];
        $oParticipantAttributeName->encrypted = 'N';
        $oParticipantAttributeName->core_attribute = 'N';
        $oParticipantAttributeName->save();

        $iAttributeID = $oParticipantAttributeName->getPrimaryKey();

        $oParticipantAttributeNameLang = new ParticipantAttributeNameLang();
        $oParticipantAttributeNameLang->attribute_id = $iAttributeID;
        $oParticipantAttributeNameLang->attribute_name = $data['defaultname'];
        $oParticipantAttributeNameLang->lang = Yii::app()->session['adminlang'];
        $oParticipantAttributeNameLang->save();

        return $iAttributeID;
    }

    /**
     * updates the attribute values in participant_attribute_values
     * @param array $data
     */
    public function saveAttributeValue($data)
    {
        Yii::app()->db->createCommand()
                    ->update('{{participant_attribute_values}}', $data, "attribute_id = :attribute_id AND value_id = :value_id", array(":attribute_id" => $data['attribute_id'], ":value_id" => $data['value_id']));
                    //->bindParam(":attribute_id", $data['attribute_id'], PDO::PARAM_INT)->bindParam(":value_id", $data['value_id'], PDO::PARAM_INT);
    }

    /**
     * @param integer $attid
     * @param string $visiblecondition
     */
    public function saveAttributeVisible($attid, $visiblecondition)
    {

        $attribute_id = explode("_", $attid);
        $data = array('visible' => $visiblecondition);
        if ($visiblecondition == "") {
            $data = array('visible' => 'FALSE');
        }
        Yii::app()->db->createCommand()->update('{{participant_attribute_names}}', $data, 'attribute_id = :attribute_id')
            ->bindParam(":attribute_id", $attribute_id[1], PDO::PARAM_INT);
    }

    /**
     * @param integer $attid
     * @param string $encryptedcondition
     */
    public function saveAttributeEncrypted($attid, $encryptedcondition)
    {

        $attribute_id = explode("_", $attid);
        $data = array('encrypted' => $encryptedcondition);
        if ($encryptedcondition == "") {
            $data = array('encrypted' => 'FALSE');
        }
        Yii::app()->db->createCommand()->update('{{participant_attribute_names}}', $data, 'attribute_id = :attribute_id')
            ->bindParam(":attribute_id", $attribute_id[1], PDO::PARAM_INT);
    }


    /**
     * @return array
     */
    public function getAttributeID()
    {
        $query = Yii::app()->db->createCommand()
            ->select('attribute_id')
            ->from('{{participant_attribute_names}}')
            ->order('attribute_id DESC')
            ->queryAll();
        return $query;
    }


    /**
     * @param array $data
     */
    public function saveParticipantAttributeValue($data)
    {
        Yii::app()->db->createCommand()->insert('{{participant_attribute}}', $data);
    }

    /**
     * Returns true if the attribute is encrypted
     * @return bool
     */
    public function isEncrypted()
    {
        return $this->encrypted == 'Y';
    }

    /**
     * Returns true if the attribute is a core attribute
     * @return bool
     */
    public function isCoreAttribute()
    {
        return $this->core_attribute == 'Y';
    }
}
