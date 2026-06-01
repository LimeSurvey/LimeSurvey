import { STATES } from 'helpers'
import { queryClient } from 'queryClient'

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
    )?.fieldname || null
  )
}
