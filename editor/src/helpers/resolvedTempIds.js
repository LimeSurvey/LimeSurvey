const resolvedTempIds = new Map()

// Remember the real ID that autosave assigned to a temporary ID
export const rememberResolvedTempId = (tempId, id) => {
  if (tempId === undefined || tempId === null) {
    return
  }
  resolvedTempIds.set(tempId.toString(), id)
}

// Get the real ID for a replaced temporary ID, or the given ID unchanged
export const resolveTempId = (id) => {
  if (id === undefined || id === null) {
    return id
  }
  return resolvedTempIds.has(id.toString())
    ? resolvedTempIds.get(id.toString())
    : id
}
