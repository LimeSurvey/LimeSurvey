/* eslint-disable no-console */
export const filterObjectByStructure = (
  data,
  scheme
  // invalidCallback = () => {}
) => {
  const filteredData = {}

  if (typeof data !== 'object' || data === null || Array.isArray(data)) {
    console.error('Invalid data: Expected an object.')
    return {}
  }

  if (typeof scheme !== 'object' || scheme === null || Array.isArray(scheme)) {
    console.error('Invalid structure: Expected an object.')
    return data
  }

  for (const key in scheme) {
    if (data.hasOwnProperty(key)) {
      const typeInStructure = scheme[key]
      const valueInData = data[key]

      // Check if the type matches
      if (
        (typeInStructure === 'string' && typeof valueInData === 'string') ||
        (typeInStructure === 'number' && typeof valueInData === 'number') ||
        (typeInStructure === 'object' &&
          typeof valueInData === 'object' &&
          valueInData !== null)
      ) {
        filteredData[key] = valueInData
      }
    } else {
      // Todo: report error
    }
  }

  return filteredData
}
