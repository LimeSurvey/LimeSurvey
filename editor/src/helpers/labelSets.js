import { Entities } from './Buffer/Entities'
import {
  getNextAnswerCode,
  getNextSubQuestionCode,
} from './questionAndAnswerCodeGenerators'

// Length of the answers.code and questions.title columns; label codes can have up to 20
const MAX_ANSWER_CODE_LENGTH = 5
const MAX_SUBQUESTION_CODE_LENGTH = 20

const isAnswerEntity = (entityType) => entityType === Entities.answer

/**
 * Turn answer options or subquestions into labels for the label set API.
 *
 * @param {object[]} children Answers or subquestions
 * @param {string} entityType Entities.answer or Entities.subquestion
 * @param {string[]} languages Languages to include
 */
export const childrenToLabels = (children = [], entityType, languages = []) => {
  const isAnswer = isAnswerEntity(entityType)
  const textKey = isAnswer ? 'answer' : 'question'

  return [...children]
    .sort((a, b) => (a.sortOrder ?? 0) - (b.sortOrder ?? 0))
    .map((child) => ({
      code: isAnswer ? child.code : child.title,
      assessmentValue: isAnswer ? Number(child.assessmentValue) || 0 : 0,
      l10ns: languages.reduce((acc, language) => {
        acc[language] = child.l10ns?.[language]?.[textKey] ?? ''
        return acc
      }, {}),
    }))
}

/**
 * Codes for the labels of a label set that is loaded into a question.
 *
 * A label keeps its code unless the code is already used by the question or by an
 * earlier label (compared case-insensitively, like the editor's code validation),
 * or is too long for the entity (answer codes have at most 5 characters).
 * Those labels get the next free code, which never takes a code another label keeps.
 *
 * @param {object[]} labels Labels from the label set API
 * @param {object} question The question the labels are loaded into
 * @param {object[]} existingChildren The question's current answers or subquestions
 * @param {string} entityType Entities.answer or Entities.subquestion
 * @returns {string[]} One code per label, in the order of the labels
 */
export const getLabelSetCodes = (
  labels = [],
  question = {},
  existingChildren = [],
  entityType
) => {
  const isAnswer = isAnswerEntity(entityType)
  const codeKey = isAnswer ? 'code' : 'title'
  const maxLength = isAnswer
    ? MAX_ANSWER_CODE_LENGTH
    : MAX_SUBQUESTION_CODE_LENGTH

  const usedCodes = existingChildren
    .map((child) => `${child[codeKey] ?? ''}`)
    .filter(Boolean)
  const usedKeys = new Set(usedCodes.map((code) => code.toUpperCase()))
  const useCode = (code) => {
    usedCodes.push(code)
    usedKeys.add(code.toUpperCase())
    return code
  }

  // Reserve the codes that are kept first, so a generated code can't take one
  const keptCodes = labels.map((label) => {
    const code = `${label.code ?? ''}`
    if (!code || code.length > maxLength || usedKeys.has(code.toUpperCase())) {
      return null
    }
    return useCode(code)
  })

  return keptCodes.map((code) => {
    if (code) {
      return code
    }
    return useCode(
      isAnswer
        ? getNextAnswerCode({
            ...question,
            answers: usedCodes.map((usedCode) => ({ code: usedCode })),
          })
        : getNextSubQuestionCode({
            ...question,
            subquestions: usedCodes.map((usedCode) => ({ title: usedCode })),
          })
    )
  })
}
