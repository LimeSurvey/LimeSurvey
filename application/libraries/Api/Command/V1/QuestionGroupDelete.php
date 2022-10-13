<?php

namespace LimeSurvey\Api\Command\V1;

use QuestionGroup;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Mixin\Auth\AuthSession;
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermission;
use LimeSurvey\Api\Command\Mixin\CommandResponse;
use LimeSurvey\Api\Command\Mixin\Accessor\QuestionGroupModel;
use LimeSurvey\Api\Command\Mixin\Accessor\SurveyModel;

class QuestionGroupDelete implements CommandInterface
{
    use AuthSession;
    use AuthPermission;
    use CommandResponse;
    use QuestionGroupModel;
    use SurveyModel;

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

        $oGroup = $this->getQuestionGroupModel($iGroupID);
        if (!isset($oGroup)) {
            return $this->responseErrorNotFound(
                array('status' => 'Error:Invalid group ID')
            );
        }
        $iSurveyID = $oGroup->sid;

        $oSurvey = $this->getSurveyModel($iSurveyID);
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
