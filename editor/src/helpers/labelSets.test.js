import { Entities } from './Buffer/Entities'
import {
  childrenToLabels,
  getSurveyLanguages,
  labelsToChildren,
} from './labelSets'

const question = { qid: 10, gid: 3, scaleId: 0 }

describe('getSurveyLanguages', () => {
  test('Returns the base language first, followed by additional languages', () => {
    expect(
      getSurveyLanguages({ language: 'en', additionalLanguages: 'de fr' })
    ).toEqual(['en', 'de', 'fr'])
  })

  test('Ignores empty and duplicate additional languages', () => {
    expect(
      getSurveyLanguages({ language: 'en', additionalLanguages: ' en  de ' })
    ).toEqual(['en', 'de'])
  })

  test('Handles a missing survey', () => {
    expect(getSurveyLanguages(undefined)).toEqual([])
  })
})

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

describe('labelsToChildren', () => {
  const labels = [
    { code: 'L1', assessmentValue: 2, l10ns: { en: 'Good', de: 'Gut' } },
    { code: 'A1', assessmentValue: 0, l10ns: { en: 'Bad', de: 'Schlecht' } },
  ]

  test('Creates answers after the existing ones, keeping free codes', () => {
    const existingAnswers = [{ code: 'X1', sortOrder: 0 }]

    const answers = labelsToChildren(
      labels,
      question,
      existingAnswers,
      Entities.answer,
      ['en', 'de']
    )

    expect(answers).toHaveLength(2)
    expect(answers[0]).toMatchObject({
      qid: 10,
      code: 'L1',
      sortOrder: 1,
      assessmentValue: 2,
    })
    expect(answers[0].l10ns.en.answer).toBe('Good')
    expect(answers[0].l10ns.de.answer).toBe('Gut')
    expect(answers[1]).toMatchObject({ code: 'A1', sortOrder: 2 })
    expect(answers[0].appKey).toBeTruthy()
  })

  test('Generates a new code when the label code is already used', () => {
    const existingAnswers = [{ code: 'a1', sortOrder: 0 }]

    const answers = labelsToChildren(
      labels,
      question,
      existingAnswers,
      Entities.answer,
      ['en']
    )

    const codes = answers.map((answer) => answer.code)
    expect(codes[0]).toBe('L1')
    expect(codes[1]).not.toBe('A1')
    expect(codes[1].toUpperCase()).not.toBe('A1')
    expect(new Set(codes.map((code) => code.toUpperCase())).size).toBe(2)
  })

  test('Creates subquestions with the title as code', () => {
    const subquestions = labelsToChildren(
      labels,
      question,
      [],
      Entities.subquestion,
      ['en']
    )

    expect(subquestions[0]).toMatchObject({
      title: 'L1',
      parentQid: 10,
      gid: 3,
      sortOrder: 0,
    })
    expect(subquestions[0].l10ns.en.question).toBe('Good')
    expect(subquestions[0]).not.toHaveProperty('assessmentValue', 2)
  })

  test('Leaves the text empty for languages the label set does not have', () => {
    const [answer] = labelsToChildren(
      [{ code: 'A9', l10ns: { en: 'Only English' } }],
      question,
      [],
      Entities.answer,
      ['en', 'fr']
    )

    expect(answer.l10ns.fr.answer).toBe('')
  })
})
