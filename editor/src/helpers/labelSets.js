import { Entities } from './Buffer/Entities'
import { getAnswerExample } from './getAnswerExample'
import { getQuestionExample } from './getQuestionExample'
import {
  getNextAnswerCode,
  getNextSubQuestionCode,
} from './questionAndAnswerCodeGenerators'
import { RandomNumber } from './RandomNumber'
import { APP_KEY_PREFIX } from './constants/constants'

/**
 * All languages of a survey, base language first.
 */
export const getSurveyLanguages = (survey = {}) => {
  const languages = survey.language ? [survey.language] : []
  const additionalLanguages = (survey.additionalLanguages || '')
    .split(' ')
    .filter((language) => language && !languages.includes(language))

  return [...languages, ...additionalLanguages]
}

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
 * Turn labels of a label set into new answer options or subquestions,
 * ready to be appended after the existing ones.
 *
 * A label keeps its code unless the code is already used by the question
 * (compared case-insensitively, like the editor's code validation);
 * then the next free code is generated.
 *
 * @param {object[]} labels Labels from the label set API
 * @param {object} question The question the children are added to
 * @param {object[]} existingChildren The question's current answers or subquestions
 * @param {string} entityType Entities.answer or Entities.subquestion
 * @param {string[]} languages Survey languages
 */
export const labelsToChildren = (
  labels = [],
  question,
  existingChildren = [],
  entityType,
  languages = []
) => {
  const isAnswer = isAnswerEntity(entityType)
  const codeKey = isAnswer ? 'code' : 'title'
  const textKey = isAnswer ? 'answer' : 'question'
  const usedChildren = [...existingChildren]
  const isCodeUsed = (code) =>
    usedChildren.some(
      (child) => `${child[codeKey]}`.toUpperCase() === `${code}`.toUpperCase()
    )

  return labels.map((label, index) => {
    let code = label.code
    if (!code || isCodeUsed(code)) {
      code = isAnswer
        ? getNextAnswerCode({ ...question, answers: usedChildren })
        : getNextSubQuestionCode({ ...question, subquestions: usedChildren })
    }

    const sortOrder = existingChildren.length + index
    const child = isAnswer
      ? getAnswerExample({
          qid: question.qid,
          code,
          sortOrder,
          languages,
        })
      : getQuestionExample({
          gid: question.gid,
          scaleId: question.scaleId,
          parentQid: question.qid,
          sortOrder,
          languages,
          title: code,
        })

    if (isAnswer) {
      child.assessmentValue = Number(label.assessmentValue) || 0
    }
    languages.forEach((language) => {
      child.l10ns[language][textKey] = label.l10ns?.[language] ?? ''
    })
    child.appKey = `${entityType}-${APP_KEY_PREFIX}${RandomNumber()}`

    usedChildren.push(child)
    return child
  })
}
