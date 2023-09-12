export const IsTrue = (value) => {
  return (
    value === '1' ||
    value === 'true' ||
    value === true ||
    value === 'on' ||
    value === 'yes' ||
    value === 'soft'
  )
}
