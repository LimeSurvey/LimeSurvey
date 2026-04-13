import { singleChoiceThemes } from 'components/QuestionTypes'

export const isSingleChoiceQuestion = (questionThemeName) => {
  return singleChoiceThemes.includes(questionThemeName)
}
