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

  test('Gives clashing numeric answer codes the default scheme after the highest number', () => {
    // Like adding an answer option: 1 and 2 are followed by A003
    expect(
      getLabelSetCodes(
        labels('1', '2'),
        question,
        [{ code: '1' }, { code: '2' }],
        Entities.answer
      )
    ).toEqual(['A003', 'A004'])
  })

  test('Keeps generated answer codes within five characters', () => {
    // The generator would continue with A10000
    expect(
      getLabelSetCodes(
        labels('A9999', 'B1'),
        question,
        [{ code: 'A9999' }, { code: 'A001' }],
        Entities.answer
      )
    ).toEqual(['A002', 'B1'])
  })

  test('Keeps generated numeric answer codes within five characters', () => {
    expect(
      getLabelSetCodes(
        labels('1'),
        question,
        [{ code: '99999' }, { code: '1' }],
        Entities.answer
      )
    ).toEqual(['A001'])
  })

  test('Fails when no answer code that fits is free', () => {
    const existing = Array.from({ length: 9999 }, (_, index) => ({
      code: `A${`${index + 1}`.padStart(3, '0')}`,
    }))

    expect(() =>
      getLabelSetCodes(labels('A001'), question, existing, Entities.answer)
    ).toThrow()
  })

  test('Does not generate a subquestion code that is already used', () => {
    // 9007199254740992 + 1 can't be represented, so the generator repeats the code
    expect(
      getLabelSetCodes(
        labels('SQ9007199254740992', 'SQ001'),
        question,
        [{ title: 'SQ001' }],
        Entities.subquestion
      )
    ).toEqual(['SQ9007199254740992', 'SQ002'])
  })

  test('Replaces the reserved subquestion code time', () => {
    expect(
      getLabelSetCodes(
        labels('time', 'Time'),
        question,
        [],
        Entities.subquestion
      )
    ).toEqual(['SQ001', 'Time'])
  })

  test('Replaces the subquestion code other while the question has Other on', () => {
    expect(
      getLabelSetCodes(
        labels('other', 'OTHER'),
        { ...question, other: 'Y' },
        [],
        Entities.subquestion
      )
    ).toEqual(['SQ001', 'SQ002'])
  })

  test('Keeps the subquestion code other while the question has Other off', () => {
    expect(
      getLabelSetCodes(
        labels('other'),
        { ...question, other: false },
        [],
        Entities.subquestion
      )
    ).toEqual(['other'])
  })

  test('Replaces subquestion codes ending in comment in multiple choice with comments', () => {
    expect(
      getLabelSetCodes(
        labels('comment', 'L1comment'),
        { ...question, type: 'P' },
        [],
        Entities.subquestion
      )
    ).toEqual(['SQ001', 'SQ002'])
  })

  test('Keeps subquestion codes with comment elsewhere or in other case in multiple choice with comments', () => {
    expect(
      getLabelSetCodes(
        labels('L1Comment', 'commentL1'),
        { ...question, type: 'P' },
        [],
        Entities.subquestion
      )
    ).toEqual(['L1Comment', 'commentL1'])
  })

  test('Keeps subquestion codes ending in comment in other question types', () => {
    expect(
      getLabelSetCodes(
        labels('L1comment'),
        { ...question, type: 'M' },
        [],
        Entities.subquestion
      )
    ).toEqual(['L1comment'])
  })

  test('Keeps the answer code other while the question has Other on', () => {
    expect(
      getLabelSetCodes(
        labels('other'),
        { ...question, other: 'Y' },
        [],
        Entities.answer
      )
    ).toEqual(['other'])
  })

  test('Keeps the answer code time', () => {
    expect(
      getLabelSetCodes(labels('time'), question, [], Entities.answer)
    ).toEqual(['time'])
  })

  test('Generates a code for labels without one', () => {
    expect(
      getLabelSetCodes([{ code: '' }], question, [], Entities.subquestion)
    ).toEqual(['SQ001'])
  })
})
