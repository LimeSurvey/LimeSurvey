<?php

/**
 * This is the model class for table "{{surveys_groups}}".
 *
 * The followings are the available columns in table '{{surveys_groups}}':
 * @property integer $gsid
 * @property string $name
 * @property string $title
 * @property string $description
 * @property integer $sortorder
 * @property integer $owner_id
 * @property integer $parent_id
 * @property string $created
 * @property string $modified
 * @property integer $created_by
 * @property object $parentgroup
 * @property boolean $hasSurveys
 */
class SurveysGroups extends LSActiveRecord
{
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
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('name, sortorder, created_by, title', 'required'),
            array('sortorder, owner_id, parent_id, created_by', 'numerical', 'integerOnly'=>true),
            array('name', 'length', 'max'=>45),
            array('name', 'match', 'pattern'=> '/^[A-Za-z0-9_\.]+$/u','message'=> gT('Group name can contain only alphanumeric character, underscore or dot.')),
            array('title', 'length', 'max'=>100),
            array('description, created, modified', 'safe'),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('gsid, name, title, description, owner_id, parent_id, created, modified, created_by', 'safe', 'on'=>'search'),
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
            'gsid'        => gT('ID'),
            'name'        => gT('Name'),
            'title'       => gT('Title'),
            'description' => gT('Description'),
            'sortorder'   => gT('Sort order'),
            'owner_id'   => gT('Owner UID'),
            'parent_id'   => gT('Parent group'),
            'created'     => gT('Created on'),
            'modified'    => gT('Modified on'),
            'created_by'  => gT('Created by'),
        );
    }

    public function getColumns()
    {
        return array(

                array(
                    'id'=>'gsid',
                    'class'=>'CCheckBoxColumn',
                    'selectableRows' => '100',
                ),

                array(
                    'header' => gT('Survey group ID'),
                    'name' => 'gsid',
                    'value'=>'$data->hasViewSurveyGroupRight ? CHtml::link($data->gsid, Yii::app()->createUrl("admin/surveysgroups/sa/update/",array("id"=>$data->gsid))) : $data->gsid',
                    'type'=>'raw',
                    'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                    'htmlOptions' => array('class' => 'hidden-xs'),
                ),

                array(
                    'header' => gT('Name'),
                    'name' => 'name',
                    'value'=>'$data->hasViewSurveyGroupRight ? CHtml::link($data->name, Yii::app()->createUrl("admin/surveysgroups/sa/update/",array("id"=>$data->gsid))) : $data->gsid',
                    'type'=>'raw',
                    'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                ),

                array(
                    'header' => gT('Title'),
                    'name' => 'title',
                    'value'=>'$data->title',
                    'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                ),

                array(
                    'header' => gT('Description'),
                    'name' => 'description',
                    'value'=>'$data->description',
                    'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                    'htmlOptions' => array('class' => 'hidden-xs'),
                ),

                array(
                    'header' => gT('Parent group'),
                    'name' => 'parent',
                    'value'=>'$data->parentTitle',
                    'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                    'htmlOptions' => array('class' => 'hidden-xs'),
                ),

                array(
                    'header' => gT('Owner'),
                    'name' => 'owner',
                    'value'=>'!empty($data->owner) ? $data->owner->users_name : ""',
                    'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                    'htmlOptions' => array('class' => 'hidden-xs'),
                ),

                array(
                    'header' => gT('Order'),
                    'name' => 'sortorder',
                    'value'=>'$data->sortorder',
                    'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                    'htmlOptions' => array('class' => 'hidden-xs'),
                ),


                array(
                    'header' => gT('Actions'),
                    'name' => 'sortorder',
                    'type' => 'raw',
                    'value'=> '$data->buttons',
                    'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                    'htmlOptions' => array('class' => 'hidden-xs'),
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
        return Permission::model()->hasSurveyGroupPermission($this->gsid, 'group', 'read');
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

        $criteria = new CDbCriteria;

        $criteria->select = array('DISTINCT t.*');

        $criteria->compare('gsid', $this->gsid);
        $criteria->compare('name', $this->name, true);
        $criteria->compare('title', $this->title, true);
        $criteria->compare('description', $this->description, true);
        $criteria->compare('sortorder', $this->sortorder);
        $criteria->compare('owner_id', $this->owner_id);
        $criteria->compare('parent_id', $this->parent_id);
        $criteria->compare('created', $this->created, true);
        $criteria->compare('modified', $this->modified, true);
        $criteria->compare('created_by', $this->created_by);

        // Permission
        $criteriaPerm = self::getPermissionCriteria();
        $criteria->mergeWith($criteriaPerm, 'AND');

        $dataProvider = new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));

        $dataProvider->setTotalItemCount(count($dataProvider->getData()));

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
        $nbSurvey = Survey::model()->countByAttributes(array("gsid"=>$this->gsid));
        return $nbSurvey > 0;
    }

    /**
     * Returns true if survey group has child survey groups
     * @return boolean
     */
    public function getHasChildGroups()
    {
        $nbSurvey = SurveysGroups::model()->countByAttributes(array("parent_id"=>$this->gsid));
        return $nbSurvey > 0;
    }


    public function getAllParents($bOnlyGsid=false)
    {
        $aParents = array();
        $oRSurveyGroup = $this;
        while (!empty($oRSurveyGroup->parent_id)){
            $oRSurveyGroup =  SurveysGroups::model()->findByPk($oRSurveyGroup->parent_id);
            $aParents[] = ($bOnlyGsid)?$oRSurveyGroup->gsid:$oRSurveyGroup;
        }

        return $aParents;
    }



    /**
     * @return string
     */
    public function getButtons()
    {
        $sDeleteUrl = App()->createUrl("admin/surveysgroups/sa/delete", array("id"=>$this->gsid));
        $sEditUrl = App()->createUrl("admin/surveysgroups/sa/update", array("id"=>$this->gsid));
        $sSurveySettingsUrl = App()->createUrl("admin/surveysgroups/sa/surveysettings", array("id"=>$this->gsid));
        $sPermissionUrl = App()->createUrl("admin/surveysgroups/sa/permissions", array("id"=>$this->gsid));
        $button = '';
        if(Permission::model()->hasSurveyGroupPermission($this->gsid,'groupsettings','read')) {
            $button .= '<a class="btn btn-default" href="'.$sEditUrl.'" role="button" data-toggle="tooltip" title="'.gT('Edit survey group').'"><i class="fa fa-edit" aria-hidden="true"></i><span class="sr-only">'.gT('Edit survey group').'</span></a>';
        }
        if(Permission::model()->hasSurveyGroupPermission($this->gsid,'surveysettings','read')) {
            $button .= '<a class="btn btn-default" href="'.$sSurveySettingsUrl.'" role="button" data-toggle="tooltip" title="'.gT('Survey settings').'"><i class="fa fa-cog" aria-hidden="true"></i><span class="sr-only">'.gT('Survey settings').'</span></a>';
        }
        if (Permission::model()->hasSurveyGroupPermission($this->gsid,'permission','read')) {
            $button .= '<a class="btn btn-default" href="'.$sPermissionUrl.'" role="button" data-toggle="tooltip" title="'.gT('Permission').'"><i class="fa fa-lock" aria-hidden="true"></i><span class="sr-only">'.gT('Permission').'</span></a>';
        }
        /* Can not delete group #1 + with survey */
        if ($this->gsid!=1 && !$this->hasSurveys && Permission::model()->hasSurveyGroupPermission($this->gsid,'group','delete')) {
            $button .= '<a class="btn btn-default" href="#" data-href="'.$sDeleteUrl.'" data-target="#confirmation-modal" role="button" data-toggle="modal" data-message="'.gT('Do you want to continue?').'" data-tooltip="true" title="'.gT('Delete survey group').'"><i class="fa fa-trash text-danger " aria-hidden="true"></i><span class="sr-only">'.gT('Delete survey group').'</span></a>';
        }

        return $button;
    }

    /**
     * Get the group list for current user
     * @return array
     */
    public static function getSurveyGroupsList()
    {
        $aSurveyList = [];
        $criteria = new CDbCriteria;
        $criteriaPerm = self::getPermissionCriteria();
        $criteria->mergeWith($criteriaPerm, 'AND');

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
        if (!empty($gsid)){
            $oSurveysGroups = SurveysGroups::model()->findAll('gsid != :gsid', array(':gsid' => $gsid));
        } else {
            $oSurveysGroups = SurveysGroups::model()->findAll();
        }
        $options = [
            '' => gT('No parent group')
        ];


        foreach ($oSurveysGroups as $oSurveysGroup) {
            //$options[] = "<option value='".$oSurveymenu->id."'>".$oSurveymenu->title."</option>";

            $aParentsGsid = $oSurveysGroup->getAllParents(true);

            if ( ! in_array( $this->gsid, $aParentsGsid  ) ) {
                $options[''.($oSurveysGroup->gsid).''] = '('.$oSurveysGroup->name.') '.$oSurveysGroup->title;
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
        $criteriaPerm = new CDbCriteria;
        if (!Permission::model()->hasGlobalPermission("surveys", 'read')) {
            $criteriaPerm->mergeWith(array(
                'join'=>"LEFT JOIN {{surveys}} AS surveys ON (surveys.gsid = t.gsid)
                        LEFT JOIN {{permissions}} AS permissions ON (permissions.entity_id = surveys.sid AND permissions.permission='survey' AND permissions.entity='survey' AND permissions.uid='".Yii::app()->user->id."') ",
            ));
            $criteriaPerm->compare('t.owner_id', Yii::app()->user->id, false);
            $criteriaPerm->compare('surveys.owner_id', Yii::app()->user->id, false, 'OR');
            $criteriaPerm->compare('permissions.read_p', '1', false, 'OR');
            $criteriaPerm->compare('t.gsid', '1', false, 'OR');  // "default" survey group
        }
        return $criteriaPerm;
    }

    /**
     * Get Permission data for Permission object
     * @param string $key
     * @return array
     */
    public static function getPermissionData($key = null)
    {
        $aPermission = array(
            'group' => array(
                'create' => false,
                'read' => true, /* Without this : no real access … */
                'update' => true,
                'delete' => true,
                'import' => false,
                'export' => false,
                'title' => gT("Group"),
                'description' => gT("Permission to update this group name, description of this group. This include deletion of this group. Read permission is used for access to this group."),
                'img' => ' fa fa-edit',
            ),
            'surveysettings' => array(
                'create' => false, /* always exist as inherit when group was created */
                'read' => true,
                'update' => true,
                'delete' => false, /* always exist as inherit when group was created */
                'import' => false,
                'export' => false,
                'title' => gT("Survey settings"),
                'description' => gT("Permission to update survey settings for this group."),
                'img' => ' fa fa-edit',
            ),
            'permission' => array(
                'create' => true,
                'read' => true,
                'update' => true,
                'delete' => true,
                'import' => false,
                'export' => false,
                'title' => gT("Survey group security"),
                'description' => gT("Permission to modify survey group security settings"),
                'img' => ' fa fa-shield',
            ),
        );
        if ($key) {
            if(isset($aPermission[$key])) {
                return $aPermission[$key];
            }
            return null;
        }
        return $aPermission;
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
}
