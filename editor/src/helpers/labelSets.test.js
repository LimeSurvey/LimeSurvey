import { Entities } from './Buffer/Entities'
import { childrenToLabels, getLabelSetCodes } from './labelSets'

const question = { qid: 10, gid: 3, scaleId: 0 }

describe('childrenToLabels', () => {
  test('Turns answers into labels, sorted, only with the selected languages', () => {
    const answers = [
      {
        code: 'A2',
        sortOrder: 1,
        assessmentValue: '3',
        l10ns: {
          en: { answer: 'No' },
          de: { answer: 'Nein' },
          fr: { answer: 'Non' },
        },
      },
      {
        code: 'A1',
        sortOrder: 0,
        assessmentValue: 1,
        l10ns: { en: { answer: 'Yes' } },
      },
    ]

    expect(childrenToLabels(answers, Entities.answer, ['en', 'de'])).toEqual([
      { code: 'A1', assessmentValue: 1, l10ns: { en: 'Yes', de: '' } },
      { code: 'A2', assessmentValue: 3, l10ns: { en: 'No', de: 'Nein' } },
    ])
  })

  test('Uses the title as code and no assessment value for subquestions', () => {
    const subquestions = [
      {
        title: 'SQ001',
        sortOrder: 0,
        l10ns: { en: { question: 'First' } },
      },
    ]

    expect(
      childrenToLabels(subquestions, Entities.subquestion, ['en'])
    ).toEqual([{ code: 'SQ001', assessmentValue: 0, l10ns: { en: 'First' } }])
  })
})

describe('getLabelSetCodes', () => {
  const labels = (...codes) => codes.map((code) => ({ code }))

  test('Keeps the label codes that are free', () => {
    expect(
      getLabelSetCodes(
        labels('L1', 'L2'),
        question,
        [{ code: 'X1' }],
        Entities.answer
      )
    ).toEqual(['L1', 'L2'])
  })

  test('Does not rename a later label when an earlier one clashes', () => {
    // Existing A1, loading A1 and A2: only the clashing A1 gets a new code
    expect(
      getLabelSetCodes(
        labels('A1', 'A2'),
        question,
        [{ code: 'A1' }],
        Entities.answer
      )
    ).toEqual(['A003', 'A2'])
  })

  test('Compares codes case-insensitively', () => {
    const [code] = getLabelSetCodes(
      labels('A1'),
      question,
      [{ code: 'a1' }],
      Entities.answer
    )

    expect(code.toUpperCase()).not.toBe('A1')
  })

  test('Gives a label set code that is used twice a new code', () => {
    const codes = getLabelSetCodes(
      labels('B1', 'b1'),
      question,
      [],
      Entities.answer
    )

    expect(codes[0]).toBe('B1')
    expect(new Set(codes.map((code) => code.toUpperCase())).size).toBe(2)
  })

  test('Replaces label codes that are too long for answer codes', () => {
    const codes = getLabelSetCodes(
      labels('ABCDEF', 'ABCDE'),
      question,
      [],
      Entities.answer
    )

    expect(codes[1]).toBe('ABCDE')
    expect(codes[0]).not.toBe('ABCDEF')
    expect(codes[0].length).toBeLessThanOrEqual(5)
  })

  test('Keeps long label codes for subquestions', () => {
    expect(
      getLabelSetCodes(
        [{ code: 'LONGSUBQUESTION1' }],
        question,
        [{ title: 'SQ001' }],
        Entities.subquestion
      )
    ).toEqual(['LONGSUBQUESTION1'])
  })

  test('Continues numeric answer codes', () => {
    expect(
      getLabelSetCodes(
        labels('1', '2'),
        question,
        [{ code: '1' }, { code: '2' }],
        Entities.answer
      )
    ).toEqual(['3', '4'])
  })

  test('Generates a code for labels without one', () => {
    expect(
      getLabelSetCodes([{ code: '' }], question, [], Entities.subquestion)
    ).toEqual(['SQ001'])
  })
})
