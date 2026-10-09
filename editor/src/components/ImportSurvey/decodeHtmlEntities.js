export const decodeHtmlEntities = (value) => {
  if (typeof value !== 'string' || !value.includes('&')) return value

  const textarea = document.createElement('textarea')
  textarea.innerHTML = value
  return textarea.value
}
