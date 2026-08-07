import { SurveyHeader } from './SurveyHeader'

export default {
  title: 'General/Survey/Header',
  component: SurveyHeader,
}

export const Header = ({ survey, update }) => {
  const allLanguages = {
    en: {
      description: 'English',
      nativedescription: 'English',
    },
  }

  return (
    <SurveyHeader
      update={(updated) => update(updated)}
      survey={survey}
      allLanguages={allLanguages}
      activeLanguage="en"
    />
  )
}
