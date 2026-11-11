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
