import React, { useCallback, useEffect, useRef } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import Container from 'react-bootstrap/Container'
import { useTranslation } from 'react-i18next'

import { useAppState, useSurvey } from 'hooks'
import {
  STATES,
  Toast,
  isSurveyExpired,
  SURVEY_MENU_TITLES,
  PAGES,
} from 'helpers'
import {
  getSurveyPauseToastOptions,
  getSurveyDatabaseToastOptions,
} from 'components'
import ThemeOptionsPreview from 'components/ThemeOptions/ThemeOptionsPreview'

import Layout from './Layout'
import { EditorTutorial } from './EditorTutorial'
import { LoadingIndicator } from './LoadingIndicator'
import { PluginSlot } from 'plugins/PluginSlot'
import { PLUGIN_SLOTS } from 'plugins/slots'

export const Editor = () => {
  const { surveyId, panel, menu } = useParams()
  const navigate = useNavigate()
  const { survey = {} } = useSurvey(surveyId)
  const { ready } = useTranslation()
  const [, setTopbarConfig] = useAppState(STATES.TOPBAR_CONFIG, {})
  const [userDetails] = useAppState(STATES.USER_DETAIL, {})
  const [loadedSurveyId] = useAppState(STATES.LOADED_SURVEY_ID, null, {
    meta: { persist: false },
  })

  const surveyActivationHandlerRef = useRef(null)
  const expiryToastRanForSurvey = useRef(null)
  const databaseAlertRanForSurvey = useRef(null)

  const [activeLanguage = undefined] = useAppState(
    STATES.ACTIVE_LANGUAGE,
    survey.language
  )
  const [hasSurveyUpdatePermission] = useAppState(
    STATES.HAS_SURVEY_UPDATE_PERMISSION
  )
  const [allLanguages] = useAppState(STATES.ALL_AVAILABLE_LANGUAGES)

  // Automatic Toast Triggers
  const tryToOpenExpiryToast = useCallback(() => {
    const surveyExpired = isSurveyExpired(survey.expires)

    if (surveyExpired) {
      const PauseToastOptions = getSurveyPauseToastOptions(survey.sid, navigate)
      Toast(PauseToastOptions)
    }
  }, [survey.expires, survey.sid])

  const isSuperAdmin = userDetails?.isSuperAdmin

  const tryToOpenSurveyDatabaseAlert = useCallback(() => {
    const shouldShowSurveyDatabaseAlert =
      survey.active && !survey.hasResponsesTable

    if (shouldShowSurveyDatabaseAlert) {
      Toast(getSurveyDatabaseToastOptions({ isSuperAdmin }))
    }
  }, [survey.active, survey.hasResponsesTable, isSuperAdmin])

  useEffect(() => {
    if (expiryToastRanForSurvey.current === survey.sid) return

    // make sure survey sid is matching new params value ( when switching between surveys )
    if (!survey.sid || surveyId != survey.sid) {
      return
    }

    tryToOpenExpiryToast()
    expiryToastRanForSurvey.current = survey.sid
  }, [survey.sid, surveyId, tryToOpenExpiryToast])

  useEffect(() => {
    if (databaseAlertRanForSurvey.current === survey.sid) return

    // only try to open when fresh survey data is loaded
    const isCurrentSurveyLoaded = Number(loadedSurveyId) === Number(surveyId)

    if (
      !survey.sid ||
      surveyId != survey.sid ||
      !isCurrentSurveyLoaded ||
      isSuperAdmin === undefined
    ) {
      return
    }

    tryToOpenSurveyDatabaseAlert()
    databaseAlertRanForSurvey.current = survey.sid
  }, [
    survey.sid,
    surveyId,
    loadedSurveyId,
    isSuperAdmin,
    tryToOpenSurveyDatabaseAlert,
  ])

  useEffect(() => {
    setTopbarConfig({
      surveyId,
      surveyActivationHandlerRef,
      pageName: PAGES.EDITOR,
      shouldAutoOpenOverview: !panel && !menu,
    })
  }, [surveyId, panel, menu])

  const isLoadingSurvey =
    !survey?.sid ||
    hasSurveyUpdatePermission === undefined ||
    activeLanguage === undefined ||
    activeLanguage === ''

  const isLoadingTranslations =
    !ready || (!allLanguages && !process.env.STORYBOOK_DEV)

  return (
    <>
      <PluginSlot slotName={PLUGIN_SLOTS.EDITOR_TOP} />
      <div id="editor" key={activeLanguage}>
        <EditorTutorial survey={survey} isSurveyActive={survey.active} />
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
    </>
  )
}
