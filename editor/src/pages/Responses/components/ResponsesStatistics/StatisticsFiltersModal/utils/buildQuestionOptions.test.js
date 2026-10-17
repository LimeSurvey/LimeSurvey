import { buildQuestionOptions } from './buildQuestionOptions'

// A survey of one group holding the given questions, in the shape the editor
// keeps them.
const survey = (questions) => ({
  questionGroups: [{ gid: 1, questions }],
})

const question = (type, overrides = {}) => ({
  qid: 1,
  title: 'Q1',
  type,
  l10ns: { en: { question: 'A question' } },
  answers: [],
  subquestions: [
    {
      qid: 11,
      title: 'SQ001',
      scaleId: 0,
      l10ns: { en: { question: 'Row 1' } },
    },
  ],
  ...overrides,
})

const optionFor = (type, overrides) =>
  buildQuestionOptions(survey([question(type, overrides)]), 'en')[0]

describe('buildQuestionOptions', () => {
  // A, B, C and E keep their scale in the question type, so `question.answers`
  // is empty for them. Treating them as answer questions left the filter with
  // no column to compare and no options to offer.
  describe.each([
    ['A', ['1', '2', '3', '4', '5']],
    ['B', ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10']],
    ['C', ['Y', 'N', 'U']],
    ['E', ['I', 'S', 'D']],
  ])('array type %s', (type, expectedCodes) => {
    it('is filtered as an array', () => {
      expect(optionFor(type).kind).toBe('arrayScale')
    })

    // Only the codes are asserted: the labels of the lettered scales go
    // through t(), which the test setup stubs out to return nothing.
    it('offers its built-in scale as the columns', () => {
      const { array } = optionFor(type)

      expect(array.columns.map((column) => column.value)).toEqual(expectedCodes)
      expect(array.columns.every((column) => 'label' in column)).toBe(true)
    })

    it('offers the subquestions as the rows', () => {
      expect(optionFor(type).array.rows).toEqual([
        { value: 11, label: 'Row 1' },
      ])
    })
  })

  // F and H keep a real answer scale, which must still win over anything
  // synthesized.
  it('uses the stored answers for the array types that have them', () => {
    const { array } = optionFor('F', {
      answers: [
        { code: 'A1', scaleId: 0, l10ns: { en: { answer: 'Agree' } } },
        { code: 'A2', scaleId: 0, l10ns: { en: { answer: 'Disagree' } } },
      ],
    })

    expect(array.columns).toEqual([
      { value: 'A1', label: 'Agree' },
      { value: 'A2', label: 'Disagree' },
    ])
  })
})
