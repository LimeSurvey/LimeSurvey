import { removePlaceholderBadges, wrapPlaceholdersInBadges } from 'helpers'

describe('wrapPlaceholdersInBadges', () => {
  it('should wrap a participant placeholder in a badge', () => {
    const result = wrapPlaceholdersInBadges('Dear {TOKEN:FIRSTNAME}')
    expect(result).toBe('Dear <badge>{TOKEN:FIRSTNAME}</badge>')
  })

  it('should wrap survey and previous answer placeholders in badges', () => {
    const result = wrapPlaceholdersInBadges('{SID} {EXPIRY} {Q00_SQ001.shown}')
    expect(result).toBe(
      '<badge>{SID}</badge> <badge>{EXPIRY}</badge> <badge>{Q00_SQ001.shown}</badge>'
    )
  })

  it('should leave text without placeholders untouched', () => {
    const result = wrapPlaceholdersInBadges('Apples')
    expect(result).toBe('Apples')
  })

  it('should not wrap a placeholder twice when called repeatedly', () => {
    const once = wrapPlaceholdersInBadges('Apples {TOKEN:FIRSTNAME}')
    const twice = wrapPlaceholdersInBadges(once)
    expect(twice).toBe(once)
  })

  it('should handle empty and missing input gracefully', () => {
    expect(wrapPlaceholdersInBadges('')).toBe('')
    expect(wrapPlaceholdersInBadges()).toBe('')
  })
})

describe('removePlaceholderBadges', () => {
  it('should unwrap a badge back to the raw placeholder', () => {
    const result = removePlaceholderBadges(
      'Dear <badge>{TOKEN:FIRSTNAME}</badge>'
    )
    expect(result).toBe('Dear {TOKEN:FIRSTNAME}')
  })

  it('should keep the original text when wrapping and unwrapping', () => {
    const value = 'Survey ID {SID}, expires {EXPIRY}.'
    expect(removePlaceholderBadges(wrapPlaceholdersInBadges(value))).toBe(value)
  })

  it('should handle empty and missing input gracefully', () => {
    expect(removePlaceholderBadges('')).toBe('')
    expect(removePlaceholderBadges()).toBe('')
  })
})
