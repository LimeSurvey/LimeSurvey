const STORAGE_KEY_PREFIX = 'responses-table-timing-column-visibility'

const getStorageKey = (surveyId) => `${STORAGE_KEY_PREFIX}:${surveyId}`

export const readTimingColumnVisibility = (surveyId) => {
  if (!surveyId || typeof localStorage === 'undefined') {
    return {}
  }

  try {
    return JSON.parse(localStorage.getItem(getStorageKey(surveyId))) || {}
  } catch {
    return {}
  }
}

export const writeTimingColumnVisibility = (surveyId, columns) => {
  if (!surveyId || typeof localStorage === 'undefined') {
    return
  }

  const visibility = Object.fromEntries(
    columns
      .filter(({ isTiming }) => isTiming)
      .map(({ id, checked }) => [id, checked])
  )

  try {
    localStorage.setItem(getStorageKey(surveyId), JSON.stringify(visibility))
  } catch {
    // Ignore disabled or full browser storage.
  }
}

export const applyStoredTimingColumnVisibility = (
  columns,
  columnVisibility,
  storedVisibility
) => {
  const timingVisibility = Object.fromEntries(
    columns
      .filter(({ meta }) => meta?.columnCategory === 'timing')
      .filter(({ id }) => typeof storedVisibility[id] === 'boolean')
      .map(({ id }) => [id, storedVisibility[id]])
  )

  return { ...columnVisibility, ...timingVisibility }
}
