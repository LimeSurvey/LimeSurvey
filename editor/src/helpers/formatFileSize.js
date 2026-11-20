/**
 * Formats a number with a unit in the user's locale. The browser translates
 * the unit (e.g. "12,5 kB", "12,5 ko"), so it needs no translation.
 *
 * @param {number} value the number
 * @param {string|undefined} locale the locale, or undefined for the browser's
 * @param {string} unit the unit, e.g. 'kilobyte'
 * @returns {string} the formatted number with its unit
 */
const formatNumber = (value, locale, unit) => {
  const options = {
    maximumFractionDigits: 1,
    style: 'unit',
    unit,
    unitDisplay: 'short',
  }
  try {
    return value.toLocaleString(locale, options)
  } catch {
    return value.toLocaleString(undefined, options)
  }
}

/**
 * Formats a file size for display in the user's locale, e.g. "5.8 MB" or
 * "5,8 MB".
 *
 * @param {number} sizeKb the file size in kilobytes
 * @param {string|undefined} locale the locale, or undefined for the browser's
 * @returns {string} the formatted size
 */
export const formatFileSize = (sizeKb, locale) => {
  const kb = Number(sizeKb) || 0
  if (kb < 1) {
    return formatNumber(Math.round(kb * 1024), locale, 'byte')
  }
  if (kb < 1024) {
    return formatNumber(kb, locale, 'kilobyte')
  }
  return formatNumber(kb / 1024, locale, 'megabyte')
}
