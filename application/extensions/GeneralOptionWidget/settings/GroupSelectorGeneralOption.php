<?php

use LimeSurvey\Datavalueobjects\GeneralOption;
use LimeSurvey\Datavalueobjects\FormElement;
use LimeSurvey\Datavalueobjects\SwitchOption;

class GroupSelectorGeneralOption extends GeneralOption
{
    /**
     * @param Question $question
     * @param SwitchOption[] $groupOptions
     */
    public function __construct(Question $question, array $groupOptions)
    {
        $this->name = 'gid';
        $this->title = gT('Question group');
        $this->inputType = 'questiongroup';
        $this->setDisableInActive($question->survey);
        $this->formElement = new FormElement(
            'gid',
            null,
            gT("Reassign this question to another group by selecting a new one"),
            $question->gid,
            [
                'classes' => ['form-control'],
                'options' => $groupOptions
            ]
        );
    }

    /**
     * @param Question $question
     * @param string $language
     * @return self
     */
    public static function make(Question $question, $language)
    {
        /** @var QuestionGroup[] */
        $groups = QuestionGroup::model()->findAllByAttributes(
            ['sid'   => $question->sid ],
            ['order' => 'group_order']
        );
        /** @var SwitchOption[] */
        $groupOptions = [];
        // TODO: array_map?
        array_walk(
            $groups,
            function ($group) use (&$groupOptions, $language) {
                $groupOptions[] = new SwitchOption(
                    $group->questiongroupl10ns[$language]->group_name,
                    $group->gid
                );
            }
        );
        return new self($question, $groupOptions);
    }
}
