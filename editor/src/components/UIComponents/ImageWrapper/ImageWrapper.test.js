// Import shared mocks
import 'tests/mocks'

import { screen } from '@testing-library/react'

import { renderWithProviders } from 'tests/testUtils'

import { ImageWrapper } from './ImageWrapper'

const baseImageObject = {
  hasImage: true,
  hasImageAsBackground: false,
  imageAlign: 'left',
  imageAltText: 'alt text',
  imagePreviewUrl: 'https://example.com/image.png',
  imageStyles: {},
}

describe('ImageWrapper', () => {
  test('Should render only children when there is no image', async () => {
    await renderWithProviders(
      <ImageWrapper imageObject={{ hasImage: false }}>
        <div data-testid="content">content</div>
      </ImageWrapper>
    )

    expect(screen.getByTestId('content')).toBeInTheDocument()
    expect(screen.queryByTestId('image')).not.toBeInTheDocument()
    expect(screen.queryByTestId('background-image')).not.toBeInTheDocument()
  })

  test('Should render image beside content when not used as background', async () => {
    await renderWithProviders(
      <ImageWrapper imageObject={baseImageObject}>
        <div data-testid="content">content</div>
      </ImageWrapper>
    )

    const image = screen.getByTestId('image')
    expect(image).toBeInTheDocument()
    expect(image).toHaveAttribute('src', baseImageObject.imagePreviewUrl)
    expect(screen.getByTestId('content')).toBeInTheDocument()
    expect(screen.queryByTestId('background-image')).not.toBeInTheDocument()
  })

  test('Should render full-width, uncropped image with an overlay stacked on top, without capping the overlay height', async () => {
    const imageObject = {
      ...baseImageObject,
      hasImageAsBackground: true,
      imageAlign: 'center',
    }

    await renderWithProviders(
      <ImageWrapper
        imageObject={imageObject}
        overlayClassName="z-1"
        backgroundImageClassName="w-100 h-auto d-block"
      >
        <div data-testid="content">content</div>
      </ImageWrapper>
    )

    const image = screen.getByTestId('background-image')
    expect(image).toBeInTheDocument()
    expect(image).toHaveAttribute('src', imageObject.imagePreviewUrl)

    // The image must keep its own width/height ratio (no cropping),
    // spanning the full width of its container.
    expect(image).toHaveClass('w-100')
    expect(image).toHaveClass('h-auto')
    expect(image).not.toHaveClass('object-fit-cover')
    expect(image).not.toHaveClass('h-100')

    // The image and the overlay must be stacked in the same grid cell
    // (rather than the overlay being absolutely positioned/capped to the
    // image's height), so the container grows to fit the taller of the two
    // instead of clipping overlay content that is taller than the image.
    const content = screen.getByTestId('content')
    expect(screen.queryByTestId('image')).not.toBeInTheDocument()

    const overlay = content.parentElement
    const stack = image.parentElement
    expect(stack).toBe(overlay.parentElement)
    expect(stack).toHaveClass('image-background-stack')
    expect(image).toHaveClass('image-background-stack-item')
    expect(overlay).toHaveClass('image-background-stack-item')
    expect(overlay).not.toHaveClass('h-100')
    expect(overlay).not.toHaveClass('position-absolute')
  })
})
