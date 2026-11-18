import 'tests/mocks'
import { renderWithProviders, setSurveyUpdatePermission } from 'tests/testUtils'

import { RatingItems } from './RatingItems'

const getSelectInputs = () =>
  document.querySelectorAll('.select-component input')

describe('RatingItems', () => {
  afterEach(async () => {
    await setSurveyUpdatePermission(true)
  })

  test('Should lock both selects without survey update permission', async () => {
    await renderWithProviders(
      <RatingItems update={() => {}} noPermissionDisabled={true} />
    )
    await setSurveyUpdatePermission(false)

    const inputs = getSelectInputs()
    expect(inputs).toHaveLength(2)
    inputs.forEach((input) => expect(input).toBeDisabled())
  })

  test('Should keep both selects editable with survey update permission', async () => {
    await renderWithProviders(
      <RatingItems update={() => {}} noPermissionDisabled={true} />
    )
    await setSurveyUpdatePermission(true)

    const inputs = getSelectInputs()
    expect(inputs).toHaveLength(2)
    inputs.forEach((input) => expect(input).toBeEnabled())
  })
})
