import 'tests/mocks'

import { generateColumns, getInitialColumnVisibility } from './generateColumns'

describe('generateColumns', () => {
  beforeAll(() => {
    globalThis.t = (text) => text
  })

  test('adds columns from timing field metadata', () => {
    const timingFields = [
      {
        fieldname: 'interviewtime',
        type: 'interview_time',
        question: 'Total time (in s)',
      },
      {
        fieldname: 'G12time',
        type: 'page_time',
        question: 'Group time: Demographics',
      },
      {
        fieldname: 'Q34time',
        qid: 34,
        type: 'answer_time',
        question: 'Question time: AGE',
      },
    ]

    const columns = generateColumns(
      { responseField: { qid: 34 } },
      {
        sid: 123,
        language: 'en',
        languages: [],
        datestamp: true,
        questionGroups: [
          {
            questions: [
              {
                qid: 34,
                title: 'AGE',
                l10ns: { en: { question: 'How old are you?' } },
              },
            ],
          },
        ],
      },
      timingFields
    )

    expect(columns).toEqual(
      expect.arrayContaining([
        expect.objectContaining({
          id: 'interviewtime',
          header: 'Total time (in s)',
          enableSorting: false,
          meta: expect.objectContaining({
            columnCategory: 'timing',
            visibleByDefault: false,
          }),
        }),
        expect.objectContaining({
          id: 'G12time',
          header: 'Group time: Demographics',
        }),
        expect.objectContaining({
          id: 'Q34time',
          header: 'How old are you?',
          meta: expect.objectContaining({
            qid: 34,
            title: 'Question time: AGE',
            questionLabel: {
              code: 'AGE',
              text: 'How old are you?',
            },
          }),
        }),
        expect.objectContaining({
          id: '34',
          header: 'How old are you?',
          meta: expect.objectContaining({
            questionLabel: {
              code: 'AGE',
              text: 'How old are you?',
            },
          }),
        }),
      ])
    )

    expect(getInitialColumnVisibility(columns)).toEqual({
      interviewtime: false,
      G12time: false,
      Q34time: false,
    })
  })

  test('ignores timing metadata without a field name', () => {
    const columns = generateColumns(
      {},
      { sid: 123, languages: [], questionGroups: [] },
      [{ type: 'interview_time', question: 'Total time' }]
    )

    expect(columns.some((column) => column.meta?.timingType)).toBe(false)
  })
})
