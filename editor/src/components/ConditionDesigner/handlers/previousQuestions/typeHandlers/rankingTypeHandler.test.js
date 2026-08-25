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
    expect(cAnswers).toEqual(
      expect.arrayContaining([
        expect.objectContaining({
          cfieldname: 'Qtemp__question_Stemp__item_1',
          value: 'SQ001',
        }),
        expect.objectContaining({
          cfieldname: 'Qtemp__question_Stemp__item_1',
          value: 'SQ002',
        }),
      ])
    )
  })
})
