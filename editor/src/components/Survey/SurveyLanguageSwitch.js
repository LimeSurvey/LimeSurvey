import { useMemo } from 'react'

import { useAppState } from 'hooks'
import { decodeHTMLEntities, STATES } from 'helpers'

import { Select } from '../UIComponents'
import { SurveyTranslationIcon } from '../icons'

export const SurveyLanguageSwitch = ({ survey, allLanguages }) => {
  const [activeLanguage, setActiveLanguage] = useAppState(
    STATES.ACTIVE_LANGUAGE,
    survey.language
  )

  const languages = useMemo(() => {
    if (!survey.additionalLanguages || survey.additionalLanguages === '') {
      return [survey.language]
    }

    return [survey.language, ...survey.additionalLanguages.split(' ')]
  }, [survey.language, survey.additionalLanguages])

  const getLanguages = (languages) => {
    if (!languages) return []

    return languages.map((language) => {
      return {
        value: language,
        label: decodeHTMLEntities(allLanguages?.[language]?.description),
      }
    })
  }

  const handleLanguageChange = (value) => {
    setActiveLanguage(value)
  }

  const surveyHasAdditionalLanguages = () => {
    // here we check if lang switch is even possible
    return survey.additionalLanguages?.trim().length !== 0
  }

  return (
    surveyHasAdditionalLanguages() && (
      <div className="language-dropdown-section d-flex mt-2">
        <Select
          onChange={({ value }) => handleLanguageChange(value)}
          value={activeLanguage}
          options={getLanguages(languages)}
          className="language-box"
          placeholder=""
        />
        <SurveyTranslationIcon
          className={'text-black fill-current'}
          bgcolor={'#f9f9fb'}
        />
      </div>
    )
  )
}
