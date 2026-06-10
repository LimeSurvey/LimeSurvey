import { PersistQueryClientProvider } from '@tanstack/react-query-persist-client'
import { queryClient, persistOptions } from 'queryClient'
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
