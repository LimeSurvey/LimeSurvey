import React, { useCallback, useEffect, useRef } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import Container from 'react-bootstrap/Container'
import { useTranslation } from 'react-i18next'

import { useAppState, useSurvey } from 'hooks'
import { STATES, Toast, isSurveyExpired, SURVEY_MENU_TITLES } from 'helpers'
import { getSurveyPauseToastOptions, TopBar } from 'components'
import ThemeOptionsPreview from 'components/ThemeOptions/ThemeOptionsPreview'

import Layout from './Layout'
import { EditorTutorial } from './EditorTutorial'
import { LoadingIndicator } from './LoadingIndicator'

export const Editor = () => {
  const { surveyId, menu } = useParams()
  const navigate = useNavigate()
  const { survey = {} } = useSurvey(surveyId)
  const { ready } = useTranslation()

  const surveyActivationHandlerRef = useRef(null)
  const setShowOverviewModalRef = useRef(null)
  const hasOverviewAutoRan = useRef(false)

  const [activeLanguage = undefined] = useAppState(
    STATES.ACTIVE_LANGUAGE,
    survey.language
  )
  const [hasSurveyUpdatePermission] = useAppState(
    STATES.HAS_SURVEY_UPDATE_PERMISSION
  )
  const [allLanguages] = useAppState(STATES.ALL_AVAILABLE_LANGUAGES)

  // Automatic Toast Triggers
  const tryToOpenOverview = () => {
    if (survey.active && setShowOverviewModalRef.current) {
      setShowOverviewModalRef.current(true)
    }
  }

  const tryToOpenExpiryToast = useCallback(() => {
    const surveyExpired = isSurveyExpired(survey.expires)

    if (surveyExpired) {
      const PauseToastOptions = getSurveyPauseToastOptions(survey.sid, navigate)
      Toast(PauseToastOptions)
    }
  }, [survey.expires, survey.sid])

  useEffect(() => {
    // make sure auto opening occurs only once
    if (hasOverviewAutoRan.current) return

    // make sure survey sid is matching new params value ( when switching between surveys )
    if (!survey.sid || surveyId != survey.sid) {
      return
    }

    // make sure automatic opening occurs only when survey is clicked in dashboard view
    const referrer = document.referrer
    const isDashboardReferrer = referrer.includes('/dashboard/view')
    if (!isDashboardReferrer) {
      return
    }

    tryToOpenOverview()
    tryToOpenExpiryToast()
    hasOverviewAutoRan.current = true
  }, [surveyId, setShowOverviewModalRef.current])

  const isLoadingSurvey =
    !survey?.sid ||
    hasSurveyUpdatePermission === undefined ||
    activeLanguage === undefined ||
    activeLanguage === ''

  const isLoadingTranslations =
    !ready || (!allLanguages && !process.env.STORYBOOK_DEV)

  return (
    <div id="editor" key={activeLanguage}>
      <EditorTutorial survey={survey} isSurveyActive={survey.active} />
      <TopBar
        surveyId={surveyId}
        surveyActivationHandlerRef={surveyActivationHandlerRef}
        setShowOverviewModalRef={setShowOverviewModalRef}
      />
      {isLoadingSurvey || isLoadingTranslations ? (
        <LoadingIndicator isLoadingSurvey={isLoadingSurvey} />
      ) : (
        <Container className="p-0" fluid>
          <div
            id="content"
            data-testid="editor"
            className="d-flex position-relative"
          >
            <Layout />
            <ThemeOptionsPreview
              shouldBeVisible={menu === SURVEY_MENU_TITLES.themeOptions}
            />
          </div>
        </Container>
      )}
    </div>
  )
}
