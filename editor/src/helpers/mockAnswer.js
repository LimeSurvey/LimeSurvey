import { RandomNumber } from './RandomNumber'

export const mockAnswer = (title, languages, data) => {
  const aid = RandomNumber()
  const l10ns = languages.reduce((acc, l10ns) => {
    return { ...acc, [l10ns]: { answer: title, aid, language: [l10ns] } }
  }, {})

  return {
    aid: 375,
    qid: 346,
    code: 'A1',
    sortOrder: 0,
    assessmentValue: 1,
    scaleId: 0,
    l10ns,
    ...data,
  }
}
