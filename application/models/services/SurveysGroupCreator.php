<?php

namespace LimeSurvey\Models\Services;

use CHtml;
use LimeSurvey\Datavalueobjects\TypedMessage;
use LSHttpRequest;
use LSWebUser;
use SurveysGroups;
use SurveysGroupsettings;

/**
 * Service class for survey group creation.
 * All dependencies are injected to enable mocking.
 */
class SurveysGroupCreator
{
    /** @var LSHttpRequest */
    private $request;

    /** @var LSWebUser */
    private $user;

    /** @var SurveysGroups */
    private $surveysGroup;

    /** @var SurveysGroupsettings */
    private $surveysGroupsettings;

    /** @var TypedMessage[] an array of messages providing extra details */
    private $messages = [];

    /**
     * @param LSHttpRequest $request
     * @param LSWebUser $user
     * @param SurveysGroups $surveysGroup
     * @param SurveysGroupsettings $surveysGroupsettings
     */
    public function __construct(
        LSHttpRequest $request,
        LSWebUser $user,
        SurveysGroups $surveysGroup,
        SurveysGroupsettings $surveysGroupsettings
    ) {
        $this->request = $request;
        $this->user = $user;
        $this->surveysGroup = $surveysGroup;
        $this->surveysGroupsettings = $surveysGroupsettings;
    }

    /**
     * Saves the SurveysGroups and SurveysGroupsettings models with data from the request.
     *
     * @return boolean True on success.
     * @todo What happen if SurveysGroups saved but no SurveysGroupsettings? Transaction?
     */
    public function save()
    {
        $this->surveysGroup->attributes = $this->request->getPost('SurveysGroups');
        $this->surveysGroup->created_by = $this->user->id;
        if ($this->surveysGroup->save()) {
            // Save new SurveysGroupsettings record
            $this->surveysGroupsettings->gsid = $this->surveysGroup->gsid;
            $this->surveysGroupsettings->setToInherit();

            if ($this->surveysGroupsettings->save()) {
                return true;
            } else {
                $this->messages[] = new TypedMessage(CHtml::errorSummary($this->surveysGroupsettings), 'error');
                return false;
            }
        } else {
            $this->messages[] = new TypedMessage(CHtml::errorSummary($this->surveysGroup), 'error');
            return false;
        }
    }

    /**
     * Returns the messages of the given type, or all messages if
     * no type is specified.
     * @param string|null $type
     * @return TypedMessage[]
     */
    public function getMessages($type = null)
    {
        if (empty($type)) {
            return $this->messages;
        }
        $messages = [];
        foreach ($this->messages as $message) {
            if ($message->getType() === $type) {
                $messages[] = $message;
            }
        }
        return $messages;
    }
}
