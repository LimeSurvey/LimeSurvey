import { keepPreviousData, useMutation, useQuery } from '@tanstack/react-query'
import { useMemo } from 'react'

import { getApiUrl, STATES } from 'helpers'
import { ResponseService } from 'services'
import { queryClient } from 'queryClient'

import useAuth from './useAuth'

/**
 * Fetch survey responses for the requested page, filters, and sorting, refetching
 * on every mount and retaining the previous result while the query changes.
 *
 * @param {string|number} surveyId Survey to query.
 * @param {Object} pagination Page selection with a zero-based pageIndex and pageSize.
 * @param {Object} filters Response filters sent to the API.
 * @param {Array} sorting Sort descriptors sent to the API.
 * @returns {Object} Responses, fetch status, refetch, and mutateOperations.
 *   Mutations invalidate response queries on success and refetch after success or failure.
 */
export function useResponses(surveyId, pagination, filters, sorting) {
  const auth = useAuth()
  const responseService = useMemo(
    () => new ResponseService(auth, surveyId, getApiUrl()),
    [auth]
  )

  const {
    data: responses,
    isFetching,
    refetch,
  } = useQuery({
    queryKey: [
      STATES.SURVEY_RESPONSES,
      surveyId,
      pagination.pageIndex,
      pagination.pageSize,
      filters,
      sorting,
    ],
    queryFn: () =>
      responseService.getSurveyResponses(surveyId, {
        pagination,
        filters,
        sorting,
      }),
    refetchOnMount: 'always',
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

  return {
    responses,
    isFetching,
    refetch,
    mutateOperations,
  }
}
