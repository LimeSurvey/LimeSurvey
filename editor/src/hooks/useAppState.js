import { useQuery } from '@tanstack/react-query'
import { useCallback } from 'react'
import { queryClient } from 'queryClient'

// This hook uses react-query to cache state making
// - values accessible between components and also
// - between application reloads

export const useAppState = (key, initValue = '', config = {}) => {
  const { data } = useQuery({
    queryKey: ['appState', key],
    queryFn: () => initValue,
    select: (data) => data,
    staleTime: Infinity,
    cacheTime: Infinity,
    meta: {
      persist: true,
    },
    ...config,
  })

  const setValue = useCallback((newValue) => {
    queryClient.setQueryData(['appState', key], newValue)
  }, [])

  return [data, setValue]
}
