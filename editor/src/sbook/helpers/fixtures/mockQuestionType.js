import { LANGUAGE_CODES } from 'helpers/constants'
import questionTypesData from 'helpers/data/question-types.json'
import { mockQuestion } from './mockQuestion'

export function mockQuestionType(questionTypeInfo, attrs = {}) {
  const question = mockQuestion(questionTypeInfo, attrs)
  question.l10ns = {
    en: {
      id: 1,
      language: LANGUAGE_CODES.EN,
      qid: 1,
      // The titles come from the API: Use the ones of a default installation
      question:
        questionTypesData.questionTypes[questionTypeInfo.theme]?.title ??
        questionTypeInfo.title ??
        '',
      script: '',
    },
  }
  question.questionThemeName = questionTypeInfo.theme
  question.type = questionTypeInfo.type

  return question
}
