import { useMemo } from 'react'
import classNames from 'classnames'
import Col from 'react-bootstrap/Col'

import { useAppState, useSurvey } from 'hooks'
import { STATES } from 'helpers'

import { SurveyHeader } from './SurveyHeader'
import { SurveyFooter } from './SurveyFooter'
import { SurveyBody } from './SurveyBody'
import { SurveyLanguageSwitch } from './SurveyLanguageSwitch'

export const Survey = ({ id }) => {
  const [activeLanguage] = useAppState(STATES.ACTIVE_LANGUAGE)
  const [userDetails] = useAppState(STATES.USER_DETAIL)
  const [numberOfQuestions] = useAppState(STATES.NUMBER_OF_QUESTIONS, 0)
  const { survey = {}, update } = useSurvey(id)
  const [allLanguages] = useAppState(STATES.ALL_AVAILABLE_LANGUAGES)
  const surveySettings = useMemo(() => {
    // we could pass all needed surveysettings down the tree from here so far only showNoAnswer is used here
    return {
      showNoAnswer: survey.showNoAnswer,
      languages: survey.languages,
    }
  }, [survey.showNoAnswer])

  const surveyHasAdditionalLanguages = () => {
    // here we check if lang switch is even possible
    return survey.additionalLanguages?.trim().length !== 0
  }

  return (
    <Col
      className={classNames(
        'd-flex',
        'mx-4',
        'flex-column',
        'justify-content-center',
        'survey'
      )}
      id="survey-col"
    >
      {surveyHasAdditionalLanguages() && (
        <SurveyLanguageSwitch
          survey={survey}
          allLanguages={allLanguages[userDetails.lang]}
        />
      )}
      <SurveyHeader
        update={(updated) => update(updated)}
        numberOfQuestions={numberOfQuestions}
        activeLanguage={activeLanguage}
        survey={survey}
        allLanguages={allLanguages}
      />
      <SurveyBody
        language={activeLanguage}
        update={update}
        questionGroups={survey.questionGroups}
        surveyId={survey.sid}
        surveySettings={surveySettings}
      />
      <SurveyFooter
        language={activeLanguage}
        survey={survey}
        update={(languageSettings) => update(languageSettings)}
      />
    </Col>
  )
}
