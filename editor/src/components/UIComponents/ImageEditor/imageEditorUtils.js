// Smallest frame size in px. There is no upper limit: In the survey,
// max-width: 100% still applies on small screens.
export const FRAME_MIN_SIZE = 0

export const ZOOM_MIN = 1
export const ZOOM_MAX = 4

export const ALT_TEXT_MAX_LENGTH = 125

/**
 * Turns a frame side into a whole number of pixels, not below the minimum.
 *
 * @param {number} value the calculated or entered size
 * @returns {number} the size as an integer of at least FRAME_MIN_SIZE
 */
const toFrameSize = (value) => Math.max(FRAME_MIN_SIZE, Math.round(value))

/**
 * Returns the default frame size for an image: Its natural size.
 *
 * @param {number} naturalWidth width of the image file in px
 * @param {number} naturalHeight height of the image file in px
 * @returns {{width: number, height: number}} the frame size in whole px, or
 *   300×300px while the image size is unknown
 */
export const getDefaultFrameSize = (naturalWidth, naturalHeight) => {
  if (!naturalWidth || !naturalHeight) {
    return { width: 300, height: 300 }
  }
  return {
    width: toFrameSize(naturalWidth),
    height: toFrameSize(naturalHeight),
  }
}

/**
 * Returns the aspect ratio of a frame.
 *
 * @param {{width: number, height: number}} frame the frame size in px
 * @returns {number|null} width / height, or null when a side is 0
 */
export const getFrameRatio = (frame) =>
  frame?.width && frame?.height ? frame.width / frame.height : null

/**
 * Applies a new width or height to the frame. With a ratio, the other side
 * follows. Both sides are whole numbers of pixels.
 *
 * @param {{width: number, height: number}} frame current frame size
 * @param {'width'|'height'} side the side that was changed
 * @param {number} value the new value for that side
 * @param {number|null} ratio width / height to keep, or null to change one side only
 * @returns {{width: number, height: number}} the new frame size
 */
export const resizeFrame = (frame, side, value, ratio = null) => {
  const size = toFrameSize(value)
  if (!ratio) {
    return { ...frame, [side]: size }
  }
  if (side === 'width') {
    return { width: size, height: toFrameSize(size / ratio) }
  }
  return { width: toFrameSize(size * ratio), height: size }
}

/**
 * Returns how many source pixels are visible in the frame, when the image
 * covers the frame and is zoomed.
 *
 * @param {number} naturalWidth width of the image file in px
 * @param {number} naturalHeight height of the image file in px
 * @param {{width: number, height: number}} frame the frame size in px
 * @param {number} zoom zoom factor, 1 = the image just covers the frame
 * @returns {{width: number, height: number}|null} the visible part in source
 *   px, or null for a frame without area
 */
export const getVisibleSourceSize = (
  naturalWidth,
  naturalHeight,
  frame,
  zoom = 1
) => {
  if (!frame.width || !frame.height) {
    return null
  }
  const frameRatio = frame.width / frame.height
  const imageRatio = naturalWidth / naturalHeight
  let width = naturalWidth
  let height = naturalHeight
  if (imageRatio > frameRatio) {
    width = naturalHeight * frameRatio
  } else {
    height = naturalWidth / frameRatio
  }
  return {
    width: Math.round(width / zoom),
    height: Math.round(height / zoom),
  }
}

/**
 * Rounds a relative setting (position, crop) to 4 decimals, enough for images
 * of up to 10000px, to keep the stored JSON short.
 *
 * @param {number} value the value
 * @returns {number} the rounded value
 */
export const roundSetting = (value) => Math.round(value * 10000) / 10000

/**
 * Returns the part of an image that is shown in a frame, like the image editor
 * does: The image covers the frame, is zoomed and moved to a position.
 *
 * @param {number} naturalWidth width of the image file in px
 * @param {number} naturalHeight height of the image file in px
 * @param {{width: number, height: number}} frame the frame size in px
 * @param {number} zoom zoom factor, 1 = the image just covers the frame
 * @param {number} positionX horizontal center of the shown part, 0 to 1
 * @param {number} positionY vertical center of the shown part, 0 to 1
 * @returns {{x: number, y: number, width: number, height: number}|null} the
 *   shown part relative to the image size, or null without sizes
 */
export const getCropRect = (
  naturalWidth,
  naturalHeight,
  frame,
  zoom = ZOOM_MIN,
  positionX = 0.5,
  positionY = 0.5
) => {
  const frameRatio = getFrameRatio(frame)
  if (!frameRatio || !naturalWidth || !naturalHeight || !zoom) {
    return null
  }
  const imageRatio = naturalWidth / naturalHeight
  const width = Math.min(1, frameRatio / imageRatio) / zoom
  const height = Math.min(1, imageRatio / frameRatio) / zoom
  const clamp = (value, max) => Math.max(0, Math.min(value, max))
  return {
    x: clamp(positionX - width / 2, 1 - width),
    y: clamp(positionY - height / 2, 1 - height),
    width,
    height,
  }
}

/**
 * Applies the frame, zoom and corner radius of an edited image to another
 * image ("Apply to all"). The other image keeps its own position, and its crop
 * is calculated for its own size.
 *
 * @param {Object} settings the settings of the edited image, see ImageEditor's onSave
 * @param {{width: number, height: number}} naturalSize size of the other image file
 * @param {Object|null} ownSettings the settings the other image had before
 * @returns {Object} the settings for the other image
 */
export const applyImageSettings = (settings, naturalSize, ownSettings) => {
  const positionX = ownSettings?.positionX ?? 0.5
  const positionY = ownSettings?.positionY ?? 0.5
  const crop = getCropRect(
    naturalSize.width,
    naturalSize.height,
    settings,
    settings.zoom,
    positionX,
    positionY
  )
  return {
    width: settings.width,
    height: settings.height,
    keepRatio: settings.keepRatio,
    zoom: settings.zoom,
    positionX,
    positionY,
    radius: settings.radius,
    crop: crop
      ? {
          x: roundSetting(crop.x),
          y: roundSetting(crop.y),
          width: roundSetting(crop.width),
          height: roundSetting(crop.height),
        }
      : null,
  }
}

/**
 * Loads an image to get its size.
 *
 * @param {string} url URL of the image
 * @returns {Promise<{width: number, height: number}|null>} the size, or null
 *   when the image can't be loaded
 */
export const loadImageSize = (url) =>
  new Promise((resolve) => {
    const image = new Image()
    image.onload = () =>
      resolve({ width: image.naturalWidth, height: image.naturalHeight })
    image.onerror = () => resolve(null)
    image.src = url
  })

/**
 * Formats a file size in bytes for display, e.g. "5.8 MB".
 *
 * @param {number} bytes the file size in bytes
 * @returns {string} the formatted size, or an empty string without a size
 */
export const formatFileSize = (bytes) => {
  if (!bytes && bytes !== 0) {
    return ''
  }
  if (bytes < 1024) {
    return `${bytes} B`
  }
  if (bytes < 1024 * 1024) {
    return `${(bytes / 1024).toFixed(1)} KB`
  }
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
}
