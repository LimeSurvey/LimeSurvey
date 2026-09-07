import { useMemo } from 'react'

import { useAppState } from 'hooks'
import { decodeHTMLEntities, STATES } from 'helpers'

import { Dropdown } from '../UIComponents'
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

  const languageOptions = useMemo(() => {
    if (!languages) return []

    return languages.map((language) => {
      return {
        value: language,
        label: decodeHTMLEntities(allLanguages?.[language]?.description),
      }
    })
  }, [languages, allLanguages])

  const activeLanguageLabel = useMemo(() => {
    return (
      languageOptions.find((language) => language.value === activeLanguage)
        ?.label || ''
    )
  }, [languageOptions, activeLanguage])

  const dropdownMenuItems = useMemo(() => {
    return languageOptions.map((language) => ({
      type: 'item',
      label: language.label,
      checked: language.value === activeLanguage,
      onClick: (event) => {
        event.preventDefault()
        setActiveLanguage(language.value)
      },
    }))
  }, [languageOptions, activeLanguage, setActiveLanguage])

  const surveyHasAdditionalLanguages = () => {
    // here we check if lang switch is even possible
    return survey.additionalLanguages?.trim().length !== 0
  }

  return (
    surveyHasAdditionalLanguages() && (
      <div className="language-dropdown-section language-box d-flex mt-2">
        <Dropdown
          menuItems={dropdownMenuItems}
          toggleSettings={{
            title: activeLanguageLabel,
            iconClassName: 'ri-arrow-down-s-line',
            variant: 'light',
            id: `survey-language-switch-${survey.sid || survey.id || 'default'}`,
          }}
        />
        <SurveyTranslationIcon
          className={'text-black fill-current'}
          bgcolor={'#f9f9fb'}
        />
      </div>
    )
  )
}
