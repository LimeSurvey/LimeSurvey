<?php

/**
 * This is the model class for table "{{tutorial_entry}}".
 *
 * The following are the available columns in table '{{tutorial_entry}}':
 * @property integer $teid
 * @property integer $tid
 * @property string $title
 * @property string $content
 * @property string $settings
 *
 * The following are the available model relations:
 * @property Tutorial $t
 */
class TutorialEntry extends LSActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{tutorial_entries}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('title, content, settings', 'required'),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('teid, title, content, settings', 'safe', 'on' => 'search'),
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
            'tutorialEntryRelation' => array(self::HAS_MANY, 'TutorialEntryRelation', 'teid'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'teid' => gT('Tutorial Entry Id'),
            'title' => gT('Title'),
            'content' => gT('Content'),
            'settings' => gT('Settings'),
        );
    }

    public function getColumns()
    {
        return [
            [
                'name' => 'teid',
            ],
            [
                'name' => 'title',
            ],
            [
                'name' => 'content',
            ],
            [
                'name' => 'settings',
            ],
        ];
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

        $criteria->compare('teid', $this->teid);
        $criteria->compare('title', $this->title, true);
        $criteria->compare('content', $this->content, true);
        $criteria->compare('settings', $this->settings, true);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return TutorialEntry the static model class
     */
    public static function model($className = __CLASS__)
    {
        /** @var TutorialEntry $model */
        $model = parent::model($className);
        return $model;
    }

    public function getStepFromEntry()
    {
        $stepArray = json_decode($this->settings, true);
        $stepArray['content'] = gT($this->content, 'unescaped');
        $stepArray['title'] = gT($this->title, 'unescaped');
        if (isset($stepArray['path'])) {
            $path = $stepArray['path'];
            $params = array();
            if (is_array($stepArray['path'])) {
                $path = $stepArray['path'][0];
                $params = $stepArray['path'][1] ?? [];
            }
            $stepArray['path'] = App()->createUrl($path, $params);
        }
        return $stepArray;
    }
}
