<?php
namespace ls\controllers;

use ls\models\questions\RadioListQuestion;
use ls\models\questions\ShortTextQuestion;
use \Yii;
use ls\components\SurveySession;
use ls\models\DummyResponse;
use ls\models\Survey;
use ls\models\SurveyLanguageSetting;
use Cake\Utility\Hash;

class TemplatesController extends Controller
{
    public $layout = 'main';
    public function actionIndex($name = 'default', $screen = 'welcome')
    {
        if (!\ls\models\Template::templateNameFilter($name)) {
            throw new \CHttpException(404, "ls\models\Template not found.");
        }
        $template = [
            'name' => $name,
            'writable' => \ls\models\Template::isStandardTemplate($name)
        ];
        $screen = Hash::get(Hash::extract($this->screens(), "{n}[id=$screen]"), 0, null);
//        $this->menus['template'] = [
//            'template' => $template,
//            'screen' => $screen,
//            'screens' => $this->screens()
//        ];
        return $this->render('editor', [
            'template' => $template,
            'screen' => $screen,
            'screens' => $this->screens()

        ]);

    }
    protected function screens() {
        $screens[] = [
            'name' => gT('Welcome Page'),
            'id' => 'welcome',
            'templates' => [
                'startpage.pstpl',
                'welcome.pstpl',
                'privacy.pstpl',
                'navigator.pstpl',
                'endpage.pstpl'
            ]
        ];
        $screens[] = [
            'name' => gT('Question Page'),
            'id' => 'question',
            'templates' => [
                'startpage.pstpl',
                'survey.pstpl',
                'startgroup.pstpl',
                'groupdescription.pstpl',
                'question.pstpl',
                'endgroup.pstpl',
                'navigator.pstpl',
                'endpage.pstpl'
            ]
        ];
        $screens[] = [
            'name' => gT('Completed Page'),
            'id' => 'completed',
            'templates' => [
                'startpage.pstpl',
                'assessment.pstpl',
                'completed.pstpl',
                'endpage.pstpl'
            ]
        ];
        $screens[] = [
            'name' => gT('Clear All Page'),
            'id' => 'clearall',
            'templates' => [
                'startpage.pstpl',
                'clearall.pstpl',
                'endpage.pstpl'
            ]
        ];

        $screens[] = [
            'name' => gT('Register Page'),
            'id' => 'register',
            'templates' => [
                'startpage.pstpl',
                'survey.pstpl',
                'register.pstpl',
                'endpage.pstpl'
            ]
        ];
        $screens[] = [
            'name' => gT('Load Page'),
            'id' => 'load',
            'templates' => [
                'startpage.pstpl',
                'load.pstpl',
                'endpage.pstpl'
            ]
        ];
        $screens[] = [
            'name' => gT('Save Page'),
            'id' => 'save',
            'templates'=> [
                'startpage.pstpl',
                'save.pstpl',
                'endpage.pstpl'
            ]
        ];
        $screens[] = [
            'name' => gT('Print answers page'),
            'id' => 'printanswers',
            'templates' => [
                'startpage.pstpl',
                'printanswers.pstpl',
                'endpage.pstpl'
            ]
        ];
        $screens[] = [
            'name' => gT('Printable survey page'),
            'id' => 'printablesurvey',
            'templates' => [
                'print_survey.pstpl',
                'print_group.pstpl',
                'print_question.pstpl'
            ]
        ];
        return $screens;
    }

    public function actionPreview($name, $page) {
        App()->disableWebLogRoutes();
        if (!\ls\models\Template::templateNameFilter($name)) {
            throw new \CHttpException(404, "Template not found.");
        }


        $search = Hash::extract($this->screens(), "{n}[id=$page]");
        if (empty($search)) {
            throw new \CHttpException(404, gT("Page not found."));

        }
        $screen = $search[0];


        $survey = $this->createSurvey($name);
        $session = new SurveySession(null, new DummyResponse($survey), 0);

        $session->setSurvey($survey);

        $survey->languagesettings = [
            App()->language => $languageSettings = new SurveyLanguageSetting()
        ];


        // FAKE DATA FOR TEMPLATES
        $survey['sid'] = 12345;
        $languageSettings->title = gT("Template Sample");
        $languageSettings->description =
            "<p>".gT('This is a sample survey description. It could be quite long.')."</p>".
            "<p>".gT("But this one isn't.")."<p>";
        $languageSettings->welcometext =
            "<p>".gT('Welcome to this sample survey')."<p>" .
            "<p>".gT('You should have a great time doing this')."<p>";
        $survey->bool_allowsave = true;
        $survey->bool_active = true;
        $survey->bool_tokenanswerspersistence = true;


        $languageSettings->url = "http://www.limesurvey.org/";
        $languageSettings->urldescription = gT("Some URL description");
        $survey->usecaptcha = "A";

        $replacements['GROUPNAME'] = gT("Group 1: The first lot of questions");
        $replacements['GROUPDESCRIPTION'] = gT("This group description is fairly vacuous, but quite important.");





        switch ($screen['id'])
        {

            case 'printablesurvey':
//                $questionoutput = [];
//                foreach (file("$templatedir/print_question.pstpl") as $op)
//                {
//                    $questionoutput[] = \ls\helpers\Replacements::templatereplace($op, [
//                        'QUESTION_NUMBER' => '1',
//                        'QUESTION_CODE' => 'Q1',
//                        'QUESTION_MANDATORY' => gT('*'),
//                        // If there are conditions on a question, list the conditions.
//                        'QUESTION_SCENARIO' => 'Only answer this if certain conditions are met.',
//                        'QUESTION_CLASS' => ' mandatory list-radio',
//                        'QUESTION_TYPE_HELP' => gT('Please choose *only one* of the following:'),
//                        // (not sure if this is used) mandatory error
//                        'QUESTION_MAN_MESSAGE' => '',
//                        // (not sure if this is used) validation error
//                        'QUESTION_VALID_MESSAGE' => '',
//                        // (not sure if this is used) file validation error
//                        'QUESTION_FILE_VALID_MESSAGE' => '',
//                        'QUESTION_TEXT' => gT('This is a sample question text. The user was asked to pick an entry.'),
//                        'QUESTIONHELP' => gT('This is some help text for this question.'),
//                        'ANSWER' =>
//                            $this->getController()->render('/admin/templates/templateeditor_printablesurvey_quesanswer_view',
//                                [
//                                    'templateurl' => $templateurl
//                                ], true),
//                    ], $aData);
//                }
//                $groupoutput = [];
//                $groupoutput[] = \ls\helpers\Replacements::templatereplace(file_get_contents("$templatedir/print_group.pstpl"),
//                    ['QUESTIONS' => implode(' ', $questionoutput)], $aData);
//
//                $myoutput[] = \ls\helpers\Replacements::templatereplace(file_get_contents("$templatedir/print_survey.pstpl"), [
//                    'GROUPS' => implode(' ', $groupoutput),
//                    'FAX_TO' => gT("Please fax your completed survey to:") . " 000-000-000",
//                    'SUBMIT_TEXT' => gT("Submit your survey."),
//                    'HEADELEMENTS' => getPrintableHeader(),
//                    'SUBMIT_BY' => sprintf(gT("Please submit by %s"), date('d.m.y')),
//                    'THANKS' => gT('Thank you for completing this survey.'),
//                    'END' => gT('This is the survey end message.')
//                ], $aData);
                break;

        }

        foreach ($screen['templates'] as $file)
        {
            if ($file == 'question.pstpl') {
                foreach($survey->questions as $question) {
                    $renderedQuestion = $question->render($session->response, $session);
                    $renderedQuestion->setTemplate(file_get_contents("{$session->templateDir}/$file"));
                    $myoutput[] = $renderedQuestion->render($session);
                }
            } else {
                $myoutput[] = \ls\helpers\Replacements::templatereplace(
                    file_get_contents($session->templateDir . "/$file"), $replacements, [], null, $session);
            }
        }

        $myoutput[] = "</html>";
        ob_start();
        doHeader();
        $cs = App()->getClientScript();
        $cs->registerPackage('SurveyRuntime');
        $cs->registerScript('ls', \LimeExpressionManager::getScript($survey), \CClientScript::POS_END);
        echo implode("\n", $myoutput);
        doFooter();

        $result = ob_get_clean();
        App()->clientScript->render($result);
        echo $result;


    }

    /**
     * Creates a survey with template set to $name.
     * @param $name
     *
     */
    protected function createSurvey($name) {
        /**
         * Construct survey.
         */
        $survey = new Survey();
        $survey->format = \ls\models\Survey::FORMAT_GROUP;
        $group = new \ls\models\QuestionGroup();
        $group->primaryKey = 0;
        $question1 = new ShortTextQuestion();
        $question2 = new ShortTextQuestion();
        $question1->primaryKey = 0;
        $question2->primaryKey = 1;
        $question1->survey = $survey;
        $question2->survey = $survey;
        $question1->group = $group;
        $question2->group = $group;
        $question1->question = 'This is a question, try entering "a" as value.';
        $question2->question = 'This is another question (it is relevant when question 1 is "a"';
        $question1->title = 'q1';
        $question2->title = 'q2';
        $question2->relevance = "q1 == 'a'";
        $question1->preg = '/^a.*$/';

        $survey->questions = $group->questions = [$question1, $question2];
        $survey->groups = [$group];
        $survey->template = $name;
        return $survey;
    }
}