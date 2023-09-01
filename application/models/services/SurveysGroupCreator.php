<?php

namespace LimeSurvey\Models\Services;

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
        $attributes = $this->request->getPost('SurveysGroups');

        // Check if the parent group is valid
        if (!empty($attributes['parent_id'])) {
            $parentId = $attributes['parent_id'] ;
            /* Check permission */
            $availableParents = $this->surveysGroup->getParentGroupOptions();
            if (!array_key_exists($parentId, $availableParents)) {
                // TODO: Is sprintf() really needed here? The message is set like this in the update action in SurveysGroupsController,
                // so there is a chance that some translations actually have the param.
                // Also, the message itself is not clear. It should be something like "You don't have rights on the parent group".
                $this->messages[] = new TypedMessage(sprintf(gT("You don't have rights on Survey group"), \CHtml::encode($parentId)), 'error');
                // Clear the parent_id to avoid saving it
                $attributes['parent_id'] = null;
            }
        }

        $this->surveysGroup->attributes = $attributes;
        $this->surveysGroup->created_by = $this->user->id;
        if ($this->surveysGroup->save()) {
            // Save new SurveysGroupsettings record
            $this->surveysGroupsettings->gsid = $this->surveysGroup->gsid;
            $this->surveysGroupsettings->setToInherit();

            return $this->surveysGroupsettings->save();
        } else {
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
