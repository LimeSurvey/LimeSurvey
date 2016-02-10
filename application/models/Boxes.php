<?php

/**
 * This is the model class for table "{{boxes}}".
 *
 * The followings are the available columns in table '{{boxes}}':
 * @property integer $id
 * @property integer $position
 * @property string $url
 * @property string $title
 * @property string $desc
 * @property string $page
 */
class Boxes extends CActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{boxes}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('url, title, ico, desc, page', 'required'),
            array('position', 'numerical', 'integerOnly'=>true),
            array('usergroup', 'numerical', 'integerOnly'=>true),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('id, position, url, title, ico, desc, page, usergroup', 'safe', 'on'=>'search'),
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
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'position' => gT('Position'),
            'url' => gT('URL that the box points to'),
            'title' => gT('Title'),
            'ico' => gT('Icon to use in the box'),
            'desc' => gT('Description'),
            'page' => gT('Name of the page where the box should be shown'),
            'usergroup'=> gT('Box will be shown for that user group')
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

        $criteria=new CDbCriteria;

        $criteria->compare('id',$this->id);
        $criteria->compare('position',$this->position);
        $criteria->compare('url',$this->url,true);
        $criteria->compare('title',$this->title,true);
        $criteria->compare('ico',$this->ico,true);
        $criteria->compare('desc',$this->desc,true);
        $criteria->compare('page',$this->page,true);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }

    public function getSpanIcon()
    {
        $spanicon = '<span class="icon-'.$this->ico.' text-success"></span>';
        return $spanicon;
    }

    public function getUsergroupname()
    {
        $usergroupid = $this->usergroup;
        if(empty($usergroupid) || $usergroupid==0)
        {
            return gT('anybody');
        }
        else
        {
            $oUsergroup = UserGroup::model()->findByPk($usergroupid);

            // The group doesn't exist anymore
            if(!is_object($oUsergroup))
                return gT('nobody');

            return $oUsergroup->name;
        }
    }

    public function getbuttons()
    {

        $url = Yii::app()->createUrl("/admin/homepagesettings/sa/update/id/");
        $url .= '/'.$this->id;
        $button = '<a class="btn btn-default" href="'.$url.'" role="button"><span class="glyphicon glyphicon-pencil" ></span></a>';
/*
        $previewUrl = Yii::app()->createUrl("survey/index/action/previewquestion/sid/");
        $previewUrl .= '/'.$this->sid.'/gid/'.$this->gid.'/qid/'.$this->qid;

        $editurl = Yii::app()->createUrl("admin/questions/sa/editquestion/surveyid/$this->sid/gid/$this->gid/qid/$this->qid");

        $button = '<a class="btn btn-default open-preview"  data-toggle="tooltip" title="'.gT("Question preview").'"  aria-data-url="'.$previewUrl.'" aria-data-sid="'.$this->sid.'" aria-data-gid="'.$this->gid.'" aria-data-qid="'.$this->qid.'" aria-data-language="'.$this->language.'" href="# role="button" ><span class="glyphicon glyphicon-eye-open"  ></span></a> ';
        $button .= '<a class="btn btn-default"  data-toggle="tooltip" title="'.gT("Edit question").'" href="'.$editurl.'" role="button"><span class="glyphicon glyphicon-pencil" ></span></a>';
        $button .= '<a class="btn btn-default"  data-toggle="tooltip" title="'.gT("Question summary").'" href="'.$url.'" role="button"><span class="glyphicon glyphicon-list-alt" ></span></a>';

        $oSurvey = Survey::model()->findByPk($this->sid);

        if($oSurvey->active != "Y" && Permission::model()->hasSurveyPermission($this->sid,'surveycontent','delete' ))
        {
                $button .= '<a class="btn btn-default"  data-toggle="tooltip" title="'.gT("Delete").'" href="#" role="button"
                            onclick="if (confirm(\' '.gT("Deleting  will also delete any answer options and subquestions it includes. Are you sure you want to continue?","js").' \' )){ '.convertGETtoPOST(Yii::app()->createUrl("admin/questions/sa/delete/surveyid/$this->sid/gid/$this->gid/qid/$this->qid")).'} ">
                                <span class="text-danger glyphicon glyphicon-trash"></span>
                            </a>';
        }
*/

        return $button;
    }


    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Boxes the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
}
