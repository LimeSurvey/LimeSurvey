export const RemoveHTMLTagsInString = (string, allowedTags = []) => {
  if (!string || typeof string !== 'string') {
    return ''
  }

  // Remove all HTML tags if no allowed tags are provided
  if (allowedTags.length === 0) {
    return string.replace(/<\/?[^>]+>/gi, '')
  }

  // Create a regex pattern to allow only the specified tags and strip their attributes
  const allowedTagsRegex = new RegExp(
    `<(/?(${allowedTags.join('|')}))\\s*[^>]*?>`,
    'gi'
  )

  // Preserve allowed tags by stripping attributes
  string = string.replace(allowedTagsRegex, (match, tag) => `<${tag}>`)

  // Remove all other HTML tags
  const disallowedTagsRegex = new RegExp(
    `<(?!/?(${allowedTags.join('|')})\\b)[^>]+>`,
    'gi'
  )
  string = string.replace(disallowedTagsRegex, '')

  return string
}
