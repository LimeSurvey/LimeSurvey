import { getAndGenerateImageStyles } from './getAndGenerateQuestionImageStyles'

/**
 * Extracts and normalizes image properties from a question's image attribute
 * Contains more props than we actually use right now (position, zoom and rotate.
 *
 * @param {Object|string} imageAttribute - The image attribute which can be either:
 *   - A JSON string containing image properties
 *   - An object with an empty string key containing the JSON string ({"": jsonString})
 *   - An object with direct image properties
 *
 * @returns {Object} Normalized image object with properties:
 *   - hasQuestionImage {boolean} - Whether an image path exists
 *   - hasQuestionImageAsBackground {boolean} - Whether the image is set as the background for a question
 *   - imageAlign {string} - Image alignment (defaults to 'left')
 *   - imageBrightness {number} - Image brightness value (defaults to 0)
 *   - imageZoom {number} - Image zoom value (defaults to 1)
 *   - imageRotate {number} - Image rotation value (defaults to 0)
 *   - imageRadius {number} - Image border radius value (defaults to 0)
 *   - imagePositionX {number} - Image horizontal position (defaults to 0.5)
 *   - imagePositionY {number} - Image vertical position (defaults to 0.5)
 *   - imageAltText {string} - Alternative text for the image (defaults to '')
 *   - imagePath {string} - Path to the image file (defaults to '')
 *   - imagePreviewUrl {string} - URL to the image preview (defaults to '')
 *   - imageStyles {Object} - CSS styles object generated from the image properties
 */
export const getQuestionImageObjectFromImageAttribute = (imageAttribute) => {
  const jsonString = imageAttribute?.[''] || imageAttribute
  let parsedValue = jsonString

  if (typeof jsonString === 'string' && jsonString !== '') {
    try {
      parsedValue = JSON.parse(jsonString)
    } catch (error) {
      parsedValue = {}
    }
  }

  const imageAlign = parsedValue?.image_align || 'left'
  const imageBrightness = parsedValue?.image_brightness || 0
  const imageZoom = parsedValue?.image_zoom || 1
  const imageRotate = parsedValue?.image_rotate || 0
  const imageRadius = parsedValue?.image_radius || 0
  const imagePositionX = parsedValue?.image_position_x || 0.5
  const imagePositionY = parsedValue?.image_position_y || 0.5
  const imageAltText = parsedValue?.image_alt_text || ''
  const imagePath = parsedValue?.image_path || ''
  const imagePreviewUrl = imagePath
    ? process.env.REACT_APP_SITE_URL + imagePath
    : null
  const hasQuestionImage = imagePath !== ''
  const hasQuestionImageAsBackground =
    hasQuestionImage && imageAlign === 'center'

  // Create the image object with all properties
  const imageObject = {
    hasQuestionImage,
    hasQuestionImageAsBackground,
    imageAlign,
    imageBrightness,
    imageZoom,
    imageRotate,
    imageRadius,
    imagePositionX,
    imagePositionY,
    imageAltText,
    imagePath,
    imagePreviewUrl,
  }

  // Generate and add the CSS styles to the image object
  imageObject.imageStyles = getAndGenerateImageStyles(imageObject)

  return imageObject
}
