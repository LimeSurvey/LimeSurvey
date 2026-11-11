import React from 'react'
import { render, screen } from '@testing-library/react'

import { EditedImage, getEditedImageStyles } from './EditedImage'

const settings = {
  width: 400,
  height: 200,
  radius: 20,
  positionX: 0.5,
  positionY: 0.5,
  // The middle half of the image width, the full height.
  crop: { x: 0.25, y: 0, width: 0.5, height: 1 },
}

describe('EditedImage', () => {
  test('Frame keeps the ratio, scaled to the preview height', () => {
    const { frame } = getEditedImageStyles(settings, 160)

    expect(frame.aspectRatio).toBe('400 / 200')
    expect(frame.width).toBe('320px')
    expect(frame.maxWidth).toBe('100%')
    expect(frame.overflow).toBe('hidden')
    // 20px of a 400×200px frame: 5% of the width, 10% of the height.
    expect(frame.borderRadius).toBe('5% / 10%')
  })

  test('The cropped part of the image fills the frame', () => {
    const { image } = getEditedImageStyles(settings, 160)

    expect(image).toEqual(
      expect.objectContaining({
        position: 'absolute',
        width: '200%',
        height: '100%',
        left: '-50%',
        top: '0%',
      })
    )
  })

  test('Without crop, the image covers the frame at its position', () => {
    const { image, frame } = getEditedImageStyles(
      { width: 100, height: 100, positionX: 0.2, positionY: 1 },
      160
    )

    expect(image.objectFit).toBe('cover')
    expect(image.objectPosition).toBe('20% 100%')
    expect(frame.borderRadius).toBeUndefined()
  })

  test('No usable settings: The image keeps its fallback style', () => {
    expect(getEditedImageStyles(null, 160)).toBeNull()
    expect(getEditedImageStyles({ width: 0, height: 100 }, 160)).toBeNull()

    render(
      <EditedImage
        src="/lime.jpg"
        alt="Lime"
        height="160px"
        fallbackStyle={{ maxHeight: '160px' }}
      />
    )

    expect(screen.queryByTestId('edited-image')).not.toBeInTheDocument()
    expect(screen.getByAltText('Lime')).toHaveStyle({ maxHeight: '160px' })
  })

  test('With settings, the image is shown inside the frame', () => {
    render(
      <EditedImage
        src="/lime.jpg"
        alt="Lime"
        settings={settings}
        height="160px"
      />
    )

    expect(screen.getByTestId('edited-image')).toContainElement(
      screen.getByAltText('Lime')
    )
  })
})
