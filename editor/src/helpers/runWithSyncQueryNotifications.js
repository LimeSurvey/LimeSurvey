import { defaultScheduler, notifyManager } from '@tanstack/react-query'

const syncScheduler = (callback) => callback()

/**
 * Runs `callback` with React Query cache notifications delivered synchronously.
 *
 * By default React Query tells components about `setQueryData` changes in a
 * `setTimeout(0)`. @hello-pangea/dnd finishes a drop inside `flushSync`, so a
 * reorder written to the query cache in `onDragEnd` arrives one frame late and
 * the list briefly snaps back to its old order. Wrap `onDragEnd` with this so
 * the reordered data renders in the same commit as the drop.
 */
export const runWithSyncQueryNotifications = (callback) => {
  notifyManager.setScheduler(syncScheduler)

  try {
    return callback()
  } finally {
    notifyManager.setScheduler(defaultScheduler)
  }
}
