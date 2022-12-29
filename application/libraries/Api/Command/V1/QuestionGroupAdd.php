<?php

namespace LimeSurvey\Api\Command\V1;

use QuestionGroup;
use QuestionGroupL10n;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response
};
use LimeSurvey\Api\Command\Mixin\{
    CommandResponseTrait,
    Auth\AuthSessionTrait,
    Auth\AuthPermissionTrait,
    Accessor\SurveyModelTrait
};

class QuestionGroupAdd implements CommandInterface
{
    use AuthSessionTrait;
    use AuthPermissionTrait;
    use CommandResponseTrait;
    use SurveyModelTrait;

    /**
     * Run group add command.
     *
     * @param Request
     * @return Response
     */
    public function run(Request $request)
    {
        $sSessionKey = (string) $request->getData('sessionKey');
        $iSurveyID = (int) $request->getData('_id');
        $sGroupTitle = (string) $request->getData('groupTitle');
        $sGroupDescription = (string) $request->getData('groupDescription');

        if (
            ($response = $this->checkKey($sSessionKey)) !== true
        ) {
            return $response;
        }

        if (
            ($response = $this->hasSurveyPermission(
                $iSurveyID,
                'survey',
                'update'
            )
            ) !== true
        ) {
            return $response;
        }

        $oSurvey = $this->getSurveyModel($iSurveyID);
        if (!isset($oSurvey)) {
            return $this->responseErrorBadRequest(
                ['status' => 'Error: Invalid survey ID']
            );
        }

        if ($oSurvey->isActive) {
            return $this->responseErrorBadRequest(
                ['status' => 'Error: Survey is active and not editable']
            );
        }

        $oGroup = new QuestionGroup();
        $oGroup->sid = $iSurveyID;
        $oGroup->group_order = getMaxGroupOrder($iSurveyID);
        if (!$oGroup->save()) {
            return $this->responseError(
                ['status' => 'Creation Failed']
            );
        }

        $oQuestionGroupL10n = new QuestionGroupL10n();
        $oQuestionGroupL10n->group_name = $sGroupTitle;
        $oQuestionGroupL10n->description = $sGroupDescription;
        $oQuestionGroupL10n->language = \Survey::model()->findByPk($iSurveyID)->language;
        $oQuestionGroupL10n->gid = $oGroup->gid;

        if ($oQuestionGroupL10n->save()) {
            return $this->responseSuccess((int) $oGroup->gid);
        } else {
            return $this->responseError(
                ['status' => 'Creation Failed']
            );
        }
    }
}
