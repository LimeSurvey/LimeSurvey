import { useQuery } from '@tanstack/react-query'

import { queryClient } from 'query'

// This hook uses react-query to cache state making
// - values accessible between components and also
// - between application reloads

export const useAppState = (key, initValue) => {
  const { data } = useQuery({
    queryKey: ['appState', key],
    queryFn: () => initValue,
    staleTime: Infinity,
    cacheTime: Infinity,
  })

  const update = (updateKey, value) => {
    return queryClient.setQueryData(['appState', updateKey], value)
  }

  const setValue = (newValue) => update(key, newValue)

  return [data, setValue]
}
