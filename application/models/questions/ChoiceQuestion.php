<?php
namespace ls\models\questions;

use Response;

/**
 * Class ChoiceQuestion
 * @package ls\models\questions
 */
class ChoiceQuestion extends \Question
{
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
           'bool_other_comment_mandatory' => gT("'Other:' comment mandatory")
        ]);
    }

    /**
     * Returns this model's validation rules
     *
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['bool_other_comment_mandatory'], 'boolean'],
        ]);
    }

    /**
     * Check if the response passes mandatory requirements for this question.
     * Check that other comment was set if mandatory.
     */
    public function validateResponse(Response $response)
    {
        $result = parent::validateResponse($response);

        // If other comment is mandatory we add some extra checks for mandatory validation.
        if ($this->bool_other_comment_mandatory) {
            $otherProperty = $this->sgqa . 'other';
            if ($response->{$this->sgqa} == '-oth-' && empty($response->$otherProperty)) {
                $otherText = isset($this->other_replace_text) ? $this->other_replace_text : gT("Other:");
                $result->addMessage($otherProperty, sprintf(gT("If you choose '%s' please also specify your choice in the accompanying text field."), $otherText));
            }
        }

        $result->setPassedMandatory(isset($response->{$this->sgqa}));
        return $result;
    }


}