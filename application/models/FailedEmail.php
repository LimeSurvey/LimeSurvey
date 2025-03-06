<?php

/**
 * This is the model class for table "{{failed_emails}}".
 *
 * The following are the available columns in table '{{failed_emails}}':
 * @property integer $id primary key
 * @property integer $surveyid the surveyid this one belongs to
 * @property integer $responseid the id of the participants response
 * @property string $email_type the email type
 * @property string $recipient the recipients email address
 * @property string $language the email language
 * @property string $error_message the error message
 * @property string $created datetime when this entry is created
 * @property string $status status in which this entry is default 'SEND FAILED'
 * @property string $updated datetime when it was last updated
 * @property Survey $survey the surveyobject related to the entry
 * @property string $resend_vars json encoded values needed to resend the email [message_type,Subject,uniqueid,boundary[1],boundary[2],boundary[3],MIMEBody]
 *
 * @psalm-suppress InvalidScalarArgument
 */
class FailedEmail extends LSActiveRecord
{
    /** @var string The success status */
    public const STATE_SUCCESS = 'SEND SUCCESS';

    /** @var string The failed status */
    public const STATE_FAILED = 'SEND FAILED';

    /**
     * @inheritdoc
     * @return string the associated database table name
     */
    public function tableName(): string
    {
        return '{{failed_emails}}';
    }

    /**
     * @inheritdoc
     * @return array validation rules for model attributes.
     */
    public function rules(): array
    {
        return [
            ['id, surveyid, responseid, email_type, recipient, error_message, created, resend_vars', 'required'],
            ['email_type', 'length', 'max' => 200],
            ['recipient', 'length', 'max' => 320],
            ['status', 'length', 'max' => 20],
            ['language', 'length', 'max' => 20],
            ['created, updated', 'safe'],
            // The following rule is used by search().
            ['id, responseid, email_type, recipient, language, created, error_message, status, updated', 'safe', 'on' => 'search'],
        ];
    }

    /**
     * @inheritdoc
     * @return array relational rules.
     */
    public function relations(): array
    {
        return [
            'survey' => array(self::HAS_ONE, 'Survey', ['sid' => 'surveyid']),
        ];
    }

    /**
     * @inheritdoc
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels(): array
    {
        return [
            'id'            => 'ID',
            'recipient'     => gT('Recipient'),
            'email_type'    => gT('Email type'),
            'language'      => gT('Email language'),
            'created'       => gT('Date'),
            'status'        => gT('Status'),
            'updated'       => gT('Updated'),
            'error_message' => gT('Error message')
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
    public function search(): CActiveDataProvider
    {
        $pageSize = App()->request->getParam('pageSize') ?? App()->user->getState('pageSize', App()->params['defaultPageSize']);
        $criteria = new CDbCriteria();

        $criteria->compare('id', $this->id);
        $criteria->compare('surveyid', $this->surveyid);
        $criteria->compare('responseid', $this->responseid);
        $criteria->compare('email_type', $this->email_type, true);
        $criteria->compare('recipient', $this->recipient, true);
        $criteria->compare('language', $this->language, true);
        $criteria->compare('error_message', $this->error_message, true);
        $criteria->compare('created', $this->created, true);
        $criteria->compare('status', $this->status, true);
        $criteria->compare('updated', $this->updated, true);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
            'pagination' => array(
                'pageSize' => $pageSize
            )
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return static the static model class
     */
    public static function model($className = __CLASS__): FailedEmail
    {
        return parent::model($className);
    }

    public function getColumns(): array
    {
        return [
            [
                'id'             => 'id',
                'class'          => 'CCheckBoxColumn',
                'selectableRows' => '100',
                'headerHtmlOptions' => ['class' => 'ls-sticky-column'],
                'filterHtmlOptions' => ['class' => 'ls-sticky-column'],
                'htmlOptions'       => ['class' => 'ls-sticky-column']
            ],
            [
                'header' => gT('Status'),
                'name'   => 'status',
                'value'  => '$data->status',
                'htmlOptions' => ['class' => 'nowrap']
            ],
            [
                'header' => gT('Response ID'),
                'name'   => 'responseUrl',
                'type'   => 'raw',
                'filter' => false,
            ],
            [
                'header' => gT('Created'),
                'name'   => 'created',
                'value'  => '$data->created',
                'filter'      => false,
                'htmlOptions' => ['class' => 'nowrap']
            ],
            [
                'header' => gT('Updated'),
                'name'   => 'updated',
                'value'  => '$data->updated',
                'filter'      => false,
                'htmlOptions' => ['class' => 'nowrap']
            ],
            [
                'header' => gT('Email type'),
                'name'   => 'email_type',
                'value'  => '$data->email_type',
                'htmlOptions' => ['class' => 'nowrap']
            ],
            [
                'header' => gT("Recipient"),
                'name'   => 'recipient',
                'value'  => '$data->recipient',
                'htmlOptions' => ['class' => 'nowrap']
            ],
            [
                'header' => gT('Language'),
                'name'   => 'language',
                'value'  => '$data->language',
            ],
            [
                'name'        => 'actions',
                'type'        => 'raw',
                'filter'      => false,
                'header'      => gT('Action'),
                'headerHtmlOptions' => ['class' => 'ls-sticky-column'],
                'filterHtmlOptions' => ['class' => 'ls-sticky-column'],
                'htmlOptions'       => ['class' => 'ls-sticky-column']
            ],
        ];
    }

    /**
     * Renders the actions for each table row
     *
     * @return string
     * @throws Exception
     */
    public function getActions(): string
    {
        $permission_responses_update = Permission::model()->hasSurveyPermission($this->surveyid, 'responses', 'update');
        $permission_responses_delete = Permission::model()->hasSurveyPermission($this->surveyid, 'responses', 'delete');
        $permission_responses_read = Permission::model()->hasSurveyPermission($this->surveyid, 'responses', 'read');

        $dropdownItems = [];
        $dropdownItems[] = [
            'title'            => gT('Resend email'),
            'linkClass'        => 'failedemail-action-modal-open',
            'iconClass'        => 'ri-mail-line',
            'linkAttributes'   => [
                'data-href'        => App()->createUrl('/failedEmail/modalcontent', ['id' => $this->id]),
                'data-contentFile' => "resend_form",
            ],
            'enabledCondition' => $permission_responses_update,
        ];
        $dropdownItems[] = [
            'title'            => gT('Email content'),
            'linkClass'        => 'failedemail-action-modal-open',
            'iconClass'        => 'ri-search-line',
            'linkAttributes'   => [
                'data-href'        => App()->createUrl('/failedEmail/modalcontent', ['id' => $this->id]),
                'data-contentFile' => "email_content",
            ],
            'enabledCondition' => $permission_responses_read,
        ];
        $dropdownItems[] = [
            'title'            => gT('Error message'),
            'linkClass'        => 'failedemail-action-modal-open',
            'iconClass'        => 'ri-alert-fill',
            'linkAttributes'   => [
                'data-href'        => App()->createUrl('/failedEmail/modalcontent', ['id' => $this->id]),
                'data-contentFile' => "email_error",
            ],
            'enabledCondition' => $permission_responses_read,
        ];
        $dropdownItems[] = [
            'title'            => gT('Delete'),
            'linkClass'        => 'failedemail-action-modal-open',
            'iconClass'        => 'ri-delete-bin-fill text-danger',
            'linkAttributes'   => [
                'data-href'        => App()->createUrl('/failedEmail/modalcontent', ['id' => $this->id]),
                'data-contentFile' => "delete_form",
            ],
            'enabledCondition' => $permission_responses_delete,
        ];
        return App()->getController()->widget('ext.admin.grid.GridActionsWidget.GridActionsWidget', ['dropdownItems' => $dropdownItems], true);
    }

    /**
     * create a link to the response if it exists, else just return the id
     * @return string
     */
    public function getResponseUrl(): string
    {
        $survey = Survey::model()->findByPk($this->surveyid);
        if ($survey !== null && $survey->hasResponsesTable) {
            $response = Response::model($this->surveyid)->findByPk($this->responseid);
            if (!empty($response)) {
                $responseUrl = App()->createUrl("responses/view/", ['surveyId' => $this->surveyid, 'id' => $this->responseid]);
                $responseLink = '<a href="' . $responseUrl . '" role="button" data-bs-toggle="tooltip" title="' . gT('View response details') . '">' . $this->responseid . '</a>';
            } else {
                $responseLink = (string)$this->responseid;
            }
        } else {
            $responseLink = (string)$this->responseid;
        }
        return $responseLink;
    }

    /**
     * @return string
     * @throws CException
     * @throws CHttpException
     */
    public function getRawMailBody(): string
    {
        $mailer = \LimeMailer::getInstance();
        $mailer->setSurvey($this->surveyid);
        $mailer->setTypeWithRaw($this->email_type, $this->language);
        $rawMail = $mailer->rawBody;
        return $rawMail;
    }

    /**
     * Returns array having surveyid as key and the localized survey title as value of all failed email entries
     * @return array
     */
    public function getFailedEmailSurveyTitles()
    {
        $allFailedEmails = $this->with('survey')->findAllByAttributes(
            [],
            "owner_id = :owner AND status != :status",
            [':owner' => App()->user->id, ':status' => self::STATE_SUCCESS]
        );
        $groupedFailedEmails = [];
        foreach ($allFailedEmails as $failedEmail) {
            $groupedFailedEmails[$failedEmail->surveyid] = $failedEmail->survey->getLocalizedTitle();
        }

        return $groupedFailedEmails;
    }
}
