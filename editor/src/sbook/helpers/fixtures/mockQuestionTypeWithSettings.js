// Modify the mockQuestionType function to include surveySettings
import { mockQuestionType } from './mockQuestionType'

export function mockQuestionTypeWithSettings(questionTypeInfo, attrs = {}) {
  const question = mockQuestionType(questionTypeInfo, attrs)
  question.surveySettings = {
    showNoAnswer: true, // or false, depending on your needs
  }
  return question
}
