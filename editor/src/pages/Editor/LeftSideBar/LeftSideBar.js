import React, { useMemo, useCallback } from 'react'
import { useNavigate, useParams } from 'react-router-dom'

import { SideBar } from 'components/SideBar'
import { SurveyStructure } from 'components/SurveyStructure'
import { useSurvey } from 'hooks'
import { getSurveyPanels } from 'helpers/options'

import {
  menuOptions,
  presentationOptions,
  settingsOptions,
} from 'components/SurveySettings/panelOptions'
import { PAGES } from 'helpers'
import { getSharingPanels } from 'shared/getSharingPanels'
import { getSharingPanelOptions } from 'shared/sharingPanelOptions'

export const LeftSideBar = ({
  surveyId,
  page = PAGES.EDITOR,
  navigatePage = null,
  showSidebarCloseButton = true,
}) => {
  const { panel } = useParams()
  const navigate = useNavigate()

  const { survey = {}, surveyMenus = {} } = useSurvey(surveyId)

  const menuEntries = useMemo(() => {
    const menuKeys = Object.keys(surveyMenus)

    // groupedMenuEntries as an array of arrays => [[ 'generalsettings', 'translations', 'permissions' ], ['structure', 'elements', 'questions' ]]
    const groupedMenuEntries = menuKeys.map((key) => {
      return Object.keys(surveyMenus[key].entries).map((entryKey) => {
        return {
          entryKey,
          menuTitle: surveyMenus[key].entries[entryKey].menuTitle,
        }
      })
    })

    /**
     * change the groupedMenuEntries into an object for easier access
     * {
     *  generalsettings: { menuTitle: 'General Settings' },
     *  translations: { menuTitle: 'Translations' },
     *  ...
     * }
     */
    return [].concat(...groupedMenuEntries)
  }, [surveyMenus]).reduce((acc, item) => {
    acc[item.entryKey] = {
      menuTitle: item.menuTitle,
    }
    return acc
  }, {})

  const panels = page !== PAGES.SHARE ? getSurveyPanels() : getSharingPanels()

  const handlePanelNavigation = useCallback(
    (panelInfo) => {
      if (panelInfo.panel === panel) return

      if (panelInfo.getRedirectUrl && !process.env.STORYBOOK_DEV) {
        window.open(panelInfo.getRedirectUrl(survey.sid), '_self')
      } else {
        const pageToNavigateTo = navigatePage || page
        const url = `/${pageToNavigateTo}/${survey.sid}/${panelInfo.panel}/${panelInfo.defaultMenu || ''}`
        navigate(url)
      }
    },
    [panel, survey.sid, navigate]
  )

  const handlePanelChange = useCallback(
    (panelKey) => {
      const panelInfo = panels[panelKey]
      handlePanelNavigation(panelInfo)
    },
    [panels, handlePanelNavigation]
  )

  const panelOptions = useMemo(() => {
    const isSharing = page === PAGES.SHARE

    if (isSharing) {
      return getSharingPanelOptions(surveyId)
    }

    return {
      [panels.presentation?.panel]: presentationOptions(surveyId, menuEntries),
      [panels.settings?.panel]: settingsOptions(surveyId, menuEntries),
      [panels.menu?.panel]: menuOptions(surveyId, menuEntries, survey.active),
    }
  }, [page, panels, surveyId, menuEntries, survey.active])

  const panelComponents = {
    [getSurveyPanels().structure.panel]: <SurveyStructure />,
  }

  const sidebarItems = useMemo(() => {
    return Object.entries(panels).map(([panelKey, panelInfo]) => {
      const handleIconClick = () => handlePanelNavigation(panelInfo)
      const handlePanelClick = () => handlePanelNavigation(panelInfo)
      const handleSidebarClose = () => navigate(`/survey/${surveyId}`)

      return {
        icon: panelInfo.icon,
        label: panelInfo.label,
        tip: panelInfo.tip,
        iconEvent: panelInfo.panel,
        panel: panelKey,
        panelItems: panelOptions[panelKey] || [],
        panelComponent: panelComponents[panelKey] || [],
        onIconClickCallback: handleIconClick,
        panelEvent: panelInfo.panel,
        onPanelClickCallback: handlePanelClick,
        onSidebarClose: handleSidebarClose,
      }
    })
  }, [
    panels,
    panelOptions,
    panelComponents,
    handlePanelNavigation,
    navigate,
    surveyId,
  ])

  return (
    <SideBar
      testId="left-sidebar"
      items={sidebarItems}
      showFeedbackButton={true}
      onIconClick={handlePanelChange}
      page={page}
      showCloseButton={showSidebarCloseButton}
    />
  )
}
