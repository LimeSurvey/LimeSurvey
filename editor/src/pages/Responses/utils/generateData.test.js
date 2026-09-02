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
})
