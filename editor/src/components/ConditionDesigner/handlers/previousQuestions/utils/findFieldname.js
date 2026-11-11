import { getQuestionTypeInfo } from 'components/QuestionTypes/index.js'
import { getQuestionById, hasTempId, STATES } from 'helpers'
import { queryClient } from 'queryClient'

// getQuestionTypeInfo builds the whole type list: Read once.
let textDisplayType
const getTextDisplayType = () =>
  (textDisplayType ??= getQuestionTypeInfo().TEXT_DISPLAY.type)

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

/**
 * The field names of the survey come from the server, which doesn't know the
 * unsaved questions and subquestions yet: Build theirs locally. Those just
 * saved are known: The save response brings the field names together with
 * the new IDs (SurveyLogicProvider). Saved ones missing from the server map
 * have no response field (e.g. a text display), so they keep having none, as
 * do unsaved text displays.
 *
 * @param {object} ids
 * @param {number|string} ids.qid question ID
 * @param {number|string} [ids.sqid] subquestion ID
 * @param {string} [ids.aid] answer code
 * @param {number} [ids.scaleId]
 * @returns {string|null} null when the question has no response field
 */
const buildFallbackFieldname = ({ qid, sqid, aid, scaleId }) => {
  if (!hasValue(qid)) return null

  const isTemp = (id) => hasValue(id) && hasTempId(id)
  // A new second scale subquestion (e.g. a new column of a saved array of
  // numbers) is unsaved too, even when the question and first scale aren't.
  const mayBeUnsaved = isTemp(qid) || isTemp(sqid) || hasValue(aid)
  if (!mayBeUnsaved) return null

  const question = findQuestion(qid)
  const secondScaleSubquestionId = findSecondScaleSubquestionId(
    question,
    sqid,
    aid
  )
  if (!isTemp(qid) && !isTemp(sqid) && !isTemp(secondScaleSubquestionId)) {
    return null
  }

  if (question?.type === getTextDisplayType()) return null
  let fieldname = `Q${qid}`

  if (hasValue(sqid)) {
    fieldname += `_S${sqid}`

    if (secondScaleSubquestionId) {
      fieldname += `_S${secondScaleSubquestionId}`
    }
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
