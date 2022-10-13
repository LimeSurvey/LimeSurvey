<?php

namespace LimeSurvey\Api\Command\V1;

use QuestionGroup;
use Survey;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Mixin\Auth\AuthSession;
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermission;
use LimeSurvey\Api\Command\Mixin\CommandResponse;

class QuestionGroupDelete implements CommandInterface
{
    use AuthSession;
    use AuthPermission;
    use CommandResponse;

    private $group = null;
    private $survey = null;

    /**
     * Get Group
     *
     * Used as a proxy for providing a mock record during testing.
     *
     * @param int $id
     * @return QuestionGroup
     */
    public function getGroup($id): ?QuestionGroup
    {
        if (!$this->group) {
            $this->group =
                QuestionGroup::model()
                    ->findByAttributes(array('gid' => $id));
        }

        return $this->group;
    }

    /**
     * Set Group
     *
     * Used to set mock record during testing.
     *
     * @param int $id
     * @return void
     */
    public function setGroup(QuestionGroup $group)
    {
        $this->group = $group;
    }

    /**
     * Get Survey
     *
     * Used as a proxy for providing a mock record during testing.
     *
     * @param int $id
     * @return Survey
     */
    public function getSurvey($id): ?Survey
    {
        if (!$this->survey) {
            Survey::model()->findByPk($id);
        }

        return $this->survey;
    }

    /**
     * Set Survey
     *
     * Used to set mock record during testing.
     *
     * @param int $id
     * @return void
     */
    public function setSurvey(Survey $survey)
    {
        $this->survey = $survey;
    }

    /**
     * Run group delete command.
     *
     * @access public
     * @param \LimeSurvey\Api\Command\Request\Request $request
     * @return \LimeSurvey\Api\Command\Response\Response
     */
    public function run(Request $request)
    {
        $sSessionKey = (string) $request->getData('sessionKey');
        $iGroupID = (int) $request->getData('groupID');

        if (
            ($response = $this->checkKey($sSessionKey)) !== true
        ) {
            return $response;
        }

        $oGroup = $this->getGroup($iGroupID);
        if (!isset($oGroup)) {
            return $this->responseErrorNotFound(
                array('status' => 'Error:Invalid group ID')
            );
        }
        $iSurveyID = $oGroup->sid;

        $oSurvey = $this->getSurvey($iSurveyID);
        if (!isset($oSurvey)) {
            return $this->responseErrorBadRequest(
                array('status' => 'Error: Invalid survey ID')
            );
        }

        if (
            ($response = $this->hasSurveyPermission(
                $iSurveyID,
                'surveycontent',
                'delete'
            )
            ) !== true
        ) {
            return $response;
        }

        if ($oSurvey->isActive) {
            return $this->responseError(
                array('status' => 'Error:Survey is active and not editable')
            );
        }

        $dependantOn = getGroupDepsForConditions(
            $oGroup->sid,
            "all",
            $iGroupID,
            "by-targgid"
        );
        if (isset($dependantOn)) {
            return $this->responseError(
                array('status' => 'Group with dependencies - deletion not allowed')
            );
        }

        $iGroupsDeleted = QuestionGroup::deleteWithDependency(
            $iGroupID,
            $iSurveyID
        );

        if ($iGroupsDeleted === 1) {
            QuestionGroup::model()
                ->updateGroupOrder($iSurveyID);
            return $this->responseSuccess(
                (int) $iGroupID
            );
        } else {
            return $this->responseError(
                array('status' => 'Group deletion failed')
            );
        }
    }
}
