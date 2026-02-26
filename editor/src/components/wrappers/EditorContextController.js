import React, { useEffect, useState } from 'react'
import { useLocation, useParams } from 'react-router-dom'

import { getQuestionTypeInfo } from 'components/QuestionTypes'
import { SiteSettingsService } from 'services'
import {
  useAppState,
  useFocused,
  useSurvey,
  useSurveyGroupsService,
  useSetAllLanguages,
  useTranslationsService,
} from 'hooks'
import {
  EntitiesType,
  STATES,
  getApiUrl,
  getGroupById,
  getQuestionById,
} from 'helpers'

import { identifyAndReportMissingTranslations } from '../../i18n/scripts/identifyAndReportMissingTranslations'

export const EditorContextController = ({ children }) => {
  const { surveyId } = useParams()
  const location = useLocation()

  const { survey = {}, update } = useSurvey(surveyId)
  const { unFocus, setFocused } = useFocused()
  const { fetchAllLanguages } = useSetAllLanguages()
  const { surveyGroupsService } = useSurveyGroupsService()
  const { translationsService } = useTranslationsService()

  const [hasNavigated, setHasNavigated] = useState(false)

  const [, setAttributeDescriptions] = useAppState(
    STATES.ATTRIBUTE_DESCRIPTIONS,
    {}
  )
  const [, setIsSurveyActive] = useAppState(STATES.IS_SURVEY_ACTIVE, false)
  const [, setCodeToQuestion] = useAppState(STATES.CODE_TO_QUESTION, {})
  const [, setSaveStatue] = useAppState(STATES.SAVE_STATUS, {})
  const [, setNumberOfQuestions] = useAppState(STATES.NUMBER_OF_QUESTIONS, 0)
  const [, setSiteSettings] = useAppState(STATES.SITE_SETTINGS, {})
  const [, setSurveyGroups] = useAppState(STATES.SURVEY_GROUPS, {})
  const [, setActiveLanguage] = useAppState(
    STATES.ACTIVE_LANGUAGE,
    survey.language
  )
  const [hasSurveyUpdatePermission, setHasSurveyUpdatePermission] = useAppState(
    STATES.HAS_SURVEY_UPDATE_PERMISSION
  )
  const [userDetails] = useAppState(STATES.USER_DETAIL)

  // Survey Metadata & State Calculation
  useEffect(() => {
    setSaveStatue('')
    setAttributeDescriptions(survey?.attributeDescriptions || {})
    if (survey.sid) fetchAllLanguages(survey.languages)
  }, [survey.sid])

  useEffect(() => {
    setIsSurveyActive(survey?.active)
  }, [survey.sid, survey?.active])

  useEffect(() => {
    if (!survey?.questionGroups) {
      return
    }
    const codeToQuestion = {}
    let questionNumber = 1
    for (const questionGroup of survey.questionGroups) {
      for (const question of questionGroup.questions) {
        if (question.title) {
          codeToQuestion[question.title] = {
            question: { ...question, questionNumber },
          }
        }

        questionNumber++
      }
    }
    setCodeToQuestion(codeToQuestion)
  }, [survey.questionGroups, survey.sid])

  useEffect(() => {
    let numberOfQuestions = 0
    if (!survey?.questionGroups || !survey?.showXQuestions) {
      return
    }
    for (const questionGroup of survey.questionGroups) {
      numberOfQuestions += questionGroup.questions.length
    }
    setNumberOfQuestions(numberOfQuestions)
  }, [survey.questionGroups])

  // New Survey Initialization
  useEffect(() => {
    if (!survey.questionGroups || !survey.newSurvey) {
      return
    }
    if (survey?.questionGroups[0]?.questions?.length === 1) {
      setFocused(survey?.questionGroups[0].questions[0], 0, 0)
      update({ newSurvey: false })
    }
  }, [survey.newSurvey])

  // URL Deep Linking & Focus Management
  useEffect(() => {
    // prevent browser auto scroll onPageLoad
    if ('scrollRestoration' in history) {
      history.scrollRestoration = 'manual'
    }

    if (!hasSurveyUpdatePermission || !survey.sid || hasNavigated) {
      return
    }

    setHasNavigated(true)
    unFocus()

    const query = new URLSearchParams(location.search)
    const queryKey = query.keys().next().value
    const queryValue = +query.get(queryKey)

    if (queryKey === EntitiesType.welcomeScreen) {
      setFocused(
        { info: getQuestionTypeInfo().WELCOME_SCREEN },
        undefined,
        undefined,
        false
      )
    } else if (queryKey === EntitiesType.endScreen) {
      setFocused(
        { info: getQuestionTypeInfo().END_SCREEN },
        undefined,
        undefined,
        false
      )
    } else if (queryValue) {
      if (queryKey === EntitiesType.question) {
        const questionInfo = getQuestionById(queryValue, survey)
        setFocused(
          questionInfo.question,
          questionInfo.groupIndex,
          questionInfo.questionIndex,
          false
        )
      } else if (queryKey === EntitiesType.group) {
        const groupInfo = getGroupById(queryValue, survey)
        setFocused(groupInfo.group, groupInfo.index, undefined, false)
      }
    } else {
      unFocus()
    }
  }, [survey.sid])

  // Permissions & Settings
  useEffect(() => {
    setActiveLanguage(survey.language)
    setHasSurveyUpdatePermission(survey.hasSurveyUpdatePermission || true)
  }, [survey.sid, survey.hasSurveyUpdatePermission, survey.language])

  useEffect(() => {
    if (
      process.env.REACT_APP_DEMO_MODE === 'true' ||
      process.env.STORYBOOK_DEV === 'true'
    ) {
      setSiteSettings({ siteName: 'LimeSurvey', timezone: 'UTC' })
      return
    }
    const siteSettingsService = new SiteSettingsService(getApiUrl())
    siteSettingsService.getSiteData().then((result) => {
      if (result) {
        setSiteSettings(result)
      }
    })
    surveyGroupsService.getSurveyGroups().then((result) => {
      if (result?.data) {
        setSurveyGroups(result.data)
      }
    })
  }, [])

  // Translations report logic
  useEffect(() => {
    let missingTranslationTimeOut
    // Check if we're in DEV_MODE
    if (
      process.env.NODE_ENV === 'development' &&
      userDetails &&
      userDetails.lang
    ) {
      // Set a new timer
      missingTranslationTimeOut = setTimeout(async () => {
        await identifyAndReportMissingTranslations(
          translationsService,
          userDetails.lang
        )
      }, 30000) // 30 seconds debounce
    }

    // Cleanup function
    return () => {
      clearTimeout(missingTranslationTimeOut)
    }
  }, [userDetails.lang])

  return <>{children}</>
}
