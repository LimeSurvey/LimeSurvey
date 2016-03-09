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
            array('usergroup', 'numerical', 'integerOnly'=>true, 'min'=>-3),
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
            'position' => gT('Position:'),
            'url' => gT('Destination URL:'),
            'title' => gT('Title:'),
            'ico' => gT('Icon:'),
            'desc' => gT('Description:'),
            'page' => gT('Name of the page where the box should be shown'),
            'usergroup'=> gT('Display this box to:')
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

        // Can't use switch because of empty case
        if ( empty($usergroupid) || $usergroupid=='-2'  )
        {
            return gT('Only Superadmin');
        }
        elseif ( $usergroupid=='-1' )
        {
            return gT('Everybody');
        }
        elseif ( $usergroupid=='-3' )
        {
            return gT('Nobody');
        }
        else
        {
            $oUsergroup = UserGroup::model()->findByPk($usergroupid);

            // The group doesn't exist anymore
            if(!is_object($oUsergroup))
                return gT("Can't find user group!");

            return $oUsergroup->name;
        }
    }

    public function getbuttons()
    {

        $url = Yii::app()->createUrl("/admin/homepagesettings/sa/update/id/");
        $url .= '/'.$this->id;
        $button = '<a class="btn btn-default" href="'.$url.'" role="button"><span class="glyphicon glyphicon-pencil" ></span></a>';

        $url = Yii::app()->createUrl("/admin/homepagesettings/sa/delete/id/");
        $url .= '/'.$this->id;
        $button .= '<a class="btn btn-default" href="'.$url.'" role="button" data-confirm="'.gT('Are you sure you want to delete this box ?').'"><span class="text-danger glyphicon glyphicon-trash" ></span></a>';
        return $button;
    }

    /**
     * List of all icons available for user
     * Command to generate this list: grep -oh "icon-[a-z]*" styles/Sea_Green/css/fonts.css | sort -u > ~/my_icon_list.txt
     * @return array
     */
    public function getIcons()
    {
        return array(
            'icon-active',
            'icon-add',
            'icon-assessments',
            'icon-browse',
            'icon-conditions',
            'icon-copy',
            'icon-cpdb',
            'icon-databack',
            'icon-databegin',
            'icon-dataend',
            'icon-dataforward',
            'icon-defaultanswers',
            'icon-do',
            'icon-edit',
            'icon-emailtemplates',
            'icon-expired',
            'icon-export',
            'icon-exportcsv',
            'icon-exportr',
            'icon-exportspss',
            'icon-exportvv',
            'icon-expression',
            'icon-expressionmanagercheck',
            'icon-global',
            'icon-import',
            'icon-importcsv',
            'icon-importldap',
            'icon-importvv',
            'icon-inactive',
            'icon-invite',
            'icon-label',
            'icon-labels',
            'icon-list',
            'icon-logout',
            'icon-maximize',
            'icon-minimize',
            'icon-organize',
            'icon-quota',
            'icon-remind',
            'icon-renumber',
            'icon-resetsurveylogic',
            'icon-responses',
            'icon-saved',
            'icon-security',
            'icon-settings',
            'icon-shield',
            'icon-superadmin',
            'icon-survey',
            'icon-takeownership',
            'icon-template',
            'icon-templatepermissions',
            'icon-templates',
            'icon-tools',
            'icon-user',
            'icon-usergroup',
            'icon-viewlast'
        );
    }

    public function getIcons_length()
    {
        return count($this->icons);
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
