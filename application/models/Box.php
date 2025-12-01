<?php

/**
 * This is the model class for table "{{boxes}}".
 *
 * The following are the available columns in table '{{boxes}}':
 * @property integer $id Primary key
 * @property integer $position
 * @property string $url
 * @property string $title
 * @property string $ico the icon ID
 * @property string $desc Description
 * @property string $page
 * @property integer $usergroup UserGroup ID
 */
class Box extends CActiveRecord
{
    /** @inheritdoc */
    public function tableName()
    {
        return '{{boxes}}';
    }

    /** @inheritdoc */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('url, title, ico, position, desc, page', 'required'),
            array('url', 'match', 'pattern' => '/(http:\/\/)?[a-zA-Z]([a-zA-Z0-9-_?&"\'=]\/?)*/'),
            array('url', 'LSYii_Validators', 'isUrl' => true),
            array('position', 'numerical', 'integerOnly' => true),
            array('position', 'unique', 'message' => gT('Position {value} already exists.')),
            array('usergroup', 'numerical', 'integerOnly' => true, 'min' => -3),
            array('ico', 'match', 'pattern' => '/^[A-Za-z0-9_ \-]+$/u','message' => gT('Icon name must be a simple class name (alphanumeric, space, minus and underscore).')),
            array('title', 'LSYii_Validators'),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('id, position, url, title, ico, desc, page, usergroup', 'safe', 'on' => 'search'),
        );
    }

    /** @inheritdoc */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
        );
    }

    /** @inheritdoc */
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
            'usergroup' => gT('Display this box to:')
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
        $pageSize = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']);
        $criteria = new CDbCriteria();

        $criteria->compare('id', $this->id);
        $criteria->compare('position', $this->position);
        $criteria->compare('url', $this->url, true);
        $criteria->compare('title', $this->title, true);
        $criteria->compare('ico', $this->ico, true);
        $criteria->compare('desc', $this->desc, true);
        $criteria->compare('page', $this->page, true);

        return new CActiveDataProvider($this, [
            'criteria'   => $criteria,
            'pagination' => [
                'pageSize' => $pageSize,
            ]
        ]);
    }

    /**
     * @return string
     */
    public function getSpanIcon()
    {
        $spanicon = '<span class="' . CHtml::encode($this->getIconName()) . ' text-success"></span>';
        return $spanicon;
    }

    /**
     * @return mixed|string
     */
    public function getUsergroupname()
    {
        $usergroupid = $this->usergroup;

        // Can't use switch because of empty case
        if (empty($usergroupid) || $usergroupid == '-2') {
            return gT('Only Superadmin');
        } elseif ($usergroupid == '-1') {
            return gT('Everybody');
        } elseif ($usergroupid == '-3') {
            return gT('Nobody');
        } else {
            $oUsergroup = UserGroup::model()->findByPk($usergroupid);

            // The group doesn't exist anymore
            if (!is_object($oUsergroup)) {
                return gT("Can't find user group!");
            }

            return $oUsergroup->name;
        }
    }

    /**
     * Returns the Buttons for the Grid View
     * @return string
     */
    public function getbuttons()
    {
        $permission_box_edit = Permission::model()->hasGlobalPermission('settings', 'update');
        $permission_box_delete = Permission::model()->hasGlobalPermission('settings', 'delete');
        $dropdownItems = [];
        $dropdownItems[] = [
            'title'            => gT('Edit'),
            'iconClass'        => 'ri-pencil-fill',
            'url'              => Yii::app()->createUrl("/homepageSettings/updateBox/id/$this->id"),
            'enabledCondition' => $permission_box_edit
        ];

        $dropdownItems[] = [
            'title'            => gT('Delete box'),
            'iconClass'        => 'ri-delete-bin-fill text-danger',
            'enabledCondition' => $permission_box_delete,
            'url' => Yii::app()->createUrl("/homepageSettings/deleteBox"),
            'linkClass' => 'selector--ConfirmModal',
            'linkAttributes'   => [
                'data-bs-toggle' => "tooltip",
                'data-bs-target' => 'top',
                'data-button-no' => gT('Cancel'),
                'data-button-yes' => gT('Delete'),
                'data-button-type' => 'btn-danger',
                'data-post'  => json_encode(['id' => $this->id]),
                'data-text'   => gT("Are you sure you want to delete this box?"),
            ]
        ];
        return App()->getController()->widget(
            'ext.admin.grid.GridActionsWidget.GridActionsWidget',
            ['dropdownItems' => $dropdownItems],
            true
        );
    }

    /**
     * List of all icons available for user [['id' => 1, 'icon' => 'name'],...]
     * Command to generate this list: grep -oh "icon-[a-z]*"
     * styles/Sea_Green/css/fonts.css | sort -u > ~/my_icon_list.txt
     * @return array
     */
    public function getIcons()
    {
        return [
            ['id' => 1 , 'icon' => 'ri-play-fill'],
            ['id' => 2 , 'icon' => 'ri-add-circle-fill'],
            ['id' => 3 , 'icon' => 'ri-chat-3-line'],
            ['id' => 4 , 'icon' => 'ri-chat-1-line'],
            ['id' => 5 , 'icon' => 'ri-git-branch-fill'],
            ['id' => 6 , 'icon' => 'ri-file-copy-line'],
            ['id' => 7 , 'icon' => 'ri-shield-user-line'],
            ['id' => 8 , 'icon' => 'ri-arrow-left-circle-fill'],
            ['id' => 9 , 'icon' => 'ri-skip-back-fill'],
            ['id' => 10, 'icon' => 'ri-skip-forward-fill'],
            ['id' => 11, 'icon' => 'ri-arrow-right-circle-fill'],
            ['id' => 12, 'icon' => 'ri-grid-line'],
            ['id' => 13, 'icon' => 'ri-settings-5-fill'],
            ['id' => 14, 'icon' => 'ri-pencil-fill'],
            ['id' => 15, 'icon' => 'ri-mail-settings-line'],
            ['id' => 17, 'icon' => 'ri-download-fill'],
            ['id' => 18, 'icon' => 'ri-superscript'],
            ['id' => 19, 'icon' => 'ri-checkbox-fill'],
            ['id' => 20, 'icon' => 'ri-list-settings-line'],
            ['id' => 21, 'icon' => 'ri-upload-fill'],
            ['id' => 22, 'icon' => 'ri-mail-send-fill'],
            ['id' => 23, 'icon' => 'ri-price-tag-3-line'],
            ['id' => 24, 'icon' => 'ri-list-unordered'],
            ['id' => 25, 'icon' => 'ri-shut-down-line'],
            ['id' => 26, 'icon' => 'ri-fullscreen-fill'],
            ['id' => 27, 'icon' => 'ri-fullscreen-exit-fill'],
            ['id' => 28, 'icon' => 'ri-shape-fill'],
            ['id' => 29, 'icon' => 'ri-eject-fill'],
            ['id' => 30, 'icon' => 'ri-mail-volume-fill'],
            ['id' => 31, 'icon' => 'ri-list-ordered'],
            ['id' => 32, 'icon' => 'ri-survey-fill'],
            ['id' => 33, 'icon' => 'ri-exchange-funds-fill'],
            ['id' => 34, 'icon' => 'ri-save-line'],
            ['id' => 35, 'icon' => 'ri-lock-line'],
            ['id' => 36, 'icon' => 'ri-shield-check-fill'],
            ['id' => 37, 'icon' => 'ri-star-fill'],
            ['id' => 38, 'icon' => 'ri-user-shared-fill'],
            ['id' => 39, 'icon' => 'ri-brush-fill'],
            ['id' => 40, 'icon' => 'ri-admin-fill'],
            ['id' => 41, 'icon' => 'ri-tools-fill'],
            ['id' => 42, 'icon' => 'ri-user-fill'],
            ['id' => 43, 'icon' => 'ri-group-fill'],
            ['id' => 44, 'icon' => 'ri-history-line'],
            ['id' => 45, 'icon' => 'ri-stop-fill'],
            ['id' => 46, 'icon' => 'ri-shopping-cart-fill'],
            ['id' => 47, 'icon' => 'ri-user-line'],
            ['id' => 48, 'icon' => 'ri-settings-5-line'],
            ['id' => 49, 'icon' => 'ri-brush-line'],
            ['id' => 50, 'icon' => 'ri-add-line'],
            ['id' => 51, 'icon' => 'ri-function-fill'],
            ['id' => 52, 'icon' => 'ri-plug-line'],
            ['id' => 53, 'icon' => 'ri-user-settings-line'],
            ['id' => 54, 'icon' => 'ri-paint-fill'],
            ['id' => 55, 'icon' => 'ri-settings-3-fill'],
            ['id' => 56, 'icon' => 'ri-group-line'],
            ['id' => 57, 'icon' => 'ri-plug-fill'],
        ];
    }

    /**
     * Search the iconName for current icon
     *
     * @return string
     */
    public function getIconName()
    {
        $icons = $this->getIcons();
        $iconArrayKey = array_search($this->ico, array_column($icons, 'icon'), false);
        $iconName = $icons[$iconArrayKey]['icon'] ?: '';
        return $iconName;
    }

    /**
     * @return int
     */
    // phpcs:ignore
    public function getIcons_length()
    {
        return count($this->icons);
    }

    /**
     * @inheritdoc
     * @return Box the static model class
     */
    public static function model($className = __CLASS__)
    {
        /** @var self $model */
        $model = parent::model($className);
        return $model;
    }

    /**
     * Method to restore the default surveymenu entries
     * This method will fail if the surveymenus have been tempered, or wrongly set
     *
     * @return boolean
     */
    public function restoreDefaults()
    {
        $oDB = Yii::app()->db;
        $sOldLanguage = App()->language;
        switchMSSQLIdentityInsert('boxes', true);
        $oTransaction = $oDB->beginTransaction();
        try {
            $oDB->createCommand()->truncateTable('{{boxes}}');

            $defaultBoxes = LsDefaultDataSets::getBoxesData();
            foreach ($defaultBoxes as $Boxes) {
                $oDB->createCommand()->insert("{{boxes}}", $Boxes);
            }
            $oTransaction->commit();
        } catch (Exception $e) {
            App()->setLanguage($sOldLanguage);
            return false;
        }
        switchMSSQLIdentityInsert('boxes', false);
        return true;
    }
}
