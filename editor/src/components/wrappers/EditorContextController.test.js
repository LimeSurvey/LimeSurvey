import { act, render, screen, waitFor } from '@testing-library/react'
import { QueryClientProvider } from '@tanstack/react-query'
import { MemoryRouter, Route, Routes } from 'react-router-dom'

import 'tests/mocks'
import { queryClient } from 'queryClient'
import { STATES } from 'helpers/constants/constants'
import { PermissionsProvider } from 'providers/PermissionsProvider'
import { Input } from 'components/UIComponents/Input/Input'

import { EditorContextController } from './EditorContextController'

global.t = (key) => key

// The survey API still reports update permission: It must not override PermissionsProvider
const mockSurvey = {
  sid: 123,
  language: 'en',
  languages: ['en'],
  active: false,
  questionGroups: [],
  hasSurveyUpdatePermission: true,
}

jest.mock('hooks/useAuth', () => {
  const useAuth = () => ({ token: 'token', restHeaders: {} })
  return { __esModule: true, useAuth, default: useAuth }
})
jest.mock('hooks/useUserService', () => ({
  useUserService: () => ({
    // Resolves after a tick like a real request: Resolving at once would race
    // the app state's own initial value
    getUserPermissions: () =>
      new Promise((resolve) =>
        setTimeout(
          () =>
            resolve({
              permissions: {
                global: {},
                survey: {},
                effective: { surveyRead: true, surveyUpdate: false },
              },
            }),
          10
        )
      ),
  }),
}))
jest.mock('hooks/useSurvey', () => ({
  useSurvey: () => ({ survey: mockSurvey, update: jest.fn() }),
}))
jest.mock('hooks/useSetAllLanguages', () => ({
  useSetAllLanguages: () => ({ fetchAllLanguages: jest.fn() }),
}))

describe('EditorContextController on the survey route', () => {
  afterEach(() => {
    queryClient.clear()
  })

  test('keeps a read-only survey locked after the survey data loaded', async () => {
    // Set by the app before the editor loads
    queryClient.setQueryData(['appState', STATES.USER_DETAIL], { lang: 'en' })

    act(() => {
      render(
        <MemoryRouter initialEntries={['/survey/123']}>
          <QueryClientProvider client={queryClient}>
            <Routes>
              <Route
                path="/survey/:surveyId/:panel?/:menu?"
                element={
                  <PermissionsProvider>
                    <EditorContextController>
                      <Input dataTestId="locked-input" noPermissionDisabled />
                    </EditorContextController>
                  </PermissionsProvider>
                }
              />
            </Routes>
          </QueryClientProvider>
        </MemoryRouter>
      )
    })

    const input = await screen.findByTestId('locked-input')

    await waitFor(() => expect(input).toBeDisabled())
    expect(
      queryClient.getQueryData([
        'appState',
        STATES.HAS_SURVEY_UPDATE_PERMISSION,
      ])
    ).toBe(false)
  })
})
