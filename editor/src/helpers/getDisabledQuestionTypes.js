import { getQuestionTypeInfo } from '../components'

export const getDisabledQuestionTypes = () => {
  return [
    getQuestionTypeInfo().EQUATION.theme,
    getQuestionTypeInfo().RANKING.theme,
  ]
}
