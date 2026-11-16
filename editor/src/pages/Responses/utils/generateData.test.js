import 'tests/mocks'

import { generateData } from './generateData'

describe('generateData', () => {
  test('maps timing values onto the response row', () => {
    const [row] = generateData(
      [
        {
          id: 10,
          answers: {},
          timings: {
            interviewtime: 125.42,
            G12time: 60.17,
            Q34time: 18.03,
          },
        },
      ],
      'en',
      []
    )

    expect(row).toEqual(
      expect.objectContaining({
        interviewtime: 125.42,
        G12time: 60.17,
        Q34time: 18.03,
      })
    )
  })

  test('supports responses without timing data', () => {
    const [row] = generateData([{ id: 10, answers: {} }], 'en', [])

    expect(row.id).toBe(10)
    expect(row.interviewtime).toBeUndefined()
  })

  describe('multiple choice Other', () => {
    const columns = [
      {
        accessorKey: 5,
        meta: {
          question: {
            qid: 5,
            questionThemeName: 'multiplechoice',
            subquestions: [{ qid: 6, title: 'SQ001', l10ns: {} }],
          },
        },
      },
    ]
    const otherText = (value) => ({
      key: 'Q5_Cother',
      qid: 5,
      aid: 'other',
      value,
    })
    const otherChecked = (value) => ({
      key: 'Q5_Cotherchecked',
      qid: 5,
      aid: 'otherchecked',
      value,
    })
    const cellOf = (answers) =>
      generateData([{ id: 1, answers }], 'en', columns)[0][5]

    test('uses the stored tick and shows no separate answer for it', () => {
      const cell = cellOf({ a: otherText(''), b: otherChecked('Y') })

      expect(cell).toHaveLength(1)
      expect(cell[0]).toEqual(
        expect.objectContaining({
          value: '',
          checked: true,
          otherText: { value: '', key: 'Q5_Cother' },
          otherChecked: { value: 'Y', key: 'Q5_Cotherchecked' },
        })
      )
    })

    test('works when the tick comes before the text', () => {
      const cell = cellOf({ b: otherChecked(''), a: otherText('') })

      expect(cell).toHaveLength(1)
      expect(cell[0].checked).toBe(false)
      expect(cell[0].key).toBe('Q5_Cother')
    })

    test('without the column the tick follows the text', () => {
      const cell = cellOf({ a: otherText('my text') })

      expect(cell).toHaveLength(1)
      expect(cell[0].checked).toBe(true)
      expect(cell[0].otherChecked).toBeUndefined()
    })
  })
})
