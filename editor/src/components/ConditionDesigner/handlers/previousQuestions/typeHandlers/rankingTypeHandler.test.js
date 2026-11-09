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
})
