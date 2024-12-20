<?php

/**
 * This is the model class for table "{{permissiontemplates}}".
 *
 * The following are the available columns in table '{{permissiontemplates}}':
 * @property integer $ptid
 * @property string $name
 * @property string $description
 * @property string $renewed_last
 * @property string $created_at
 * @property integer $created_by
 */
class Permissiontemplates extends CActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{permissiontemplates}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('name, description, renewed_last, created_at, created_by', 'required'),
            array('created_by', 'numerical', 'integerOnly' => true),
            array('name', 'length', 'max' => 192),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('ptid, name, description, renewed_last, created_at, created_by', 'safe', 'on' => 'search'),
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
            'connectedusers' => array(self::HAS_MANY, 'UserInPermissionrole', ['ptid']),
        );
    }

    /**
     * Collects and maps the connected userids to userobjects
     *
     * @return array filled with usermodels
     */
    public function getConnectedUserobjects(): array
    {
        return array_map(
            function ($oMappingInstance) {
                return User::model()->findByPk($oMappingInstance->uid);
            },
            $this->connectedusers
        );
    }

    /**
     * Apply a user role to the user.
     *
     * A user role is defined in table prefix_permissiontemplates.
     * If user does not have the user role already, a new entry will be made in
     * table prefix_user_in_permissionrole
     *
     * @param int $iUserId
     * @param int $ptid Permissiontemplates id
     * @return boolean
     */
    public function applyToUser(int $iUserId, int $ptid = null): bool
    {
        if ($ptid == null) {
            $ptid = $this->ptid;
        }

        $oModel = UserInPermissionrole::model()->findByPk(['ptid' => $ptid, 'uid' => $iUserId]);

        if ($oModel == null) {
            $oModel = new UserInPermissionrole();
            $oModel->ptid = $ptid;
            $oModel->uid = $iUserId;
        }

        return $oModel->save();
    }

    /**
     * Clear User.
     * @param int $iUserId
     * @return boolean
     */
    public function clearUser(int $iUserId): bool
    {
        $aModels = UserInPermissionrole::model()->findAllByAttributes(['uid' => $iUserId]);

        if (safecount($aModels) == 0) {
            return true;
        }

        return array_reduce(
            $aModels,
            function ($cur, $oModel) {
                return $cur && $oModel->delete();
            },
            true
        );
    }

    /**
     * Returns Date Format.
     * @return string
     */
    public function getDateFormat(): string
    {
        $dateFormat = getDateFormatData(Yii::app()->session['dateformat']);
        return $dateFormat['phpdate'];
    }

    /**
     * Returns formatted 'created at' date.
     * @return string
     */
    public function getFormattedDateCreated(): string
    {
        $dateCreated = $this->created_at;
        $date = new DateTime($dateCreated);
        return $date->format($this->dateFormat);
    }

    /**
     * Returns formatted 'renewed_last' date.
     * @return string
     */
    public function getFormattedDateModified(): string
    {
        $dateModified = $this->renewed_last;
        $date = new DateTime($dateModified);
        return $date->format($this->dateFormat);
    }
    /**
     * Gets the buttons for the GridView
     *
     * @return string
     */
    public function getButtons(): string
    {
        $detailUrl         = Yii::app()->getController()->createUrl('userRole/viewRole', ['ptid' => $this->ptid]);
        $editUrl           = Yii::app()->getController()->createUrl('userRole/editRoleModal', ['ptid' => $this->ptid]);
        $exportRoleUrl     = Yii::app()->getController()->createUrl('userRole/runExport', ['ptid' => $this->ptid]);
        $setPermissionsUrl = Yii::app()->getController()->createUrl(
            'userRole/renderModalPermissions',
            ['ptid' => $this->ptid]
        );
        $deleteUrl         = Yii::app()->getController()->createUrl('userRole/delete');

        //currently there are no special permissions for user role (see controller actions...)
        $permissionSuperAdminRead = Permission::model()->hasGlobalPermission('superadmin', 'read');

        $dropdownItems = [];

        $dropdownItems[] = [
            'title'            => gT('Edit role'),
            'iconClass'        => 'ri-pencil-fill',
            'enabledCondition' => $permissionSuperAdminRead,
            'linkClass'        => 'RoleControl--action--openmodal RoleControl--action--edituser',
            'linkAttributes'   => [
                'data-href' => $editUrl
            ]
        ];
        $dropdownItems[] = [
            'title'            => gT('View role details'),
            'iconClass'        => 'ri-search-line',
            'enabledCondition' => $permissionSuperAdminRead,
            'linkClass'        => 'RoleControl--action--openmodal RoleControl--action--userdetail',
            'linkAttributes'   => [
                'data-href' => $detailUrl
            ]
        ];

        $dropdownItems[] = [
            'title'            => gT('Edit permission'),
            'iconClass'        => 'ri-lock-fill',
            'enabledCondition' => $permissionSuperAdminRead,
            'linkClass'        => 'RoleControl--action--openmodal RoleControl--action--permissions',
            'linkAttributes'   => [
                'data-href' => $setPermissionsUrl,
                'data-modalSize' => 'modal-lg',
            ]
        ];

        $dropdownItems[] = [
            'title'            => gT('Export role'),
            'iconClass'        => 'ri-download-fill',
            'enabledCondition' => $permissionSuperAdminRead,
            'linkClass'        => 'RoleControl--action--link',
            'url'              => $exportRoleUrl,
        ];

        $deletePostData = json_encode(['ptid' => $this->ptid]);
        $dropdownItems[] = [
            'title'            => gT('Delete user role'),
            'iconClass'        => 'ri-delete-bin-fill text-danger',
            'enabledCondition' => $permissionSuperAdminRead,
            'linkAttributes'   => [
                'data-bs-toggle' => "modal",
                'data-post-url'  => $deleteUrl,
                'data-post-datas' => $deletePostData,
                'data-message'   => sprintf(gt("Are you sure you want to delete user role '%s'?"), CHtml::encode($this->name)),
                'data-bs-target' => "#confirmation-modal",
                'data-btnclass'  => 'btn-danger',
                'data-btntext'   => gt('Delete'),
                'data-title'     => gt('Delete user role')
            ]
        ];

        return App()->getController()->widget(
            'ext.admin.grid.GridActionsWidget.GridActionsWidget',
            ['dropdownItems' => $dropdownItems],
            true
        );
    }

    /**
     * Returns Columns.s
     * @return array
     */
    public function getColumns()
    {
        // TODO should be static
        $cols = array(
            array(
                'value' => "\"<input type='checkbox' class='RoleControl--selector-roleCheckbox' name='selectedRole[]' value='\".\$data->ptid.\"' />\"",
                'type' => 'raw',
                'header' => "<input type='checkbox' id='RoleControl--action-toggleAllRoles' />",
                'filter' => false
            ),
            array(
                "name" => 'name',
                "header" => gT("Name")
            ),
            array(
                "name" => 'description',
                "header" => gT("Description"),
                "value" => '$data->description',
                "htmlOptions" => ["style" => "white-space: pre-wrap"],
                "headerHtmlOptions" => ["style" => "max-width: 35%"],
            ),
            array(
                "name" => 'renewed_last',
                "header" => gT("Modified"),
                "value" => '$data->formattedDateModified'
            ),
            array(
                "name" => "created_at",
                "header" => gT("Created"),
                "value" => '$data->formattedDateCreated',

            ),
            array(
                "name" => 'buttons',
                "type" => 'raw',
                "header" => gT("Action"),
                'filter' => false,
                'headerHtmlOptions' => ['class' => 'ls-sticky-column'],
                'htmlOptions'       => ['class' => 'text-center button-column ls-sticky-column'],
            ),
        );

        return $cols;
    }

    /**
     * @return SimpleXMLElement
     */
    public function compileExportXML()
    {
        $xml = new SimpleXMLElement('<limepermissionrole/>');

        //Meta section
        $meta = $xml->addChild('meta');
        $meta->addChild('name', '<![CDATA[' . $this->name . ']]>');
        $meta->addChild('description', '<![CDATA[' . $this->description . ']]>');
        $meta->addChild('date', date('Y-m-d H:i:s'));
        $meta->addChild('createdOn', Yii::app()->getConfig('sitename'));
        $meta->addChild('createdBy', Yii::app()->user->id);

        // Get base permissions
        $aBasePermissions = Permission::model()->getGlobalBasePermissions();

        //Permission section
        $permission = $xml->addChild('permissions');
        foreach ($aBasePermissions as $sPermissionKey => $aCRUDPermissions) {
            $curKeyRow = $permission->addChild($sPermissionKey);
            foreach ($aCRUDPermissions as $sCRUDKey => $CRUDValue) {
                $curKeyRow->addChild(
                    $sCRUDKey,
                    ($this->getHasPermission($sPermissionKey, $sCRUDKey) ? 1 : 0)
                );
            }
        }

        return $xml;
    }

    /**
     * @param SimpleXMLElement $xmlEntitiy
     * @param boolean $includeRootData
     * @return Permissiontemplates|boolean
     */
    public function createFromXML($xmlEntitiy, $includeRootData = false)
    {
        $name = $this->removeCdataFormat($xmlEntitiy->meta->name);
        $iExisiting = self::model()->countByAttributes(['name' => $name]);
        if ($iExisiting > 0) {
            return false;
        }
        $oRole = new self();
        $oRole->name = $this->removeCdataFormat($xmlEntitiy->meta->name);
        $oRole->description = $this->removeCdataFormat($xmlEntitiy->meta->description);

        if ($includeRootData) {
            $oRole->created_at = $this->removeCdataFormat($xmlEntitiy->meta->createdOn);
            $oRole->created_by = $this->removeCdataFormat($xmlEntitiy->meta->createdBy);
        } else {
            $oRole->created_by = App()->user->id;
            $oRole->created_at = date('Y-m-d H:i');
        }
        $oRole->renewed_last = date('Y-m-d H:i');

        if ($oRole->save()) {
            return $oRole;
        }

        return false;
    }

    /**
     * @param mixed $node XML node?
     * @return string
     */
    public function removeCdataFormat($node)
    {
        $nodeText = (string) $node;
        $regex_replace = array('','');
        $regex_patterns = array(
            '/<!\[CDATA\[/',
            '/\]\]>/'
        );
        return trim(preg_replace($regex_patterns, $regex_replace, $nodeText));
    }

    /**
     * Return true if this role GIVE a permission
     * Used in self::compileExportXML only
     * @param string $sPermission
     * @param string $sCRUD
     * @return boolean
     */
    public function getHasPermission($sPermission, $sCRUD)
    {
        return Permission::model()->roleHasPermission($this->ptid, $sPermission, $sCRUD);
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
        $criteria = new CDbCriteria();

        $criteria->compare('ptid', $this->ptid);
        $criteria->compare('name', $this->name, true);
        $criteria->compare('description', $this->description, true);
        $criteria->compare('renewed_last', $this->renewed_last, true);
        $criteria->compare('created_at', $this->created_at, true);
        $criteria->compare('created_by', $this->created_by);

        return new CActiveDataProvider($this, array(
            'criteria'   => $criteria,
            'pagination' => array(
                'pageSize' => App()->user->getState('pageSize', App()->params['defaultPageSize']),
            )
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Permissiontemplates the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
