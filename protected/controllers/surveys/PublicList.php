<?php
namespace ls\controllers\surveys;

use ls\models\Survey;
class PublicList extends \Action
{
    public function run($language = null)
    {
        if (isset($language)) {
            App()->setLanguage($language);
        }
        /**
         * @todo Use dataproviders instead.
         */
        $this->render('publicSurveyList', [
            'publicSurveys' => Survey::model()->active()->open()->public()->with('languagesettings')->findAll(),
            'futureSurveys' => Survey::model()->active()->registration()->public()->with('languagesettings')->findAll(),

        ]);
    }

}