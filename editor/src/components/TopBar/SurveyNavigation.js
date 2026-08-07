import { useLocation, useNavigate } from 'react-router-dom'
import { useMemo } from 'react'

import { useAppState } from 'hooks'
import { PAGES, STATES, SURVEY_MENU_TITLES } from 'helpers'
import { getSharingPanels } from 'shared/getSharingPanels'
import { ToggleButtons } from 'components/UIComponents/Buttons/ToggleButtons'

const isActiveMenuItem = (url, location) => {
  if (!url) {
    return false
  }

  const currentPath = location.pathname

  return currentPath === url || currentPath.startsWith(`${url}/`)
}

const EditorIcon = () => <i className="ri-pencil-line" />
const ShareIcon = () => <i className="ri-share-forward-line" />
const ResultsIcon = () => <i className="ri-bar-chart-2-line" />

const NAV_CONFIG = [
  {
    page: PAGES.EDITOR,
    value: 'editor',
    getUrl: (surveyId) => `/${PAGES.EDITOR}/${surveyId}/structure`,
  },
  {
    page: PAGES.SHARE,
    value: 'share',
    getUrl: (surveyId) =>
      `/${PAGES.SHARE}/${surveyId}/${getSharingPanels().sharing.panel}/${SURVEY_MENU_TITLES.sharingOverview}`,
  },
  {
    page: PAGES.RESPONSES,
    value: 'results',
    getUrl: (surveyId) => `/${PAGES.RESPONSES}/${surveyId}`,
  },
]

export const SurveyNavigation = ({ surveyId }) => {
  const location = useLocation()
  const navigate = useNavigate()
  const [isSurveyActive] = useAppState(STATES.IS_SURVEY_ACTIVE, false)

  const currentValue = useMemo(
    () =>
      NAV_CONFIG.find(({ page }) =>
        isActiveMenuItem(`/${page}/${surveyId}`, location)
      )?.value ?? '',
    [location.pathname, surveyId]
  )

  const toggleOptions = [
    { name: t('Editor'), value: 'editor', icon: EditorIcon },
    { name: t('Share'), value: 'share', icon: ShareIcon },
    {
      name: t('Results'),
      value: 'results',
      icon: ResultsIcon,
      disabled: !isSurveyActive,
    },
  ]

  const handleNavigate = (value) => {
    const config = NAV_CONFIG.find((_config) => _config.value === value)

    if (config) {
      navigate(config.getUrl(surveyId))
    }
  }

  return (
    <div className="survey-navigation">
      <ToggleButtons
        id="survey-navigation"
        name="survey-navigation"
        toggleOptions={toggleOptions}
        value={currentValue}
        update={handleNavigate}
      />
    </div>
  )
}
