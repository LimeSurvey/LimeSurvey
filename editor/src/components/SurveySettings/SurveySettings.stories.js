import { SurveySettings } from './SurveySettings'

export default {
  title: 'General/SurveySettings',
  component: SurveySettings,
}

export const Basic = () => {
  return <SurveySettings />
}

export const WithSurveyId = () => {
  const surveyId = '78f91e52-6028-11ed-82e1-7ac846e3af9d'

  return <SurveySettings surveyId={surveyId} />
}
