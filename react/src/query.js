import { QueryClient } from '@tanstack/react-query'
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
  storage: window.localStorage,
  serialize: (data) => {
    // console.log('serialize');
    return JSON.stringify(data)
  },
  deserialize: (data) => {
    // console.log('deserialize');
    return JSON.parse(data)
  },
})

export const persistOptions = {
  persister,
  maxAge: persistenceMaxAgeMilliseconds,
}
