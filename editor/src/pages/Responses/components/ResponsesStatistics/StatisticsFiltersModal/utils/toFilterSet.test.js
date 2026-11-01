import { SOURCE, SURVEY_FIELD, createEmptyFilter } from './filterModel'
import { toFilterSet } from './toFilterSet'

// A complete row of the given source, on top of the model's defaults.
const filter = (overrides) => ({ ...createEmptyFilter(), ...overrides })

const question = (overrides) =>
  filter({ source: SOURCE.QUESTION, questionQid: 42, ...overrides })

describe('toFilterSet', () => {
  it('sends nothing when nothing is filtered', () => {
    expect(toFilterSet([])).toEqual([])
    expect(toFilterSet()).toEqual([])
  })

  it('renames the model fields the backend spells differently', () => {
    const [entry] = toFilterSet([
      question({ questionKind: 'text', textValue: 'refund' }),
    ])

    expect(entry).toEqual({
      join: 'and',
      source: SOURCE.QUESTION,
      qid: 42,
      text: 'refund',
    })
  })

  // `id` is a React list key and `questionKind` is derived from the question's
  // type server-side; a client able to send it could aim a filter elsewhere.
  it('never sends the list key or the question kind', () => {
    const [entry] = toFilterSet([
      question({ questionKind: 'answers', answerCodes: ['Y'] }),
    ])

    expect(entry).not.toHaveProperty('id')
    expect(entry).not.toHaveProperty('questionKind')
  })

  it('leaves out fields the user never filled in', () => {
    const [entry] = toFilterSet([
      question({ questionKind: 'answers', answerCodes: ['Y', 'N'] }),
    ])

    expect(entry).toEqual({
      join: 'and',
      source: SOURCE.QUESTION,
      qid: 42,
      answerCodes: ['Y', 'N'],
    })
  })

  it('keeps a zero, which is an answer rather than a blank', () => {
    const [entry] = toFilterSet([
      question({ questionKind: 'number', numberMin: 0 }),
    ])

    expect(entry.numberMin).toBe(0)
  })

  it('sends only the fields of the row’s own source', () => {
    const [entry] = toFilterSet([
      filter({
        source: SOURCE.SURVEY_DATA,
        surveyField: SURVEY_FIELD.RESPONSE_ID,
        numberMin: '5',
        // Left over from a question the user picked before switching source.
        questionQid: 42,
        answerCodes: ['Y'],
      }),
    ])

    expect(entry).toEqual({
      join: 'and',
      source: SOURCE.SURVEY_DATA,
      field: SURVEY_FIELD.RESPONSE_ID,
      included: 'all',
      numberMin: '5',
    })
  })

  it('maps a participant row to its attribute and value', () => {
    const [entry] = toFilterSet([
      filter({
        source: SOURCE.PARTICIPANT,
        attribute: '{TOKEN:EMAIL}',
        attributeValue: 'example.org',
      }),
    ])

    expect(entry).toEqual({
      join: 'and',
      source: SOURCE.PARTICIPANT,
      attribute: '{TOKEN:EMAIL}',
      value: 'example.org',
    })
  })

  // The toggle always holds a value, so it would otherwise ride along on
  // every question row.
  it('sends the upload toggle only for a file upload question', () => {
    const [upload] = toFilterSet([
      question({ questionKind: 'fileUpload', fileUploaded: 'Y' }),
    ])
    const [text] = toFilterSet([
      question({ questionKind: 'text', textValue: 'x' }),
    ])

    expect(upload.fileUploaded).toBe('Y')
    expect(text).not.toHaveProperty('fileUploaded')
  })

  it('keeps each row’s join, so the rows still combine as shown', () => {
    const entries = toFilterSet([
      question({ questionKind: 'text', textValue: 'a' }),
      question({ questionKind: 'text', textValue: 'b', join: 'or' }),
    ])

    expect(entries.map((entry) => entry.join)).toEqual(['and', 'or'])
  })

  it('keeps the rows in the order they were built', () => {
    const entries = toFilterSet([
      question({ questionQid: 1, questionKind: 'text', textValue: 'a' }),
      question({ questionQid: 2, questionKind: 'text', textValue: 'b' }),
    ])

    expect(entries.map((entry) => entry.qid)).toEqual([1, 2])
  })

  it('leaves out rows that are not finished', () => {
    const entries = toFilterSet([
      question({ questionKind: 'text', textValue: 'refund' }),
      // A question picked but no value given yet.
      question({ questionQid: 43, questionKind: 'text' }),
      createEmptyFilter(),
    ])

    expect(entries).toHaveLength(1)
    expect(entries[0].text).toBe('refund')
  })

  it('sends a dual-scale row with both scales', () => {
    const [entry] = toFilterSet([
      question({
        questionKind: 'arrayDual',
        row: 101,
        column: 'A1',
        column2: 'B1',
      }),
    ])

    expect(entry).toEqual({
      join: 'and',
      source: SOURCE.QUESTION,
      qid: 42,
      row: 101,
      column: 'A1',
      column2: 'B1',
    })
  })
})
