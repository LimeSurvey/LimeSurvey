import { useLocation, useNavigate } from 'react-router-dom'

import { useAppState } from 'hooks'
import { PAGES, STATES, SURVEY_MENU_TITLES } from 'helpers'
import { getSharingPanels } from 'shared/getSharingPanels'
import { ToggleButtons } from 'components/UIComponents/Buttons/ToggleButtons'
import { useMemo } from 'react'

const isActiveMenuItem = (url, location) => {
  if (!url) {
    return false
  }

  const currentPath = location.pathname
  const cleanURL = url.replace('#', '')

  return currentPath === cleanURL || currentPath.startsWith(`${cleanURL}/`)
}

const EditorIcon = () => <i className="ri-pencil-line" />
const ShareIcon = () => <i className="ri-share-forward-line" />
const ResultsIcon = () => <i className="ri-bar-chart-2-line" />

const PAGE_TO_VALUE = {
  [PAGES.EDITOR]: 'editor',
  [PAGES.SHARE]: 'share',
  [PAGES.RESPONSES]: 'results',
}

export const SurveyNavigation = ({ surveyId }) => {
  const location = useLocation()
  const navigate = useNavigate()
  const [isSurveyActive] = useAppState(STATES.IS_SURVEY_ACTIVE, false)

  const editorUrl = `/${PAGES.EDITOR}/${surveyId}/structure`
  const shareUrl = `/${PAGES.SHARE}/${surveyId}/${getSharingPanels().sharing.panel}/${SURVEY_MENU_TITLES.sharingOverview}`
  const resultsUrl = `/${PAGES.RESPONSES}/${surveyId}`

  const urlMap = {
    editor: editorUrl,
    share: shareUrl,
    results: resultsUrl,
  }

  const currentValue = useMemo(
    () =>
      Object.entries(PAGE_TO_VALUE).find(([page]) =>
        isActiveMenuItem(`/${page}/${surveyId}`, location)
      )?.[1] ?? '',
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
    if (urlMap[value]) {
      navigate(urlMap[value])
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
