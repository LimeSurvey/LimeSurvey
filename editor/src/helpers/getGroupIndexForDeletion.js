import { resolveTempId } from './resolvedTempIds'

// Find the group to delete by its gid, falling back to the real gid of a replaced temporary gid
export const getGroupIndexForDeletion = (questionGroups = [], gid) => {
  const findByGid = (id) =>
    questionGroups.findIndex(
      (group) => group.gid?.toString() === id?.toString()
    )

  // A live group wins over a remembered mapping: Temporary IDs can be reused
  const liveIndex = findByGid(gid)
  if (liveIndex !== -1) {
    return liveIndex
  }
  return findByGid(resolveTempId(gid))
}
