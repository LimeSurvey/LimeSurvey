<?php


/**
 * This is the model class for table "{{failed_email}}".
 *
 * The followings are the available columns in table '{{failed_email}}':
 * @property integer $id primary key
 * @property string $subject the email subject
 * @property string $recipient the recipients email address
 * @property string $content the content of the failed email
 * @property string $created datetime when this entry is created
 * @property string $status status in which this entry is default 'SEND FAILED'
 * @property string $update datetim when it was last updated
 */
class FailedEmail extends CActiveRecord
{
    /** @inheritdoc */
    public function tableName()
    {
        return '{{failed_email}}';
    }

    /** @inheritdoc */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('id, subject, recipient,content, created', 'required'),
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
            'subject' => gt('Email subject'),
            'recipient' => gt('Recipient'),
            'content' => gt('Email content'),
            'created' => gt('Date of email failing'),
            'status' => gt('Status'),
            'update' => gt('Updated')
        );
    }
}
