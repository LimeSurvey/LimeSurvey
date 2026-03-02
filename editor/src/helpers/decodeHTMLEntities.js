export const decodeHTMLEntities = (text) => {
  const element = document.createElement('span')
  if (text) {
    element.innerHTML = text
    return element.textContent
  }

  return ''
}
