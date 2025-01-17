<?php

/**
 * This is the model class for table "{{surveys_groups}}".
 *
 * The following are the available columns in table '{{surveys_groups}}':
 * @property integer $gsid
 * @property string $name
 * @property string $title
 * @property string $description
 * @property integer $sortorder
 * @property integer $owner_id
 * @property integer $parent_id
 * @property boolean|integer $alwaysavailable
 * @property string $created
 * @property string $modified
 * @property integer $created_by
 * @property object $parentgroup
 * @property boolean $hasSurveys
 */
class SurveysGroups extends LSActiveRecord implements PermissionInterface
{
    use PermissionTrait;

    /* @var boolean|integer alwaysavailable : set default, and set for old DB , usage of integer for DB compatibility */
    public $alwaysavailable = 0;

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{surveys_groups}}';
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return 'gsid';
    }

    /**
     * @inheritdoc
     * Set public for default group (gsid == 1)
     */
    protected function afterFind()
    {
        parent::afterFind();
        if ($this->gsid == 1) {
            $this->alwaysavailable = 1;
        }
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('name, sortorder, created_by, title', 'required'),
            array('sortorder, owner_id, parent_id, created_by', 'numerical', 'integerOnly' => true),
            array('name', 'length', 'max' => 45),
            array('name', 'match', 'pattern' => '/^[A-Za-z0-9_\.]+$/u','message' => gT('Group code can contain only alphanumeric character, underscore or dot. Spaces are not allowed.')),
            array('title', 'length', 'max' => 100),
            array('alwaysavailable', 'boolean'),
            array('description, created, modified', 'safe'),
            array('parent_id', 'in', 'range' => array_keys(self::getSurveyGroupsList()), 'allowEmpty' => true, 'message' => gT("You are not allowed to set this group as parent")),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('gsid, name, title, description, owner_id, parent_id, created, modified, created_by', 'safe', 'on' => 'search'),
            array('name', 'unsafe' , 'on' => ['update']),
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
            'parentgroup' => array(self::BELONGS_TO, 'SurveysGroups', array('parent_id' => 'gsid'), 'together' => true),
            'owner'       => array(self::BELONGS_TO, 'User', 'owner_id', 'together' => true),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'gsid'              => gT('ID'),
            'name'              => gT('Code'),
            'title'             => gT('Title'),
            'description'       => gT('Description'),
            'sortorder'         => gT('Sort order'),
            'owner_id'          => gT('Owner'),
            'parent_id'         => gT('Parent group'),
            'alwaysavailable'   => gT('Always available'),
            'created'           => gT('Created on'),
            'modified'          => gT('Modified on'),
            'created_by'        => gT('Created by'),
        );
    }

    /**
     * Returns Columns for grid view
     * @param array
     **/
    public function getColumns()
    {
        return array(

                array(
                    'id' => 'gsid',
                    'class' => 'CCheckBoxColumn',
                    'selectableRows' => '100',
                    'htmlOptions' => ['class' => 'ls-sticky-column'],
                ),
                array(
                    'header' => gT('Survey group ID'),
                    'name' => 'gsid',
                    'value' => '$data->gsid',
                    'htmlOptions' => ['class' => 'has-link'],
                ),


                array(
                    'header' => gT('Code'),
                    'name' => 'name',
                    'value' => '$data->name',
                    'htmlOptions' => ['class' => 'has-link'],
                ),

                array(
                    'header' => gT('Title'),
                    'name' => 'title',
                    'value' => '$data->title',
                    'htmlOptions' => ['class' => 'has-link'],
                ),

                array(
                    'header' => gT('Description'),
                    'name' => 'description',
                    'value' => '$data->description',
                    'htmlOptions' => ['class' => 'has-link'],
                ),

                array(
                    'header' => gT('Parent group'),
                    'name' => 'parent',
                    'value' => '$data->parentTitle',
                    'htmlOptions' => ['class' => 'has-link'],
                ),

                array(
                    'header' => gT('Available'),
                    'name' => 'alwaysavailable',
                    'value' => '$data->alwaysavailable',
                    'htmlOptions' => ['class' => 'has-link'],
                ),

                array(
                    'header' => gT('Owner'),
                    'name' => 'owner',
                    'value' => '$data->owner->users_name',
                    'htmlOptions' => ['class' => 'has-link'],
                ),

                array(
                    'header' => gT('Order'),
                    'name' => 'sortorder',
                    'value' => '$data->sortorder',
                    'htmlOptions' => ['class' => 'has-link'],
                ),
                array(
                    'header' => gT('Action'),
                    'name' => 'actions',
                    'type' => 'raw',
                    'value' => '$data->buttons',
                    'headerHtmlOptions' => ['class' => 'ls-sticky-column'],
                    'htmlOptions'       => ['class' => 'text-center ls-sticky-column'],
                ),
            );
    }

    /**
     * Retrieve if current user have update rights on this SurveysGroups
     * Used for buttons
     * @return boolean
     */
    public function getHasViewSurveyGroupRight()
    {
        return $this->hasPermission('group', 'read');
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
        $pageSize = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']);
        $criteria = new LSDbCriteria();

        $criteria->select = array('DISTINCT t.*');

        $criteria->compare('t.gsid', $this->gsid);
        $criteria->compare('t.name', $this->name, true);
        $criteria->compare('t.title', $this->title, true);
        $criteria->compare('t.description', $this->description, true);
        $criteria->compare('t.sortorder', $this->sortorder);
        $criteria->compare('t.owner_id', $this->owner_id);
        $criteria->compare('t.parent_id', $this->parent_id);
        $criteria->compare('t.created', $this->created, true);
        $criteria->compare('t.modified', $this->modified, true);
        $criteria->compare('t.created_by', $this->created_by);

        // Permission
        $criteriaPerm = self::getPermissionCriteria();
        $criteria->mergeWith($criteriaPerm, 'AND');

        $dataProvider = new CActiveDataProvider($this, array(
            'criteria' => $criteria,
            'pagination' => array(
                'pageSize' => $pageSize
            )
        ));


        return $dataProvider;
    }

    public function getParentTitle()
    {
        // "(gsid: ".$data->parent_id.")"." ".$data->parentgroup->title,
        if (empty($this->parent_id)) {
            return "";
        } else {
            return $this->parentgroup->title;
        }
    }

    /**
     * Returns true if survey group has surveys
     * @return boolean
     */
    public function getHasSurveys()
    {
        $nbSurvey = Survey::model()->countByAttributes(array("gsid" => $this->gsid));
        return $nbSurvey > 0;
    }

    /**
     * Returns true if survey group has child survey groups
     * @return boolean
     */
    public function getHasChildGroups()
    {
        $nbSurvey = SurveysGroups::model()->countByAttributes(array("parent_id" => $this->gsid));
        return $nbSurvey > 0;
    }


    public function getAllParents($bOnlyGsid = false)
    {
        $aParents = array();
        $oRSurveyGroup = $this;
        while (!empty($oRSurveyGroup->parent_id)) {
            $oRSurveyGroup =  SurveysGroups::model()->findByPk($oRSurveyGroup->parent_id);
            $aParents[] = ($bOnlyGsid) ? $oRSurveyGroup->gsid : $oRSurveyGroup;
        }

        return $aParents;
    }

    /**
     * Returns the actions for gridview
     * @return string
     */
    public function getButtons()
    {
        $deleteUrl = App()->createUrl("admin/surveysgroups/sa/delete", array("id" => $this->gsid));
        $editUrl = App()->createUrl("admin/surveysgroups/sa/update", array("id" => $this->gsid));
        $surveySettingsUrl = App()->createUrl("admin/surveysgroups/sa/surveysettings", array("id" => $this->gsid));
        $permissionUrl = App()->createUrl("surveysGroupsPermission/index", array("id" => $this->gsid));
        $permissions = [
            'group_read'          => $this->hasPermission('group', 'read'),
            'permission_read'     => $this->hasPermission('permission', 'read'),
            'surveysettings_read' => $this->hasPermission('surveysettings', 'read'),
            'group_delete'        => $this->gsid != 1 && !$this->hasSurveys && $this->hasPermission('group', 'delete')
        ];
        $dropdownItems = [];
        $dropdownItems[] = [
            'title'            => gT('Edit survey group'),
            'url'              => $editUrl,
            'iconClass'        => 'ri-pencil-fill',
            'enabledCondition' =>
                $permissions['group_read'],
        ];
        $dropdownItems[] = [
            'title'            => gT('Permission'),
            'url'              => $permissionUrl,
            'iconClass'        => 'ri-lock-fill',
            'enabledCondition' =>
                $permissions['permission_read'],
        ];
        $dropdownItems[] = [
            'title'            => gT('Survey settings'),
            'url'              => $surveySettingsUrl,
            'iconClass'        => 'ri-settings-5-fill',
            'enabledCondition' =>
                $permissions['surveysettings_read'],
        ];
        $dropdownItems[] = [
            'title'            => gT('Delete survey group'),
            'url'              => $deleteUrl,
            'iconClass'        => 'ri-delete-bin-fill text-danger',
            'enabledCondition' =>
                $permissions['group_delete'],
            'linkAttributes'   => [
                'data-bs-toggle' => "modal",
                'data-post-url'  => $deleteUrl,
                'data-message'   => gT('Do you want to continue?'),
                'data-bs-target' => "#confirmation-modal"
            ]
        ];

        return App()->getController()->widget('ext.admin.grid.GridActionsWidget.GridActionsWidget', ['dropdownItems' => $dropdownItems], true);
    }

    /**
     * Get the group list for current user
     * @return array
     */
    public static function getSurveyGroupsList()
    {
        $aSurveyList = [];
        $criteria = new CDbCriteria();
        $criteriaPerm = self::getPermissionCriteria();
        $criteria->mergeWith($criteriaPerm, 'AND');
        $criteria->order = 'title ASC';
        $oSurveyGroups = self::model()->findAll($criteria);

        foreach ($oSurveyGroups as $oSurveyGroup) {
            $aSurveyList[$oSurveyGroup->gsid] = $oSurveyGroup->title;
        }

        return $aSurveyList;
    }

    public function getNextOrderPosition()
    {
        $oSurveysGroups = SurveysGroups::model()->findAll();
        return count($oSurveysGroups) + 1;
    }

    public function getParentGroupOptions($gsid = null)
    {
        $criteria = new CDbCriteria();
        if (!empty($gsid)) {
            $criteria->compare("t.gsid", '<>' . $gsid);
        }
        // Permission
        $criteriaPerm = self::getPermissionCriteria();
        $criteria->mergeWith($criteriaPerm, 'AND');
        if ($gsid && $this->parent_id) {
            /* If gsid is set : be sure to add current parent */
            $criteria->compare("t.gsid", $this->parent_id, false, 'OR');
        }
        $oSurveysGroups = SurveysGroups::model()->findAll($criteria);
        $options = [
            '' => gT('No parent group')
        ];

        foreach ($oSurveysGroups as $oSurveysGroup) {
            //$options[] = "<option value='".$oSurveymenu->id."'>".$oSurveymenu->title."</option>";

            $aParentsGsid = $oSurveysGroup->getAllParents(true);

            if (! in_array($this->gsid, $aParentsGsid)) {
                $options['' . ($oSurveysGroup->gsid) . ''] = '(' . $oSurveysGroup->name . ') ' . $oSurveysGroup->title;
            }
        }
        //return join('\n',$options);
        return $options;
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return SurveysGroups the static model class
     */
    public static function model($className = __CLASS__)
    {
        /** @var self $model */
        $model = parent::model($className);
        return $model;
    }

    /**
     * get criteria from Permission
     * @return CDbCriteria
     */
    protected static function getPermissionCriteria()
    {
        $criteriaPerm = new CDbCriteria();
        if (!Permission::model()->hasGlobalPermission("surveys", 'read') || !Permission::model()->hasGlobalPermission("surveysgroups", 'read')) {
            $userid = App()->getCurrentUserId();
            if (!empty($userid)) {
                /* owner of surveygroup */
                $criteriaPerm->compare('t.owner_id', $userid, false);
                /* Simple permission on SurveysGroup inside a group */
                $criteriaPerm->mergeWith(array(
                    'join' => "LEFT JOIN {{permissions}} AS permissions ON (permissions.entity_id = t.gsid AND permissions.permission='group' AND permissions.entity='surveysgroups' AND permissions.uid='" . $userid . "') ",
                ));
                $criteriaPerm->compare('permissions.read_p', '1', false, 'OR');
                /* Permission on Survey inside a group */
                $criteriaPerm->mergeWith(array(
                    'join' => "LEFT JOIN {{surveys}} AS surveys ON (surveys.gsid = t.gsid)
                            LEFT JOIN {{permissions}} AS surveypermissions ON (surveypermissions.entity_id = surveys.sid AND surveypermissions.permission='survey' AND surveypermissions.entity='survey' AND surveypermissions.uid='" . $userid . "') ",
                ));
                $criteriaPerm->compare('surveys.owner_id', $userid, false, 'OR');
                $criteriaPerm->compare('surveypermissions.read_p', '1', false, 'OR');
            }
            /* default survey group is always avaliable */
            $criteriaPerm->compare('t.gsid', '1', false, 'OR');
            /* survey group set as avaiable */
            $criteriaPerm->compare('t.alwaysavailable', '1', false, 'OR'); // Is public
        }
        return $criteriaPerm;
    }

    /**
     * Get Permission data for SurveysGroup
     * @return array
     */
    public static function getPermissionData()
    {
        $aPermission = array(
            'group' => array(
                'create' => false,
                'read' => true, /* Minimal : forced to true when edit */
                'update' => true,
                'delete' => true,
                'import' => false,
                'export' => false,
                'title' => gT("Group"),
                'description' => gT("Permission to update name/description of this group or to delete this group. Read permission is used to give access to this group."),
                'img' => ' ri-file-edit-line',
            ),
            'surveysettings' => array(
                'create' => false, /* always exist as inherit when group was created */
                'read' => true,
                'update' => true,
                'delete' => false, /* always exist as inherit when group was created */
                'import' => false,
                'export' => false,
                'title' => gT("Survey settings"),
                'description' => gT("Permission to update survey settings for this group"),
                'img' => ' ri-file-edit-line',
            ),
            'permission' => array(
                'create' => true, /* allowed to add new users or group */
                'read' => true,
                'update' => true,
                'delete' => true, /* update ? */
                'import' => false,
                'export' => false,
                'title' => gT("Survey group security"),
                'description' => gT("Permission to modify survey group security settings"),
                'img' => ' ri-shield-check-fill',
            ),
        );
        return $aPermission;
    }

    /**
     * @inheritdoc
     */
    public static function getMinimalPermissionRead()
    {
        return 'group';
    }

    /**
     * Get the owner id of this Survey group
     * Used for Permission
     * @return integer
     */
    public function getOwnerId()
    {
        return $this->owner_id;
    }

    /**
     * @inheritdoc
     */
    public function hasPermission($sPermission, $sCRUD = 'read', $iUserID = null)
    {
        /* If have global : return true */
        if (Permission::model()->hasPermission(0, 'global', 'surveysgroups', $sCRUD, $iUserID)) {
            return true;
        }
        /* Specific need gsid */
        if (!$this->gsid) {
            return false;
        }
        /* Finally : return specific one */
        return Permission::model()->hasPermission($this->gsid, 'surveysgroups', $sPermission, $sCRUD, $iUserID);
    }

    /**
     * Returns an available code based on the current group count.
     * @return string
     */
    public static function getNewCode()
    {
        $attempts = 0;
        $surveyGroupCount = self::model()->count();
        $groupNumber = $surveyGroupCount + 1;
        $groupCode = "SG" . str_pad($groupNumber, 2, '0', STR_PAD_LEFT);
        while (self::model()->countByAttributes(['name' => $groupCode]) > 0) {
            $attempts++;
            $groupNumber++;
            $groupCode = "SG" . str_pad($groupNumber, 2, '0', STR_PAD_LEFT);
            // We only try a number of times, based on the record count.
            if ($attempts > $surveyGroupCount + 1) {
                throw new \Exception("Unable to get a valid survey group code after " . ($surveyGroupCount + 1) . " attempts");
            }
        }
        return $groupCode;
    }
}
