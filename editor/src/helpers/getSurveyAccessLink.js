import getSiteUrl from 'helpers/getSiteUrl'

export const getSurveyAccessLink = ({ survey, language }) => {
  const alias = survey.languageSettings[language]?.alias?.trim() || ''
  const link = alias || survey.sid
  const lang =
    language && language !== survey.langauge ? `?lang=${language}&` : ''

  return getSiteUrl(`/${link}${lang}newtest=Y`)
}
