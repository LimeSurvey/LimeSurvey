import 'tests/mocks'

import { fireEvent, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { renderWithProviders } from 'tests/testUtils'

import { ImageChoice } from './ImageChoice'

const IMAGE_URL = '/upload/surveys/1/images/lime.png'

// URL of the uploaded image, set by the test.
let mockUploadedUrl
const mockFileService = {
  uploadSurveyImage: jest.fn(async () => ({
    success: true,
    uploaded: { filePath: IMAGE_URL, previewUrl: mockUploadedUrl },
  })),
}
jest.mock('hooks/useFileService', () => ({
  useFileService: () => ({ fileService: mockFileService }),
}))

// jsdom does not implement URL.createObjectURL / revokeObjectURL
beforeAll(() => {
  global.URL.createObjectURL = jest.fn(() => 'blob:mock-url')
  global.URL.revokeObjectURL = jest.fn()
})

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
      value={IMAGE_URL}
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

  test('Shows the placeholder in view mode when the image is not found', async () => {
    await renderOption({ isFocused: false })

    // The first image is the one shown in view mode, the second one belongs
    // to the hidden upload area.
    fireEvent.error(screen.getAllByAltText('A1')[0])

    // The no-image-found placeholder, stubbed in the tests, with an alt text
    // telling screen reader users that the image is missing.
    expect(screen.getByAltText('No image found for A1')).toHaveAttribute(
      'src',
      'test-file-stub'
    )
  })

  test('Shows a missing image again in view mode when a file with the same name is uploaded', async () => {
    await renderOption({ isFocused: false, update: jest.fn() })
    const viewImage = screen.getAllByAltText('A1')[0]
    // The server answers with the URL of the missing image.
    mockUploadedUrl = viewImage.getAttribute('src')
    fireEvent.error(viewImage)
    expect(viewImage).toHaveAttribute('src', 'test-file-stub')

    const input = screen.getByTestId('dropzone-a11-i0').querySelector('input')
    await userEvent.upload(
      input,
      new File(['image'], 'lime.png', { type: 'image/png' })
    )

    expect(mockFileService.uploadSurveyImage).toHaveBeenCalledTimes(1)
    expect(viewImage).toHaveAttribute('src', mockUploadedUrl)
  })
})
