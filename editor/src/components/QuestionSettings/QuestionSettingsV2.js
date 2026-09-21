import { useQuestionAttributes, useSurvey } from 'hooks'
import { QuestionSettings } from './QuestionSettings'

export const QuestionSettingsv2 = ({ surveyId, focused = {} }) => {
  const { survey, update } = useSurvey(surveyId)

  const {
    attributes: { attributesDetails = {} },
  } = useQuestionAttributes()

  const attributes =
    attributesDetails[focused.questionThemeName]?.attributes || {}

  Object.keys(attributes).map((key) => {
    console.log(key, attributes[key])
  })

  return <QuestionSettings surveyId={surveyId} />
}
