import React, { useEffect, useMemo } from 'react'
import { useNavigate, useParams } from 'react-router-dom'

import { SideBar } from 'components/SideBar'
import { useSurvey } from 'hooks'

import { getResponsesPanels, panelOptions } from './getResponsesPanels'

export const LeftSideBar = ({ surveyId }) => {
  const navigate = useNavigate()
  const { panel } = useParams()

  const { survey = {} } = useSurvey(surveyId)

  const handlePanelChange = (panelKey) => {
    const panelInfo = getResponsesPanels()[panelKey]

    if (panelInfo.panel === panel) {
      return
    }

    if (panelInfo.getRedirectUrl && !process.env.STORYBOOK_DEV) {
      window.open(panelInfo.getRedirectUrl(survey.sid), '_self')
    } else {
      navigate(
        `/responses/${survey.sid}/${panelInfo.panel}/${panelInfo.defaultMenu || ''}`
      )
    }
  }

  const items = useMemo(() => {
    const items = []

    const panelComponents = {}

    Object.keys(getResponsesPanels()).forEach((panelKey) => {
      const panelInfo = getResponsesPanels()[panelKey]

      items.push({
        icon: panelInfo.icon,
        label: panelInfo.label,
        tip: panelInfo.tip,
        iconEvent: panelInfo.panel,
        panel: panelKey,
        panelItems: panelOptions()[panelKey],
        panelComponent: panelComponents[panelKey] || [],
        onIconClickCallback: () => {
          handlePanelChange(panelInfo)
        },
        panelEvent: panelInfo.panel,
        onPanelClickCallback: () => {
          handlePanelChange(panelInfo)
        },
      })
    })

    return items
  }, [])

  useEffect(() => {
    if (panel) {
      return
    }

    const panelInfo = getResponsesPanels()[getResponsesPanels().results.panel]
    navigate(
      `/responses/${survey.sid}/${panelInfo.panel}/${panelInfo.defaultMenu || ''}`
    )
  }, [panel])

  return (
    <SideBar
      testId="left-sidebar"
      items={items}
      showFeedbackButton={true}
      onIconClick={(panel) => handlePanelChange(panel)}
      page="responses"
      sidebarClassName="med14-c"
      showCloseButton={false}
    />
  )
}
