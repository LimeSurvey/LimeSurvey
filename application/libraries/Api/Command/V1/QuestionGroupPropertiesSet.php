<?php

namespace LimeSurvey\Api\Command\V1;

use Exception;
use Permission;
use QuestionGroup;
use QuestionGroupL10n;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Mixin\Auth\AuthSession;
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermission;
use LimeSurvey\Api\Command\Mixin\CommandResponse;
use LimeSurvey\Api\Command\Mixin\Accessor\QuestionGroupModelWithL10nsById;
use LimeSurvey\Api\Command\Mixin\Accessor\SurveyModel;

class QuestionGroupPropertiesSet implements CommandInterface
{
    use AuthSession;
    use AuthPermission;
    use CommandResponse;
    use QuestionGroupModelWithL10nsById;
    use SurveyModel;

    /**
     * Run group properties get command.
     *
     * @access public
     * @param \LimeSurvey\Api\Command\Request\Request $request
     * @return \LimeSurvey\Api\Command\Response\Response
     */
    public function run(Request $request)
    {
        $sSessionKey = (string) $request->getData('sessionKey');
        $iGroupID = (int) $request->getData('groupID');
        $aGroupData = $request->getData('groupData', null);

        if (
            ($response = $this->checkKey($sSessionKey)) !== true
        ) {
            return $response;
        }

        $oGroup = $this->getQuestionGroupModelWithL10nsById($iGroupID);
        if (is_null($oGroup)) {
            return $this->responseErrorNotFound(
                ['status' => 'Error: Invalid group ID']
            );
        }

        if (
            ($response = $this->hasSurveyPermission(
                $oGroup->sid,
                'survey',
                'update'
            )
            ) !== true
        ) {
            return $response;
        }

        $aResult = [];
        // Remove fields that may not be modified
        unset($aGroupData['sid']);
        unset($aGroupData['gid']);

        // Backwards compatibility for L10n data
        if (!empty($aGroupData['language'])) {
            $language = $aGroupData['language'];
            $aGroupData['questiongroupl10ns'][$language] = [
                'language' => $language,
                'group_name' => !empty($aGroupData['group_name'])
                ? $aGroupData['group_name'] : '',
                'description' => !empty($aGroupData['description'])
                ? $aGroupData['description'] : '',
            ];
        }

        // Process L10n data
        if (
            !empty($aGroupData['questiongroupl10ns'])
            && is_array($aGroupData['questiongroupl10ns'])
        ) {
            $aL10nDestinationFields = array_flip(
                QuestionGroupL10n::model()->tableSchema->columnNames
            );
            foreach ($aGroupData['questiongroupl10ns'] as $language => $aLanguageData) {
                // Get existing L10n data or create new
                if (isset($oGroup->questiongroupl10ns[$language])) {
                    $oQuestionGroupL10n = $oGroup->questiongroupl10ns[$language];
                } else {
                    $oQuestionGroupL10n = new QuestionGroupL10n();
                    $oQuestionGroupL10n->gid = $iGroupID;
                    $oQuestionGroupL10n->setAttribute('language', $language);
                    $oQuestionGroupL10n->setAttribute('group_name', '');
                    $oQuestionGroupL10n->setAttribute('description', '');
                    if (!$oQuestionGroupL10n->save()) {
                        $aResult['questiongroupl10ns'][$language] = false;
                        continue;
                    }
                }

                // Remove invalid fields
                $aGroupL10nData = array_intersect_key(
                        $aLanguageData,
                        $aL10nDestinationFields
                    );
                if (empty($aGroupL10nData)) {
                    $aResult['questiongroupl10ns'][$language] = 'Empty group L10n data';
                    continue;
                }

                $aGroupL10nAttributes = $oQuestionGroupL10n->getAttributes();
                foreach ($aGroupL10nData as $sFieldName => $sValue) {
                    $oQuestionGroupL10n->setAttribute($sFieldName, $sValue);
                    try {
                        // save the change to database - one by one to allow for validation to work
                        $bSaveResult = $oQuestionGroupL10n->save();
                        if (!is_array($aResult['questiongroupl10ns'][$language])) {
                            $aResult['questiongroupl10ns'][$language] = [];
                        }
                        $aResult['questiongroupl10ns'][$language][$sFieldName] = $bSaveResult;
                        //unset failed values
                        if (!$bSaveResult) {
                            $oQuestionGroupL10n->$sFieldName = $aGroupL10nAttributes[$sFieldName];
                        }
                    } catch (Exception $e) {
                        //unset values that cause exception
                        $oQuestionGroupL10n->$sFieldName = $aGroupL10nAttributes[$sFieldName];
                    }
                }
            }
        }

        // Remove invalid fields
        $aDestinationFields = array_flip(
            QuestionGroup::model()->tableSchema->columnNames
        );
        $aGroupData = array_intersect_key(
            $aGroupData,
            $aDestinationFields
        );
        $aGroupAttributes = $oGroup->getAttributes();
        if (empty($aGroupData)) {
            if (empty($aResult)) {
                return $this->responseSuccess(
                    ['status' => 'No valid Data']
                );
            } else {
                return $this->responseSuccess($aResult);
            }
        }

        foreach ($aGroupData as $sFieldName => $sValue) {
            //all dependencies this group has
            $has_dependencies = getGroupDepsForConditions(
                $oGroup->sid,
                $iGroupID
            );
            //all dependencies on this group
            $dependantOn = getGroupDepsForConditions(
                $oGroup->sid,
                "all",
                $iGroupID,
                "by-targgid"
            );
            //We do not allow groups with dependencies to change order - that would lead to broken dependencies
            if (
                (isset($has_dependencies) || isset($dependantOn))
                && $sFieldName == 'group_order'
            ) {
                $aResult[$sFieldName] = 'Group with dependencies - Order cannot be changed';
                continue;
            }
            $oGroup->setAttribute($sFieldName, $sValue);

            try {
                // save the change to database - one by one to allow for validation to work
                $bSaveResult = $oGroup->save();
                QuestionGroup::model()
                    ->updateGroupOrder($oGroup->sid);
                $aResult[$sFieldName] = $bSaveResult;
                //unset failed values
                if (!$bSaveResult) {
                    $oGroup->$sFieldName = $aGroupAttributes[$sFieldName];
                }
            } catch (Exception $e) {
                // unset values that cause exception
                $oGroup->$sFieldName = $aGroupAttributes[$sFieldName];
            }
        }
        return $this->responseSuccess($aResult);
    }
}
