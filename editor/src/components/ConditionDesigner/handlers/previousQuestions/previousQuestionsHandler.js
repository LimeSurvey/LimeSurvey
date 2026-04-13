import { getPreviousQuestions, NEW_OBJECT_ID_PREFIX } from 'helpers'
import { getQuestionTypeInfo } from 'components/QuestionTypes'

import {
  yesNoUncertainTypeHandler,
  increaseDecreaseTypeHandler,
  arrayTypeHandler,
  genderTypeHandler,
  arrayNumbersTextsTypeHandler,
  arrayDualScaleTypeHandler,
  multipleInputsTypeHandler,
  multipleChoiceTypeHandler,
  rankingTypeHandler,
  yesNoTypeHandler,
  numericTypeHandler,
  dateTimeTypeHandler,
  singleChoiceTypeHandler,
  defaultTypeHandler,
  fivePointChoiceTypeHandler,
  tenPointChoiceTypeHandler,
} from './typeHandlers'

export const previousQuestionsHandler = (
  language,
  survey,
  groupIndex,
  questionIndex
) => {
  let cQuestions = []
  let cAnswers = []

  const questionTypeInfo = getQuestionTypeInfo()
  const previousQuestions = getPreviousQuestions(
    survey,
    groupIndex,
    questionIndex
  )

  // A, B, C and E are question types that are not supported yet.
  const handlers = {
    [questionTypeInfo.FIVE_POINT_CHOICE.type]: fivePointChoiceTypeHandler,
    [questionTypeInfo.TEN_POINT_CHOICE.type]: tenPointChoiceTypeHandler,
    [questionTypeInfo.YES_NO_UNCERTAIN.type]: yesNoUncertainTypeHandler,
    [questionTypeInfo.INCREASE_DECREASE.type]: increaseDecreaseTypeHandler,
    [questionTypeInfo.ARRAY.type]: arrayTypeHandler,
    [questionTypeInfo.ARRAY_COLUMN.type]: arrayTypeHandler,
    [questionTypeInfo.ARRAY_NUMBERS.type]: arrayNumbersTextsTypeHandler,
    [questionTypeInfo.ARRAY_TEXT.type]: arrayNumbersTextsTypeHandler,
    [questionTypeInfo.ARRAY_DUAL_SCALE.type]: arrayDualScaleTypeHandler,
    [questionTypeInfo.MULTIPLE_NUMERICAL_INPUTS.type]:
      multipleInputsTypeHandler,
    [questionTypeInfo.MULTIPLE_SHORT_TEXTS.type]: multipleInputsTypeHandler,
    [questionTypeInfo.MULTIPLE_CHOICE.type]: multipleChoiceTypeHandler,
    [questionTypeInfo.MULTIPLE_CHOICE_WITH_COMMENTS.type]:
      multipleChoiceTypeHandler,
    [questionTypeInfo.RANKING.type]: rankingTypeHandler,
    [questionTypeInfo.YES_NO.type]: yesNoTypeHandler,
    [questionTypeInfo.GENDER.type]: genderTypeHandler,
    [questionTypeInfo.NUMERIC.type]: numericTypeHandler,
    [questionTypeInfo.DATE_TIME.type]: dateTimeTypeHandler,
    [questionTypeInfo.SINGLE_CHOICE_LIST_RADIO.type]: singleChoiceTypeHandler,
    [questionTypeInfo.SINGLE_CHOICE_DROPDOWN.type]: singleChoiceTypeHandler,
    [questionTypeInfo.SINGLE_CHOICE_FIVE_POINT_CHOICE.type]:
      fivePointChoiceTypeHandler,
  }

  for (const question of previousQuestions) {
    if (String(question.qid).toLowerCase().includes(NEW_OBJECT_ID_PREFIX))
      continue

    const handler = handlers[question.type] || defaultTypeHandler
    handler(question, language, cQuestions, cAnswers)
  }

  cQuestions.forEach((question) => {
    question.answers = cAnswers.filter(
      (answer) => answer.cfieldname === question.cfieldname
    )
  })

  return { cQuestions, cAnswers }
}
