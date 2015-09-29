<?php
namespace ls\models;

use ls\models\ActiveRecord;

class Quota extends ActiveRecord
{
    /**
     * Returns the static model of Settings table
     *
     * @static
     * @access public
     * @param string $class
     * @return CActiveRecord
     */
    public static function model($class = __CLASS__)
    {
        return parent::model($class);
    }

    /**
     * Returns the setting's table name to be used by the model
     *
     * @access public
     * @return string
     */
    public function tableName()
    {
        return '{{quota}}';
    }

    /**
     * Returns the relations
     *
     * @access public
     * @return array
     */
    public function relations()
    {
        $alias = $this->getTableAlias();

        return [
            'languagesettings' => [self::HAS_MANY, QuotaLanguageSetting::class, 'quotals_quota_id']
        ];
    }

    /**
     * Returns this model's validation rules
     *
     */
    public function rules()
    {
        return array(
            array('name', 'required'),
            // Maybe more restrictive
            array('qlimit', 'numerical', 'integerOnly' => true, 'min' => '0', 'allowEmpty' => true),
            array('action', 'numerical', 'integerOnly' => true, 'min' => '1', 'max' => '2', 'allowEmpty' => true),
            // Default is null ?
            array('active', 'numerical', 'integerOnly' => true, 'min' => '0', 'max' => '1', 'allowEmpty' => true),
            array('autoload_url', 'numerical', 'integerOnly' => true, 'min' => '0', 'max' => '1', 'allowEmpty' => true),
        );
    }


    function deleteQuota($condition = false, $recursive = true)
    {
        if ($recursive == true) {
            $oResult = Quota::model()->findAllByAttributes($condition);
            foreach ($oResult as $aRow) {
                QuotaLanguageSetting::model()->deleteAllByAttributes(array('quotals_quota_id' => $aRow['id']));
                QuotaMember::model()->deleteAllByAttributes(array('quota_id' => $aRow['id']));
            }
        }

        Quota::model()->deleteAllByAttributes($condition);
    }


    /**
     * Returns the relations that map to dependent records.
     * Dependent records should be deleted when this object gets deleted.
     * @return string[]
     */
    public function dependentRelations()
    {
        return [
            'languagesettings',
        ];
    }

    /**
     * Deletes this record and all dependent records.
     * @throws CDbException
     */
    public function deleteDependent()
    {
        if (App()->db->getCurrentTransaction() == null) {
            $transaction = App()->db->beginTransaction();
        }
        foreach ($this->dependentRelations() as $relation) {
            /** @var CActiveRecord $record */
            foreach ($this->$relation as $record) {
                if (method_exists($record, 'deleteDependent')) {
                    $record->deleteDependent();
                } else {
                    $record->delete();
                }
            }
        }
        $this->delete();

        if (isset($transaction)) {
            $transaction->commit();
        }
    }
}

