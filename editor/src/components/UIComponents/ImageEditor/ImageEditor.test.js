// Import shared mocks
import 'tests/mocks'

import React from 'react'
import { screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { renderWithProviders } from 'tests/testUtils'
import { ImageEditor } from './ImageEditor'

// jsdom has no canvas: Replace the editor by a stub that reports its props.
jest.mock('react-avatar-editor', () => {
  const React = require('react')
  const AvatarEditorMock = React.forwardRef((props, ref) => {
    React.useImperativeHandle(ref, () => ({
      getCroppingRect: () => ({ x: 0.125, y: 0, width: 0.75, height: 1 }),
    }))
    return (
      <button
        type="button"
        data-testid="avatar-editor"
        data-width={props.width}
        data-height={props.height}
        data-scale={props.scale}
        onClick={() => props.onPositionChange({ x: 0.3, y: 0.6 })}
      />
    )
  })
  AvatarEditorMock.displayName = 'AvatarEditorMock'
  return AvatarEditorMock
})

const IMAGE_URL = '/upload/surveys/1/images/lime.jpg'

beforeAll(() => {
  // jsdom does not load images: Report a 400×300 image right away.
  global.Image = class {
    set src(value) {
      this.naturalWidth = 400
      this.naturalHeight = 300
      setTimeout(() => this.onload && this.onload(), 0)
    }
  }
  global.fetch = jest.fn(() =>
    Promise.resolve({
      headers: { get: () => String(5.8 * 1024 * 1024) },
    })
  )
})

const renderEditor = async (props = {}) => {
  const onSave = jest.fn()
  await renderWithProviders(
    <ImageEditor
      showModal={true}
      imageUrl={IMAGE_URL}
      onSave={onSave}
      {...props}
    />
  )
  await waitFor(() =>
    expect(screen.getByLabelText('Width (px)')).toHaveValue(400)
  )
  return onSave
}

describe('ImageEditor', () => {
  test('Starts with the natural image size and shows the source info', async () => {
    await renderEditor()

    expect(screen.getByRole('dialog')).toHaveAccessibleName('Edit image')
    expect(screen.getByLabelText('Height (px)')).toHaveValue(300)
    expect(screen.getByTestId('image-editor-ratio-lock')).toHaveAttribute(
      'aria-pressed',
      'true'
    )
    // Zoom 1: There is nothing to drag yet, the hint only keeps its space.
    expect(screen.getByTestId('image-editor-drag-hint')).toHaveClass(
      'invisible'
    )
    expect(screen.getByLabelText('Alt text')).toHaveValue('')
    await waitFor(() =>
      expect(screen.getByTestId('image-editor-source-info')).toHaveTextContent(
        '400×300px · 5.8 MB'
      )
    )
    expect(
      screen.queryByTestId('image-editor-blurry-warning')
    ).not.toBeInTheDocument()
  })

  test('Keeps the aspect ratio when the width changes', async () => {
    await renderEditor()

    const width = screen.getByLabelText('Width (px)')
    await userEvent.clear(width)
    await userEvent.type(width, '200')

    expect(screen.getByLabelText('Height (px)')).toHaveValue(150)
  })

  test('Changes only one side without aspect ratio and warns about blur', async () => {
    await renderEditor()

    await userEvent.click(screen.getByTestId('image-editor-ratio-lock'))
    const height = screen.getByLabelText('Height (px)')
    await userEvent.clear(height)
    await userEvent.type(height, '400')
    await userEvent.tab()

    expect(screen.getByLabelText('Width (px)')).toHaveValue(400)
    expect(height).toHaveValue(400)
    // A 400×300 image can't fill a 400×400 frame without being enlarged.
    expect(screen.getByTestId('image-editor-blurry-warning')).toHaveTextContent(
      '(300×300px) is smaller than the frame (400×400px)'
    )
    // A square frame cuts off the sides of the image: It can move at zoom 1.
    expect(screen.getByTestId('image-editor-drag-hint')).not.toHaveClass(
      'invisible'
    )
  })

  test('Accepts any frame size from 0 without upper limit', async () => {
    await renderEditor()

    await userEvent.click(screen.getByTestId('image-editor-ratio-lock'))
    const width = screen.getByLabelText('Width (px)')
    await userEvent.clear(width)
    await userEvent.type(width, '5000')
    await userEvent.tab()
    expect(width).toHaveValue(5000)

    await userEvent.clear(width)
    await userEvent.type(width, '0')
    await userEvent.tab()
    expect(width).toHaveValue(0)
  })

  test('Keeps the ratio of the width and height entered before the ratio was locked', async () => {
    await renderEditor()

    const keepRatio = screen.getByTestId('image-editor-ratio-lock')
    await userEvent.click(keepRatio)
    const width = screen.getByLabelText('Width (px)')
    const height = screen.getByLabelText('Height (px)')
    await userEvent.clear(width)
    await userEvent.type(width, '250')
    expect(height).toHaveValue(300)

    await userEvent.click(keepRatio)
    // Locking changes nothing.
    expect(width).toHaveValue(250)
    expect(height).toHaveValue(300)

    // 100 * 250 / 300 = 83.33: The width is a whole number.
    await userEvent.clear(height)
    await userEvent.type(height, '100')
    expect(width).toHaveValue(83)

    // Typing digit by digit (5×6px on the way) doesn't change the ratio.
    await userEvent.clear(width)
    await userEvent.type(width, '500')
    expect(height).toHaveValue(600)
  })

  test('The ratio lock button names what a click does', async () => {
    await renderEditor()

    const lock = screen.getByRole('button', { name: 'Unlock aspect ratio' })
    expect(screen.getByLabelText('Width (px)')).toHaveClass(
      'image-editor-ratio-locked'
    )
    await userEvent.click(lock)

    expect(lock).toHaveAccessibleName('Lock aspect ratio')
    expect(lock).toHaveAttribute('aria-pressed', 'false')
    expect(screen.getByLabelText('Width (px)')).not.toHaveClass(
      'image-editor-ratio-locked'
    )
  })

  test('Apply to all is only offered when asked for and is passed on save', async () => {
    const onSave = await renderEditor()
    expect(
      screen.queryByLabelText('Apply to all images of this question')
    ).not.toBeInTheDocument()

    const onSaveAll = jest.fn()
    await renderWithProviders(
      <ImageEditor
        showModal={true}
        imageUrl={IMAGE_URL}
        onSave={onSaveAll}
        showApplyToAll={true}
      />
    )
    const [checkbox] = screen.getAllByLabelText(
      'Apply to all images of this question'
    )
    expect(checkbox).not.toBeChecked()
    await userEvent.click(checkbox)
    const saveButtons = screen.getAllByRole('button', { name: 'Save' })
    await userEvent.click(saveButtons[saveButtons.length - 1])

    expect(onSaveAll).toHaveBeenCalledWith(
      expect.objectContaining({ applyToAll: true })
    )
    expect(onSave).not.toHaveBeenCalled()
  })

  test('Saves frame, position, crop and alt text', async () => {
    const onSave = await renderEditor()

    await userEvent.click(screen.getByTestId('avatar-editor'))
    await userEvent.type(screen.getByLabelText('Alt text'), ' Lime on a board ')
    await userEvent.click(screen.getByRole('button', { name: 'Save' }))

    expect(onSave).toHaveBeenCalledWith({
      width: 400,
      height: 300,
      keepRatio: true,
      zoom: 1,
      positionX: 0.3,
      positionY: 0.6,
      radius: 0,
      crop: { x: 0.125, y: 0, width: 0.75, height: 1 },
      altText: 'Lime on a board',
    })
  })

  test('Opens with saved settings and resets to the full image', async () => {
    const onSave = await renderEditor({
      settings: {
        width: 400,
        height: 300,
        keepRatio: false,
        zoom: 2,
        positionX: 0.2,
        positionY: 0.8,
        radius: 20,
      },
      altText: 'Lime',
    })

    expect(screen.getByTestId('image-editor-ratio-lock')).toHaveAttribute(
      'aria-pressed',
      'false'
    )
    expect(screen.getByTestId('image-editor-drag-hint')).not.toHaveClass(
      'invisible'
    )
    expect(screen.getByTestId('avatar-editor')).toHaveAttribute(
      'data-scale',
      '2'
    )
    expect(screen.getByLabelText('Alt text')).toHaveValue('Lime')

    await userEvent.click(screen.getByRole('button', { name: 'Reset' }))
    await userEvent.click(screen.getByRole('button', { name: 'Save' }))

    expect(onSave).toHaveBeenCalledWith(
      expect.objectContaining({
        width: 400,
        height: 300,
        keepRatio: true,
        zoom: 1,
        positionX: 0.5,
        positionY: 0.5,
        radius: 0,
        // Reset is about the image display: The alt text stays.
        altText: 'Lime',
      })
    )
  })
})
