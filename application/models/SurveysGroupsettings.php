<?php

/**
 * This is the model class for table "{{surveys_groupsettings}}".
 *
 * The following are the available columns in table '{{surveys_groupsettings}}':
 * @property integer $gsid
 * @property integer $owner_id
 * @property string $admin
 * @property string $expires
 * @property string $startdate
 * @property string $adminemail
 * @property string $anonymized
 * @property string $format
 * @property string $savetimings
 * @property string $template
 * @property string $datestamp
 * @property string $usecookie
 * @property string $allowregister
 * @property string $allowsave
 * @property integer $autonumber_start
 * @property string $autoredirect
 * @property string $allowprev
 * @property string $printanswers
 * @property string $ipaddr
 * @property string $refurl
 * @property string $datecreated
 * @property integer $showsurveypolicynotice
 * @property string $publicstatistics
 * @property string $publicgraphs
 * @property string $listpublic
 * @property string $htmlemail
 * @property string $sendconfirmation
 * @property string $tokenanswerspersistence
 * @property string $assessments
 * @property string $usecaptcha
 * @property string $bounce_email
 * @property string $attributedescriptions
 * @property string $emailresponseto
 * @property string $emailnotificationto
 * @property integer $tokenlength
 * @property string $showxquestions
 * @property string $showgroupinfo
 * @property string $shownoanswer
 * @property string $showqnumcode
 * @property string $showwelcome
 * @property string $showprogress
 * @property integer $questionindex
 * @property integer $navigationdelay
 * @property string $nokeyboard
 * @property string $alloweditaftercompletion
 * @property string $ipanonymize
 */
class SurveysGroupsettings extends LSActiveRecord
{
    // survey options
    public $oOptions;
    public $oOptionLabels;
    // used for twig files, same content as $oOptions, but in array format
    public $aOptions = array();

    // attribute names from surveys_groupsettings table definition
    protected $optionAttributes = array();

    // attributes separated by column datatype, used by setToInherit method
    protected $optionAttributesInteger  = array('owner_id', 'tokenlength', 'questionindex', 'navigationdelay');
    protected $optionAttributesChar     = array('anonymized', 'savetimings', 'datestamp', 'usecookie', 'allowregister', 'allowsave', 'autoredirect', 'allowprev', 'printanswers',
                                                'ipaddr','ipanonymize', 'refurl', 'publicstatistics', 'publicgraphs', 'listpublic', 'htmlemail', 'sendconfirmation', 'tokenanswerspersistence',
                                                'assessments', 'showxquestions', 'showgroupinfo', 'shownoanswer', 'showqnumcode', 'showwelcome', 'showprogress', 'nokeyboard',
                                                'alloweditaftercompletion');
    protected $optionAttributesText     = array('admin', 'adminemail', 'template', 'bounce_email', 'emailresponseto', 'emailnotificationto');

    public $showInherited = 1;
    public $active;
    public $additional_languages;

    /* self[] used in self::getInstance() */
    private static $aSurveysGroupSettings = [];

    /**
     * @return string the associated database table name
     */


    public function tableName()
    {
        return '{{surveys_groupsettings}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        $validator = new LSYii_Validators();
        return array(
            array('autonumber_start, showsurveypolicynotice, tokenlength, questionindex, navigationdelay, owner_id', 'numerical', 'integerOnly' => true),
            array('admin', 'length', 'max' => 50),
            array('anonymized, format, savetimings, datestamp, usecookie, allowregister, allowsave, autoredirect, allowprev, printanswers, ipaddr, refurl, publicstatistics, publicgraphs, listpublic, htmlemail, sendconfirmation, tokenanswerspersistence, assessments, usecaptcha, showxquestions, showgroupinfo, shownoanswer, showqnumcode, showwelcome, showprogress, nokeyboard, alloweditaftercompletion, ipanonymize', 'length', 'max' => 1),
            array('adminemail, bounce_email', 'length', 'max' => 255),
            array('template', 'length', 'max' => 100),
            array('expires, startdate, datecreated, attributedescriptions, emailresponseto, emailnotificationto', 'safe'),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('gsid, owner_id, admin, expires, startdate, adminemail, anonymized, format,
			savetimings, template, datestamp, usecookie, allowregister, allowsave, autonumber_start,
			autoredirect, allowprev, printanswers, ipaddr, refurl, datecreated, showsurveypolicynotice,
			publicstatistics, publicgraphs, listpublic, htmlemail, sendconfirmation, tokenanswerspersistence,
			assessments, usecaptcha, bounce_email, attributedescriptions, emailresponseto, emailnotificationto,
			tokenlength, showxquestions, showgroupinfo, shownoanswer, showqnumcode, showwelcome, showprogress,
			questionindex, navigationdelay, nokeyboard, alloweditaftercompletion', 'safe', 'on' => 'search'),
        );
    }

    /** @inheritdoc
     * unset static aSurveysGroupSettings
     **/
    public function save($runValidation = true, $attributes = null)
    {
        unset(self::$aSurveysGroupSettings[$this->gsid]);
        return parent::save($runValidation, $attributes);
    }
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'Survey' => array(self::HAS_MANY, 'Survey', 'gsid'),
            'SurveysGroups' => array(self::HAS_ONE, 'SurveysGroups', 'gsid'),
            'owner' => array(self::BELONGS_TO, 'User', 'owner_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'gsid' => 'Gsid',
            'owner_id' => 'OwnerId',
            'admin' => 'Admin',
            'expires' => 'Expires',
            'startdate' => 'Startdate',
            'adminemail' => 'Adminemail',
            'anonymized' => 'Anonymized',
            'format' => 'Format',
            'savetimings' => 'Savetimings',
            'template' => 'Template',
            'datestamp' => 'Datestamp',
            'usecookie' => 'Usecookie',
            'allowregister' => 'Allowregister',
            'allowsave' => 'Allowsave',
            'autonumber_start' => 'Autonumber Start',
            'autoredirect' => 'Autoredirect',
            'allowprev' => 'Allowprev',
            'printanswers' => 'Printanswers',
            'ipaddr' => 'Ipaddr',
            'refurl' => 'Refurl',
            'datecreated' => 'Datecreated',
            'showsurveypolicynotice' => 'Showsurveypolicynotice',
            'publicstatistics' => 'Publicstatistics',
            'publicgraphs' => 'Publicgraphs',
            'listpublic' => 'Listpublic',
            'htmlemail' => 'Htmlemail',
            'sendconfirmation' => 'Sendconfirmation',
            'tokenanswerspersistence' => 'Tokenanswerspersistence',
            'assessments' => 'Assessments',
            'usecaptcha' => 'Usecaptcha',
            'bounce_email' => 'Bounce Email',
            'attributedescriptions' => 'Attributedescriptions',
            'emailresponseto' => 'Emailresponseto',
            'emailnotificationto' => 'Emailnotificationto',
            'tokenlength' => 'Tokenlength',
            'showxquestions' => 'Showxquestions',
            'showgroupinfo' => 'Showgroupinfo',
            'shownoanswer' => 'Shownoanswer',
            'showqnumcode' => 'Showqnumcode',
            'showwelcome' => 'Showwelcome',
            'showprogress' => 'Showprogress',
            'questionindex' => 'Questionindex',
            'navigationdelay' => 'Navigationdelay',
            'nokeyboard' => 'Nokeyboard',
            'alloweditaftercompletion' => 'Alloweditaftercompletion',
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

        $criteria = new CDbCriteria();

        $criteria->compare('gsid', $this->gsid);
        $criteria->compare('owner_id', $this->owner_id);
        $criteria->compare('admin', $this->admin, true);
        $criteria->compare('expires', $this->expires, true);
        $criteria->compare('startdate', $this->startdate, true);
        $criteria->compare('adminemail', $this->adminemail, true);
        $criteria->compare('anonymized', $this->anonymized, true);
        $criteria->compare('format', $this->format, true);
        $criteria->compare('savetimings', $this->savetimings, true);
        $criteria->compare('template', $this->template, true);
        $criteria->compare('datestamp', $this->datestamp, true);
        $criteria->compare('usecookie', $this->usecookie, true);
        $criteria->compare('allowregister', $this->allowregister, true);
        $criteria->compare('allowsave', $this->allowsave, true);
        $criteria->compare('autonumber_start', $this->autonumber_start);
        $criteria->compare('autoredirect', $this->autoredirect, true);
        $criteria->compare('allowprev', $this->allowprev, true);
        $criteria->compare('printanswers', $this->printanswers, true);
        $criteria->compare('ipaddr', $this->ipaddr, true);
        $criteria->compare('refurl', $this->refurl, true);
        $criteria->compare('datecreated', $this->datecreated, true);
        $criteria->compare('showsurveypolicynotice', $this->showsurveypolicynotice);
        $criteria->compare('publicstatistics', $this->publicstatistics, true);
        $criteria->compare('publicgraphs', $this->publicgraphs, true);
        $criteria->compare('listpublic', $this->listpublic, true);
        $criteria->compare('htmlemail', $this->htmlemail, true);
        $criteria->compare('sendconfirmation', $this->sendconfirmation, true);
        $criteria->compare('tokenanswerspersistence', $this->tokenanswerspersistence, true);
        $criteria->compare('assessments', $this->assessments, true);
        $criteria->compare('usecaptcha', $this->usecaptcha, true);
        $criteria->compare('bounce_email', $this->bounce_email, true);
        $criteria->compare('attributedescriptions', $this->attributedescriptions, true);
        $criteria->compare('emailresponseto', $this->emailresponseto, true);
        $criteria->compare('emailnotificationto', $this->emailnotificationto, true);
        $criteria->compare('tokenlength', $this->tokenlength);
        $criteria->compare('showxquestions', $this->showxquestions, true);
        $criteria->compare('showgroupinfo', $this->showgroupinfo, true);
        $criteria->compare('shownoanswer', $this->shownoanswer, true);
        $criteria->compare('showqnumcode', $this->showqnumcode, true);
        $criteria->compare('showwelcome', $this->showwelcome, true);
        $criteria->compare('showprogress', $this->showprogress, true);
        $criteria->compare('questionindex', $this->questionindex);
        $criteria->compare('navigationdelay', $this->navigationdelay);
        $criteria->compare('nokeyboard', $this->nokeyboard, true);
        $criteria->compare('alloweditaftercompletion', $this->alloweditaftercompletion, true);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return SurveysGroupsettings the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return bool
     */
    public function getIsActive()
    {
        if (is_object($this->Survey)) {
            return ($this->Survey->active === 'Y');
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function getIsAnonymized()
    {
        if (is_object($this->Survey)) {
            return ($this->Survey->anonymized === 'Y');
        } else {
            return false;
        }
    }

    /**
     * Recursive function
     *
     * Gets the real values for a group.
     * A group could inherit from a group, this one could inherit from a group ...
     * It steps up (see param $iStep) until it has found the real settings ...
     *
     * @param int $iSurveyGroupId
     * @param  \Survey|null $oSurvey
     * @param \self|null $instance
     * @param int $iStep      this is inheritance step (recursive step) (parent, parentParent, parentParentParent ?)
     * @param bool $bRealValues
     * @return SurveysGroupsettings instance
     */
    public static function getInstance($iSurveyGroupId = 0, $oSurvey = null, $instance = null, $iStep = 1, $bRealValues = false)
    {
        if (!array_key_exists($iSurveyGroupId, self::$aSurveysGroupSettings)) {
            if ($iSurveyGroupId > 0) {
                self::$aSurveysGroupSettings[$iSurveyGroupId] = SurveysGroupsettings::model()->with('SurveysGroups')->findByPk($iSurveyGroupId);
            } else {
                //this is the default group setting with gsid=0 !!!
                self::$aSurveysGroupSettings[$iSurveyGroupId] = SurveysGroupsettings::model()->findByPk(0);
            }
        }
        $model = self::$aSurveysGroupSettings[$iSurveyGroupId];

        // set initial values to instance on first run
        if ($instance === null) {
            if ($model === null) {
                $instance = new SurveysGroupsettings();
                $instance->optionAttributes = new stdClass();
            } else {
                $instance = $model;
                $instance->optionAttributes = array_keys($model->attributes);
                // unset gsid
                unset($instance->optionAttributes[array_search('gsid', $instance->optionAttributes)]);
            }
            $instance->oOptions = new stdClass();
            $instance->oOptionLabels = new stdClass();

            // set visibility of 'inherit' options on buttons
            if ($iSurveyGroupId == 0) {
                $instance->showInherited = 0;
            } else {
                $instance->showInherited = 1;
            }

            // set instance options from survey model, used for frontend rendering
            if (($oSurvey !== null && $bRealValues)) {
                foreach ($instance->optionAttributes as $key => $attribute) {
                    $instance->oOptions->{$attribute} = $oSurvey->$attribute;
                    $instance->oOptionLabels->{$attribute} = self::translateOptionLabels($instance, $attribute, $oSurvey->$attribute);
                }
            }

            // set instance options from global model
            if ($iSurveyGroupId == 0) {
                foreach ($instance->optionAttributes as $key => $attribute) {
                    $instance->oOptions->{$attribute} = $model->$attribute;
                    $instance->oOptionLabels->{$attribute} = self::translateOptionLabels($instance, $attribute, $model->$attribute);
                }
            }
        }

        // set instance options only if option needs to be inherited
        if ($oSurvey !== null || ($oSurvey === null && $iStep > 1)) {
            foreach ($instance->optionAttributes as $key => $attribute) {
                if ($instance->shouldInherit($attribute)) {
                    $instance->oOptions->{$attribute} = $model->$attribute;
                    $instance->oOptionLabels->{$attribute} = self::translateOptionLabels($instance, $attribute, $model->$attribute);
                }
            }
        }

        // check if the template actually exists and modify it if invalid
        if (
            !$instance->shouldInherit('template')
            && !Template::checkIfTemplateExists($instance->oOptions->template)
        ) {
            if ($iSurveyGroupId === 0) {
                $instance->oOptions->template = App()->getConfig('defaulttheme');
            } else {
                $instance->oOptions->template = 'inherit';
            }
        }

        // check the global configuration for template inheritance if surveygroup is 0 (global survey) and template set to inherit
        if (
            $iSurveyGroupId === 0
            && $instance->shouldInherit('template')
        ) {
            $instance->oOptions->template = App()->getConfig('defaulttheme');
        }

        // fetch parent instance only if parent_id exists
        if ($iSurveyGroupId > 0 && !empty($model->SurveysGroups) && $model->SurveysGroups->parent_id !== null) {
            self::getInstance($model->SurveysGroups->parent_id, null, $instance, $iStep + 1);
        }

        // fetch global instance
        if ($iSurveyGroupId > 0 && !empty($model->SurveysGroups) && $model->SurveysGroups->parent_id === null) {
            self::getInstance(0, null, $instance, $iStep + 1); // calling global settings
        }

        return $instance;
    }

    /**
     * @return string
     */
    protected static function translateOptionLabels($instance, $attribute, $value)
    {
        if (is_null($value)) {
            return '';
        }
        // replace option labels on forms
        if ($attribute == 'usecaptcha') {
            $usecap = $value;
            if ($usecap === 'A' || $usecap === 'B' || $usecap === 'C' || $usecap === 'X' || $usecap === 'F' || $usecap === 'H' || $usecap === 'K' || $usecap === '0') {
                $instance->oOptionLabels->useCaptchaSurveyAccess = gT("On");
            } else {
                $instance->oOptionLabels->useCaptchaSurveyAccess = gT("Off");
            }
            if ($usecap === 'A' || $usecap === 'B' || $usecap === 'D' || $usecap === 'R' || $usecap === 'F' || $usecap === 'G' || $usecap === 'I' || $usecap === 'M') {
                $instance->oOptionLabels->useCaptchaRegistration = gT("On");
            } else {
                $instance->oOptionLabels->useCaptchaRegistration = gT("Off");
            }
            if ($usecap === 'A' || $usecap === 'C' || $usecap === 'D' || $usecap === 'S' || $usecap === 'G' || $usecap === 'H' || $usecap === 'J' || $usecap === 'L') {
                $instance->oOptionLabels->useCaptchaSaveAndLoad = gT("On");
            } else {
                $instance->oOptionLabels->useCaptchaSaveAndLoad = gT("Off");
            }
        } elseif ($attribute == 'owner_id' && $value != -1) {
            $instance->oOptions->owner = "";
            $instance->oOptions->ownerLabel = "";
            /* \User|false[] see mantis #19426 */
            static $oStaticUsers = array();
            if (!array_key_exists($instance->oOptions->{$attribute}, $oStaticUsers)) {
                $oStaticUsers[$instance->oOptions->{$attribute}] = User::model()->findByPk($instance->oOptions->{$attribute});
            }
            $oUser = $oStaticUsers[$instance->oOptions->{$attribute}];
            if (!empty($oUser)) {
                $instance->oOptions->owner = $oUser->attributes;
                $instance->oOptions->ownerLabel = $oUser->users_name . ($oUser->full_name ? " - " . $oUser->full_name : "");
            }
        } elseif ($attribute == 'format' && $value != -1) {
            return str_replace(array('S', 'G', 'A'), array(gT("Question by question"), gT("Group by group"), gT("All in one")), (string) $value);
        } elseif ($attribute == 'questionindex' && $value != -1) {
            return str_replace(array('0', '1', '2'), array(gT("Disabled"), gT("Incremental"), gT("Full")), (string) $value);
        } elseif ($attribute == 'showgroupinfo') {
            return str_replace(array('B', 'D', 'N', 'X'), array(gT("Show both"), gT("Show group description only"), gT("Show group name only"), gT("Hide both")), (string) $value);
        } elseif ($attribute == 'showqnumcode') {
            return str_replace(array('B', 'C', 'N', 'X'), array(gT("Show both"), gT("Show question code only"), gT("Show question number only"), gT("Hide both")), (string) $value);
        } elseif ($value == 'N' || $value == 'Y') {
            return str_replace(array('Y', 'N'), array(gT("On"), gT("Off")), (string) $value);
        }
        return (string) $value;
    }

    /**
     *  Gets the "values" from the group that inherits to this group and ...
     *
     *  ... sets the variables (not DB attributes) of "oOptions", "oOptionLabels", "aOptions"
     *  and "showInherited" (most of them used for frontend i think)
     *
     */
    public function setOptions()
    {
        $instance = SurveysGroupsettings::getInstance($this->gsid);
        // set SurveysGroupsettings properties from $instance
        $this->oOptions = $instance->oOptions;
        $this->oOptionLabels = $instance->oOptionLabels;
        $this->aOptions = (array) $instance->oOptions;
        $this->showInherited = $instance->showInherited;
    }

    public function setToInherit()
    {
        // set attribute values to inherit, used only when creating new Survey instance
        $this->usecaptcha = 'E';
        $this->format = 'I';
        foreach ($this->optionAttributesInteger as $attribute) {
            $this->$attribute = -1;
        }
        foreach ($this->optionAttributesChar as $attribute) {
            //fix for 16179
            $dbversion = App()->getConfig('DBVersion');
            if (!($attribute === 'ipanonymize' && ( $dbversion < 412 ))) {
                $this->$attribute = 'I';
            }
        }
        foreach ($this->optionAttributesText as $attribute) {
            $this->$attribute = 'inherit';
        }
    }

    public function setToDefault()
    {
        // set attribute values to default values, used only when creating new top level SurveysGroupsettings instance
        $this->owner_id = 1;
        $this->usecaptcha = 'N';
        $this->format = 'G';
        $this->admin = substr((string) App()->getConfig('siteadminname'), 0, 50);
        $this->adminemail = substr((string) App()->getConfig('siteadminemail'), 0, 254);
        $this->template = Template::templateNameFilter(App()->getConfig('defaulttheme'));
    }

    /**
     * Returns true if the attribute should be inherited according to it's value.
     * @param string $attribute
     * @return bool
     */
    private function shouldInherit($attribute)
    {
        // If the attribute is not defined
        if (!property_exists($this->oOptions, $attribute)) {
            return true;
        }

        // The attribute should be inherited if its value is 'inherit', 'I' or '-1'.
        if (
            !empty($this->oOptions->{$attribute})
            && (
                $this->oOptions->{$attribute} === 'inherit'
                || $this->oOptions->{$attribute} === 'I'
                // NB: Do NOT use === here, it won't work with Postgresql.
                || $this->oOptions->{$attribute} == '-1'
            )
        ) {
            return true;
        }

        // Since survey settings inheritance have been introduced, empty
        // attributes have always been inherited. But for some attributes,
        // an empty value is actually a valid attribute.
        $attributesAllowedToBeEmpty = ['emailnotificationto', 'emailresponseto'];
        if (empty($this->oOptions->{$attribute}) && !in_array($attribute, $attributesAllowedToBeEmpty)) {
            return true;
        }

        return false;
    }
}
