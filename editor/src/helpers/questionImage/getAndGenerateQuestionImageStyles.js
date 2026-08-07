/**
 * Generates CSS styles based on image transformation properties
 *
 * @param {Object} imageProps - Object containing image transformation properties
 * @param {boolean} [asString=false] - Whether to return styles as a CSS string (for Twig templates) or as an object (for React)
 * @returns {Object|string} CSS styles as an object or string depending on asString parameter
 */
export const getAndGenerateImageStyles = (imageProps, asString = false) => {
  // Create the styles object first
  const styles = {
    borderRadius: `${imageProps.imageRadius || 0}px`,
    filter: '',
  }

  // Custom brightness value expected between -100 and 100
  const imageBrightness = imageProps.imageBrightness || 0

  // Map -100 to 100 -> 0 to 2 (CSS brightness range)
  const brightnessValue = (imageBrightness + 100) / 100

  styles.filter = `brightness(${brightnessValue})`

  // If asString is true, convert the styles object to a CSS string with proper kebab-case
  if (asString) {
    return Object.entries(styles)
      .map(([property, value]) => {
        // Convert camelCase to kebab-case (e.g., borderRadius -> border-radius)
        const cssProperty = property.replace(/([A-Z])/g, '-$1').toLowerCase()
        return `${cssProperty}: ${value};`
      })
      .join(' ')
  }

  // Otherwise return the styles object for React
  return styles
}
