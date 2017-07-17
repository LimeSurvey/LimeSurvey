<?php

/**
 * This is the model class for table "{{template_configuration}}".
 *
 * The followings are the available columns in table '{{template_configuration}}':
 * @property integer $id
 * @property string $templates_name
 * @property integer $sid
 * @property integer $gsid
 * @property integer $uid
 * @property string $files_css
 * @property string $files_js
 * @property string $files_print_css
 * @property string $options
 * @property string $cssframework_name
 * @property string $cssframework_css
 * @property string $cssframework_js
 * @property string $packages_to_load
 */
class TemplateOptions extends CActiveRecord
{

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{template_configuration}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('templates_name', 'required'),
            array('sid, gsid, uid', 'numerical', 'integerOnly'=>true),
            array('templates_name', 'length', 'max'=>150),
            array('cssframework_name', 'length', 'max'=>45),
            array('files_css, files_js, files_print_css, options, cssframework_css, cssframework_js, packages_to_load', 'safe'),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('id, templates_name, sid, gsid, uid, files_css, files_js, files_print_css, options, cssframework_name, cssframework_css, cssframework_js, packages_to_load', 'safe', 'on'=>'search'),
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
            'templates_name' => 'Templates Name',
            'sid' => 'Sid',
            'gsid' => 'Gsid',
            'uid' => 'Uid',
            'files_css' => 'Files Css',
            'files_js' => 'Files Js',
            'files_print_css' => 'Files Print Css',
            'options' => 'Options',
            'cssframework_name' => 'Cssframework Name',
            'cssframework_css' => 'Cssframework Css',
            'cssframework_js' => 'Cssframework Js',
            'packages_to_load' => 'Packages To Load',
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
        $criteria->compare('templates_name',$this->templates_name,true);
        $criteria->compare('sid',$this->sid);
        $criteria->compare('gsid',$this->gsid);
        $criteria->compare('uid',$this->uid);
        $criteria->compare('files_css',$this->files_css,true);
        $criteria->compare('files_js',$this->files_js,true);
        $criteria->compare('files_print_css',$this->files_print_css,true);
        $criteria->compare('options',$this->options,true);
        $criteria->compare('cssframework_name',$this->cssframework_name,true);
        $criteria->compare('cssframework_css',$this->cssframework_css,true);
        $criteria->compare('cssframework_js',$this->cssframework_js,true);
        $criteria->compare('packages_to_load',$this->packages_to_load,true);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return TemplateOptions the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
}
