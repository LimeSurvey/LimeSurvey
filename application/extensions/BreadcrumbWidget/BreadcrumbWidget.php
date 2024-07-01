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
        // check length of whole breadcrumb
        $countChars = $this->getLengthOfBreadcrumb($breadcrumbs);

        // maxlength should be not longer than configured chars
        if ($countChars > self::THRESHOLD) {
            $breadcrumbs = $this->reduceBreadcrumbStringLength($breadcrumbs, $countChars);
        }
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
        $breadcrumbs[] =
            [
                'id' => 'breadcrumb__surveylist--link',
                'href' => App()->createUrl('surveyAdministration/listsurveys'),
                'text' => gt('Surveys')
            ];
        if (isset($survey)) {
            if (!isset($questionGroup)) {
                $breadcrumbs[] =
                    [
                        'id' => 'breadcrumb__survey--overview',
                        'href' => App()->createUrl('/surveyAdministration/view/', ['iSurveyID' => $survey->sid]),
                        'text' => flattenText($survey->defaultlanguage->surveyls_title) . ' (' . $survey->sid . ')',
                    ];
            } else {
                $breadcrumbs[] =
                    [
                        'id' => 'breadcrumb__survey--overview',
                        'href' => App()->createUrl('/surveyAdministration/view/', ['iSurveyID' => $survey->sid]),
                        'text' => flattenText($survey->defaultlanguage->surveyls_title),
                    ];
            }
            if (isset($subAction) && !isset($questionGroup) && !isset($question)) {
                $breadcrumbs[] =
                    [
                        'text' => gt($subAction),
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
        if (isset($questionGroup)) {
            // If the questiongroup view is active right now, don't link it?
            if (!$subAction && !isset($question)) {
                $breadcrumbs[] = [
                    'text' => $questionGroup->isNewRecord ? gT('New question group') : flattenText(
                        $questionGroup->questiongroupl10ns[$survey->language]->group_name
                    )
                ];
            } else {
                $breadcrumbs[] =
                    [
                        'id' => 'breadcrumb__group--detail',
                        'href' => App()->createUrl(
                            'questionGroupsAdministration/view/',
                            ['surveyid' => $questionGroup->sid, 'gid' => $questionGroup->gid]
                        ),
                        'text' => flattenText($questionGroup->questiongroupl10ns[$survey->language]->group_name),
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
        if (isset($question)) {
            // If the question view is active right now, don't link it
            if (!isset($subAction)) {
                $breadcrumbs[] =
                    [
                        'text' => $question->title,
                    ];
            } else {
                $breadcrumbs[] =
                    [
                        'id' => 'breadcrumb__question--detail',
                        'href' => App()->createUrl(
                            'questionAdministration/view/',
                            ['surveyid' => $question->sid, 'gid' => $question->gid, 'qid' => $question->qid]
                        ),
                        'text' => $question->title,
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

    /**
     * returns the number of chars of the whole breadcrumb text
     * @param array $breadcrumbs
     * @return int
     */
    private function getLengthOfBreadcrumb(array $breadcrumbs)
    {
        $countChars = 0;
        foreach ($breadcrumbs as $breadcrumbArray) {
            // counts the number of chars
            $countChars += strlen((string) $breadcrumbArray['text']) + 1;
        }
        return $countChars;
    }

    /**
     * Replaces 2nd and maybe 3rd element string with ellipsis to fit the maximum characters allowed for the whole breadcrumb
     * @param array $breadcrumbs
     * @param int $countChars number of characters of the whole breadcrumbs text
     * @return mixed
     */
    private function reduceBreadcrumbStringLength(array $breadcrumbs, int $countChars)
    {
        // keep the first breadcrumb full, only touch 2nd and maybe 3rd entry
        $charsTooMuch = $countChars - self::THRESHOLD;
        $charsOf2nd = array_key_exists(1, $breadcrumbs) ? strlen((string) $breadcrumbs[1]['text']) : 0;
        $charsOf3rd = array_key_exists(2, $breadcrumbs) ? strlen((string) $breadcrumbs[2]['text']) : 0;
        $secondIsLastElement = count($breadcrumbs) === 2;
        $thirdIsLastElement = count($breadcrumbs) === 3;
        if ($charsOf2nd > $charsTooMuch) {
            // Replace whole 2nd element with ellipsis
            if ($secondIsLastElement) {
                // if it's the löast element, only cut it
                $breadcrumbs = $this->replaceCharsWithEllipsis($breadcrumbs, 1, $charsOf2nd - $charsTooMuch - 3);
            } else {
                $breadcrumbs = $this->replaceCharsWithEllipsis($breadcrumbs, 1);
            }
        } elseif ($charsOf3rd > $charsTooMuch && !$thirdIsLastElement) {
            // Replace whole 3rd element with ellipsis
            $breadcrumbs = $this->replaceCharsWithEllipsis($breadcrumbs, 2);
        } else {
            // Fallback: then we're just replacing those elements possible with ellipsis and hope for the best
            $breadcrumbs = $this->replaceCharsWithEllipsis($breadcrumbs, 1);
            if (!$thirdIsLastElement) {
                $breadcrumbs = $this->replaceCharsWithEllipsis($breadcrumbs, 2);
            }
        }
        return $breadcrumbs;
    }

    /**
     * Returns the breadcrumbs array after replacing the/some text of a element with ellipsis
     * As of now the partially replacement of a string is not used.
     * @param array $breadcrumbs the full breadcrumbs array
     * @param int $index location of the element which needs to be fixed in the array
     * @param int $location number of chars after the ellipsis should replace the remaining text. 0 means fully replace
     * @return array
     */
    private function replaceCharsWithEllipsis(array $breadcrumbs, int $index, int $location = 0)
    {
        $location = $location <= 3 ? 0 : $location;
        $ellipsis = "<i class='ri-more-fill'></i>";
        $breadcrumbs[$index]['text'] = ellipsize($breadcrumbs[$index]['text'], $location, 1, $ellipsis);

        return $breadcrumbs;
    }
}
