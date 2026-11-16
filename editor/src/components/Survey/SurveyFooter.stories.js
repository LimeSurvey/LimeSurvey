import { SurveyFooter } from './SurveyFooter'

export default {
  title: 'General/Survey/Footer',
  component: SurveyFooter,
}

export const Footer = ({ survey, update }) => {
  return (
    <SurveyFooter
      language="en"
      update={(languageSettings) => update(languageSettings)}
      isEmpty={!survey.questionGroups?.length}
      survey={survey}
    />
  )
}
