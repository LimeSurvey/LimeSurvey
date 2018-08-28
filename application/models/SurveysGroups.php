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
 * @property integer $owner_uid
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

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('name, sortorder, created_by, title', 'required'),
            array('sortorder, owner_uid, parent_id, created_by', 'numerical', 'integerOnly'=>true),
            array('name', 'length', 'max'=>45),
            array('title', 'length', 'max'=>100),
            array('description, created, modified', 'safe'),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('gsid, name, title, description, owner_uid, parent_id, created, modified, created_by', 'safe', 'on'=>'search'),
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
            'owner'       => array(self::BELONGS_TO, 'User', 'owner_uid', 'together' => true),
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
            'owner_uid'   => gT('Owner UID'),
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
                    'type' => 'raw',
                    'value'=>'CHtml::link($data->gsid, Yii::app()->createUrl("admin/surveysgroups/sa/update/",array("id"=>$data->gsid)))',
                    'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                    'htmlOptions' => array('class' => 'hidden-xs has-link'),
                ),

                array(
                    'header' => gT('Name'),
                    'name' => 'name',
                    'type' => 'raw',
                    'value'=>'CHtml::link($data->name, Yii::app()->createUrl("admin/surveysgroups/sa/update/",array("id"=>$data->gsid)))',
                    'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                    'htmlOptions' => array('class' => 'has-link'),
                ),

                array(
                    'header' => gT('Title'),
                    'name' => 'title',
                    'type' => 'raw',
                    'value'=>'CHtml::link($data->title, Yii::app()->createUrl("admin/surveysgroups/sa/update/",array("id"=>$data->gsid)))',
                    'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                    'htmlOptions' => array('class' => 'has-link'),
                ),

                array(
                    'header' => gT('Description'),
                    'name' => 'description',
                    'type' => 'raw',
                    'value'=>'CHtml::link($data->description, Yii::app()->createUrl("admin/surveysgroups/sa/update/",array("id"=>$data->gsid)))',
                    'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                    'htmlOptions' => array('class' => 'hidden-xs has-link'),
                ),

                array(
                    'header' => gT('Parent group'),
                    'name' => 'parent',
                    'type' => 'raw',
                    'value'=>'CHtml::link( $data->parentTitle, Yii::app()->createUrl("admin/surveysgroups/sa/update/",array("id"=>$data->gsid)))',
                    'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                    'htmlOptions' => array('class' => 'hidden-xs has-link'),
                ),

                array(
                    'header' => gT('Owner'),
                    'name' => 'owner',
                    'value'=>'$data->owner->users_name',
                    'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                    'htmlOptions' => array('class' => 'hidden-xs has-link'),
                ),

                array(
                    'header' => gT('Order'),
                    'name' => 'sortorder',
                    'type' => 'raw',
                    'value'=>'CHtml::link($data->sortorder, Yii::app()->createUrl("admin/surveysgroups/sa/update/",array("id"=>$data->gsid)))',
                    'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                    'htmlOptions' => array('class' => 'hidden-xs has-link'),
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
        $criteria->compare('owner_uid', $this->owner_uid);
        $criteria->compare('parent_id', $this->parent_id);
        $criteria->compare('created', $this->created, true);
        $criteria->compare('modified', $this->modified, true);
        $criteria->compare('created_by', $this->created_by);

        // Permission
        // Note: reflect Permission::hasPermission
        if (!Permission::model()->hasGlobalPermission("surveys", 'read')) {
            $criteriaPerm = new CDbCriteria;

            // Multiple ON conditions with string values such as 'survey'
            $criteriaPerm->mergeWith(array(
                'join'=>"JOIN {{surveys}} AS surveys ON (surveys.gsid = t.gsid)
                        JOIN {{permissions}} AS permissions ON (permissions.entity_id = surveys.sid AND permissions.permission='survey' AND permissions.entity='survey' AND permissions.uid='".Yii::app()->user->id."') ",
            ));

            $criteriaPerm->compare('t.owner_uid', Yii::app()->user->id, false);
            $criteriaPerm->compare('surveys.owner_id', Yii::app()->user->id, false, 'OR');
            $criteriaPerm->compare('permissions.read_p', '1', false, 'OR');
            $criteria->mergeWith($criteriaPerm, 'AND');
        }

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
     * @return string
     */
    public function getButtons()
    {
        $sDeleteUrl     = App()->createUrl("admin/surveysgroups/sa/delete", array("id"=>$this->gsid));
        $sEditUrl = App()->createUrl("admin/surveysgroups/sa/update", array("id"=>$this->gsid));
        $button         = '';

        if (!$this->gsid !== 1) {
            $button .= '<a class="btn btn-default" href="'.$sEditUrl.'" role="button" data-toggle="tooltip" title="'.gT('Edit survey group').'"><i class="fa fa-edit" ></i><span class="sr-only">'.gT('Edit survey group').'</span></a>';
        }
        if (!$this->hasSurveys) {
            $button .= '<a class="btn btn-default" href="#" data-href="'.$sDeleteUrl.'" data-target="#confirmation-modal" role="button" data-toggle="modal" data-message="'.gT('Do you want to continue?').'" data-tooltip="true" title="'.gT('Delete survey group').'"><i class="fa fa-trash text-danger "></i><span class="sr-only">'.gT('Delete survey group').'</span></a>';
        }

        return $button;
    }

    public static function getSurveyGroupsList()
    {
        $aSurveyList = [];
        $oSurveyGroups = self::model()->findAll();

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
            '' => gT('No parent menu')
        ];
        foreach ($oSurveysGroups as $oSurveysGroup) {
            //$options[] = "<option value='".$oSurveymenu->id."'>".$oSurveymenu->title."</option>";
            $options[''.($oSurveysGroup->gsid).''] = '('.$oSurveysGroup->name.') '.$oSurveysGroup->title;
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
}
