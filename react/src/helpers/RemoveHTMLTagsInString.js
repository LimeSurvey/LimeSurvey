export const RemoveHTMLTagsInString = (string) => {
  if (!string || typeof string !== 'string') {
    return ''
  }

  return string.replace(/(<([^>]+)>)/gi, '')
}
