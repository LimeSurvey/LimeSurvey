import { hasTempId } from './hasTempId'

/**
 * Find the index of the group to delete by its gid.
 * Falls back to the opened index only if the gid was a temp id.
 *
 * @param {Array} questionGroups The current question groups
 * @param {number|string} gid The group's gid
 * @param {number} openedIndex The group's index when the dialog opened
 * @returns {number} The group's index, or -1 if not found
 */
export const getGroupIndexForDeletion = (questionGroups, gid, openedIndex) => {
  const index = questionGroups.findIndex(
    (group) => group.gid?.toString() === gid?.toString()
  )
  if (index !== -1) {
    return index
  }

  if (hasTempId(gid) && questionGroups[openedIndex]) {
    return openedIndex
  }

  return -1
}
