import { rankingTypeHandler } from './rankingTypeHandler'

describe('rankingTypeHandler', () => {
  beforeEach(() => {
    globalThis.t = (key) => key
  })

  test('adds ranking subquestions to previous question options', () => {
    const question = {
      qid: 'temp__question',
      type: 'R',
      mandatory: false,
      l10ns: {
        en: {
          question: 'Rank these items',
        },
      },
      subquestions: [
        {
          qid: 'temp__item_1',
          title: 'SQ001',
          l10ns: {
            en: {
              question: 'First item',
            },
          },
        },
        {
          qid: 'temp__item_2',
          title: 'SQ002',
          l10ns: {
            en: {
              question: 'Second item',
            },
          },
        },
      ],
    }
    const cQuestions = []
    const cAnswers = []

    rankingTypeHandler(question, 'en', cQuestions, cAnswers)

    expect(cQuestions).toEqual([
      expect.objectContaining({
        qid: 'temp__question',
        cfieldname: 'Qtemp__question_Stemp__item_1',
      }),
      expect.objectContaining({
        qid: 'temp__question',
        cfieldname: 'Qtemp__question_Stemp__item_2',
      }),
    ])
    // Every ranking slot offers every item, then "No answer" (optional question).
    const slotAnswers = (cfieldname) => [
      { cfieldname, value: 'SQ001', label: 'SQ001 (First item)' },
      { cfieldname, value: 'SQ002', label: 'SQ002 (Second item)' },
      { cfieldname, value: ' ', label: 'No answer' },
    ]

    expect(cAnswers).toEqual([
      ...slotAnswers('Qtemp__question_Stemp__item_1'),
      ...slotAnswers('Qtemp__question_Stemp__item_2'),
    ])
  })

  test('offers only max_subquestions rank slots, each with every item', () => {
    const item = (n) => ({
      qid: `temp__item_${n}`,
      title: `SQ00${n}`,
      l10ns: { en: { question: `Item ${n}` } },
    })
    const question = {
      qid: 'temp__question',
      type: 'R',
      mandatory: true,
      attributes: { max_subquestions: { '': '2' } },
      l10ns: { en: { question: 'Rank these items' } },
      subquestions: [item(1), item(2), item(3)],
    }
    const cQuestions = []
    const cAnswers = []

    rankingTypeHandler(question, 'en', cQuestions, cAnswers)

    expect(cQuestions.map((q) => q.cfieldname)).toEqual([
      'Qtemp__question_Stemp__item_1',
      'Qtemp__question_Stemp__item_2',
    ])
    expect(
      cAnswers
        .filter((a) => a.cfieldname === 'Qtemp__question_Stemp__item_2')
        .map((a) => a.value)
    ).toEqual(['SQ001', 'SQ002', 'SQ003'])
  })

  test.each([undefined, '', '0', '5'])(
    'offers every rank slot when max_subquestions is %p',
    (max) => {
      const cQuestions = []
      rankingTypeHandler(
        {
          qid: 'temp__question',
          type: 'R',
          mandatory: true,
          attributes: max === undefined ? {} : { max_subquestions: max },
          l10ns: { en: { question: 'Rank' } },
          subquestions: [1, 2, 3].map((n) => ({
            qid: `temp__item_${n}`,
            title: `SQ00${n}`,
            l10ns: { en: { question: `Item ${n}` } },
          })),
        },
        'en',
        cQuestions,
        []
      )

      expect(cQuestions).toHaveLength(3)
    }
  )
})
