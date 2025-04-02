<?php

/**
 * Creates the breadcrumbs for the topbar
 * If the text legth of the full breadcrumbs are higher than the configured threshold,
 * some elements will be replaced with an ellipsis
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class BreadcrumbWidget extends CWidget
{
    /**
     * @const int the allowed maximal number of characters the breadcrumb should display
     */
    private const THRESHOLD = 44;

    /**
     * @var array containing different objects and strings needed for the building of the breadcrumbs
     */
    public $breadCrumbConfigArray;

    /** @var array html options */
    public $htmlOptions = [];

    /**
     * @return void
     * @throws CException
     */
    public function run()
    {
        $this->render('breadcrumb', [
            'breadcrumbs' => $this->getBreadcrumbsArray($this->breadCrumbConfigArray),
            'extraClass' => $this->breadCrumbConfigArray['extraClass'] ?? '',
            'htmlOptions' => $this->htmlOptions,
        ]);
    }

    /**
     * builds the whole breadcrumb array for the view file
     * @param array $breadcrumbConfigArray
     * @return array
     */
    public function getBreadcrumbsArray(array $breadcrumbConfigArray)
    {
        $breadcrumbs = [];
        // First create the basis with a surveylink if set
        $breadcrumbs = $this->buildSurveyBreadCrumbs($breadcrumbConfigArray, $breadcrumbs);
        // If we are in a questiongroup view render the breadcrumb with question group
        $breadcrumbs = $this->buildQuestionGroupBreadCrumbs($breadcrumbConfigArray, $breadcrumbs);
        // If we are in a question view render the breadcrumb with the question
        $breadcrumbs = $this->buildQuestionBreadCrumbs($breadcrumbConfigArray, $breadcrumbs);
        // If we are in a token view render the breadcrumb with the token
        $breadcrumbs = $this->buildTokenBreadCrumbs($breadcrumbConfigArray, $breadcrumbs);
        // If we are in a moduleSubAction view render the breadcrumb with the moduleSubAction
        $breadcrumbs = $this->buildModuleSubActionBreadCrumbs($breadcrumbConfigArray, $breadcrumbs);
        // After building of the full breadcrumb array we want to preserve the full texts for the tooltip
        // before they potentially get shortened
        $breadcrumbs = $this->preserveOriginalBreadcrumbTexts($breadcrumbs);

        return $breadcrumbs;
    }

    /**
     * Returns the breadcrumb array after adding breadcrumb elements for survey
     * @param array $breadcrumbConfigArray
     * @param array $breadcrumbs
     * @return array
     */
    private function buildSurveyBreadCrumbs(array $breadcrumbConfigArray, array $breadcrumbs)
    {
        $survey = $breadcrumbConfigArray['oSurvey'];
        $questionGroup = $breadcrumbConfigArray['oQuestionGroup'];
        $subAction = $breadcrumbConfigArray['sSubaction'];
        $question = $breadcrumbConfigArray['oQuestion'];
        $mode = $breadcrumbConfigArray['mode'];
        /*$breadcrumbs[] =
            [
                'id' => 'breadcrumb__surveylist--link',
                'href' => App()->createUrl('surveyAdministration/listsurveys'),
                'text' => gT('Surveys')
            ];*/
        if (isset($survey)) {
            $surveyTitle = flattenText($survey->defaultlanguage->surveyls_title);
            if ($mode == 'long' || (!isset($questionGroup) && !isset($question))) {
                $surveyTitle .= ' (' . $survey->sid . ')';
            }
            if (empty($surveyTitle)) {
                $surveyTitle = "&nbsp;";
            }
            $breadcrumbs[] =
                [
                    'id' => 'breadcrumb__survey--overview',
                    'href' => App()->createUrl('/surveyAdministration/view/', ['iSurveyID' => $survey->sid]),
                    'text' => $surveyTitle,
                    'title' => gT('Survey overview'),
                ];
            if (isset($subAction) && !isset($questionGroup) && !isset($question)) {
                $breadcrumbs[] =
                    [
                        'text' => gT($subAction),
                    ];
            }
        }
        return $breadcrumbs;
    }

    /**
     * Returns the breadcrumb array after potentially adding breadcrumb elements for question group
     * @param array $breadcrumbConfigArray
     * @param array $breadcrumbs
     * @return array
     */
    private function buildQuestionGroupBreadCrumbs(array $breadcrumbConfigArray, array $breadcrumbs)
    {
        $survey = $breadcrumbConfigArray['oSurvey'];
        $questionGroup = $breadcrumbConfigArray['oQuestionGroup'];
        $subAction = $breadcrumbConfigArray['sSubaction'];
        $question = $breadcrumbConfigArray['oQuestion'];
        $mode = $breadcrumbConfigArray['mode'];
        if (isset($questionGroup)) {
            $groupTitle = flattenText($questionGroup->questiongroupl10ns[$survey->language]->group_name);
            if ($mode == 'long') {
                $groupTitle .= ' (' . $questionGroup->gid . ')';
            }
            if (empty($groupTitle)) {
                $groupTitle = "&nbsp;";
            }
            // If the questiongroup view is active right now, don't link it?
            if (!$subAction && !isset($question)) {
                $breadcrumbs[] = [
                    'text' => $questionGroup->isNewRecord ? gT('New question group') : $groupTitle,
                ];
            } else {
                $breadcrumbs[] =
                    [
                        'id' => 'breadcrumb__group--detail',
                        'href' => App()->createUrl(
                            'questionGroupsAdministration/view/',
                            ['surveyid' => $questionGroup->sid, 'gid' => $questionGroup->gid]
                        ),
                        'text' => $groupTitle,
                        'title' => gT('Group summary'),
                    ];
                if (isset($subAction) && !isset($question)) {
                    $breadcrumbs[] =
                        [
                            'text' => $subAction,
                        ];
                }
            }
        }
        return $breadcrumbs;
    }

    /**
     * Returns the breadcrumb array after potentially adding breadcrumb elements for question
     * @param array $breadcrumbConfigArray
     * @param array $breadcrumbs
     * @return array
     */
    private function buildQuestionBreadCrumbs(array $breadcrumbConfigArray, array $breadcrumbs)
    {
        $subAction = $breadcrumbConfigArray['sSubaction'];
        $question = $breadcrumbConfigArray['oQuestion'];
        $survey = $breadcrumbConfigArray['oSurvey'];
        $mode = $breadcrumbConfigArray['mode'];
        if (isset($question)) {
            $questionTitle = $question->title . ' - ' . flattenText($question->questionl10ns[$survey->language]->question);
            if ($mode == 'long') {
                $questionTitle .= ' (' . $question->qid . ')';
            }
            if (empty($questionTitle)) {
                $questionTitle = "&nbsp;";
            }
            // If the question view is active right now, don't link it
            if (!isset($subAction)) {
                $breadcrumbs[] =
                    [
                        'text' => $questionTitle,
                    ];
            } else {
                $breadcrumbs[] =
                    [
                        'id' => 'breadcrumb__question--detail',
                        'href' => App()->createUrl(
                            'questionAdministration/view/',
                            ['surveyid' => $question->sid, 'gid' => $question->gid, 'qid' => $question->qid]
                        ),
                        'text' => $questionTitle,
                        'title' => gT('Question summary'),
                    ];
                $breadcrumbs[] =
                    [
                        'text' => $subAction
                    ];
            }
        }
        return $breadcrumbs;
    }

    /**
     * Returns the breadcrumb array after potentially adding breadcrumb elements for token
     * @param array $breadcrumbConfigArray
     * @param array $breadcrumbs
     * @return array
     */
    private function buildTokenBreadCrumbs(array $breadcrumbConfigArray, array $breadcrumbs)
    {
        $survey = $breadcrumbConfigArray['oSurvey'];
        $token = $breadcrumbConfigArray['token'];
        $active = $breadcrumbConfigArray['active'];
        if (isset($token)) {
            $breadcrumbs[] =
                [
                    'id' => 'breadcrumb__survey--participants',
                    'href' => App()->createUrl('admin/tokens/sa/index/', ['surveyid' => $survey->sid]),
                    'text' => gT('Survey participants'),
                ];
            $breadcrumbs[] =
                [
                    'text' => gT($active),
                ];
        }
        return $breadcrumbs;
    }

    /**
     * Returns the breadcrumb array after potentially adding breadcrumb elements for module sub action
     * @param array $breadcrumbConfigArray
     * @param array $breadcrumbs
     * @return array
     */
    private function buildModuleSubActionBreadCrumbs(array $breadcrumbConfigArray, array $breadcrumbs)
    {
        $moduleSubAction = $breadcrumbConfigArray['module_subaction'];
        $moduleSubActionUrl = $breadcrumbConfigArray['module_subaction_url'];
        $moduleCurrentAction = $breadcrumbConfigArray['module_current_action'];
        $active = $breadcrumbConfigArray['active'];
        if (isset($moduleSubAction)) {
            $breadcrumbs[] =
                [
                    'id' => 'breadcrumb__module--subaction',
                    'href' => $moduleSubActionUrl,
                    'text' => $moduleSubAction,
                ];

            if (isset($moduleCurrentAction)) {
                $breadcrumbs[] =
                    [
                        'text' => gT($active),
                    ];
            }
        }
        return $breadcrumbs;
    }

    /**
     * copies full text of each breadcrumb into separate element 'fullText' for the tooltip
     * @param array $breadcrumbs
     * @return array
     */
    private function preserveOriginalBreadcrumbTexts(array $breadcrumbs)
    {
        foreach ($breadcrumbs as $i => $breadcrumbArray) {
            //
            $breadcrumbs[$i]['fullText'] = $breadcrumbArray['text'];
        }
        return $breadcrumbs;
    }
}
