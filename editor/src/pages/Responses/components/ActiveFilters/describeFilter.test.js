import {
  FILE_UPLOADED,
  INCLUDED,
  SOURCE,
  SURVEY_FIELD,
  createEmptyFilter,
} from '../ResponsesStatistics/StatisticsFiltersModal/utils'
import { describeFilter } from './describeFilter'

const rows = [
  { value: 11, label: 'Row 1' },
  { value: 12, label: 'Row 2' },
]

// One option per question kind, in the shape buildQuestionOptions returns.
const questionOptions = [
  {
    value: 1,
    code: 'Q001',
    text: 'What is your role?',
    kind: 'answers',
    answerOptions: [
      { value: 'A1', label: 'Product management' },
      { value: 'A2', label: 'Product design' },
    ],
  },
  { value: 2, code: 'Q002', text: 'Your name?', kind: 'text' },
  { value: 3, code: 'Q003', text: 'Your age?', kind: 'number' },
  { value: 4, code: 'Q004', text: 'Your birthday?', kind: 'date' },
  {
    value: 5,
    code: 'Q005',
    text: 'Contact details',
    kind: 'subText',
    subquestions: [{ value: 51, label: 'City' }],
  },
  {
    value: 6,
    code: 'Q006',
    text: 'Hours',
    kind: 'subNumber',
    subquestions: [{ value: 61, label: 'Weekdays' }],
  },
  {
    value: 7,
    code: 'Q007',
    text: 'Rate these',
    kind: 'arrayScale',
    array: { rows, columns: [{ value: 'A1', label: 'Good' }] },
  },
  {
    value: 8,
    code: 'Q008',
    text: 'Rate both',
    kind: 'arrayDual',
    array: {
      rows,
      columns: [{ value: 'A1', label: 'Good' }],
      columns2: [{ value: 'B1', label: 'Often' }],
      columnLabel: 'Quality',
      columnLabel2: 'Frequency',
    },
  },
  {
    value: 9,
    code: 'Q009',
    text: 'Grid',
    kind: 'arrayGrid',
    array: { rows, columns: [{ value: 91, label: 'Column 1' }] },
  },
  {
    value: 10,
    code: 'Q010',
    text: 'How would you rank these soccer teams?',
    kind: 'ranking',
    ranking: {
      ranks: [{ value: '1', label: 'Rank 1' }],
      items: [{ value: 'SQ001', label: 'FC Bayern Munich' }],
    },
  },
  { value: 20, code: 'Q020', text: 'Upload your CV', kind: 'fileUpload' },
]

const question = (questionQid, questionKind, values) => ({
  ...createEmptyFilter(),
  questionQid,
  questionKind,
  ...values,
})

const surveyData = (surveyField, values) => ({
  ...createEmptyFilter(),
  source: SOURCE.SURVEY_DATA,
  surveyField,
  ...values,
})

const chipFor = (filter, survey = {}) =>
  describeFilter(filter, { questionOptions, survey })

describe('describeFilter', () => {
  beforeAll(() => {
    globalThis.t = (text) => text
  })

  describe('question filters', () => {
    test('show the code and the number of values, with the values in the tooltip', () => {
      expect(
        chipFor(question(1, 'answers', { answerCodes: ['A1', 'A2'] }))
      ).toEqual({
        label: 'Q001 (2)',
        heading: 'Q001',
        text: 'What is your role?',
        lines: ['Product management', 'Product design'],
      })
    })

    test.each([
      ['text', question(2, 'text', { textValue: 'sara' }), ['"sara"']],
      [
        'a number range',
        question(3, 'number', { numberMin: '10', numberMax: '50' }),
        ['10 – 50'],
      ],
      [
        'a lower bound only',
        question(3, 'number', { numberMin: '10' }),
        ['≥ 10'],
      ],
      [
        'an upper bound only',
        question(3, 'number', { numberMax: '50' }),
        ['≤ 50'],
      ],
      [
        'a date range',
        question(4, 'date', { dateFrom: '2026-04-10', dateTo: '2026-04-22' }),
        ['10 Apr 2026 – 22 Apr 2026'],
      ],
      [
        'a sub-question text',
        question(5, 'subText', { subquestion: 51, textValue: 'Berlin' }),
        ['City: "Berlin"'],
      ],
      [
        'a sub-question number',
        question(6, 'subNumber', { subquestion: 61, numberMax: '8' }),
        ['Weekdays: ≤ 8'],
      ],
      [
        'an array row and column',
        question(7, 'arrayScale', { row: 12, column: 'A1' }),
        ['Row 2: Good'],
      ],
      [
        'both scales of a dual array',
        question(8, 'arrayDual', { row: 11, column: 'A1', column2: 'B1' }),
        ['Row 1 (Quality): Good', 'Row 1 (Frequency): Often'],
      ],
      [
        'one scale of a dual array',
        question(8, 'arrayDual', { row: 11, column2: 'B1' }),
        ['Row 1 (Frequency): Often'],
      ],
      [
        'an array grid cell with a number',
        question(9, 'arrayGrid', { row: 11, column: 91, numberMin: '3' }),
        ['Row 1 / Column 1: ≥ 3'],
      ],
      [
        'an array grid cell with text',
        question(9, 'arrayGrid', { row: 11, column: 91, textValue: 'yes' }),
        ['Row 1 / Column 1: "yes"'],
      ],
      [
        'a rank and an item',
        question(10, 'ranking', { row: '1', column: 'SQ001' }),
        ['Rank 1', 'FC Bayern Munich'],
      ],
      [
        'an uploaded file with a title',
        question(20, 'fileUpload', {
          fileUploaded: FILE_UPLOADED.YES,
          textValue: 'cv',
        }),
        ['File uploaded: Yes', '"cv"'],
      ],
      [
        'no uploaded file',
        question(20, 'fileUpload', { fileUploaded: FILE_UPLOADED.NO }),
        ['File uploaded: No'],
      ],
    ])('word %s', (_, filter, lines) => {
      const chip = chipFor(filter)

      expect(chip.lines).toEqual(lines)
      expect(chip.label).toMatch(new RegExp(`\\(${lines.length}\\)$`))
    })

    test('fall back to the answer code when the answer is gone', () => {
      expect(
        chipFor(question(1, 'answers', { answerCodes: ['A9'] })).lines
      ).toEqual(['A9'])
    })

    test('fall back to "Question" when the question is gone', () => {
      expect(chipFor(question(99, 'answers', { answerCodes: ['A1'] }))).toEqual(
        { label: 'Question', heading: 'Question', lines: [] }
      )
    })
  })

  describe('response data filters', () => {
    test.each([
      [INCLUDED.ALL, 'Included responses: All'],
      [INCLUDED.COMPLETE, 'Included responses: Complete'],
      [INCLUDED.INCOMPLETE, 'Included responses: Incomplete'],
    ])('word included responses "%s"', (included, label) => {
      expect(chipFor(surveyData(SURVEY_FIELD.INCLUDED, { included }))).toEqual({
        label,
        heading: label,
        lines: [],
      })
    })

    test('show a date range as the chip and name the date in the tooltip', () => {
      expect(
        chipFor(
          surveyData(SURVEY_FIELD.SUBMIT_DATE, {
            dateFrom: '2026-04-10',
            dateTo: '2026-04-22',
          })
        )
      ).toEqual({
        label: '10 Apr 2026 – 22 Apr 2026',
        heading: 'Submit date',
        lines: ['10 Apr 2026 – 22 Apr 2026'],
      })
    })

    test('show the response ID range', () => {
      expect(
        chipFor(
          surveyData(SURVEY_FIELD.RESPONSE_ID, {
            numberMin: '10',
            numberMax: '50',
          })
        ).label
      ).toBe('Response ID: 10 – 50')
    })

    test('count the languages and list them in the tooltip', () => {
      expect(
        chipFor(surveyData(SURVEY_FIELD.LANGUAGE, { languages: ['en', 'de'] }))
      ).toEqual({
        label: 'Language (2)',
        heading: 'Language',
        lines: ['en', 'de'],
      })
    })

    test.each([
      SURVEY_FIELD.LAST_ACTION,
      SURVEY_FIELD.SEED,
      SURVEY_FIELD.LANGUAGE,
    ])('show only the field name when "%s" has no value', (surveyField) => {
      const chip = chipFor(surveyData(surveyField))

      expect(chip.label).toBe(chip.heading)
      expect(chip.lines).toEqual([])
    })
  })

  describe('participant filters', () => {
    const participant = (attribute, attributeValue = '') => ({
      ...createEmptyFilter(),
      source: SOURCE.PARTICIPANT,
      attribute,
      attributeValue,
    })

    test('show the attribute and its value', () => {
      expect(
        chipFor(participant('{TOKEN:EMAIL}', 'sara@example.com')).label
      ).toBe('Email address: sara@example.com')
    })

    test('name a custom attribute by its description', () => {
      const survey = {
        attributeDescriptions: { attribute_1: { description: 'Department' } },
      }

      expect(
        chipFor(participant('{TOKEN:ATTRIBUTE_1}', 'Sales'), survey).label
      ).toBe('Department: Sales')
    })

    test('show only the attribute when there is no value', () => {
      expect(chipFor(participant('{TOKEN:FIRSTNAME}')).label).toBe('First name')
    })
  })
})
