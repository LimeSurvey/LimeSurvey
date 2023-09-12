import { useEffect, useState } from 'react'
import classNames from 'classnames'
import Col from 'react-bootstrap/Col'

import { useAppState, useSurvey } from 'hooks'

import { SurveyHeader } from './SurveyHeader'
import { SurveyFooter } from './SurveyFooter'
import { SurveyBody } from './SurveyBody'
import {
  AdvancedOptionsSettings,
  GeneralSettings,
  ParticipantsSettings,
  PresentationSettings,
  PrivacyPolicySettings,
  PublicationAccessSettings,
} from 'components/SurveySettings'

const surveySettings = {
  general: GeneralSettings,
  presentation: PresentationSettings,
  privacyPolicy: PrivacyPolicySettings,
  participants: ParticipantsSettings,
  publicationAccess: PublicationAccessSettings,
  advancedOptions: AdvancedOptionsSettings,
}

export const Survey = ({ id }) => {
  const { survey, save, update, language } = useSurvey(id)
  const [clickedQuestionSettings] = useAppState('clickedQuestionSettings', {
    label: 'General',
    value: 'general',
  })

  const [numberOfQuestions, setNumberOfQuestions] = useState(0)
  const [, setIsSurveyActive] = useAppState('isSurveyActive', false)
  const [, setCodeToQuestion] = useAppState('codeToQuestion', {})
  const [settingsPanelOpen] = useAppState('settingsPanelOpen', false)

  useEffect(() => {
    let numberOfQuestions = 0

    if (!survey?.questionGroups || !survey?.showXQuestions) {
      return
    }

    for (const questionGroup of survey.questionGroups) {
      numberOfQuestions += questionGroup.questions.length
    }

    setNumberOfQuestions(numberOfQuestions)
  }, [survey?.questionGroups, survey?.showXQuestions])

  useEffect(() => {
    setIsSurveyActive(survey?.active)
  }, [survey.sid, setIsSurveyActive, survey?.active])

  useEffect(() => {
    if (!survey?.questionGroups) {
      return
    }

    const codeToQuestion = {}
    for (const questionGroup of survey.questionGroups) {
      for (const question of questionGroup.questions) {
        codeToQuestion[question.title] = { question }
      }
    }

    setCodeToQuestion(codeToQuestion)
  }, [setCodeToQuestion, survey.questionGroups, survey.sid])

  if (!survey.sid) {
    return <>Loading...</>
  }

  const SelectedSettings = surveySettings[clickedQuestionSettings?.value]
  if (!SelectedSettings) return <>Loading...</>
  return (
    <Col
      className={classNames(
        'd-flex',
        'mx-4',
        'flex-column',
        'justify-content-center',
        'survey'
      )}
    >
      {settingsPanelOpen && clickedQuestionSettings?.value ? (
        <SelectedSettings survey={survey} handleUpdate={update} />
      ) : (
        <>
          <SurveyHeader
            update={(updated) => update(updated)}
            numberOfQuestions={numberOfQuestions}
            survey={survey}
          />
          <SurveyBody
            language={language}
            defaultLanguage={survey.defaultLanguage.language}
            update={update}
            questionGroups={survey.questionGroups}
            surveyId={survey.sid}
          />
          <SurveyFooter
            language={language}
            languageSettings={survey.languageSettings}
            update={(languageSettings) => update(languageSettings)}
            isEmpty={!survey.questionGroups?.length}
            save={save}
          />
        </>
      )}
    </Col>
  )
}
