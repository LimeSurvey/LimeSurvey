import { Survey as SurveyComponent } from './Survey'

export default {
  title: 'General/Survey',
  component: SurveyComponent,
}

const surveyId = '78f91e52-6028-11ed-82e1-7ac846e3af9d'

export const Survey = () => {
  return <SurveyComponent id={surveyId} />
}
