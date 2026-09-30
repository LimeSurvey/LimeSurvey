import getSiteUrl from 'helpers/getSiteUrl'

export const getSurveyAccessLink = ({
  survey,
  language,
  isPreviewLink = false,
}) => {
  const alias = survey.languageSettings[language]?.alias?.trim() || ''
  const link = alias || survey.sid
  const lang =
    language && language !== survey.langauge ? `?lang=${language}&` : ''
  const params = isPreviewLink ? '' : `${lang}newtest=Y`
  return getSiteUrl(`/${link}${params}`)
}
