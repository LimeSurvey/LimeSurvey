import { isTempId, STATES } from 'helpers'
import { queryClient } from 'queryClient'

/**
 * Builds a fieldname the same way the server's field map does
 * (Q{qid}, Q{qid}_S{sqid}, Q{qid}_S{sqid}_S{xSqid}, Q{qid}_S{sqid}#{scaleId}).
 * Used for questions and subquestions the server's map doesn't know yet.
 */
const buildFieldname = ({ qid, sqid, xSqid, scaleId }) =>
  `Q${qid}` +
  (sqid ? `_S${sqid}` : '') +
  (xSqid ? `_S${xSqid}` : '') +
  (scaleId !== undefined ? `#${scaleId}` : '')

/**
 * Finds the fieldname of a question, subquestion or answer column in the
 * server's field map, or builds it for a question or subquestion the map
 * doesn't know yet: Unsaved (temp IDs), or saved after the map was loaded.
 *
 * @param {object} field The field to find, with the IDs below as needed.
 * @param {boolean} field.hasQuestionField Whether the question has a field of
 *   its own, false for types with only subquestion fields (arrays).
 * @returns {string|null} The fieldname, null if the question has no such field.
 */
export function findFieldname({
  qid,
  sqid,
  aid,
  scaleId,
  xSqid,
  hasQuestionField = true,
}) {
  const questionsFieldNamesMap =
    queryClient.getQueryData([STATES.SURVEY_QUESTIONS_FIELDNAME]) || {}
  const questionFields = questionsFieldNamesMap[qid]

  const fieldname = questionFields?.find(
    (item) =>
      (qid ? item.qid == qid : true) &&
      (sqid ? item.sqid == sqid : true) &&
      (aid ? item.aid == aid : true) &&
      (scaleId !== undefined ? item.scale_id == scaleId : true)
  )?.fieldname
  if (fieldname) return fieldname

  // A question or subquestion that is in the map without this field really
  // has none. A subquestion missing from the map was saved after it loaded.
  const isUnknownToMap =
    !questionFields ||
    [qid, sqid, xSqid].some((id) => isTempId(id)) ||
    (sqid && !questionFields.some((item) => item.sqid == sqid)) ||
    (xSqid &&
      !questionFields.some((item) =>
        String(item.fieldname).includes(`_S${xSqid}`)
      ))
  if (!qid || !isUnknownToMap || (!sqid && !hasQuestionField)) return null

  return buildFieldname({ qid, sqid, xSqid, scaleId })
}
