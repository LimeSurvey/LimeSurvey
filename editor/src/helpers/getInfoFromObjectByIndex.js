export const getInfoFromObjectByIndex = (obj = {}, index = 0) => {
  if (!obj) {
    return { value: '', key: '' }
  }

  const values = Object.values(obj ?? {})
  const keys = Object.keys(obj ?? {})

  return { value: values?.[index], key: keys?.[index] }
}
