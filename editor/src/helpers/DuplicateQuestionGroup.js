import { DuplicateQuestion, NEW_OBJECT_ID_PREFIX } from 'helpers'
import { RandomNumber } from './RandomNumber'
import { cloneDeep } from 'lodash'

export const DuplicateQuestionGroup = (questionGroup) => {
  const duplicatedQuestionGroup = cloneDeep(questionGroup)
  const gid = `${NEW_OBJECT_ID_PREFIX}${RandomNumber()}`
  duplicatedQuestionGroup.gid = gid
  duplicatedQuestionGroup.tempId = gid

  for (const [languageKey, language] of Object.entries(
    duplicatedQuestionGroup.l10ns ?? {}
  )) {
    duplicatedQuestionGroup.l10ns[languageKey] = {
      groupName: language.groupName,
      description: language.description,
    }
  }

  duplicatedQuestionGroup.questions = duplicatedQuestionGroup.questions.map(
    (question) => {
      question.gid = gid
      return DuplicateQuestion(question, false)
    }
  )

  return duplicatedQuestionGroup
}
