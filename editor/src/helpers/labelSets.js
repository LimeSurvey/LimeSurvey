import { Entities } from './Buffer/Entities'
import { QT_P_MULTIPLE_CHOICE_WITH_COMMENTS } from './constants/questionTypes'
import { isTrue } from './isTrue'
import {
  getNextAnswerCode,
  getNextSubQuestionCode,
} from './questionAndAnswerCodeGenerators'

// Length of the answers.code and questions.title columns; label codes can have up to 20
const MAX_ANSWER_CODE_LENGTH = 5
const MAX_SUBQUESTION_CODE_LENGTH = 20

const isAnswerEntity = (entityType) => entityType === Entities.answer

/**
 * The first free code of the editor's default scheme (A001, A002, ... or SQ001, ...).
 *
 * @param {boolean} isAnswer Whether the code is for an answer option
 * @param {(code: string) => boolean} isFree Whether a code is unused and short enough
 * @returns {string} The code
 * @throws {Error} When every code of the scheme that fits is taken
 */
const getFirstFreeCode = (isAnswer, isFree) => {
  const prefix = isAnswer ? 'A' : 'SQ'
  const maxLength = isAnswer
    ? MAX_ANSWER_CODE_LENGTH
    : MAX_SUBQUESTION_CODE_LENGTH
  // Ends at the first code that is too long; only codes that are taken are skipped
  for (
    let number = 1;
    prefix.length + Math.max(3, `${number}`.length) <= maxLength;
    number++
  ) {
    const code = `${prefix}${`${number}`.padStart(3, '0')}`
    if (isFree(code)) {
      return code
    }
  }
  throw new Error(t('There is no free code left for the label set.'))
}

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
 * is too long for the entity (answer codes have at most 5 characters) or is a
 * subquestion code the question doesn't allow (see isReservedSubquestionCode).
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

  // Subquestion codes that Question::rules() rejects: "time" (case-sensitive),
  // "other" (case-insensitive) while the question has "Other" on, and, in
  // multiple choice with comments, codes ending in "comment" (case-sensitive)
  const hasOther = isTrue(question.other)
  const isCommentType = question.type === QT_P_MULTIPLE_CHOICE_WITH_COMMENTS
  const isReservedSubquestionCode = (code) =>
    code === 'time' ||
    (hasOther && code.toUpperCase() === 'OTHER') ||
    (isCommentType && code.endsWith('comment'))

  const isFree = (code) =>
    !!code &&
    code.length <= maxLength &&
    !usedKeys.has(code.toUpperCase()) &&
    (isAnswer || !isReservedSubquestionCode(code))

  // Reserve the codes that are kept first, so a generated code can't take one
  const keptCodes = labels.map((label) => {
    const code = `${label.code ?? ''}`
    return isFree(code) ? useCode(code) : null
  })

  return keptCodes.map((code) => {
    if (code) {
      return code
    }
    const nextCode = isAnswer
      ? getNextAnswerCode({
          ...question,
          answers: usedCodes.map((usedCode) => ({ code: usedCode })),
        })
      : getNextSubQuestionCode({
          ...question,
          subquestions: usedCodes.map((usedCode) => ({ title: usedCode })),
        })
    // The generator continues after the highest code, which can be too long
    // (A9999 -> A10000) or, past Number's precision, a code that is taken
    return useCode(
      isFree(nextCode) ? nextCode : getFirstFreeCode(isAnswer, isFree)
    )
  })
}
