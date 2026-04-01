import { EndScreenSettings as EndScreenSettingsComponent } from './EndScreenSettings'

export default {
  title: 'EndScreenSettings',
  component: EndScreenSettingsComponent,
}

export const EndScreenSettings = () => {
  const surveyId = '78f91e52-6028-11ed-82e1-7ac846e3af9d'
  return <EndScreenSettingsComponent surveyId={surveyId} />
}
