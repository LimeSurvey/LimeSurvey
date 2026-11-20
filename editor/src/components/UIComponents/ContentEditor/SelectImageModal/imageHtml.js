import { format } from 'util'

// Largest width and height in px. The server's HTML filter cuts larger
// values down to this, one side at a time, which would distort the image.
export const IMAGE_MAX_SIZE = 1200

// Largest uploaded file in bytes, the server checks it too
export const IMAGE_MAX_FILE_SIZE = 10 * 1024 * 1024

// Image types that can be uploaded, the server checks the file content too
export const IMAGE_ACCEPT = {
  'image/png': ['.png'],
  'image/jpeg': ['.jpg', '.jpeg'],
  'image/webp': ['.webp'],
}

/**
 * Escapes a value for an HTML attribute in double quotes.
 *
 * @param {string|number} value the value
 * @returns {string} the escaped value
 */
export const escapeAttribute = (value) =>
  String(value)
    .replace(/&/g, '&amp;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')

/**
 * Builds the HTML of an image. The alt attribute is always written, empty for
 * decorative images. img-fluid keeps the image inside its container on small
 * screens, with its aspect ratio.
 *
 * @param {{src: string, alt: string, width: number, height: number}} image
 *   the image: Its path, alternative text (empty for decorative images) and
 *   size in px
 * @returns {string} the HTML of the img element
 */
export const buildImageHtml = ({ src, alt = '', width, height }) =>
  `<img src="${escapeAttribute(src)}" alt="${escapeAttribute(alt.trim())}"` +
  ` width="${escapeAttribute(width)}" height="${escapeAttribute(height)}"` +
  ` class="img-fluid">`

/**
 * Scales a size down to IMAGE_MAX_SIZE on its longer side, keeping the
 * aspect ratio. Smaller sizes stay the same.
 *
 * @param {number} width width in px
 * @param {number} height height in px
 * @returns {{width: number, height: number, isReduced: boolean}} the size in
 *   whole px, and whether it was scaled down
 */
export const fitToMaxSize = (width, height) => {
  const scale = Math.min(1, IMAGE_MAX_SIZE / Math.max(width, height))
  if (scale === 1) {
    return { width, height, isReduced: false }
  }
  return {
    width: Math.max(1, Math.round(width * scale)),
    height: Math.max(1, Math.round(height * scale)),
    isReduced: true,
  }
}

/**
 * Returns the other side of a size for a new width or height, keeping a
 * ratio. Never smaller than 1 px.
 *
 * @param {number} value the new width or height
 * @param {'width'|'height'} side the side that was changed
 * @param {number} ratio width / height to keep
 * @returns {number} the other side in whole px
 */
export const getOtherSide = (value, side, ratio) =>
  Math.max(1, Math.round(side === 'width' ? value / ratio : value * ratio))

/**
 * Checks a width or height typed by the author.
 *
 * @param {string} value the typed value
 * @returns {string} an error message, empty if the value is a whole number of
 *   px from 1 to IMAGE_MAX_SIZE
 */
export const getSizeError = (value) => {
  if (!/^\d+$/.test(String(value).trim()) || Number(value) < 1) {
    return t('Enter a whole number of pixels greater than 0.')
  }
  if (Number(value) > IMAGE_MAX_SIZE) {
    return format(t('The maximum is %s pixels.'), IMAGE_MAX_SIZE)
  }
  return ''
}
