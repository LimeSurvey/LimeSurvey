import { SOURCE, isFilterComplete } from './filterModel'

// Turns the modal's local model into the `filterSet` the backend accepts.
//
// The two shapes differ on purpose. The model carries every field on every row
// so the UI can switch sources without losing what was typed, and it holds
// things the server must not be told: `id` is a React list key, and
// `questionKind` is derived from the question's type server-side — a client
// that could send it could point a filter at columns the user never chose.
//
// The backend rejects properties it does not know rather than ignoring them,
// so a rename missed here fails loudly instead of quietly returning more rows
// than were asked for.

// Contract key -> model key, per source. Only the active source's fields are
// sent; the rest are defaults the user never touched.
const FIELDS = {
  [SOURCE.QUESTION]: {
    qid: 'questionQid',
    answerCodes: 'answerCodes',
    text: 'textValue',
    numberMin: 'numberMin',
    numberMax: 'numberMax',
    dateFrom: 'dateFrom',
    dateTo: 'dateTo',
    subquestion: 'subquestion',
    row: 'row',
    column: 'column',
    column2: 'column2',
    fileUploaded: 'fileUploaded',
  },
  [SOURCE.SURVEY_DATA]: {
    field: 'surveyField',
    included: 'included',
    languages: 'languages',
    numberMin: 'numberMin',
    numberMax: 'numberMax',
    dateFrom: 'dateFrom',
    dateTo: 'dateTo',
  },
  [SOURCE.PARTICIPANT]: {
    attribute: 'attribute',
    value: 'attributeValue',
  },
}

// A field the user left alone. Zero is a real answer, so only blanks count.
const isEmpty = (value) =>
  value === null ||
  value === undefined ||
  value === '' ||
  (Array.isArray(value) && value.length === 0)

const toEntry = (filter) => {
  const entry = { join: filter.join, source: filter.source }

  Object.entries(FIELDS[filter.source] ?? {}).forEach(([key, modelKey]) => {
    const value = filter[modelKey]
    if (!isEmpty(value)) {
      entry[key] = value
    }
  })

  // The uploaded/not toggle always holds a value, so it would ride along on
  // every question row. Only a file upload question means anything by it.
  if (filter.questionKind !== 'fileUpload') {
    delete entry.fileUploaded
  }

  return entry
}

/**
 * @param {Array} filters Rows from the filter modal.
 * @returns {Array} The `filterSet` payload; empty when nothing is filtered.
 */
export const toFilterSet = (filters = []) =>
  filters.filter(isFilterComplete).map(toEntry)
