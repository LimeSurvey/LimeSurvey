import { L10ns } from 'helpers'
import { getQuestionTypeInfo } from 'components/QuestionTypes'

const getImageChoiceThemes = () => [
  getQuestionTypeInfo().SINGLE_CHOICE_IMAGE_SELECT.theme,
  getQuestionTypeInfo().MULTIPLE_CHOICE_IMAGE_SELECT.theme,
]

export const getAnswerOrSubquestionOptions = ({ question, language }) => {
  const answers = question?.answers || []
  const subquestions = question?.subquestions || []
  const isImageChoiceTheme = getImageChoiceThemes().includes(
    question?.questionThemeName
  )

  if (answers.length) {
    return answers.map((answer = {}) => ({
      label: isImageChoiceTheme
        ? answer.code
        : L10ns({
            prop: 'answer',
            language,
            l10ns: answer.l10ns,
          }) || answer.code,
      value: answer.code,
    }))
  }

  return subquestions.map((subquestion = {}) => ({
    label: isImageChoiceTheme
      ? subquestion.title
      : L10ns({
          prop: 'question',
          language,
          l10ns: subquestion.l10ns,
        }) || subquestion.title,
    value: subquestion.title,
  }))
}

const getAnswerOptions = ({ question, language }) =>
  (question?.answers || []).map((answer = {}) => ({
    label:
      L10ns({ prop: 'answer', language, l10ns: answer.l10ns }) || answer.code,
    value: answer.code,
  }))

const getSubquestionOptions = ({ question, language }) =>
  (question?.subquestions || []).map((subquestion = {}) => ({
    label:
      L10ns({ prop: 'question', language, l10ns: subquestion.l10ns }) ||
      subquestion.title,
    value: subquestion.title,
  }))

// Referenced from the react/*.xml files via <optionsSource>
export const optionsSources = {
  answersOrSubquestions: getAnswerOrSubquestionOptions,
  answers: getAnswerOptions,
  subquestions: getSubquestionOptions,
}
