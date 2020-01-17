<?php


class CentralParticipantController extends LSYii_Controller
{

    public function actiondisplayParticipants(){

        //Get list of surveys.
        //Should be all surveys owned by user (or all surveys for super admin)
        $surveys = Survey::model();
        //!!! Is this even possible to execute?
        if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            $surveys->permission(Yii::app()->user->getId());
        }

        /** @var Survey[] $aSurveyNames */
        $aSurveyNames = $surveys->model()->with(array('languagesettings' => array('condition' => 'surveyls_language=language'), 'owner'))->findAll();

        /* Build a list of surveys that have tokens tables */
        $tSurveyNames = array();
        foreach ($aSurveyNames as $row) {
            $trow = array_merge($row->attributes, $row->defaultlanguage->attributes);
            if ($row->hasTokensTable) {
                $tSurveyNames[] = $trow;
            }
        }

        // if superadmin all the records in the cpdb will be displayed
        $iUserId = App()->user->getId();
        if (Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            $iTotalRecords = Participant::model()->count();
        } else { // if not only the participants on which he has right on (shared and owned)
            $iTotalRecords = Participant::model()->getParticipantsOwnerCount($iUserId);
        }
        $model = new Participant();
        $request = Yii::app()->request;
        $participantParam = $request->getParam('Participant');
        if ($participantParam) {
            $model->setAttributes($participantParam, false);
        }
        /* @todo : See when/where it's used */
        $searchcondition = $request->getParam('searchcondition');
        $searchparams = array();
        if ($searchcondition) {
            $searchparams = explode('||', $searchcondition);
            $model->addSurveyFilter($searchparams);
        }

        $model->bEncryption = true;

        // data to be passed to view
        $aData = array(
            'names' => User::model()->findAll(),
            'attributes' => ParticipantAttributeName::model()->getVisibleAttributes(),
            'allattributes' => ParticipantAttributeName::model()->getAllAttributes(),
            'attributeValues' => ParticipantAttributeName::model()->getAllAttributesValues(),
            'surveynames' => $aSurveyNames,
            'tokensurveynames' => $tSurveyNames,
            'searchcondition' => $searchparams,
            'aAttributes' => ParticipantAttributeName::model()->getAllAttributes(),
            'totalrecords' => $iTotalRecords,
            'model' => $model,
            'debug' => $request->getParam('Participant')
        );

        $aData['pageSizeParticipantView'] = Yii::app()->user->getState('pageSizeParticipantView');
        $searchstring = $request->getPost('searchstring');
        $aData['searchstring'] = $searchstring;
        Yii::app()->clientScript->registerPackage('bootstrap-datetimepicker');
        Yii::app()->clientScript->registerPackage('bootstrap-switch');

        // check global and custom permissions and pass them to $aData
        $aData['permissions'] = permissionsAsArray(
            [
                'superadmin' => ['read'],
                'templates' => ['read'],
                'labelsets' => ['read'],
                'users' => ['read'],
                'usergroups' => ['read'],
                'participantpanel' => ['read', 'create', 'update', 'delete', 'export', 'import'],
                'settings' => ['read']
            ],
            [
                'participantpanel' => [
                    'editSharedParticipants' => empty(ParticipantShare::model()->findAllByAttributes(
                        ['share_uid' =>  $iUserId],
                        ['condition' => 'can_edit = \'0\' OR can_edit = \'\'',]
                    )),
                    'sharedParticipantExists' => ParticipantShare::model()->exists('share_uid = :userid', [':userid' => $iUserId]),
                    'isOwner' => isset($participantParam['owner_uid']) && ($participantParam['owner_uid'] === $iUserId) ? true : false
                ],

            ]
        );
        $aData['massiveAction'] = App()->getController()->renderPartial('/admin/participants/massive_actions/_selector', array('permissions' => $aData['permissions']), true, false);

        // Set page size
        if ($request->getPost('pageSizeParticipantView')) {
            Yii::app()->user->setState('pageSizeParticipantView', $request->getPost('pageSizeParticipantView'));
        }

        // Loads the participant panel view and display participant view
        App()->getClientScript()->registerPackage('bootstrap-multiselect');
        $aData['display']['menu_bars'] = false;

        // Add "_view" to urls
        if (is_array($aViewUrls)) {
            array_walk($aViewUrls, function (&$url) {
                $url .= "_view";
            });
        } elseif (is_string($aViewUrls)) {
            $aViewUrls .= "_view";
        } else {
            // Complete madness
            throw new \InvalidArgumentException("aViewUrls must be either string or array");
        }

        /** Todo
         * here we should get out of Survey_Common_Action
         *
         * use return $this->render(...) instead
         *
         */
//        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData, $sRenderFile);
    }

}
