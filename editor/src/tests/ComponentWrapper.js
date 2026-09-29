import { useNavigate } from 'react-router-dom'
import React, { useEffect, cloneElement } from 'react'

import { STATES } from 'helpers'
import { useAppState, useSurvey } from 'hooks'

import { languages } from './constants'

export const ComponentWrapper = ({ children }) => {
  const { survey, update } = useSurvey()
  const navigate = useNavigate()
  const [, setUserDetails] = useAppState(STATES.USER_DETAIL)

  const [, setActiveLanguage] = useAppState(STATES.ACTIVE_LANGUAGE, 'en')

  const [siteSettings = {}, setSiteSettings] = useAppState(
    STATES.SITE_SETTINGS,
    { siteName: 'LimeSurvey', timezone: 'UTC', languages }
  )
  const [, setHasSurveyUpdatePermission] = useAppState(
    STATES.HAS_SURVEY_UPDATE_PERMISSION,
    true
  )
  const [, setSurveyGroups] = useAppState(STATES.SURVEY_GROUPS, {})

  const [allLanguages, setAllAvailableLanguages] = useAppState(
    STATES.ALL_AVAILABLE_LANGUAGES,
    {
      en: {
        en: {
          description: 'English',
          nativedescription: 'English',
        },
      },
    }
  )

  useEffect(() => {
    setHasSurveyUpdatePermission(true)
    setSiteSettings({ siteName: 'LimeSurvey', timezone: 'UTC', languages })
    setSurveyGroups({})
    setActiveLanguage('en')
    setAllAvailableLanguages({
      en: {
        en: {
          description: 'English',
          nativedescription: 'English',
        },
      },
    })
    setUserDetails({ lang: 'en' })
  }, [])

  if (survey?.sid === undefined || siteSettings.siteName === undefined) {
    return <span>Loading data...</span>
  }

  return (
    <span data-testid="component-wrapper">
      {cloneElement(children, {
        survey,
        update,
        navigate,
        allLanguages,
        siteSettings,
      })}
    </span>
  )
}
