import { keepPreviousData, useMutation, useQuery } from '@tanstack/react-query'
import { useMemo, useRef } from 'react'

import { getApiUrl, STATES } from 'helpers'
import { ResponseService } from 'services'
import { queryClient } from 'queryClient'

import useAuth from './useAuth'

// A failed request resolves with the normalized error rather than rejecting,
// so the payload has to be told apart from a real answer. Only errors carry an
// http status — and the status is 0 when the request never reached the server,
// so the key has to be looked for rather than read for truth.
const isApiError = (payload) =>
  typeof payload === 'object' && payload !== null && 'httpStatus' in payload

export function useResponses(
  surveyId,
  pagination,
  filters,
  sorting,
  filterSet
) {
  const auth = useAuth()
  const responseService = useMemo(
    () => new ResponseService(auth, surveyId, getApiUrl()),
    [auth]
  )

  const {
    data: payload,
    isFetching,
    isPlaceholderData,
    refetch,
  } = useQuery({
    // filterSet is part of the key, so changing the filter refetches rather
    // than showing the previous filter's rows.
    queryKey: [
      STATES.SURVEY_RESPONSES,
      surveyId,
      pagination.pageIndex,
      pagination.pageSize,
      filters,
      sorting,
      filterSet,
    ],
    queryFn: () =>
      responseService.getSurveyResponses(surveyId, {
        pagination,
        filters,
        sorting,
        filterSet,
      }),
    select: (data) => data,
    placeholderData: keepPreviousData,
  })

  const invalidate = () => {
    queryClient.invalidateQueries({
      queryKey: [STATES.SURVEY_RESPONSES, surveyId],
    })
  }

  const patchMutation = useMutation({
    mutationFn: (operations) => responseService.patchResponses(operations),
    onSuccess: invalidate,
    onSettled: refetch,
  })

  const mutateOperations = (operations) => {
    patchMutation.mutate(operations)
  }

  const error = isApiError(payload) ? payload : null

  // The last answer that actually held responses. A rejected filter would
  // otherwise leave the page with an error object where the rows should be —
  // either read as responses and crash, or withheld and leave the page loading
  // forever. Keeping the previous rows lets the message explain itself.
  const lastResponses = useRef({ surveyId: null, payload: undefined })
  if (!error && payload && !isPlaceholderData) {
    lastResponses.current = { surveyId, payload }
  }

  const previous =
    lastResponses.current.surveyId === surveyId
      ? lastResponses.current.payload
      : undefined

  return {
    responses: error ? previous : payload,
    error,
    isFetching,
    refetch,
    mutateOperations,
  }
}
