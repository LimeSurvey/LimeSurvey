import { screen } from '@testing-library/react'

import 'tests/mocks'
import { renderWithProviders, setAppState } from 'tests/testUtils'

import { STATES } from 'helpers'

import { FormCheck } from './FormCheck'

describe('FormCheck activeDisabled', () => {
  afterEach(async () => {
    await setAppState(STATES.IS_SURVEY_ACTIVE, false)
  })

  // Callers combine activeDisabled with the survey's active state before
  // passing it (see SettingItem and Setting), so FormCheck must not check the
  // active state again. The survey is set to inactive on purpose: the field
  // must still be locked, which proves FormCheck trusts the prop as is.
  test('Should be disabled when activeDisabled is set, even on an inactive survey', async () => {
    await renderWithProviders(
      <FormCheck
        id="locked-check"
        type="checkbox"
        label="Locked"
        activeDisabled={true}
      />
    )
    await setAppState(STATES.IS_SURVEY_ACTIVE, false)

    expect(screen.getByRole('checkbox', { name: 'Locked' })).toBeDisabled()
  })

  test('Should be enabled when activeDisabled is not set', async () => {
    await renderWithProviders(
      <FormCheck id="open-check" type="checkbox" label="Open" />
    )

    expect(screen.getByRole('checkbox', { name: 'Open' })).toBeEnabled()
  })
})
