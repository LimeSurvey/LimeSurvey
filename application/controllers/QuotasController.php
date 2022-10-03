<?php

class QuotasController extends LSBaseController
{
    /**
     * Here we have to use the correct layout (NOT main.php)
     *
     * @param string $view
     * @return bool
     */
    protected function beforeRender($view)
    {
        $this->layout = 'layout_questioneditor';
        LimeExpressionManager::SetSurveyId($this->aData['surveyid']);
        LimeExpressionManager::StartProcessingPage(false, true);

        return parent::beforeRender($view);
    }


    public function actionIndex($surveyid)
    {
        $surveyid = sanitize_int($surveyid);
        if (!(Permission::model()->hasSurveyPermission($surveyid, 'quotas'))) {
            Yii::app()->user->setFlash('error', gT("Access denied."));
            $this->redirect(Yii::app()->request->urlReferrer);
        }
        $oSurvey = Survey::model()->findByPk($surveyid);

        //logic part here, get data for the index view

        // Set number of page
        if (Yii::app()->getRequest()->getQuery('pageSize')) {
            Yii::app()->user->setState('pageSize', (int) Yii::app()->getRequest()->getQuery('pageSize'));
        }
        $aData['iGridPageSize'] = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']);
        $aData['oDataProvider'] = new CArrayDataProvider($oSurvey->quotas, array(
            'pagination' => array(
                'pageSize' => $aData['iGridPageSize'],
                'pageVar' => 'page'
            ),
        ));
        $aData['title_bar']['title'] = $oSurvey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $surveyid . ")";
        $aData['subaction'] = gT("Survey quotas");
        $aData['sidemenu']['state'] = false;
        $this->aData = $aData;
        $this->render('index', [

        ]);
    }

    /**
     * @param $surveyid
     * @return void
     */
    public function actionQuickCSVReport($surveyid)
    {
        $surveyid = sanitize_int($surveyid);
        if (!(Permission::model()->hasSurveyPermission($surveyid, 'quotas'))) {
            Yii::app()->user->setFlash('error', gT("Access denied."));
            $this->redirect(Yii::app()->request->urlReferrer);
        }
        $oSurvey = Survey::model()->findByPk($surveyid);

        /* Export a quickly done csv file */
        header("Content-Disposition: attachment; filename=quotas-survey" . $surveyid . ".csv");
        header("Content-type: text/comma-separated-values; charset=UTF-8");
        echo gT("Quota name") . "," . gT("Limit") . "," . gT("Completed") . "," . gT("Remaining") . "\r\n";
        if (!empty($oSurvey->quotas)) {
            foreach ($oSurvey->quotas as $oQuota) {
                $completed = $oQuota->completeCount;
                echo $oQuota->name . "," . $oQuota->qlimit . "," . $completed . "," . ($oQuota->qlimit - $completed) . "\r\n";
            }
        }
        App()->end();
    }
}
