import { QueryClient, defaultShouldDehydrateQuery } from '@tanstack/react-query'
import { createSyncStoragePersister } from '@tanstack/query-sync-storage-persister'

const millisecondsPerDay = 1000 * 60 * 60 * 24
const persistenceMaxAgeMilliseconds = millisecondsPerDay * 30

export const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      // These are default values and can be set per query.

      refetchOnWindowFocus: true,

      // The staleTime is the time in milliseconds after data is considered stale.
      staleTime: Infinity,

      // The cacheTime is the time in milliseconds that unused/inactive cache data remains in memory.
      // - When a query's cache becomes unused or inactive, that cache data will be garbage collected after
      // - this duration. When different cache times are specified, the longest one will be used.
      cacheTime: persistenceMaxAgeMilliseconds,
    },
  },
})

const persister = createSyncStoragePersister({
  storage: process.env.NODE_ENV === 'test' ? null : window.localStorage,
  serialize: (data) => {
    return JSON.stringify(data)
  },
  deserialize: (data) => {
    return JSON.parse(data)
  },
})

const commitHash = process.env.REACT_APP_COMMIT_HASH
  ? process.env.REACT_APP_COMMIT_HASH
  : ''

export const persistOptions = {
  persister,
  buster: process.env.REACT_APP_RELEASE + '-' + commitHash.substring(0, 10),
  dehydrateOptions: {
    shouldDehydrateQuery: (query) => {
      return (
        defaultShouldDehydrateQuery(query) &&
        query.meta &&
        query.meta.persist === true
      )
    },
  },
}
