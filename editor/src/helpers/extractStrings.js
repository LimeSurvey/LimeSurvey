export const extractStrings = (obj, ignoredKeys = []) => {
  const result = []

  function traverse(value, key) {
    if (ignoredKeys.includes(key)) {
      return // Skip this key
    }

    if (typeof value === 'string') {
      result.push(value)
    } else if (Array.isArray(value)) {
      value.forEach((item) => traverse(item, key))
    } else if (typeof value === 'object' && value !== null) {
      Object.entries(value).forEach(([k, v]) => traverse(v, k))
    }
  }

  if (Array.isArray(obj)) {
    obj.forEach((item) => traverse(item, null))
  } else {
    traverse(obj, null)
  }

  return result
}
