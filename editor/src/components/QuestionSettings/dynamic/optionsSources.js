import { L10ns } from 'helpers'
import { getImageSelectThemes } from 'components/QuestionTypes/imageOptionSettings'

import { getStatisticsAttributes } from '../attributes/getStatisticsAttributes'

const isImageChoiceTheme = (question) =>
  getImageSelectThemes().includes(question?.questionThemeName)

// Image select themes show the code: their answer/subquestion text is the image
const mapAnswers = ({ question, language }) => {
  const showCode = isImageChoiceTheme(question)

  return (question?.answers || []).map((answer = {}) => ({
    label: showCode
      ? answer.code
      : L10ns({ prop: 'answer', language, l10ns: answer.l10ns }) || answer.code,
    value: answer.code,
  }))
}

const mapSubquestions = ({ question, language }) => {
  const showCode = isImageChoiceTheme(question)

  return (question?.subquestions || []).map((subquestion = {}) => ({
    label: showCode
      ? subquestion.title
      : L10ns({ prop: 'question', language, l10ns: subquestion.l10ns }) ||
        subquestion.title,
    value: subquestion.title,
  }))
}

export const getAnswerOrSubquestionOptions = (args) =>
  args.question?.answers?.length ? mapAnswers(args) : mapSubquestions(args)

// Referenced from the react/*.xml files via <optionsSource>
export const optionsSources = {
  answersOrSubquestions: getAnswerOrSubquestionOptions,
  answers: mapAnswers,
  subquestions: mapSubquestions,
  // Default + "Don't show" + the charts the question type supports
  statisticsGraphs: (args) =>
    getStatisticsAttributes().SHOW_IN_STATISTICS.getOptions(args),
}
