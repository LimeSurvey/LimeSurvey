import { getQuestionTypeInfo } from '../QuestionTypes'
import {
  TableIcon,
  QuestionInserterRankIcon,
  QuestionInserterDataIcon,
  QuestionInserterTextIcon,
  QuestionInserterNumberIcon,
  QuestionInserterSingleChoiceIcon,
  QuestionInserterMultipleChoiceIcon,
} from 'components/icons'

/** Icons a category of react/questiontypes.xml can name */
const CATEGORY_ICONS = {
  TableIcon,
  QuestionInserterRankIcon,
  QuestionInserterDataIcon,
  QuestionInserterTextIcon,
  QuestionInserterNumberIcon,
  QuestionInserterSingleChoiceIcon,
  QuestionInserterMultipleChoiceIcon,
}

export const getQuestionGroupItem = () => ({
  value: getQuestionTypeInfo().QUESTION_GROUP.type,
  label: getQuestionTypeInfo().QUESTION_GROUP.title,
  theme: getQuestionTypeInfo().QUESTION_GROUP.theme,
})

/**
 * Returns the question types grouped by category for the question type selector.
 * Only themes the editor can render are offered, and not the ones an administrator hid.
 * @param {{name: string, title: string, icon: string}[]} categories Categories from useQuestionTypes
 * @param {Object<string, Object>} questionTypes Question types from useQuestionTypes, keyed by theme name
 * @returns {{title: string, icon: ?JSX.Element, items: {value: string, label: string, theme: string, hidden: boolean}[]}[]}
 */
export const getQuestionItemsList = (categories = [], questionTypes = {}) => {
  const renderableThemes = new Set(
    Object.values(getQuestionTypeInfo())
      .filter((info) => info.isQuestionType !== false)
      .map((info) => info.theme)
  )
  const offeredTypes = Object.values(questionTypes).filter(
    (questionType) =>
      questionType.category &&
      questionType.visible !== false &&
      renderableThemes.has(questionType.name)
  )

  return categories.map((category) => {
    const Icon = CATEGORY_ICONS[category.icon]

    return {
      title: category.title,
      icon: Icon ? <Icon /> : null,
      items: offeredTypes
        .filter((questionType) => questionType.category === category.name)
        .sort((a, b) => a.sortOrder - b.sortOrder)
        .map((questionType) => ({
          value: questionType.type,
          label: questionType.title,
          theme: questionType.name,
          hidden: !!questionType.devOnly && !process.env.REACT_APP_DEV_MODE,
        })),
    }
  })
}
