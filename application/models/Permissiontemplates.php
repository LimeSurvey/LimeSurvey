<?php

/**
 * This is the model class for table "{{permissiontemplates}}".
 *
 * The followings are the available columns in table '{{permissiontemplates}}':
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
			array('created_by', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>192),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('ptid, name, description, renewed_last, created_at, created_by', 'safe', 'on'=>'search'),
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
    public function getConnectedUserobjects() 
    {
        return array_map(
            function ($oMappingInstance) {
                return User::model()->findByPk($oMappingInstance->uid);
            }, 
            $this->connectedusers
        );
    }

    public function applyToUser($iUserId, $ptid = null) {

        if($ptid == null) {
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

    public function clearUser($iUserId) {
        $aModels = UserInPermissionrole::model()->findAllByAttributes(['uid' => $iUserId]);

        if (safecount($aModels) == 0) {
            return true;
        }

        return array_reduce(
            $aModels, 
            function ($cur,  $oModel) { 
                return $cur && $oModel->delete(); 
            },
            true
        );
    }

    /**
     * @return string
     */
    public function getDateFormat()
    {
        $dateFormat = getDateFormatData(Yii::app()->session['dateformat']);
        return $dateFormat['phpdate'];
    }

    public function getFormattedDateCreated()
    {
        $dateCreated = $this->created_at;
        $date = new DateTime($dateCreated);
        return $date->format($this->dateFormat);
    }

    public function getFormattedDateModified()
    {
        $dateModified = $this->renewed_last;
        $date = new DateTime($dateModified);
        return $date->format($this->dateFormat);
    }
    /**
     * Gets the buttons for the GridView
     * @return string
     */
    public function getButtons()
    {
        $detailUrl = Yii::app()->getController()->createUrl('/admin/roles/sa/viewrole', ['ptid' => $this->ptid]);
        $editUrl = Yii::app()->getController()->createUrl('/admin/roles/sa/editrolemodal', ['ptid' => $this->ptid]);
        $exportRoleUrl = Yii::app()->getController()->createUrl('/admin/roles/sa/runexport', ['ptid' => $this->ptid]);
        $setPermissionsUrl = Yii::app()->getController()->createUrl('/admin/roles/sa/setpermissions', ['ptid' => $this->ptid]);
        $deleteUrl = Yii::app()->getController()->createUrl('/admin/roles/sa/deleteconfirm');
        

        $roleDetail = ""
            ."<button 
                class='btn btn-sm btn-default RoleControl--action--openmodal RoleControl--action--userdetail' 
                data-href='".$detailUrl."'><i class='fa fa-search'></i></button>";

        $editPermissionButton = ""
            ."<button 
                class='btn btn-sm btn-default RoleControl--action--openmodal RoleControl--action--permissions' 
                data-href='".$setPermissionsUrl."'><i class='fa fa-lock'></i></button>";
        $editRoleButton = ""
            ."<button 
                class='btn btn-sm btn-default RoleControl--action--openmodal RoleControl--action--edituser' 
                data-href='".$editUrl."'><i class='fa fa-edit'></i></button>";
                
        $exportRoleButton = ""
            ."<a class='btn btn-sm btn-default RoleControl--action--link'
                href='".$exportRoleUrl."'><i class='fa fa-download'></i></a>";
                
        $deleteRoleButton = ""
            ."<button 
                id='RoleControl--delete-".$this->ptid."' 
                class='btn btn-sm btn-danger' 
                data-toggle='modal' 
                data-target='#confirmation-modal' 
                data-url='".$deleteUrl."' 
                data-ptid='".$this->ptid."'
                data-action='delrole' 
                data-onclick='(LS.RoleControl.triggerRunAction(\"#RoleControl--delete-".$this->ptid."\"))()' 
                data-message='".gt('Do you want to delete this role?')."'>
                    <i class='fa fa-trash text-danger'></i>
              </button>";

        return join("\n",[
            $roleDetail, 
            $editPermissionButton, 
            $editRoleButton, 
            $exportRoleButton, 
            $deleteRoleButton
        ]);
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        // TODO should be static
        $cols = array(
            array(
                'value' => "<input type='checkbox' class='RoleControl--selector-roleCheckbox' name='selectedRole[]' value='".$this->ptid."'>",
                'type' => 'raw',
                'header' => "<input type='checkbox' id='RoleControl--action-toggleAllRoles' />",
                'filter' => false
            ),
            array(
                "name" => 'buttons',
                "type" => 'raw',
                "header" => gT("Action"),
                'filter' => false
            ),
            array(
                "name" => 'name',
                "header" => gT("Name")
            ),
            array(
                "name" => 'description',
                "header" => gT("Description"),
                "value" => 'ellipsize($data->description, 40)'
            ),
            array(
                "name" => 'renewed_last',
                "header" => gT("Renewed"),
                "value" => '$data->formattedDateModified'
            ),
            array(
                "name" =>"created_at",
                "header" => gT("Created on"),
                "value" => '$data->formattedDateCreated',
    
            )
        );

        return $cols;
    }

    public function compileExportXML () {
        $xml = new SimpleXMLElement('<limepermissionrole/>');

        //Meta section
        $meta = $xml->addChild('meta');
        $meta->addChild('name', '<![CDATA['.$this->name.']]>');
        $meta->addChild('description', '<![CDATA['.$this->description.']]>');
        $meta->addChild('date', date('Y-m-d H:i:s'));
        $meta->addChild('createdOn', Yii::app()->getConfig('sitename'));
        $meta->addChild('createdBy', Yii::app()->user->id);

        
        // Get base permissions
        $aBasePermissions = Permission::model()->getGlobalBasePermissions();

        //Permission section
        $permission = $xml->addChild('permissions');
        foreach($aBasePermissions as $sPermissionKey=>$aCRUDPermissions) {
            $curKeyRow = $permission->addChild($sPermissionKey);
            foreach ($aCRUDPermissions as $sCRUDKey=>$CRUDValue) {
                $curKeyRow->addChild(
                    $sCRUDKey, 
                    ($this->getHasPermission($sPermissionKey, $sCRUDKey) ? 1 : 0)  
                );
            }
        }
        
        return $xml;
    }

    public function importFromXML ($xmlEntitiy) {
       return true;
    }

    public function getHasPermission($sPermission, $sCRUD) {
        return Permission::model()->hasRolePermission($this->ptid, $sPermission, $sCRUD);
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

		$criteria->compare('ptid',$this->ptid);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('renewed_last',$this->renewed_last,true);
		$criteria->compare('created_at',$this->created_at,true);
		$criteria->compare('created_by',$this->created_by);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Permissiontemplates the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
