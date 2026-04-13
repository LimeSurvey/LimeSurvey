import { LANGUAGE_CODES } from 'helpers/constants'
import { mockQuestion } from './mockQuestion'

export function mockQuestionType(questionTypeInfo, attrs = {}) {
  const question = mockQuestion(questionTypeInfo, attrs)
  question.l10ns = {
    en: {
      id: 1,
      language: LANGUAGE_CODES.EN,
      qid: 1,
      question: questionTypeInfo.title,
      script: '',
    },
  }
  question.questionThemeName = questionTypeInfo.theme
  question.type = questionTypeInfo.type

  return question
}
