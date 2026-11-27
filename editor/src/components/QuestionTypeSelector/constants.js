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
 * Only themes the editor can render (same theme and type) are offered, not the ones an administrator hid,
 * and the dev-only ones only in dev mode.
 * @param {{name: string, title: string, icon: string}[]} categories Categories from useQuestionTypes
 * @param {Object<string, Object>} questionTypes Question types from useQuestionTypes, keyed by theme name
 * @returns {{title: string, icon: ?JSX.Element, items: {value: string, label: string, theme: string}[]}[]}
 */
export const getQuestionItemsList = (categories = [], questionTypes = {}) => {
  // A custom theme can reuse the name of a core theme for another question type
  const renderableTypes = new Set(
    Object.values(getQuestionTypeInfo())
      .filter((info) => info.isQuestionType !== false)
      .map((info) => `${info.type}:${info.theme}`)
  )
  const isDevMode = !!process.env.REACT_APP_DEV_MODE
  const offeredTypes = Object.values(questionTypes).filter(
    (questionType) =>
      questionType.category &&
      questionType.visible !== false &&
      (isDevMode || !questionType.devOnly) &&
      renderableTypes.has(`${questionType.type}:${questionType.name}`)
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
        })),
    }
  })
}
