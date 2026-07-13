// Local-state model for the statistics filter builder (frontend only).
// Nothing here maps to the backend / useStatistics — "Apply" just emits this shape.
// What does a single filter looks like as data.

// SOURCE.QUESTION: filter by a specific question and answer(s)
export const SOURCE = {
  QUESTION: 'question',
  SURVEY_DATA: 'surveyData',
  PARTICIPANT: 'participant',
}

// JOIN.AND: all filters must match (default)
// JOIN.OR: any filter may match
export const JOIN = { AND: 'and', OR: 'or' }

// SURVEY_FIELD.RESPONSE_ID: filter by the response ID
export const SURVEY_FIELD = {
  RESPONSE_ID: 'id',
  SEED: 'seed',
  SUBMIT_DATE: 'submitdate',
  LAST_ACTION: 'datestamp',
  INCLUDED: 'completed',
  LANGUAGE: 'startlanguage',
}

// INCLUDED.ALL: all responses, regardless of completion status
export const INCLUDED = {
  ALL: 'all',
  COMPLETE: 'complete',
  INCOMPLETE: 'incomplete',
}

// makes an unique ID for each filter row, so React can track them in a list. Uses crypto.randomUUID() if available, otherwise falls back to a simple counter.
let _fallbackId = 0
const createId = () => {
  if (typeof crypto !== 'undefined' && crypto.randomUUID) {
    return crypto.randomUUID()
  }
  _fallbackId += 1
  return `filter-${_fallbackId}`
}

// One filter row. Fields are grouped by source; only the active source's fields
// are read by the UI, the rest stay at their defaults.
// Function that hands back a fresh filter object with everything empty
// Every time the user clicks "+ Add Filter", we'll call this to make a new blank row.
export const createEmptyFilter = () => ({
  id: createId(),
  join: JOIN.AND, // operator shown ABOVE this row (ignored for the first row)
  source: SOURCE.QUESTION,
  // QUESTION
  questionQid: null,
  questionKind: null, // set when a question is picked; drives which value UI shows
  answerCodes: [], // answer-based questions
  textValue: '', // free-text questions (short/long/huge text)
  subquestion: null, // sub-question (multiple choice / multiple text / multiple numeric)
  checkState: 'Y', // multiple choice: 'Y' checked / 'N' not checked
  // number/date questions reuse numberMin/numberMax and dateFrom/dateTo below
  // SURVEY_DATA
  surveyField: null,
  included: INCLUDED.ALL,
  dateFrom: null,
  dateTo: null,
  numberMin: '',
  numberMax: '',
  languages: [],
  // PARTICIPANT
  attribute: null,
  attributeValue: '',
})

// createInitialFilters() just returns [ createEmptyFilter() ] — an array with one blank filter — so the modal opens showing a single empty row.
export const createInitialFilters = () => [createEmptyFilter()]

// Has the row's primary dropdown been chosen yet? (question / field / attribute)
// Drives whether the "Reset filter" button is shown.
export const hasPrimarySelection = (filter) => {
  switch (filter.source) {
    case SOURCE.QUESTION:
      return filter.questionQid != null
    case SOURCE.SURVEY_DATA:
      return filter.surveyField != null
    case SOURCE.PARTICIPANT:
      return Boolean(filter.attribute)
    default:
      return false
  }
}

// Does a value/range field hold anything? (shared by question value-kinds and
// the survey-data fields)
const hasValue = (filter) =>
  filter.answerCodes.length > 0 ||
  filter.numberMin !== '' ||
  filter.numberMax !== '' ||
  filter.dateFrom != null ||
  filter.dateTo != null ||
  filter.textValue !== ''

// A question row is complete once the question AND its value are set. The value
// depends on the question kind: answer/number/date/text, or (for subquestion
// types) a chosen sub-question plus its value.
const isQuestionComplete = (filter) => {
  if (filter.questionQid == null) return false
  switch (filter.questionKind) {
    case 'subCheckbox': // sub-question chosen; checkState always has a value
      return filter.subquestion != null
    case 'subText':
      return filter.subquestion != null && filter.textValue !== ''
    case 'subNumber':
      return (
        filter.subquestion != null &&
        (filter.numberMin !== '' || filter.numberMax !== '')
      )
    default: // answers / number / date / text
      return hasValue(filter)
  }
}

// Is the row fully specified? Drives enabling "Apply filter" and showing
// "+ Add filter". The non-question sources only require their primary selection
// (their exact "complete" rules aren't defined by the design yet).
export const isFilterComplete = (filter) => {
  switch (filter.source) {
    case SOURCE.QUESTION:
      return isQuestionComplete(filter)
    case SOURCE.SURVEY_DATA:
      return filter.surveyField != null
    case SOURCE.PARTICIPANT:
      return Boolean(filter.attribute)
    default:
      return false
  }
}
