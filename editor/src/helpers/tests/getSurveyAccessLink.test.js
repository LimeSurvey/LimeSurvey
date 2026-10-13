import { getSiteUrl, getSurveyAccessLink } from 'helpers'

const survey = {
  sid: 123456,
  language: 'en',
  languageSettings: {
    en: { alias: '' },
    de: { alias: '' },
    fr: { alias: 'mon-sondage' },
  },
}

describe('getSurveyAccessLink', () => {
  test('should not add a language parameter for the base language', () => {
    expect(getSurveyAccessLink({ survey, language: 'en' })).toBe(
      getSiteUrl('/123456?newtest=Y')
    )
  })

  test('should add a language parameter for an additional language', () => {
    expect(getSurveyAccessLink({ survey, language: 'de' })).toBe(
      getSiteUrl('/123456?lang=de&newtest=Y')
    )
  })

  test('should use the alias of the language if one is set', () => {
    expect(getSurveyAccessLink({ survey, language: 'fr' })).toBe(
      getSiteUrl('/mon-sondage?lang=fr&newtest=Y')
    )
  })

  test('should not add any parameters for a preview link', () => {
    expect(
      getSurveyAccessLink({ survey, language: 'de', isPreviewLink: true })
    ).toBe(getSiteUrl('/123456'))
  })
})
