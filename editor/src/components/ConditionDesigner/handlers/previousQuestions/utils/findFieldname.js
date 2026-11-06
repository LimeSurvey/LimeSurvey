import { getQuestionById, STATES } from 'helpers'
import { queryClient } from 'queryClient'

const hasValue = (value) =>
  value !== undefined && value !== null && value !== ''
const isSameValue = (a, b) => String(a) === String(b)

const findQuestion = (qid) => {
  const survey = queryClient.getQueryData([STATES.SURVEY])?.survey

  return survey?.questionGroups ? getQuestionById(qid, survey).question : null
}

const findSecondScaleSubquestionId = (question, sqid, aid) => {
  if (!question || !hasValue(sqid) || !hasValue(aid)) return null

  const subquestions = question.subquestions ?? []
  const firstScaleSubquestion = subquestions.find((subquestion) =>
    isSameValue(subquestion.qid, sqid)
  )

  if (!firstScaleSubquestion?.title) return null

  const secondScaleTitlePrefix = `${firstScaleSubquestion.title}_`
  const aidString = String(aid)

  if (!aidString.startsWith(secondScaleTitlePrefix)) return null

  const secondScaleTitle = aidString.slice(secondScaleTitlePrefix.length)

  return subquestions.find(
    (subquestion) =>
      isSameValue(subquestion.scaleId, 1) &&
      isSameValue(subquestion.title, secondScaleTitle)
  )?.qid
}

const buildFallbackFieldname = ({ qid, sqid, aid, scaleId }) => {
  if (!hasValue(qid)) return null

  const question = findQuestion(qid)
  let fieldname = `Q${qid}`

  if (hasValue(sqid)) {
    fieldname += `_S${sqid}`

    const secondScaleSubquestionId = findSecondScaleSubquestionId(
      question,
      sqid,
      aid
    )

    if (secondScaleSubquestionId) {
      fieldname += `_S${secondScaleSubquestionId}`
    }
  } else if (question?.type === 'R' && hasValue(aid)) {
    fieldname += `_S${aid}`
  }

  if (hasValue(scaleId)) {
    fieldname += `#${scaleId}`
  }

  return fieldname
}

export function findFieldname({ qid, sqid, aid, scaleId }) {
  const questionsFieldNamesMap =
    queryClient.getQueryData([STATES.SURVEY_QUESTIONS_FIELDNAME]) || {}

  return (
    questionsFieldNamesMap[qid]?.find(
      (item) =>
        (qid ? item.qid == qid : true) &&
        (sqid ? item.sqid == sqid : true) &&
        (aid ? item.aid == aid : true) &&
        (scaleId !== undefined ? item.scale_id == scaleId : true)
    )?.fieldname || buildFallbackFieldname({ qid, sqid, aid, scaleId })
  )
}
