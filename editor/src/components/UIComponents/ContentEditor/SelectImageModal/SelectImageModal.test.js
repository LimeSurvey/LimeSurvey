import React from 'react'
import { render, screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'

import { SelectImageModal } from './SelectImageModal'

const UPLOADED = {
  success: true,
  uploaded: {
    filePath: '/limesurvey/upload/surveys/123/images/photo.png',
    previewUrl:
      'http://localhost/limesurvey/upload/surveys/123/images/photo.png',
  },
}

const OriginalImage = global.Image

/**
 * Replaces the browser's Image, which jsdom never loads, by one that loads
 * with the given size, or fails without a size.
 *
 * @param {number} naturalWidth width of the loaded image
 * @param {number} naturalHeight height of the loaded image
 * @returns {void}
 */
const mockImageSize = (naturalWidth, naturalHeight) => {
  global.Image = class {
    set src(url) {
      setTimeout(() => {
        if (naturalWidth) {
          this.naturalWidth = naturalWidth
          this.naturalHeight = naturalHeight
          this.onload()
        } else {
          this.onerror()
        }
      })
    }
  }
}

const renderModal = (uploadResult = UPLOADED) => {
  const fileService = {
    uploadContentImage: jest.fn(() => Promise.resolve(uploadResult)),
  }
  const onInsert = jest.fn()
  render(
    <SelectImageModal show fileService={fileService} onInsert={onInsert} />
  )
  return { fileService, onInsert }
}

const uploadFile = async (name = 'photo.png', type = 'image/png') => {
  const user = userEvent.setup({ applyAccept: false })
  await user.upload(
    screen.getByTestId('select-image-file-input'),
    new File(['image'], name, { type })
  )
  return user
}

const getInsertButton = () => screen.getByTestId('select-image-insert')

beforeAll(() => {
  global.t = (text) => text
})

beforeEach(() => {
  mockImageSize(1600, 1200)
})

afterAll(() => {
  global.Image = OriginalImage
})

test('starts with all image settings and Insert disabled', () => {
  renderModal()

  expect(
    screen.getByRole('dialog', { name: 'Select image' })
  ).toBeInTheDocument()
  expect(screen.getByTestId('select-image-width')).toBeDisabled()
  expect(screen.getByTestId('select-image-height')).toBeDisabled()
  expect(screen.getByTestId('select-image-ratio')).toBeDisabled()
  expect(screen.getByTestId('select-image-alt-text')).toBeDisabled()
  expect(screen.getByLabelText('This image is decorative')).toBeDisabled()
  expect(getInsertButton()).toBeDisabled()
  expect(screen.queryByAltText('Preview of the uploaded image')).toBeNull()
})

test('inserts an uploaded image with its alt text and a reduced size', async () => {
  const { fileService, onInsert } = renderModal()

  const user = await uploadFile()

  expect(
    await screen.findByAltText('Preview of the uploaded image')
  ).toBeInTheDocument()
  expect(fileService.uploadContentImage).toHaveBeenCalledTimes(1)
  // 1600 x 1200 is reduced to the maximum of 1200 px, keeping the ratio
  expect(screen.getByTestId('select-image-width')).toHaveValue(1200)
  expect(screen.getByTestId('select-image-height')).toHaveValue(900)
  expect(getInsertButton()).toBeDisabled()

  await user.type(
    screen.getByTestId('select-image-alt-text'),
    'A flying elephant'
  )
  expect(getInsertButton()).toBeEnabled()
  await user.click(getInsertButton())

  expect(onInsert).toHaveBeenCalledWith(
    '<img src="/limesurvey/upload/surveys/123/images/photo.png"' +
      ' alt="A flying elephant" width="1200" height="900" class="img-fluid">'
  )
})

test('keeps the aspect ratio only while it is locked', async () => {
  mockImageSize(400, 200)
  renderModal()
  const user = await uploadFile()
  const width = await screen.findByTestId('select-image-width')
  const height = screen.getByTestId('select-image-height')

  await user.clear(width)
  await user.type(width, '300')
  expect(height).toHaveValue(150)

  await user.click(screen.getByRole('button', { name: 'Unlock aspect ratio' }))
  await user.clear(width)
  await user.type(width, '100')
  expect(height).toHaveValue(150)

  // Locking again keeps the current ratio of 100 x 150
  await user.click(screen.getByRole('button', { name: 'Lock aspect ratio' }))
  await user.clear(height)
  await user.type(height, '300')
  expect(width).toHaveValue(200)
})

test('blocks Insert for invalid sizes', async () => {
  renderModal()
  const user = await uploadFile()
  await user.click(await screen.findByLabelText('This image is decorative'))
  await user.click(screen.getByRole('button', { name: 'Unlock aspect ratio' }))

  const width = screen.getByTestId('select-image-width')
  await user.clear(width)
  await user.type(width, '0')

  expect(width).toHaveAttribute('aria-invalid', 'true')
  expect(
    screen.getByText('Enter a whole number of pixels greater than 0.')
  ).toBeInTheDocument()
  expect(getInsertButton()).toBeDisabled()
})

test('inserts a decorative image with an empty alt text', async () => {
  const { onInsert } = renderModal()
  const user = await uploadFile()
  const altText = await screen.findByTestId('select-image-alt-text')
  await user.type(altText, 'Ignored')

  await user.click(screen.getByLabelText('This image is decorative'))

  expect(altText).toBeDisabled()
  expect(altText).toHaveValue('')
  expect(
    screen.getByText('Alternative text is not required for decorative images.')
  ).toBeInTheDocument()
  await user.click(getInsertButton())
  expect(onInsert.mock.calls[0][0]).toContain(' alt="" ')
})

test('shows the reason when the server rejects the image', async () => {
  renderModal({
    success: false,
    uploadResultMessage: 'This file is not a valid PNG, JPEG or WebP image.',
  })

  await uploadFile()

  expect(await screen.findByRole('alert')).toHaveTextContent(
    'This file is not a valid PNG, JPEG or WebP image.'
  )
  expect(screen.getByTestId('select-image-width')).toBeDisabled()
  expect(getInsertButton()).toBeDisabled()
})

test('refuses other file types without uploading them', async () => {
  const { fileService } = renderModal()

  await uploadFile('animation.gif', 'image/gif')

  expect(await screen.findByRole('alert')).toHaveTextContent(
    'This file type is not supported. Choose a PNG, JPEG or WebP image.'
  )
  expect(fileService.uploadContentImage).not.toHaveBeenCalled()
})

test('reports an image that can not be loaded', async () => {
  mockImageSize(0, 0)
  renderModal()

  await uploadFile()

  expect(await screen.findByRole('alert')).toHaveTextContent(
    'The image could not be loaded. It may be damaged.'
  )
  expect(getInsertButton()).toBeDisabled()
})

test('removing the image clears the settings', async () => {
  renderModal()
  const user = await uploadFile()

  await user.click(await screen.findByRole('button', { name: 'Remove image' }))

  await waitFor(() =>
    expect(screen.queryByAltText('Preview of the uploaded image')).toBeNull()
  )
  expect(screen.getByTestId('select-image-width')).toBeDisabled()
  expect(screen.getByTestId('select-image-width')).toHaveValue(null)
  expect(getInsertButton()).toBeDisabled()
})
