import { RemoveHTMLTagsInString } from 'helpers'

describe('RemoveHTMLTagsInString', () => {
  it('should remove all HTML tags if no allowed tags are specified', () => {
    const input = '<div>Hello <b>World</b></div>'
    const result = RemoveHTMLTagsInString(input)
    expect(result).toBe('Hello World')
  })

  it('should preserve allowed tags and remove their attributes', () => {
    const input = '<div class="container">Hello <b class="bold">World</b></div>'
    const result = RemoveHTMLTagsInString(input, ['b'])
    expect(result).toBe('Hello <b>World</b>')
  })

  it('should preserve allowed tags and remove their attributes', () => {
    const input = '<div class="container">Hello <b class="bold">World</b></div>'
    const result = RemoveHTMLTagsInString(input, ['b', 'div'])
    expect(result).toBe('<div>Hello <b>World</b></div>')
  })

  it('should preserve allowed tags and remove their attributes', () => {
    const input = '<div class="container"><br></div>'
    const result = RemoveHTMLTagsInString(input, ['br', 'div'])
    expect(result).toBe('<div><br></div>')
  })

  it('should preserve multiple allowed tags and strip their attributes', () => {
    const input =
      '<div><b class="bold">Bold</b> <i style="color: red;">Italic</i></div>'
    const result = RemoveHTMLTagsInString(input, ['b', 'i'])
    expect(result).toBe('<b>Bold</b> <i>Italic</i>')
  })

  it('should remove unallowed tags while keeping allowed tags intact', () => {
    const input = '<p>Text <b>Bold</b> <i>Italic</i></p>'
    const result = RemoveHTMLTagsInString(input, ['b'])
    expect(result).toBe('Text <b>Bold</b> Italic')
  })

  it('should return an empty string for non-string input', () => {
    const result = RemoveHTMLTagsInString(null)
    expect(result).toBe('')
  })

  it('should handle empty input string gracefully', () => {
    const result = RemoveHTMLTagsInString('')
    expect(result).toBe('')
  })

  it('should not decode HTML entities in the string', () => {
    const input = '&lt;div&gt;Hello World&lt;/div&gt;'
    const result = RemoveHTMLTagsInString(input)
    expect(result).toBe('&lt;div&gt;Hello World&lt;/div&gt;')
  })

  it('should handle a string with no HTML tags correctly', () => {
    const input = 'Plain text with no tags.'
    const result = RemoveHTMLTagsInString(input)
    expect(result).toBe('Plain text with no tags.')
  })

  it('should remove all attributes from allowed tags and keep the tags', () => {
    const input = '<b class="bold" id="test">Text</b>'
    const result = RemoveHTMLTagsInString(input, ['b'])
    expect(result).toBe('<b>Text</b>')
  })

  it('should remove self-closing tags not in the allowed list', () => {
    const input = '<img src="image.jpg" /><b>Text</b>'
    const result = RemoveHTMLTagsInString(input, ['b'])
    expect(result).toBe('<b>Text</b>')
  })
})
