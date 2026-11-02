// Image select choices (subquestions/answer options of the image select themes)
// keep their image as HTML, e.g. <img src="/upload/..." alt="..." />.
// Older choices can still hold a bare image URL.

const containsHtml = (value) => /<[^>]+>/.test(value)

const getImageElement = (value) =>
  new DOMParser().parseFromString(value, 'text/html').querySelector('img')

/**
 * Returns the image URL of an image select choice text, or null if it has no image.
 *
 * @param {string} value Choice text: HTML or a bare image URL
 * @returns {string|null}
 */
export const getImageChoiceSrc = (value) => {
  if (!value || typeof value !== 'string') {
    return null
  }
  if (!containsHtml(value)) {
    return value.trim() || null
  }
  return getImageElement(value)?.getAttribute('src') || null
}

/**
 * Returns the alternative text of the image of an image select choice text.
 *
 * @param {string} value Choice text: HTML or a bare image URL
 * @returns {string}
 */
export const getImageChoiceAlt = (value) => {
  if (!value || typeof value !== 'string' || !containsHtml(value)) {
    return ''
  }
  return getImageElement(value)?.getAttribute('alt') || ''
}

/**
 * Returns the choice text as HTML with the image URL and/or the alternative
 * text changed. Other content of the choice text is kept.
 *
 * @param {string} value Current choice text: HTML, a bare image URL or empty
 * @param {{src?: string, alt?: string}} changes
 * @returns {string}
 */
export const setImageChoiceImage = (value, { src, alt } = {}) => {
  const isHtml = typeof value === 'string' && containsHtml(value)
  const doc = new DOMParser().parseFromString(isHtml ? value : '', 'text/html')
  let img = doc.querySelector('img')
  if (!img) {
    img = doc.createElement('img')
    img.setAttribute('src', !isHtml && value ? value.trim() : '')
    img.setAttribute('alt', '')
    doc.body.prepend(img)
  }
  if (src !== undefined) {
    img.setAttribute('src', src)
  }
  if (alt !== undefined) {
    img.setAttribute('alt', alt)
  }
  return doc.body.innerHTML
}
