<?php

/**
 * This is the model class for table "{{surveymenu}}".
 *
 * The following are the available columns in table '{{surveymenu}}':
 * @property integer $id
 * @property integer $parent_id
 * @property integer $survey_id
 * @property integer $user_id
 * @property integer $ordering
 * @property integer $level
 * @property string $title
 * @property string $description
 * @property string $changed_at
 * @property integer $changed_by
 * @property string $created_at
 * @property integer $created_by
 * @property integer $active
 *
 * The following are the available model relations:
 * @property SurveymenuEntries[] $surveymenuEntries
 */
class Surveymenu extends LSActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{surveymenu}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('changed_at, name', 'required'),
            array('name', 'unique'),
            array('ordering, level, changed_by, created_by', 'numerical', 'integerOnly' => true),
            array('parent_id, survey_id, user_id', 'default', 'value' => null),
            array('title, position', 'length', 'max' => 255),
            array('name', 'length', 'max' => 128),
            array('description, created_at', 'safe'),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('id, parent_id, survey_id, user_id, ordering, level, position, name, title, description, changed_at, changed_by, created_at, created_by', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'surveymenuEntries' => array(self::HAS_MANY, 'SurveymenuEntries', 'menu_id'),
            'survey' => array(self::BELONGS_TO, 'Survey', ['survey_id' => 'sid']),
            'user' => array(self::BELONGS_TO, 'User', 'user_id'),
            'parent' => array(self::BELONGS_TO, 'Surveymenu', 'parent_id'),
        );
    }


    public static function staticAddMenu($menuArray)
    {
        $oSurveymenu = new Surveymenu();
        $oSurveymenu->parent_id = $menuArray['parent_id'];
        $oSurveymenu->name = $menuArray['name'];
        $oSurveymenu->title = $menuArray['title'];
        $oSurveymenu->position = $menuArray['position'];
        $oSurveymenu->description = $menuArray['description'];

        $oSurveymenu->changed_at = date('Y-m-d H:i:s');
        $oSurveymenu->changed_by = Yii::app()->user->getId();
        $oSurveymenu->created_at = date('Y-m-d H:i:s');
        $oSurveymenu->created_by = Yii::app()->user->getId();

        $oSurveymenu->save();
        return Surveymenu::model()->find('name=:name', [':name' => $menuArray['name']])->id;
    }

    public static function staticRemoveMenu($menuName, $recursive = false)
    {
        $oSurveymenu = Surveymenu::model()->find('name=:name', [':name' => $menuName]);

        if ($recursive !== true && count($oSurveymenu->surveymenuEntries) > 0) {
            return false;
        }

        foreach ($oSurveymenu->surveymenuEntries as $oSurveymenuEntry) {
            $oSurveymenuEntry->delete();
        }

        $oSurveymenu->delete();
    }

    public function getMenuesForGlobalSettings()
    {
        $oSettingsMenu = Surveymenu::model()->findByPk(1);
        $aResultCollected = $this->createSurveymenuArray([$oSettingsMenu], false);
        $resultMenu = $aResultCollected[1];
        $resultMenu['entries'] = array_filter(
            $resultMenu['entries'],
            function ($entry) {
                //@TODO add a database hook to make this more abstract
                return in_array($entry['name'], ['generalsettings','presentation','tokens','notification','publication']);
            }
        );

        return [$resultMenu];
    }

    private function __useTranslationForSurveymenu(&$entryData)
    {
        $entryData['title']             = gT($entryData['title']);
        $entryData['menu_title']        = gT($entryData['menu_title']);
        $entryData['menu_description']  = gT($entryData['menu_description']);
    }

    /**
     * @param $oSurveyMenuObjects
     * @param boolean $collapsed
     * @param null $oSurvey
     * @return array
     */
    public function createSurveymenuArray($oSurveyMenuObjects, $collapsed = false, $oSurvey = null)
    {
        //Posibility to add more languages to the database is given, so it is possible to add a call by language
        //Also for peripheral menues we may add submenus someday.
        $aResultCollected = [];
        foreach ($oSurveyMenuObjects as $oSurveyMenuObject) {
            $entries = [];
            $aMenuEntries = $oSurveyMenuObject->surveymenuEntries;
            $submenus = $this->getSurveymenuSubmenus($oSurveyMenuObject, $collapsed);
            foreach ($aMenuEntries as $menuEntry) {
                $aEntry = $menuEntry->attributes;
                //Skip menuentry if not activated in collapsed mode
                if ($collapsed && $aEntry['showincollapse'] == 0) {
                    continue;
                }

                //Skip menuentry if no permission
                if (!empty($aEntry['permission']) && !empty($aEntry['permission_grade'])) {
                    $inArray = array_search($aEntry['permission'], array_keys(Permission::getGlobalBasePermissions()));
                    if ($inArray) {
                        $hasPermission = Permission::model()->hasGlobalPermission($aEntry['permission'], $aEntry['permission_grade']);
                    } elseif ($oSurvey !== null) {
                        $hasPermission = Permission::model()->hasSurveyPermission($oSurvey->sid, $aEntry['permission'], $aEntry['permission_grade']);
                    } else {
                        $hasPermission = true;
                    }

                    if (!$hasPermission) {
                        continue;
                    }
                }

                // Check if a specific user owns this menuentry.
                if (!empty($aEntry['user_id'])) {
                    $userId = Yii::app()->session['loginID'];
                    if ($userId != $aEntry['user_id']) {
                        continue;
                    }
                }

                //parse the render part of the data attribute
                $oDataAttribute = new SurveymenuEntryData();
                $oDataAttribute->apply($menuEntry, ($oSurvey ? $oSurvey->sid : null));

                if ($oDataAttribute->isActive !== null && $oSurvey != null) {
                    if ($oDataAttribute->isActive == true && $oSurvey->active == 'N') {
                        $aEntry['disabled'] = true;
                        if ($aEntry['name'] === 'responses') {
                            $aEntry['disabled_tooltip'] = gT("This survey is not active and has no responses.");
                        } elseif ($aEntry['name'] === 'statistics') {
                            $aEntry['disabled_tooltip'] = gT("This survey has not been activated. There are no results to browse.");
                        }
                    } elseif ($oDataAttribute->isActive == false && $oSurvey->active == 'Y') {
                        $aEntry['disabled'] = true;
                        $aEntry['disabled_tooltip'] = sprintf(gT("The '%s' section is not available while the survey is active."), gT($aEntry['menu_title']));
                    }
                }

                $aEntry['link'] = $oDataAttribute->linkCreator();
                $aEntry['link_external'] = $oDataAttribute->linkExternal;
                $aEntry['debugData'] = $oDataAttribute->attributes;
                $aEntry['pjax'] = $oDataAttribute->pjaxed;
                $this->__useTranslationForSurveymenu($aEntry);
                $entries[$aEntry['id']] = $aEntry;
            }

            $aResultCollected[$oSurveyMenuObject->id] = [
                "id" => $oSurveyMenuObject->id,
                "title" => gT($oSurveyMenuObject->title),
                "name" => $oSurveyMenuObject->name,
                "ordering" => $oSurveyMenuObject->ordering,
                "level" => $oSurveyMenuObject->level,
                "description" => gT($oSurveyMenuObject->description),
                "entries" => $entries,
                "submenus" => $submenus
            ];
        }
        return $aResultCollected;
    }

    public function getSurveymenuSubmenus($oParentSurveymenu, $collapsed = false)
    {
        $criteria = new CDbCriteria();
        $criteria->addCondition('survey_id=:surveyid OR survey_id IS NULL');
        $criteria->addCondition('parent_id=:parentid');
        $criteria->addCondition('level=:level');

        if ($collapsed === true) {
            $criteria->addCondition('showincollapse=1');
        }

        $criteria->params = [
            ':surveyid' => $oParentSurveymenu->survey_id,
            ':parentid' =>  $oParentSurveymenu->id,
            ':level' => ($oParentSurveymenu->level + 1)
        ];

        $oMenus = Surveymenu::model()->findAll($criteria);

        $aResultCollected = $this->createSurveymenuArray($oMenus, $collapsed);
        return $aResultCollected;
    }

    public function getDefaultSurveyMenus($position = '', $oSurvey = null)
    {
        $criteria = new CDbCriteria();
        $criteria->condition = 'survey_id IS NULL AND (parent_id IS NULL OR parent_id=0)';
        $collapsed = $position === 'collapsed';

        if ($position != '' && !$collapsed) {
            $criteria->condition .= ' AND position=:position';
            $criteria->params = array(':position' => $position);
        }

        if ($collapsed) {
            $criteria->condition .= ' AND (position=:position OR showincollapse=1 )';
            $criteria->params = array(':position' => $position);
            $collapsed = true;
        }

        $oDefaultMenus = Surveymenu::model()->findAll($criteria);
        $aResultCollected = $this->createSurveymenuArray($oDefaultMenus, $collapsed, $oSurvey);

        return $aResultCollected;
    }

    public function getMenuIdOptions()
    {
        $oSurveymenus = Surveymenu::model()->findAll();
        $options = [
            '' => gT('No parent menu')
        ];
        foreach ($oSurveymenus as $oSurveymenu) {
            //$options[] = "<option value='".$oSurveymenu->id."'>".$oSurveymenu->title."</option>";
            $options['' . ($oSurveymenu->id) . ''] = '(' . $oSurveymenu->id . ') ' . $oSurveymenu->title;
        }
        //return join('\n',$options);
        return $options;
    }

    public function getSurveyIdOptions()
    {
        $oSurveys = Survey::model()->findAll('expires < :expire', ['expire' => date('Y-m-d H:i:s', strtotime('+1 hour'))]);
        $options = [
            null => gT('All surveys')
        ];
        foreach ($oSurveys as $oSurvey) {
            //$options[] = "<option value='".$oSurveymenu->id."'>".$oSurveymenu->title."</option>";
            $options[$oSurvey->sid] = $oSurvey->defaultlanguage->surveyls_title;
        }
        //return join('\n',$options);
        return $options;
    }

    public function getUserIdOptions()
    {
        $oUsers = User::model()->findAll();
        $options = [
            null => gT('All users')
        ];
        foreach ($oUsers as $oUser) {
            //$options[] = "<option value='".$oSurveymenu->id."'>".$oSurveymenu->title."</option>";
            $options[$oUser->uid] = $oUser->full_name;
        }
        //return join('\n',$options);
        return $options;
    }



    public function getNextOrderPosition()
    {
        $oSurveymenus = Surveymenu::model()->findAll('parent_id=:parent_id', array('parent_id' => 0));
        return count($oSurveymenus);
    }

    public function getOrderOptions()
    {
        $oSurveymenus = Surveymenu::model()->findAll();
        $options = [];
        $arraySize = count($oSurveymenus);
        for ($i = 0; $i <= $arraySize; $i++) {
            $options[$i] = $i;
        }
        //return join('\n',$options);
        return $options;
    }
    public function getPositionOptions()
    {
        $options = [
            'side' => gT('Sidemenu'),
            'collapsed' => gT('Collapsed menu'),
            'top' => gT('Top bar'),
            'bottom' => gT('Bottom bar')
        ];
        //return join('\n',$options);
        return $options;
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'parent_id'     => gT('Parent'),
            'survey_id'     => gT('Survey'),
            'user_id'       => gT('User'),
            'ordering'      => gT('Order'),
            'level'         => gT('Level'),
            'name'          => gT('Name'),
            'title'         => gT('Title'),
            'position'      => gT('Position'),
            'description'   => gT('Description'),
            'changed_at'    => gT('Changed on'),
            'changed_by'    => gT('Changed by'),
            'created_at'    => gT('Created on'),
            'created_by'    => gT('Created by'),
        );
    }

    /**
     * Returns the buttons for gridview.
     **/
    public function getButtons()
    {
        $permission_settings_update = Permission::model()->hasGlobalPermission('settings', 'update');
        $dropdownItems = [];
        $dropdownItems[] = [
            'title'            => gT('Edit this survey menu'),
            'linkClass'        => 'action_surveymenu_editModal',
            'iconClass'        => 'ri-pencil-fill',
            'enabledCondition' => $permission_settings_update
        ];
        $dropdownItems[] = [
            'title'            => gT('Delete this survey menu'),
            'linkClass'        => 'action_surveymenu_deleteModal',
            'iconClass'        => 'ri-delete-bin-fill text-danger',
            'enabledCondition' => $permission_settings_update
        ];
        return App()->getController()->widget('ext.admin.grid.GridActionsWidget.GridActionsWidget', ['dropdownItems' => $dropdownItems], true);
    }

    /**
     * Returns the columns for gridview.
     * @return array
     */
    public function getColumns()
    {
        $cols = [
            [
                'value'             => '\'<input type="checkbox" name="id[]" class="action_selectthismenu" value="\'.$data->id.\'" />\'',
                'type'              => 'raw',
                'headerHtmlOptions' => ['class' => 'ls-sticky-column'],
                'filterHtmlOptions' => ['class' => 'ls-sticky-column'],
                'htmlOptions'       => ['class' => 'ls-sticky-column']
            ],
            [
                'name' => 'name',
            ],
            [
                'name' => 'title',
            ],
            [
                'name' => 'description',
            ],
            [
                'name' => 'ordering',
            ],
            [
                'name' => 'level',
            ],
            [
                'name' => 'position',
            ],
            [
                'name'  => 'parent_id',
                'value' => '$data->parent_id ? $data->parent[\'title\']." (".$data->parent_id.")" : "<i class=\'ri-subtract-fill\'></i>"',
                'type'  => 'raw'
            ],
            [
                'name'  => 'survey_id',
                'value' => '$data->survey_id ? $data->survey->defaultlanguage->surveyls_title : "<i class=\'ri-subtract-fill\'></i>"',
                'type'  => 'raw'
            ],
            [
                'name'  => 'user_id',
                'value' => '$data->user_id ? $data->user->full_name : "<i class=\'ri-subtract-fill\'></i>"',
                'type'  => 'raw'
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
            // array(
            //  'name' => 'changed_at',
            // ),
            // array(
            //  'name' => 'changed_by',
            // ),
            // array(
            //  'name' => 'created_at',
            // ),
            // array(
            //  'name' => 'created_by',
            // ),
        ];

        return $cols;
    }

    public function onBeforeSave($event)
    {
        if ($this->parent_id) {
            $parentMenu = Surveymenu::model()->findByPk($this->parent_id);
            $this->level = (((int) $parentMenu->level) + 1);
        }
        return parent::onBeforeSave($event);
    }

    public function onAfterSave($event)
    {
        $criteria = new CDbCriteria();

        $criteria->addCondition(['position=:position']);
        $criteria->addCondition(['ordering=:ordering']);
        $criteria->addCondition(['id!=:id']);
        $criteria->params = ['position' => $this->position, 'ordering' => (int) $this->ordering, 'id' => (int) $this->id];
        $criteria->limit = 1;

        $collidingMenu = Surveymenu::model()->find($criteria);

        if ($collidingMenu != null) {
            $collidingMenu->ordering = (((int) $collidingMenu->ordering) + 1);
            $collidingMenu->save();
        }
        return parent::onAfterSave($event);
    }

    /**
     * Method to restore the default surveymenu entries
     * This method will fail if the surveymenus have been tempered, or wrongly set
     *
     * @return boolean
     */
    public function restoreDefaults()
    {
        $sOldLanguage = App()->language;
        $oDB = Yii::app()->db;
        switchMSSQLIdentityInsert('surveymenu', true);
        $oTransaction = $oDB->beginTransaction();
        try {
            $oDB->createCommand()->truncateTable('{{surveymenu}}');

            $basicMenues = LsDefaultDataSets::getSurveyMenuData();
            foreach ($basicMenues as $basicMenu) {
                $oDB->createCommand()->insert("{{surveymenu}}", $basicMenu);
            }
            $oTransaction->commit();
        } catch (Exception $e) {
            App()->setLanguage($sOldLanguage);
            return false;
        }
        switchMSSQLIdentityInsert('surveymenu', false);
        return true;
    }

    /**
     * @return array
     */
    public function getShortListColumns()
    {
        $cols = array(
            array(
            'name' => 'id',
            ),
            array(
                'name' => 'title',
            ),
            array(
                'name' => 'description',
            ),
            array(
                'name' => 'ordering',
            ),
            array(
                'name' => 'position',
            ),
            array(
                'name' => 'parent_id',
                'value' => '$data->parent_id ? $data->parent->title : "<i class=\'ri-subtract-fill\'></i>"',
                'type' => 'raw'
            ),
            array(
                'name' => 'survey_id',
                'value' => '$data->survey_id ? $data->survey->defaultlanguage->surveyls_title : "<i class=\'ri-subtract-fill\'></i>"',
                'type' => 'raw'
            )
        );

        return $cols;
    }
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search()
    {
        // @todo Please modify the following code to remove attributes that should not be searched.

        $criteria = new CDbCriteria();

        //Don't show main menu when not superadmin
        if (Yii::app()->getConfig('demoMode') || !Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            $criteria->compare('id', '<> 1');
            $criteria->compare('id', '<> 2');
        }

        $criteria->compare('id', $this->id);
        $criteria->compare('parent_id', $this->parent_id);
        $criteria->compare('survey_id', $this->survey_id);
        $criteria->compare('user_id', $this->user_id);
        $criteria->compare('ordering', $this->ordering);
        $criteria->compare('level', $this->level);
        $criteria->compare('name', $this->name, true);
        $criteria->compare('title', $this->title, true);
        $criteria->compare('position', $this->position, true);
        $criteria->compare('description', $this->description, true);
        $criteria->compare('changed_at', $this->changed_at, true);
        $criteria->compare('changed_by', $this->changed_by);
        $criteria->compare('created_at', $this->created_at, true);
        $criteria->compare('created_by', $this->created_by);

        $pageSize = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
            'pagination' => array(
                'pageSize' => $pageSize
            )
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Surveymenu the static model class
     */
    public static function model($className = __CLASS__)
    {
        /** @var self $model */
        $model = parent::model($className);
        return $model;
    }


    public function delete()
    {
        foreach ($this->surveymenuEntries as $oSurveymenuEntry) {
            $oSurveymenuEntry->delete();
        }
        parent::delete();
    }
}
