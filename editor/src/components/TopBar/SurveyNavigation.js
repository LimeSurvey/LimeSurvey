import { useLocation } from 'react-router-dom'

import { useAppState } from 'hooks'
import { PAGES, STATES, SURVEY_MENU_TITLES } from 'helpers'
import { getSharingPanels } from 'shared/getSharingPanels'
import { ToggleButtons } from 'components/UIComponents/Buttons/ToggleButtons'

const isActiveMenuItem = (url, location) => {
  if (!url) return false
  const currentPath = (window.location.hash || location.pathname || '').replace(
    '#',
    ''
  )
  const cleanURL = url.replace('#', '')
  return currentPath.startsWith(cleanURL)
}

const EditorIcon = () => <i className="ri-pencil-line" />
const ShareIcon = () => <i className="ri-share-forward-line" />
const ResultsIcon = () => <i className="ri-bar-chart-2-line" />

export const SurveyNavigation = ({ surveyId }) => {
  const location = useLocation()
  const [isSurveyActive] = useAppState(STATES.IS_SURVEY_ACTIVE, false)

  const editorUrl = `#/${PAGES.EDITOR}/${surveyId}/structure`
  const shareUrl = `#/${PAGES.SHARE}/${surveyId}/${getSharingPanels().sharing.panel}/${SURVEY_MENU_TITLES.sharingOverview}`
  const resultsUrl = `#/${PAGES.RESPONSES}/${surveyId}`

  const urlMap = {
    editor: editorUrl,
    share: shareUrl,
    results: resultsUrl,
  }

  const getCurrentValue = () => {
    if (isActiveMenuItem(`/${PAGES.EDITOR}/${surveyId}`, location))
      return 'editor'
    if (isActiveMenuItem(`/${PAGES.SHARE}/${surveyId}`, location))
      return 'share'
    if (isActiveMenuItem(`/${PAGES.RESPONSES}/${surveyId}`, location))
      return 'results'
    return ''
  }

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
      window.location.href = urlMap[value]
    }
  }

  return (
    <div className="survey-navigation ">
      <ToggleButtons
        id="survey-navigation"
        name="survey-navigation"
        toggleOptions={toggleOptions}
        value={getCurrentValue()}
        update={handleNavigate}
      />
    </div>
  )
}
