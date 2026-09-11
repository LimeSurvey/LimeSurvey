import { useCallback } from 'react'
import { useQuery } from '@tanstack/react-query'

import { queryClient } from 'queryClient'
import { extractStrings, STATES } from 'helpers'

// todo: create a hash for the error messages
export const useErrors = () => {
  let { data = {} } = useQuery({
    queryKey: [STATES.ERRORS],
    queryFn: () => [],
    cacheTime: Infinity,
    staleTime: Infinity,
  })

  let { data: errorMessages } = useQuery({
    queryKey: [STATES.ERROR_MESSAGES],
    queryFn: () => [],
    cacheTime: Infinity,
    staleTime: Infinity,
  })

  const setErrorsFromPatchResponse = useCallback((patchResponse = []) => {
    queryClient.setQueryData([STATES.ERRORS], {})

    const errors = organizeErrors(patchResponse)
    queryClient.setQueryData([STATES.ERRORS], { ...errors })
  }, [])

  const setErrors = (errors) => {
    queryClient.setQueryData([STATES.ERRORS], { ...errors })
  }

  const setErrorMessages = (errorMessages) => {
    queryClient.setQueryData([STATES.ERROR_MESSAGES], errorMessages)
  }

  const clearErrorMessages = () => {
    setErrorMessages([])
  }

  const clearErrors = () => {
    setErrorsFromPatchResponse([])
  }

  const getError = (id, entity) => {
    if (data[id] && data[id][entity]) {
      return data[id][entity]
    }

    return null
  }

  const removeError = (id, entity) => {
    const errors = { ...data }

    if (getError(id, entity)) {
      delete errors[id][entity]
    }

    setErrors(errors)
  }

  const organizeErrors = (data) => {
    let errorInfo = {}
    let errorMessages = extractStrings(data, ['id', 'entity', 'context', 'op'])

    data.forEach((item) => {
      const { id, entity, error } = item
      if (!errorInfo[id]) {
        errorInfo[id] = {}
      }
      errorInfo[id][entity] = error
    })

    setErrorMessages(errorMessages)
    return errorInfo
  }

  return {
    errors: data,
    errorMessages,
    clearErrors,
    getError,
    setErrorsFromPatchResponse,
    clearErrorMessages,
    removeError,
  }
}
