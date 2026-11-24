import React from 'react'
import { Image } from 'react-bootstrap'

import { getFrameRatio } from './imageEditorUtils'

/**
 * Returns the styles that show an image the way it was set up in the image
 * editor: The frame's aspect ratio, the cropped part and the corner radius.
 * The frame size itself is not used: The frame is scaled to the given height,
 * but never gets wider than its container.
 *
 * @param {Object|null} settings the settings from ImageEditor's onSave
 * @param {number} height the preview height in px
 * @returns {{frame: Object, image: Object}|null} the styles of the frame and
 *   the image, or null when there are no usable settings
 */
export const getEditedImageStyles = (settings, height) => {
  const ratio = getFrameRatio(settings)
  if (!ratio || !height) {
    return null
  }
  const radius = settings.radius || 0
  const frame = {
    position: 'relative',
    overflow: 'hidden',
    // A fixed width: Inside containers that take the width of their content,
    // a percentage would collapse the frame. maxWidth still keeps it inside.
    width: `${Math.round(height * ratio)}px`,
    maxWidth: '100%',
    aspectRatio: `${settings.width} / ${settings.height}`,
    // Percentages scale the radius along with the frame.
    borderRadius: radius
      ? `${(radius / settings.width) * 100}% / ${(radius / settings.height) * 100}%`
      : undefined,
  }
  const crop = settings.crop
  const image =
    crop?.width > 0 && crop?.height > 0
      ? {
          // The cropped part of the image fills the frame exactly.
          position: 'absolute',
          maxWidth: 'none',
          width: `${100 / crop.width}%`,
          height: `${100 / crop.height}%`,
          left: `${(-crop.x / crop.width) * 100}%`,
          top: `${(-crop.y / crop.height) * 100}%`,
        }
      : {
          width: '100%',
          height: '100%',
          objectFit: 'cover',
          objectPosition: `${(settings.positionX ?? 0.5) * 100}% ${
            (settings.positionY ?? 0.5) * 100
          }%`,
        }
  return { frame, image }
}

/**
 * Shows an image as set up in the image editor, see getEditedImageStyles().
 * Without settings, the image is shown with the fallback style.
 *
 * @param {string} src URL of the image
 * @param {string} alt alt text of the image
 * @param {Object|null} settings the settings from ImageEditor's onSave
 * @param {number|string} height the preview height in px, e.g. 160 or "160px"
 * @param {Object} fallbackStyle style of the image without settings
 * @returns {JSX.Element} the image
 */
export const EditedImage = ({
  src,
  alt,
  settings = null,
  height,
  fallbackStyle = {},
}) => {
  const styles = getEditedImageStyles(settings, parseFloat(height))
  if (!styles) {
    return <Image src={src} alt={alt} style={fallbackStyle} />
  }
  return (
    <div style={styles.frame} data-testid="edited-image">
      <img src={src} alt={alt} style={styles.image} />
    </div>
  )
}
