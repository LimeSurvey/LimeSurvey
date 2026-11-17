import 'tests/mocks'

import { act } from 'react'
import { render, screen } from '@testing-library/react'
import { renderWithProviders } from 'tests/testUtils'
import { queryClient } from 'queryClient'
import { STATES } from 'helpers'

import { Select } from './Select'

const options = [
  { label: 'One', value: '1' },
  { label: 'Two', value: '2' },
]

// React Query notifies subscribers on a setTimeout(0), so wait a tick for the re-render
const setSurveyUpdatePermission = (value) =>
  act(async () => {
    queryClient.setQueryData(
      ['appState', STATES.HAS_SURVEY_UPDATE_PERMISSION],
      value
    )
    await new Promise((resolve) => setTimeout(resolve, 0))
  })

const getSelectInput = (testId) =>
  screen.getByTestId(testId).querySelector('input')

describe('Select disabled states', () => {
  afterEach(async () => {
    await setSurveyUpdatePermission(true)
  })

  test('is disabled when activeDisabled is set', async () => {
    await renderWithProviders(
      <Select dataTestId="locked-select" options={options} activeDisabled />
    )

    expect(getSelectInput('locked-select')).toBeDisabled()
  })

  test('is disabled without survey update permission', async () => {
    await renderWithProviders(
      <Select
        dataTestId="locked-select"
        options={options}
        noPermissionDisabled
      />
    )
    await setSurveyUpdatePermission(false)

    expect(getSelectInput('locked-select')).toBeDisabled()
  })

  test('stays enabled with survey update permission', async () => {
    await renderWithProviders(
      <Select
        dataTestId="locked-select"
        options={options}
        noPermissionDisabled
      />
    )
    await setSurveyUpdatePermission(true)

    expect(getSelectInput('locked-select')).toBeEnabled()
  })

  test('renders outside a QueryClientProvider', () => {
    render(
      <Select
        dataTestId="plain-select"
        options={options}
        noPermissionDisabled
      />
    )

    expect(getSelectInput('plain-select')).toBeEnabled()
  })
})
