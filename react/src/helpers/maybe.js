const maybe = (maybeName, locale = 'en', defaultValue = '') => {
  if (!maybeName) return defaultValue
  return maybeName[locale] || defaultValue
}

export default maybe
