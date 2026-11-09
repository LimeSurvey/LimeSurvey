<?php

namespace LimeSurvey\Models\Services;

use Answer;
use AnswerL10n;
use Question;
use QuestionGroup;
use QuestionGroupL10n;
use QuestionL10n;
use Survey;
use SurveyLanguageSetting;

/**
 * This class is responsible for quick translation and all DB actions needed.
 *
 * @todo All the swithc-statements could be remade using OOP instead.
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class QuickTranslation
{
    /** @var string Placeholder for an expression while its text goes through machine translation */
    private const EXPRESSION_MARKER = 'LSEXPR';

    /** @var Survey the survey */
    private $survey;

    private static $translateFields = [
        'title' =>
        [
            'type' => 1,
            'dbColumn' => 'surveyls_title',
            'id1' => '',
            'id2' => '',
            'gid' => false,  //boolean value to indicate if used or not
            'qid' => false,  //boolean value to indicate if used or not
            'HTMLeditorType' => "title",
            'HTMLeditorDisplay' => "Inline",
            'associated' => "description" //this is the second field of the tab content
        ],
        'description' =>
        [
            'type' => 1,
            'dbColumn' => 'surveyls_description',
            'id1' => '',
            'id2' => '',
            'gid' => false,
            'qid' => false,
            'HTMLeditorType' => "description",
            'HTMLeditorDisplay' => "Inline",
            'associated' => ""
        ],
        'welcome' =>
        [
            'type' => 1,
            'dbColumn' => 'surveyls_welcometext',
            'id1' => '',
            'id2' => '',
            'gid' => false,
            'qid' => false,
            'HTMLeditorType' => "welcome",
            'HTMLeditorDisplay' => "Inline",
            'associated' => "end"
        ],
        'end' =>
        [
            'type' => 1,
            'dbColumn' => 'surveyls_endtext',
            'id1' => '',
            'id2' => '',
            'gid' => false,
            'qid' => false,
            'HTMLeditorType' => "end",
            'HTMLeditorDisplay' => "Inline",
            'associated' => ""
        ],
        'group' =>
        [
            'type' => 2,
            'dbColumn' => 'group_name',
            'id1' => 'gid',
            'id2' => '',
            'gid' => true,
            'qid' => false,
            'HTMLeditorType' => "group",
            'HTMLeditorDisplay' => "Modal",
            'associated' => "group_desc"
        ],
        'group_desc' =>
        [
            'type' => 2,
            'dbColumn' => 'description',
            'id1' => 'gid',
            'id2' => '',
            'gid' => true,
            'qid' => false,
            'HTMLeditorType' => "group_desc",
            'HTMLeditorDisplay' => "Modal",
            'associated' => ""
        ],
        'question' =>
        [
            'type' => 3,
            'dbColumn' => 'question',
            'id1' => 'qid',
            'id2' => '',
            'gid' => true,
            'qid' => true,
            'HTMLeditorType' => "question",
            'HTMLeditorDisplay' => "Modal",
            'associated' => "question_help"
        ],
        'question_help' =>
        [
            'type' => 3,
            'dbColumn' => 'help',
            'id1' => 'qid',
            'id2' => '',
            'gid' => true,
            'qid' => true,
            'HTMLeditorType' => "question_help",
            'HTMLeditorDisplay' => "Modal",
            'associated' => ""
        ],
        'subquestion' =>
        [
            'type' => 4,
            'dbColumn' => 'question',
            'id1' => 'qid',
            'id2' => '',
            'gid' => true,
            'qid' => true,
            'HTMLeditorType' => "question",
            'HTMLeditorDisplay' => "Modal",
            'associated' => ""
        ],
        'answer' =>
        [
            'type' => 5,
            'dbColumn' => 'answer',
            'id1' => 'qid',
            'id2' => 'code',
            'scaleid' => 'scale_id',
            'gid' => false,
            'qid' => true,
            'HTMLeditorType' => "subquestion",
            'HTMLeditorDisplay' => "Modal",
            'associated' => ""
        ],
        'emailinvite' =>
        [
            'type' => 1,
            'dbColumn' => 'surveyls_email_invite_subj',
            'id1' => '',
            'id2' => '',
            'gid' => false,
            'qid' => false,
            'HTMLeditorType' => "email",
            'HTMLeditorDisplay' => "",
            'associated' => "emailinvitebody"
        ],
        'emailinvitebody' =>
        [
            'type' => 1,
            'dbColumn' => 'surveyls_email_invite',
            'id1' => '',
            'id2' => '',
            'gid' => false,
            'qid' => false,
            'HTMLeditorType' => "email",
            'HTMLeditorDisplay' => "Modal",
            'associated' => ""
        ],
        'emailreminder' =>
        [
            'type' => 1,
            'dbColumn' => 'surveyls_email_remind_subj',
            'id1' => '',
            'id2' => '',
            'gid' => false,
            'qid' => false,
            'HTMLeditorType' => "email",
            'HTMLeditorDisplay' => "",
            'associated' => "emailreminderbody"
        ],
        'emailreminderbody' =>
        [
            'type' => 1,
            'dbColumn' => 'surveyls_email_remind',
            'id1' => '',
            'id2' => '',
            'gid' => false,
            'qid' => false,
            'HTMLeditorType' => "email",
            'HTMLeditorDisplay' => "Modal",
            'associated' => ""
        ],
        'emailconfirmation' =>
        [
            'type' => 1,
            'dbColumn' => 'surveyls_email_confirm_subj',
            'id1' => '',
            'id2' => '',
            'gid' => false,
            'qid' => false,
            'HTMLeditorType' => "email",
            'HTMLeditorDisplay' => "",
            'associated' => "emailconfirmationbody"
        ],
        'emailconfirmationbody' =>
        [
            'type' => 1,
            'dbColumn' => 'surveyls_email_confirm',
            'id1' => '',
            'id2' => '',
            'gid' => false,
            'qid' => false,
            'HTMLeditorType' => "email",
            'HTMLeditorDisplay' => "Modal",
            'associated' => ""
        ],
        'emailregistration' =>
        [
            'type' => 1,
            'dbColumn' => 'surveyls_email_register_subj',
            'id1' => '',
            'id2' => '',
            'gid' => false,
            'qid' => false,
            'HTMLeditorType' => "email",
            'HTMLeditorDisplay' => "",
            'associated' => "emailregistrationbody"
        ],
        'emailregistrationbody' =>
        [
            'type' => 1,
            'dbColumn' => 'surveyls_email_register',
            'id1' => '',
            'id2' => '',
            'gid' => false,
            'qid' => false,
            'HTMLeditorType' => "email",
            'HTMLeditorDisplay' => "Modal",
            'associated' => ""
        ],
        'emailbasicadminnotification' =>
        [
            'type' => 1,
            'dbColumn' => 'email_admin_notification_subj',
            'id1' => '',
            'id2' => '',
            'gid' => false,
            'qid' => false,
            'HTMLeditorType' => "email",
            'HTMLeditorDisplay' => "",
            'associated' => "emailbasicadminnotificationbody"
        ],
        'emailbasicadminnotificationbody' =>
        [
            'type' => 1,
            'dbColumn' => 'email_admin_notification',
            'id1' => '',
            'id2' => '',
            'gid' => false,
            'qid' => false,
            'HTMLeditorType' => "email",
            'HTMLeditorDisplay' => "Modal",
            'associated' => ""
        ],
        'emaildetailedadminnotification' =>
        [
            'type' => 1,
            'dbColumn' => 'email_admin_responses_subj',
            'id1' => '',
            'id2' => '',
            'gid' => false,
            'qid' => false,
            'HTMLeditorType' => "email",
            'HTMLeditorDisplay' => "",
            'associated' => "emaildetailedadminnotificationbody"
        ],
        'emaildetailedadminnotificationbody' =>
        [
            'type' => 1,
            'dbColumn' => 'email_admin_responses',
            'id1' => '',
            'id2' => '',
            'gid' => false,
            'qid' => false,
            'HTMLeditorType' => "email",
            'HTMLeditorDisplay' => "Modal",
            'associated' => ""
        ]
    ];

    /**
     * Quicktranslation constructor.
     *
     * @param Survey $survey the survey object
     *
     */
    public function __construct(Survey $survey)
    {
        $this->survey = $survey;
    }

    /**
     * This function gets the translation for a specific type.
     * Different types need different query.
     *
     * @param $type
     * @param $language
     * @return array|\CActiveRecord|mixed|Question[]|SurveyLanguageSetting[]|void|null
     */
    public function getTranslations($type, $language)
    {
        switch ($type) {
            case 'title':
            case 'description':
            case 'welcome':
            case 'end':
            case 'emailinvite':
            case 'emailinvitebody':
            case 'emailreminder':
            case 'emailreminderbody':
            case 'emailconfirmation':
            case 'emailconfirmationbody':
            case 'emailregistration':
            case 'emailregistrationbody':
            case 'email_confirm':
            case 'email_confirmbody':
            case 'emailbasicadminnotification':
            case 'emailbasicadminnotificationbody':
            case 'emaildetailedadminnotification':
            case 'emaildetailedadminnotificationbody':
                return SurveyLanguageSetting::model()->resetScope()->findAllByPk([
                    'surveyls_survey_id' => $this->survey->sid,
                    'surveyls_language' => $language
                ]);
            case 'group':
            case 'group_desc':
                return QuestionGroup::model()
                    ->with('questiongroupl10ns', ['condition' => 'language =:baselang ',
                        'params' => [':baselang' => $language]])
                    ->findAllByAttributes(['sid' => $this->survey->sid], ['order' => 't.gid']);
            case 'question':
            case 'question_help':
                return $this->getQuestionTranslations($language);
            case 'subquestion':
                return $this->getSubquestionTranslations($language);
            case 'answer':
                return $this->getAnswerTranslations($language);
        }
    }

    public function getQuestionTranslations($baselang)
    {
        return Question::model()
            ->with('questionl10ns', ['condition' => 'language =:baselang ',
                'params' => [':baselang' => $baselang]])
            ->with('parent', 'group')
            ->findAllByAttributes(
                ['sid' => $this->survey->sid, 'parent_qid' => 0],
                ['order' => 'group_order, t.question_order, t.scale_id']
            );
    }

    public function getSubquestionTranslations($baselang)
    {
        return Question::model()
            ->with('questionl10ns', array('condition' => 'language =:baselang ',
                'params' => [':baselang' => $baselang]))
            ->with('parent', array('condition' => 'language =:baselang ',
                'params' => [':baselang' => $baselang]))
            ->with('group', array('condition' => 'language =:baselang ',
                'params' => [':baselang' => $baselang]))
            ->findAllByAttributes(
                ['sid' => $this->survey->sid],
                [
                    'order' => 'group_order, parent.question_order,t.scale_id, t.question_order',
                    'condition' => 't.parent_qid>0',
                    'params' => array()]
            );
    }

    public function getAnswerTranslations($baselang)
    {
        return Answer::model()
            ->resetScope()
            ->with('answerl10ns', [
                'condition' => 'language =:baselang ',
                'params' => [':baselang' => $baselang]])
            ->with('question')
            ->with('group')
            ->findAllByAttributes(
                [],
                [
                    'order' => 'group_order, question.question_order, t.scale_id, t.sortorder, t.code',
                    'condition' => 'question.sid=:sid',
                    'params' => array(':sid' => $this->survey->sid)
                ]
            );
    }

    /**
     * Updates the translation for a given field name (e.g. surveyls_title)
     *
     * Note: Groups, questions and answers must belong to the survey of this service,
     * and the value is validated (XSS filtered) like in the regular editors.
     *
     * @param string $fieldName the field name from frontend
     * @param string $tolang shortcut for language (e.g. 'de')
     * @param string $new the new value to save as translation
     * @param int $qidOrgid groupid or questionid
     * @param string $answerCode the answer code
     * @param int|string $iScaleID the answer scale
     *
     * @return int|null number of updated rows, null for an unknown field name
     */
    public function updateTranslations($fieldName, $tolang, $new, $qidOrgid = 0, $answerCode = '', $iScaleID = '')
    {
        $languageSettingFields = [
            'title' => 'surveyls_title',
            'description' => 'surveyls_description',
            'welcome' => 'surveyls_welcometext',
            'end' => 'surveyls_endtext',
            'emailinvite' => 'surveyls_email_invite_subj',
            'emailinvitebody' => 'surveyls_email_invite',
            'emailreminder' => 'surveyls_email_remind_subj',
            'emailreminderbody' => 'surveyls_email_remind',
            'emailconfirmation' => 'surveyls_email_confirm_subj',
            'emailconfirmationbody' => 'surveyls_email_confirm',
            'emailregistration' => 'surveyls_email_register_subj',
            'emailregistrationbody' => 'surveyls_email_register',
            'emailbasicadminnotification' => 'email_admin_notification_subj',
            'emailbasicadminnotificationbody' => 'email_admin_notification',
            'emaildetailedadminnotification' => 'email_admin_responses_subj',
            'emaildetailedadminnotificationbody' => 'email_admin_responses',
        ];
        if (isset($languageSettingFields[$fieldName])) {
            if ($fieldName == 'title') {
                $new = mb_substr((string) $new, 0, 200);
            }
            $oLanguageSetting = SurveyLanguageSetting::model()->findByPk(
                ['surveyls_survey_id' => $this->survey->sid, 'surveyls_language' => $tolang]
            );
            return $this->saveTranslatedAttribute($oLanguageSetting, $languageSettingFields[$fieldName], $new);
        }
        switch ($fieldName) {
            case 'group':
            case 'group_desc':
                if (!QuestionGroup::model()->exists('gid = :gid AND sid = :sid', [':gid' => (int) $qidOrgid, ':sid' => $this->survey->sid])) {
                    return 0;
                }
                $oGroupL10n = QuestionGroupL10n::model()->find('gid = :gid AND language = :language', [':gid' => (int) $qidOrgid, ':language' => $tolang]);
                if ($fieldName == 'group') {
                    return $this->saveTranslatedAttribute($oGroupL10n, 'group_name', mb_substr((string) $new, 0, 100));
                }
                return $this->saveTranslatedAttribute($oGroupL10n, 'description', $new);
            case 'question':
            case 'question_help':
            case 'subquestion':
                if (!Question::model()->exists('qid = :qid AND sid = :sid', [':qid' => (int) $qidOrgid, ':sid' => $this->survey->sid])) {
                    return 0;
                }
                $oQuestionL10n = QuestionL10n::model()->find('qid = :qid AND language = :language', [':qid' => (int) $qidOrgid, ':language' => $tolang]);
                return $this->saveTranslatedAttribute($oQuestionL10n, $fieldName == 'question_help' ? 'help' : 'question', $new);
            case 'answer':
                if (!Question::model()->exists('qid = :qid AND sid = :sid', [':qid' => (int) $qidOrgid, ':sid' => $this->survey->sid])) {
                    return 0;
                }
                $oAnswer = Answer::model()->find('qid = :qid and code = :code and scale_id = :scale_id', array(':qid' => $qidOrgid, ':code' => $answerCode, ':scale_id' => $iScaleID));
                if (empty($oAnswer)) {
                    return 0;
                }
                $oAnswerL10n = AnswerL10n::model()->find('aid = :aid AND language = :language', [':aid' => $oAnswer->aid, ':language' => $tolang]);
                return $this->saveTranslatedAttribute($oAnswerL10n, 'answer', $new);
            default:
                return null;
        }
    }

    /**
     * Validates and saves a single translated attribute of a model.
     *
     * Only the rules of this attribute are validated (e.g. the XSS filter),
     * and only this attribute is saved.
     *
     * @param \LSActiveRecord|null $model the language setting or L10n model, null if not found
     * @param string $attribute the attribute to update
     * @param string $value the new value
     * @return int number of updated rows
     */
    private function saveTranslatedAttribute($model, $attribute, $value)
    {
        if (empty($model)) {
            return 0;
        }
        $model->$attribute = $value;
        if (!$model->validate([$attribute])) {
            return 0;
        }
        return $model->save(false, [$attribute]) ? 1 : 0;
    }

    /**
     * Creates a customised array with database information for use by survey translation.
     * This array structure is the base for the whole algorithm.
     * Each returned array consists of the following information
     *
     *  type -->  this seems to be the db table in types (e.g. 1 --> surveys_languagesettings etc.)
     *  dbColumn  -->  the name of the db column where to find the
     *  id1  -->
     *  id2  -->
     *  gid  -->
     *  qid  -->
     *  description -->  the tab title
     *  HTMLeditorType -->  parameter for CKEditor
     *  HTMLeditorDisplay --> inline, modal for CKEditor to load it
     *  associated --> the associated field for the current one. If empty string this one has no associated field
     *
     * @param string $type Type of database field that is being translated, e.g. title, question, etc.
     * @return array
     */
    public function setupTranslateFields($type)
    {
        $data = self::$translateFields[$type];
        // NB: Can't put descriptions in static array since it contains translations.
        $descriptions = [
            'title'                             => gT("Survey title and description"), //this is the tab title
            'description'                       => gT("Description:"),
            'welcome'                           => gT("Welcome and end text"),
            'end'                               => gT("End message:"),
            'emailinvite'                       => gT("Invitation email subject"),
            'emailinvitebody'                   => gT("Invitation email"),
            'emailreminder'                     => gT("Reminder email subject"),
            'emailreminderbody'                 => gT("Reminder email"),
            'emailconfirmation'                 => gT("Confirmation email subject"),
            'emailconfirmationbody'             => gT("Confirmation email"),
            'emailregistration'                 => gT("Registration email subject"),
            'emailregistrationbody'             => gT("Registration email"),
            'emailbasicadminnotification'       => gT("Basic admin notification subject"),
            'emailbasicadminnotificationbody'   => gT("Basic admin notification"),
            'emaildetailedadminnotification'    => gT("Detailed admin notification subject"),
            'emaildetailedadminnotificationbody' => gT("Detailed admin notification"),
            'group'                             => gT("Question groups"),
            'group_desc'                        => gT("Group description"),
            'question'                          => gT("Questions"),
            'question_help'                     => gT("Question help"),
            'subquestion'                       => gT("Subquestions"),
            'answer'                            => gT("Answer options")
        ];
        $data['description'] = $descriptions[$type];
        return $data;
    }

    /**
     * Get all single array elements from setupTranslateFields() in one array
     * which are in getTabNames().
     *
     * @return array
     */
    public function getAllTranslateFields()
    {
        return array_map([$this, 'setupTranslateFields'], $this->getTabNames());
    }

    /**
     * Returns all tab names.
     *
     * @return string[]
     */
    public function getTabNames()
    {
        return [
            "title",
            "welcome",
            "group",
            "question",
            "subquestion",
            "answer",
            "emailinvite",
            "emailreminder",
            "emailconfirmation",
            "emailregistration",
            "emailbasicadminnotification",
            "emaildetailedadminnotification"
        ];
    }

    /**
     * Returns the survey object
     *
     * @return Survey
     */
    public function getSurvey()
    {
        return $this->survey;
    }

    /**
     * Replaces every expression ({...}) in a text with a numbered marker that machine translation
     * leaves alone, so the text can be translated in one piece with its HTML tags intact.
     *
     * @param string $text Text to translate, may contain HTML and expressions
     * @return array{text: string, expressions: string[], hasText: bool} The text with markers,
     *         the expressions indexed by marker number, and whether there is any text to translate
     */
    public static function protectExpressions(string $text): array
    {
        $protectedText = '';
        $expressions = [];
        $hasText = false;
        foreach (\LimeExpressionManager::SplitStringOnExpressions($text) as $part) {
            if ($part[2] === 'EXPRESSION') {
                $protectedText .= '<span class="notranslate" translate="no">'
                    . self::EXPRESSION_MARKER . count($expressions) . '</span>';
                $expressions[] = $part[0];
            } else {
                $protectedText .= $part[0];
                $hasText = $hasText || trim(strip_tags($part[0])) !== '';
            }
        }
        return ['text' => $protectedText, 'expressions' => $expressions, 'hasText' => $hasText];
    }

    /**
     * Puts the expressions back in place of the markers set by protectExpressions().
     *
     * @param string $text Translated text that contains the markers
     * @param string[] $expressions The expressions as returned by protectExpressions()
     * @return string|null The text with its expressions, or null if the translation lost one of them
     */
    public static function restoreExpressions(string $text, array $expressions): ?string
    {
        $restored = [];
        $replaceMarker = function (array $matches) use ($expressions, &$restored) {
            $index = (int)$matches[1];
            if (!isset($expressions[$index])) {
                return $matches[0];
            }
            $restored[$index] = true;
            return $expressions[$index];
        };
        // Markers with their wrapper first, then any marker whose wrapper the translation dropped
        $text = preg_replace_callback(
            '#<span\b[^>]*>\s*' . self::EXPRESSION_MARKER . '(\d+)\s*</span>#i',
            $replaceMarker,
            $text
        );
        $text = preg_replace_callback('#' . self::EXPRESSION_MARKER . '(\d+)#', $replaceMarker, (string)$text);
        return count($restored) === count($expressions) ? $text : null;
    }
}
