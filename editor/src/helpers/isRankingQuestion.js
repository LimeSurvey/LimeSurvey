import { getQuestionTypeInfo } from 'components/QuestionTypes'

const rankingQuestionThemes = [
  getQuestionTypeInfo().RANKING.theme,
  getQuestionTypeInfo().RANKING_ADVANCED.theme,
]

export const isRankingQuestion = (questionThemeName) => {
  return rankingQuestionThemes.includes(questionThemeName)
}
