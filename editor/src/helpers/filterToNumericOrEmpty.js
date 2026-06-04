/**
 * Empty stings or numeric values will be returned as they came in. Everything else will be returned as an empty string.
 * Used for numeric inputs in the survey view mode to mimic the behaviour of the actual survey.
 * @param value
 * @returns {*|string}
 */
export const filterToNumericOrEmpty = (value) => {
  if (value === '' || /^-?\d*\.?\d*$/.test(value)) {
    return value
  }
  return ''
}
