import { resolveTempId } from './resolvedTempIds'

// Find the group to delete by its (possibly replaced temporary) gid
export const getGroupIndexForDeletion = (questionGroups = [], gid) => {
  const currentGid = resolveTempId(gid)?.toString()
  return questionGroups.findIndex(
    (group) => group.gid?.toString() === currentGid
  )
}
