import React from 'react'
import { render, screen } from '@testing-library/react'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'

import surveyData from 'helpers/data/survey-detail.json'
import pluginManager from 'plugins/PluginManager'
import { PLUGIN_SLOTS } from 'plugins/slots'

import { PublicSurveyAlias } from './PublicSurveyAlias'

global.t = (key) => key

describe('PublicSurveyAlias - alias card actions slot', () => {
  const survey = surveyData.survey
  const PluginAction = jest.fn(() => <button>Plugin action</button>)

  const renderComponent = (props = {}) =>
    render(
      <QueryClientProvider client={new QueryClient()}>
        <PublicSurveyAlias
          survey={survey}
          update={jest.fn()}
          language="en"
          setLink={jest.fn()}
          currentSurveyAccessMode={survey.access_mode}
          createBufferOperation={jest.fn()}
          addToBuffer={jest.fn()}
          {...props}
        />
      </QueryClientProvider>
    )

  beforeEach(() => {
    PluginAction.mockClear()
    jest.spyOn(pluginManager, 'hasPlugin').mockReturnValue(true)
    jest.spyOn(pluginManager, 'getPlugin').mockReturnValue(PluginAction)
  })

  afterEach(() => {
    jest.restoreAllMocks()
  })

  it('passes the documented props to the plugin', () => {
    renderComponent({ showPluginActions: true })

    expect(pluginManager.hasPlugin).toHaveBeenCalledWith(
      PLUGIN_SLOTS.SHARING_OVERVIEW_ALIAS_CARD_ACTIONS
    )
    expect(screen.getByText('Plugin action')).toBeInTheDocument()
    const props = PluginAction.mock.calls[0][0]
    expect(Object.keys(props).sort()).toEqual([
      'alias',
      'hasUpdatePermission',
      'link',
      'selectedLanguage',
      'surveyId',
    ])
    expect(props).toMatchObject({
      surveyId: survey.sid,
      selectedLanguage: 'en',
    })
    expect(typeof props.link).toBe('string')
    expect(typeof props.alias).toBe('string')
  })

  it('does not render the slot without showPluginActions', () => {
    renderComponent()

    expect(screen.queryByText('Plugin action')).not.toBeInTheDocument()
  })

  it('does not render the slot when the card is not editable', () => {
    renderComponent({ showPluginActions: true, editable: false })

    expect(screen.queryByText('Plugin action')).not.toBeInTheDocument()
  })

  it('renders only the customize button when no plugin is registered', () => {
    pluginManager.hasPlugin.mockReturnValue(false)
    renderComponent({ showPluginActions: true })

    expect(screen.getByText('Customize link')).toBeInTheDocument()
    expect(screen.queryByText('Plugin action')).not.toBeInTheDocument()
  })
})
