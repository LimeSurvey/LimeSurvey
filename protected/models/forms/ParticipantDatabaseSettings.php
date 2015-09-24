<?php
namespace ls\models\forms;


class ParticipantDatabaseSettings extends \CFormModel
{
    // Blacklist settings
    public $blacklistallsurveys;
    public $blacklistnewsurveys;
    public $blockaddingtosurveys;
    public $hideblacklisted;
    public $allowunblacklist;
    public $deleteblacklisted;

    public $userideditable;

    public function attributeLabels() {
        /**
         * @todo Remove the colon postfix from each of these strings.
         *
         */
        return [
            'blacklistallsurveys' => gT('Blacklist all current surveys for participant once the global field is set:'),
            'blacklistnewsurveys' => gT('Blacklist participant for any new added survey once the global field is set:'),
            'blockaddingtosurveys' => gT('Allow blacklisted participants to be added to a survey:'),
            'hideblacklisted' => gT('Hide blacklisted participants:'),
            'allowunblacklist' => gT('Allow participant to unblacklist himself/herself:'),
            'deleteblacklisted' => gT('Delete globally blacklisted participant from the database:'),
            'userideditable'=> gT('User ID editable:')
        ];
    }
    public function init()
    {
        parent::init();
        $this->load();
    }

    public function save($runValidation = true)
    {
        /**
         * Update inside transaction.
         */
        $transaction = App()->db->beginTransaction();
        $success = true;
        foreach($this->attributes as $name => $value) {
            $success = $success && \SettingGlobal::set($name, $value);
        }
        if ($success) {
            $transaction->commit();
        } else {
            $transaction->rollback();
        }
        return $success;
    }

    /**
     * Define validation rules here.
     * @return array
     */
    public function rules()
    {
        /**
         * @todo Implement proper validation.
         */
        return [
            [$this->attributeNames(), 'safe']
        ];
    }

    protected function load()
    {
        foreach ($this->attributeNames() as $attributeName) {
            $this->$attributeName = \SettingGlobal::get($attributeName, App()->getConfig($attributeName));
        }
    }
}