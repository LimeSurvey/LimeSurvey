import { PersistQueryClientProvider } from '@tanstack/react-query-persist-client'
import { queryClient, persistOptions } from 'queryClient'
import { STATES } from 'helpers/constants/constants'
import { I18Provider } from 'providers'
import { i18nInstance } from 'i18nInit'
import { MemoryRouter, Route, Routes } from 'react-router-dom'
import { ComponentWrapper } from './ComponentWrapper'

import React, { act } from 'react'
import { render } from '@testing-library/react'
import surveyData from 'helpers/data/survey-detail.json'

const JestWrapWithProviders = ({ children }) => {
  return (
    <MemoryRouter
      future={{ v7_relativeSplatPath: true, v7_startTransition: true }}
      initialEntries={[`/survey/${surveyData.surveyId}/structure/`]}
    >
      <PersistQueryClientProvider
        client={queryClient}
        persistOptions={persistOptions}
      >
        <I18Provider language={'en'} i18n={() => i18nInstance('en')}>
          <Routes>
            <Route
              path="/survey/:surveyId/:panel?/:menu?"
              element={<ComponentWrapper>{children}</ComponentWrapper>}
            />
          </Routes>
        </I18Provider>
      </PersistQueryClientProvider>
    </MemoryRouter>
  )
}

export async function renderWithProviders(ui) {
  await act(async () => {
    render(<JestWrapWithProviders>{ui}</JestWrapWithProviders>)
  })
}

/**
 * Sets an app state value and waits until subscribed components re-rendered.
 * React Query notifies subscribers on a setTimeout(0), so this waits one tick.
 * @param {string} key The app state key, e.g. a value from STATES.
 * @param {*} value The new value.
 * @returns {Promise<void>} Resolves after the re-render.
 */
export const setAppState = (key, value) =>
  act(async () => {
    queryClient.setQueryData(['appState', key], value)
    await new Promise((resolve) => setTimeout(resolve, 0))
  })

/**
 * Sets whether the current user may update the survey.
 * @param {boolean} value Whether the user has survey update permission.
 * @returns {Promise<void>} Resolves after the re-render.
 */
export const setSurveyUpdatePermission = (value) =>
  setAppState(STATES.HAS_SURVEY_UPDATE_PERMISSION, value)
