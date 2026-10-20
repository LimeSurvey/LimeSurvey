const STORAGE_KEY_PREFIX = 'responses-table-column-visibility'
const LEGACY_STORAGE_KEY_PREFIX = 'responses-table-timing-column-visibility'

const getStorageKey = (surveyId) => `${STORAGE_KEY_PREFIX}:${surveyId}`
const getLegacyStorageKey = (surveyId) =>
  `${LEGACY_STORAGE_KEY_PREFIX}:${surveyId}`

export const readColumnVisibility = (surveyId) => {
  if (!surveyId || typeof localStorage === 'undefined') {
    return {}
  }

  try {
    const storedVisibility = localStorage.getItem(getStorageKey(surveyId))
    const legacyVisibility = localStorage.getItem(getLegacyStorageKey(surveyId))

    return JSON.parse(storedVisibility ?? legacyVisibility) || {}
  } catch {
    return {}
  }
}

export const writeColumnVisibility = (surveyId, columns) => {
  if (!surveyId || typeof localStorage === 'undefined') {
    return
  }

  const visibility = Object.fromEntries(
    columns
      .filter(({ checked }) => typeof checked === 'boolean')
      .map(({ id, checked }) => [id, checked])
  )

  try {
    localStorage.setItem(getStorageKey(surveyId), JSON.stringify(visibility))
  } catch {
    // Ignore disabled or full browser storage.
  }
}

export const applyStoredColumnVisibility = (
  columns,
  columnVisibility,
  storedVisibility
) => {
  const storedColumnVisibility = Object.fromEntries(
    columns
      .filter(({ id }) => typeof storedVisibility[id] === 'boolean')
      .map(({ id }) => [id, storedVisibility[id]])
  )

  return { ...columnVisibility, ...storedColumnVisibility }
}
