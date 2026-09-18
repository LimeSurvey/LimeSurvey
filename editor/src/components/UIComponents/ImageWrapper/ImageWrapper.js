import React from 'react'
import classNames from 'classnames'

/**
 * Generic, presentation-agnostic layout for a survey image (the same
 * JSON structure produced by getImageObjectFromJsonData, used
 * both by question images and the survey welcome screen image).
 *
 * Handles the three possible states:
 *  - no image: renders children as-is
 *  - image used as background (imageAlign === 'center'): renders the image
 *    together with an overlay that contains the children
 *  - image beside content (imageAlign === 'left' | 'right'): renders a flex
 *    row with an image column and a content column
 *
 * All layout/spacing classNames are supplied by the caller so this component
 * can be reused for visually different contexts (e.g. question preview vs.
 * survey welcome screen) without duplicating the branching logic above.
 *
 * `className` styles the outer wrapper of the "background" mode, while
 * `rowClassName` styles the outer wrapper of the "side-by-side" mode, since
 * those two modes often need different outer spacing (e.g. an edge-to-edge
 * background image vs. an inset row).
 *
 * `imageClassName` styles the <img> in "side-by-side" mode, while
 * `backgroundImageClassName` styles the <img> in "background" mode (it
 * defaults to `imageClassName` for backwards compatibility). In "background"
 * mode the image is rendered at its natural ratio (full width, auto height,
 * never cropped/stretched) and the overlay is stacked on top of it (both
 * share the same CSS grid cell via the `image-background-stack` class), so
 * the container grows to fit whichever of the two is taller instead of
 * clipping the overlay content to the image's height.
 */
export const ImageWrapper = ({
  imageObject,
  children,
  className,
  rowClassName,
  imageContainerClassName,
  contentContainerClassName,
  imageInnerClassName = 'position-relative overflow-hidden w-100',
  backgroundInnerClassName = 'position-relative w-100',
  overlayClassName,
  imageClassName = 'w-100 h-auto d-block',
  backgroundImageClassName = imageClassName,
  imageTestId = 'image',
  backgroundImageTestId = 'background-image',
}) => {
  const hasImage = imageObject?.hasImage
  const hasImageAsBackground = imageObject?.hasImageAsBackground

  // If no image, just render children
  if (!hasImage) {
    return children
  }

  // If image is used as background, use an actual img element instead of background-image
  if (hasImageAsBackground) {
    return (
      <div
        className={classNames('position-relative overflow-hidden', className)}
      >
        {/*
          The image and the overlay are stacked on top of each other in the
          same grid cell (instead of the overlay being absolutely positioned
          over the image). This way the row/container height grows to fit
          whichever of the two is taller, so the overlay content is never
          cropped when it is taller than the image, while the image itself
          is never stretched/cropped and always keeps its own ratio.
        */}
        <div
          className={classNames(
            'image-background-stack',
            backgroundInnerClassName
          )}
        >
          <img
            className={classNames(
              'image-background-stack-item align-self-start',
              backgroundImageClassName
            )}
            src={imageObject.imagePreviewUrl}
            alt={imageObject.imageAltText || ''}
            style={imageObject.imageStyles}
            data-testid={backgroundImageTestId}
          />

          {/* Content overlay */}
          <div
            className={classNames(
              'image-background-stack-item',
              overlayClassName
            )}
          >
            {children}
          </div>
        </div>
      </div>
    )
  }

  // If image exists and not used as background, render flex layout
  return (
    <div
      className={classNames(
        'd-flex',
        { 'flex-row-reverse': imageObject.imageAlign === 'right' },
        rowClassName
      )}
    >
      <div className={imageContainerClassName}>
        <div className={imageInnerClassName}>
          <img
            className={imageClassName}
            src={imageObject.imagePreviewUrl}
            alt={imageObject.imageAltText || ''}
            style={imageObject.imageStyles}
            data-testid={imageTestId}
          />
        </div>
      </div>
      <div className={contentContainerClassName}>{children}</div>
    </div>
  )
}
