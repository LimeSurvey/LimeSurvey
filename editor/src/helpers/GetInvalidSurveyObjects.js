import { L10ns } from './L10ns/L10ns'

import { TestValidation as TestGroupValidation } from 'components/Survey/QuestionGroups/QuestionGroupSchema'
import { TestValidation as TestQuestionValidation } from 'components/Survey/Questions/QuestionSchema'
import { RemoveHTMLTagsInString } from './RemoveHTMLTagsInString'

export const GetInvalidSurveyObjects = (survey) => {
  let invalidObjectIds = {}
  const questionGroups = survey.questionGroups || []
  const invalidQuestionGroupsIds = validateArray(
    questionGroups,
    'groupName',
    'gid',
    survey.language,
    TestGroupValidation
  )

  invalidObjectIds['gid'] = { ...invalidQuestionGroupsIds }

  for (let i = 0; i < questionGroups.length; i++) {
    const questions = questionGroups[i].questions || []
    const invalidQuestionsIds = validateArray(
      questions,
      'question',
      'qid',
      survey.language,
      TestQuestionValidation,
      i
    )

    invalidObjectIds['qid'] = {
      ...invalidObjectIds['qid'],
      ...invalidQuestionsIds,
    }
  }

  return invalidObjectIds
}

const validateArray = (
  array,
  nameProp,
  idProp,
  language,
  validationSchema,
  groupIndex
) => {
  const invalidObjectIds = {}
  for (let i = 0; i < array.length; i++) {
    const item = array[i]
    const itemName = L10ns({
      prop: nameProp,
      language,
      l10ns: item.l10ns,
    })

    const errors = validationSchema(RemoveHTMLTagsInString(itemName)).error

    if (errors) {
      const isNotGroup = idProp !== 'gid'
      invalidObjectIds[item[idProp]] = isNotGroup
        ? { groupIndex: groupIndex, questionIndex: i }
        : { groupIndex: i }
    }
  }

  return invalidObjectIds
}
