import { getQuestionTypeInfo } from '../components'

export const getDisabledQuestionTypes = () => {
  return [getQuestionTypeInfo().RANKING.theme]
}
