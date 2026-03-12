import React from 'react'
import { useParams } from 'react-router-dom'

import { STATES } from 'helpers'
import {
  useAppState,
  useAuth,
  useElementClick,
  useFocused,
  useSurvey,
} from 'hooks'
import {
  getCurrentLayout,
  getSurveyPanels,
  LAYOUT_TYPES,
} from 'helpers/options'
import { Survey } from 'components'
import { SurveySettings } from 'components/SurveySettings/SurveySettings'

import { LeftSideBar } from './LeftSideBar/LeftSideBar'
import { i18nInstance } from '../../i18nInit'
import { I18Provider } from '../../providers'
import { RightSideBar } from './RightSideBar/RightSideBar'

const Layout = () => {
  const { surveyId, panel } = useParams()
  const { survey = {} } = useSurvey(surveyId)
  const auth = useAuth()
  const { unFocus } = useFocused()

  const [activeLanguage = undefined] = useAppState(
    STATES.ACTIVE_LANGUAGE,
    survey.language
  )
  const [allLanguages, setAllLanguages] = useAppState(
    STATES.ALL_AVAILABLE_LANGUAGES
  )

  const [isAddingQuestionOrGroup, setIsAddingQuestionOrGroup] = useAppState(
    STATES.IS_ADDING_QUESTION_OR_GROUP,
    false
  )

  const handleClickInside = () => {
    unFocus()
  }

  const ref = useElementClick(handleClickInside, false)

  const mainSurveyComponent = () => {
    return (
      <I18Provider
        language={activeLanguage}
        i18n={() =>
          i18nInstance(
            activeLanguage,
            auth,
            setAllLanguages,
            allLanguages,
            true
          )
        }
      >
        <Survey id={surveyId} />
      </I18Provider>
    )
  }

  const renderMainContent = () => {
    const layout = getCurrentLayout(panel)

    switch (layout) {
      case LAYOUT_TYPES.Settings:
        return <SurveySettings id={surveyId} />
      case LAYOUT_TYPES.Survey:
        return mainSurveyComponent()
      default:
        return mainSurveyComponent()
    }
  }

  return (
    <>
      <LeftSideBar surveyId={surveyId} />
      <div className="main-body position-relative right-side-margin">
        <div className="survey-part">{renderMainContent()}</div>
        {(!panel || panel === getSurveyPanels().structure.panel) && (
          <div className="inner-wrap" ref={ref} />
        )}
        {isAddingQuestionOrGroup && (
          <div
            className="adding-question-group-wrapper"
            onClick={() => setIsAddingQuestionOrGroup(false)}
          />
        )}
      </div>
      <RightSideBar surveyId={surveyId} />
    </>
  )
}

export default Layout
