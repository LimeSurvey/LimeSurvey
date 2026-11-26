// Import shared mocks
import 'tests/mocks'

import { act, screen, waitFor } from '@testing-library/react'

import { renderWithProviders } from 'tests/testUtils'
import { queryClient } from 'queryClient'
import { STATES } from 'helpers'

import { Select } from './Select'

const options = [
  { label: 'First', value: 'first' },
  { label: 'Second', value: 'second' },
]

const getSelectInput = (testId) =>
  screen.getByTestId(testId).querySelector('input')

describe('Select with activeDisabled', () => {
  test('Should be disabled when activeDisabled is set', async () => {
    await renderWithProviders(
      <Select dataTestId="locked" options={options} activeDisabled={true} />
    )

    expect(getSelectInput('locked')).toBeDisabled()
  })

  test('Should stay enabled without activeDisabled', async () => {
    await renderWithProviders(
      <Select dataTestId="unlocked" options={options} />
    )

    expect(getSelectInput('unlocked')).toBeEnabled()
  })
})

describe('Select with noPermissionDisabled', () => {
  const setHasSurveyUpdatePermission = (value) => {
    act(() => {
      queryClient.setQueryData(
        ['appState', STATES.HAS_SURVEY_UPDATE_PERMISSION],
        value
      )
    })
  }

  afterEach(() => {
    setHasSurveyUpdatePermission(true)
  })

  test('Should be disabled without survey update permission', async () => {
    await renderWithProviders(
      <Select
        dataTestId="no-permission"
        options={options}
        noPermissionDisabled={true}
      />
    )
    setHasSurveyUpdatePermission(false)

    await waitFor(() => expect(getSelectInput('no-permission')).toBeDisabled())
  })

  test('Should stay enabled with survey update permission', async () => {
    await renderWithProviders(
      <Select
        dataTestId="permission"
        options={options}
        noPermissionDisabled={true}
      />
    )

    expect(getSelectInput('permission')).toBeEnabled()
  })
})
