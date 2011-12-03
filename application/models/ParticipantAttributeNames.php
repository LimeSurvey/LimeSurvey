<?php

/**
 * This is the model class for table "{{{{participant_attribute_names}}}}".
 *
 * The followings are the available columns in table '{{{{participant_attribute_names}}}}':
 * @property integer $attribute_id
 * @property string $attribute_type
 * @property string $visible
 */
class ParticipantAttributeNames extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return ParticipantAttributeNames the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{participant_attribute_names}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('attribute_type, visible', 'required'),
			array('attribute_type', 'length', 'max'=>4),
			array('visible', 'length', 'max'=>5),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('attribute_id, attribute_type, visible', 'safe', 'on'=>'search'),
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
			'attribute_id' => 'Attribute',
			'attribute_type' => 'Attribute Type',
			'visible' => 'Visible',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('attribute_id',$this->attribute_id);
		$criteria->compare('attribute_type',$this->attribute_type,true);
		$criteria->compare('visible',$this->visible,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	function getVisibleAttributes()
    {
        return Yii::app()->db->createCommand()->select('{{participant_attribute_names}}.*,{{participant_attribute_names}}_lang.*')->from('{{participant_attribute_names}}')->order('{{participant_attribute_names}}.attribute_id', 'desc')->join('{{participant_attribute_names}}_lang', '{{participant_attribute_names}}_lang.attribute_id = {{participant_attribute_names}}.attribute_id')->where('{{participant_attribute_names}}.visible = "TRUE" AND {{participant_attribute_names}}_lang.lang = "'.Yii::app()->session['adminlang'].'"')->queryAll();
    }

    function getAllAttributes()
    {
        return Yii::app()->db->createCommand()->select('{{participant_attribute_names}}.*,{{participant_attribute_names}}_lang.*')->from('{{participant_attribute_names}}')->order('{{participant_attribute_names}}.attribute_id', 'desc')->join('{{participant_attribute_names}}_lang', '{{participant_attribute_names}}_lang.attribute_id = {{participant_attribute_names}}.attribute_id')->where('{{participant_attribute_names}}_lang.lang = "'.Yii::app()->session['adminlang'].'"')->queryAll();
    }

    function getAllAttributesValues()
    {
        return Yii::app()->db->createCommand()->select('*')->from('{{participant_attribute_values}}')->queryAll();
    }
    
    function getAttributeVisibleID()
    {
        return Yii::app()->db->createCommand()->select('{{participant_attribute_names}}.*,{{participant_attribute_names_lang}}.*')->from('{{participant_attribute_names}}')->order('{{participant_attribute_names}}.attribute_id', 'desc')->join('{{participant_attribute_names_lang}}', '{{participant_attribute_names_lang}}.attribute_id = {{participant_attribute_names}}.attribute_id')->where('{{participant_attribute_names_lang}}.lang = "'.Yii::app()->session['adminlang'].'" AND {{participant_attribute_names}}.visible = "TRUE"')->queryAll();
    }

    function getParticipantVisibleAttribute($participant_id)
    {
        return Yii::app()->db->createCommand()->select('{{participant_attribute}}.*,{{participant_attribute_names}}.*,{{participant_attribute_names_lang}}.*')->from('{{participant_attribute}}')->order('{{participant_attribute}}.attribute_id','desc')->join('{{participant_attribute_names_lang}}','{{participant_attribute}}.attribute_id = {{participant_attribute_names_lang}}.attribute_id')->join('{{participant_attribute_names}}', '{{participant_attribute}}.attribute_id = {{participant_attribute_names}}.attribute_id')->where('{{participant_attribute_names_lang}}.lang = "'.Yii::app()->session['adminlang'].'" AND lang = "'.Yii::app()->session['adminlang'].'" AND participant_id = "'.$participant_id.'"')->queryAll();
    }

    function getAttributeValue($participantid,$attributeid)
    {
        $data = Yii::app()->db->createCommand()->select('*')->from('{{participant_attribute}}')->where('participant_id = "'.$participantid.'" AND attribute_id = '.$attributeid)->queryRow();
        return $data;
    }

    function getAttributes()
    {
        return Yii::app()->db->createCommand()->select('{{participant_attribute_names}}.*,{{participant_attribute_names_lang}}.*')->from('{{participant_attribute_names}}')->join('{{participant_attribute_names_lang}}', '{{participant_attribute_names}}.attribute_id = {{participant_attribute_names_lang}}.attribute_id')->where('lang = "'.Yii::app()->session['adminlang'].'"')->queryAll();
    }

    function getAttributesValues($attribute_id)
    {
       return Yii::app()->db->createCommand()->select('*')->from('{{participant_attribute_values}}')->where('attribute_id = '.$attribute_id)->queryAll();
    }

    // this is a very specific function used to get the attributes that are not present for the participant
    function getnotaddedAttributes($attributeid)
    {
    	$attrid = array('not in','{{participant_attribute_names}}.attribute_id');
    	foreach($attributeid as $row)
    	{
    		$attrid[] = $row;
    	}
        return Yii::app()->db->createCommand()->select('*')->from('{{participant_attribute_names}}')->join('{{participant_attribute_names_lang}}', '{{participant_attribute_names}}.attribute_id = {{participant_attribute_names_lang}}.attribute_id')->where($attrid)->queryAll();
    }    

    function storeAttribute($data)
    {
        $insertnames = array('attribute_type' => $data['attribute_type'],
                            'visible' => $data['visible']);
        Yii::app()->db->createCommand()->insert('{{participant_attribute_names}}',$insertnames);
        $insertnameslang = array('attribute_id' => Yii::app()->db->getLastInsertID(),
                                 'attribute_name'=>$data['attribute_name'],
                                 'lang' => Yii::app()->session['adminlang']);
        Yii::app()->db->createCommand()->insert('{{participant_attribute_names_lang}}',$insertnameslang);
        
    }

    function editParticipantAttributeValue($data)
    {
	   	$query = Yii::app()->db->createCommand()->where('participant_id = "'.$data['participant_id'].'" AND attribute_id = '. $data['attribute_id'])->from('{{participant_attribute}}')->select('*')->queryAll();
	        if(count($query) == 0)
	        {
	            Yii::app()->db->createCommand()->insert('{{participant_attribute}}',$data); 
	        }
	        else
	        {
	        Yii::app()->db->createCommand()->update('{{participant_attribute}}',$data,'participant_id = "'.$data['participant_id'].'" AND attribute_id = '.$data['attribute_id']); 
	    }
   	
    }

    function delAttribute($attid)
    {
        Yii::app()->db->createCommand()->delete('{{participant_attribute_names_lang}}', 'attribute_id = '.$attid); 
        Yii::app()->db->createCommand()->delete('{{participant_attribute_names}}', 'attribute_id = '.$attid); 
        Yii::app()->db->createCommand()->delete('{{participant_attribute_values}}', 'attribute_id = '.$attid); 
        Yii::app()->db->createCommand()->delete('{{participant_attribute}}', 'attribute_id = '.$attid); 
    }

    function getAttributeNames($attributeid)
    {
        return Yii::app()->db->createCommand()->where('attribute_id = '.$attributeid)->where('lang = "'.Yii::app()->session['adminlang'].'"')->from('{{participant_attribute_names_lang}}')->select('*')->queryAll();
    }
    function getAttribute($attribute_id)
    {
        $data = Yii::app()->db->createCommand()->from('{{participant_attribute_names}}')->where('{{participant_attribute_names}}.attribute_id = '.$attribute_id)->select('*')->queryRow();
        return $data;
    }

    function saveAttribute($data)
    {
        Yii::app()->db->createCommand()->update('{{participant_attribute_names}}',$data,'attribute_id = '.$data['attribute_id']);
    }

    function saveAttributeLanguages($data)
    {
        $query = Yii::app()->db->createCommand()->from('{{participant_attribute_names_lang}}')->where('attribute_id = '.$data['attribute_id'].' AND lang = "'.$data['lang'].'"')->select('*')->queryAll();
        if (count($query) == 0) 
        {
              // A record does not exist, insert one.
               $record = array('attribute_id'=>$data['attribute_id'],'attribute_name'=>$data['attribute_name'],'lang'=>$data['lang']);
               $query = Yii::app()->db->createCommand()->insert('{{participant_attribute_names_lang}}', $data);
        }
        else 
        {
             // A record does exist, update it.
            $query = Yii::app()->db->createCommand()->update('{{participant_attribute_names_lang}}',array('attribute_id'=>$data['attribute_id'],'lang'=>$data['lang']),'attribute_name = "'.$data['attribute_name'].'"');
        }
    }

    function storeAttributeValues($data)
    {
    	foreach ($data as $record) {
    		Yii::app()->db->createCommand()->insert('{{participant_attribute_values}}',$record);
    	}
    }

    //updates the attribute values in participant_attribute_values
    function saveAttributeValue($data)
    {
        Yii::app()->db->createCommand()->update('{{participant_attribute_values}}', array('attribute_id' => $data['attribute_id'],'value_id'=>$data['value_id']));
    }

    function saveAttributeVisible($attid,$visiblecondition)
    {
    
        $attribute_id = explode("_", $attid);
        $data=array('visible'=>$visiblecondition);
        if($visiblecondition == "")
        {
            $data=array('visible'=>'FALSE');
        }
        Yii::app()->db->createCommand()->update('{{participant_attribute_names}}',$data,'attribute_id = '.$attribute_id[1]); 
    }
}
