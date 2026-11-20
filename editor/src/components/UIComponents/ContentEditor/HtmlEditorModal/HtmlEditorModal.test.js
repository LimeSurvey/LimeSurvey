import React from 'react'
import { render, screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'

import { HtmlEditorModal } from './HtmlEditorModal'

const HTML = '<p class="intro">Hello</p>'
const IMAGE_PATH = '/limesurvey/upload/surveys/123/images/photo.png'

const OriginalImage = global.Image

const renderEditor = () => {
  const fileService = {
    uploadContentImage: jest.fn(() =>
      Promise.resolve({
        success: true,
        uploaded: {
          filePath: IMAGE_PATH,
          previewUrl: `http://localhost${IMAGE_PATH}`,
        },
      })
    ),
  }
  const onSave = jest.fn()
  const onHide = jest.fn()
  render(
    <HtmlEditorModal
      show
      value={HTML}
      onSave={onSave}
      onHide={onHide}
      fileService={fileService}
    />
  )
  return { onSave, onHide }
}

beforeAll(() => {
  global.t = (text) => text
  // CodeMirror measures text, jsdom has no layout
  const emptyRect = {
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    width: 0,
    height: 0,
  }
  Range.prototype.getClientRects = () => []
  Range.prototype.getBoundingClientRect = () => emptyRect
  // jsdom never loads images
  global.Image = class {
    set src(url) {
      setTimeout(() => {
        this.naturalWidth = 40
        this.naturalHeight = 20
        this.onload()
      })
    }
  }
})

afterAll(() => {
  global.Image = OriginalImage
})

test('saves the edited HTML', async () => {
  const user = userEvent.setup()
  const { onSave } = renderEditor()

  await user.click(screen.getByTestId('html-editor-save'))

  expect(onSave).toHaveBeenCalledWith(HTML)
})

test('inserts an image at the cursor, outside of tags', async () => {
  const user = userEvent.setup({ applyAccept: false })
  const { onSave } = renderEditor()

  await user.click(screen.getByTestId('html-editor-insert-image'))
  await user.upload(
    await screen.findByTestId('select-image-file-input'),
    new File(['image'], 'photo.png', { type: 'image/png' })
  )
  await user.click(await screen.findByLabelText('This image is decorative'))
  await user.click(screen.getByTestId('select-image-insert'))

  await waitFor(() =>
    expect(screen.queryByTestId('select-image-modal')).toBeNull()
  )
  await user.click(screen.getByTestId('html-editor-save'))
  // The cursor was at the start of the text
  expect(onSave).toHaveBeenCalledWith(
    `<img src="${IMAGE_PATH}" alt="" width="40" height="20" class="img-fluid">` +
      HTML
  )
})

test('Escape closes only the image dialog and keeps the HTML', async () => {
  const user = userEvent.setup()
  const { onSave, onHide } = renderEditor()

  await user.click(screen.getByTestId('html-editor-insert-image'))
  await screen.findByTestId('select-image-modal')
  await user.keyboard('{Escape}')

  await waitFor(() =>
    expect(screen.queryByTestId('select-image-modal')).toBeNull()
  )
  expect(onHide).not.toHaveBeenCalled()
  expect(screen.getByTestId('html-editor-modal')).toBeInTheDocument()
  await user.click(screen.getByTestId('html-editor-save'))
  expect(onSave).toHaveBeenCalledWith(HTML)
})

test('has no "Insert image" button without a file service', () => {
  render(<HtmlEditorModal show value={HTML} />)

  expect(screen.queryByTestId('html-editor-insert-image')).toBeNull()
})
