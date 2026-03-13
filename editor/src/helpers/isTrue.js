export const isTrue = (value = '') => {
  return (
    value === true ||
    value === 'true' ||
    value === '1' ||
    value === 'on' ||
    value === 'yes' ||
    // check option value has value like this
    value === 'crop' ||
    value === 'Y'
  )
}
