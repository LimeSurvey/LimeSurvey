<?php


namespace ls\controllers\tokens;

use ls\models\Survey;
use ls\models\Token;

/**
 * This action removes all tokesn from a token table.
 * @package ls\controllers\responses
 */
class Clear extends \Action
{
    /**
     * @param $surveyId
     * @throws \CHttpException
     * @todo Add permission check.
     */
    public function run($surveyId)
    {
        $survey = Survey::model()->findByPk($surveyId);
        $this->controller->menus['survey'] = $survey;
        if (App()->request->getIsDeleteRequest()) {
            /** @todo Implement this. */
            if (Token::model($surveyId)->deleteAll()) {
                App()->user->setFlash('success', gT("Tokens removed"));
                $this->redirect(['tokens/index', 'surveyId' => $surveyId]);
            }
        } else {
            throw new \CHttpException(405, "Method not allowed.");
        }
    }
}