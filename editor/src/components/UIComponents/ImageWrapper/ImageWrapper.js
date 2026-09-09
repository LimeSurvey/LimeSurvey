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
 * defaults to `imageClassName` for backwards compatibility). These are kept
 * separate because the "background" mode typically needs the image to be
 * absolutely positioned/stretched to cover its container (which requires
 * explicit width/height, e.g. `w-100 h-100`, in addition to the inset
 * classes), while "side-by-side" mode needs a normal, naturally sized image.
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
        <div className={backgroundInnerClassName}>
          <img
            className={backgroundImageClassName}
            src={imageObject.imagePreviewUrl}
            alt={imageObject.imageAltText || ''}
            style={imageObject.imageStyles}
            data-testid={backgroundImageTestId}
          />

          {/* Content overlay */}
          <div className={overlayClassName}>{children}</div>
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
