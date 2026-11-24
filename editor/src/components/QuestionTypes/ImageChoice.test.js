import 'tests/mocks'

import { screen } from '@testing-library/react'
import { renderWithProviders } from 'tests/testUtils'

import { ImageChoice } from './ImageChoice'

/**
 * Renders an image option in edit mode.
 *
 * @param {Object} props props of ImageChoice to add or change
 * @returns {Promise<void>}
 */
const renderOption = (props = {}) =>
  renderWithProviders(
    <ImageChoice
      id={11}
      idPrefix="a"
      index={0}
      code="A1"
      inputType="radio"
      isFocused={true}
      value="/upload/surveys/1/images/lime.png"
      {...props}
    />
  )

describe('ImageChoice', () => {
  test('Has an edit button when the image settings can be saved', async () => {
    await renderOption({ onImageSettingsSave: jest.fn() })

    expect(
      screen.getByRole('button', { name: 'Edit image A1', hidden: true })
    ).toBeInTheDocument()
  })

  test('Has no edit button without a way to save the image settings', async () => {
    await renderOption()

    expect(
      screen.queryByRole('button', { name: 'Edit image A1', hidden: true })
    ).not.toBeInTheDocument()
    expect(
      screen.getByRole('button', { name: 'Delete image A1', hidden: true })
    ).toBeInTheDocument()
  })
})
