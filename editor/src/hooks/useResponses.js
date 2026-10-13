import { keepPreviousData, useMutation, useQuery } from '@tanstack/react-query'
import { useMemo, useRef } from 'react'

import { getApiUrl, STATES } from 'helpers'
import { ResponseService } from 'services'
import { queryClient } from 'queryClient'

import useAuth from './useAuth'

const isApiError = (payload) =>
  typeof payload === 'object' && payload !== null && 'httpStatus' in payload

/**
 * Fetch survey responses for the requested page, filters, and sorting, refetching
 * on every mount and retaining the previous result while the query changes.
 *
 * @param {string|number} surveyId Survey to query.
 * @param {Object} pagination Page selection with a zero-based pageIndex and pageSize.
 * @param {Object} filters Response filters sent to the API.
 * @param {Array} sorting Sort descriptors sent to the API.
 * @param {Array} filterSet Condition designer filter, in the shape the API accepts.
 * @returns {Object} Responses, the error a rejected filter came back with, fetch
 *   status, refetch, and mutateOperations. A rejected filter keeps the rows this
 *   survey already had rather than emptying the table.
 *   Mutations invalidate response queries on success and refetch after success or failure.
 */
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

  const error = isApiError(payload) ? payload : null

  const lastResponses = useRef({ surveyId: null, payload: undefined })
  if (!error && payload && !isPlaceholderData) {
    lastResponses.current = { surveyId, payload }
  }

  const previous =
    lastResponses.current.surveyId === surveyId
      ? lastResponses.current.payload
      : undefined

  const exportMutation = useMutation({
    mutationFn: (options) => responseService.exportResponses(options),
  })

  /**
   * Find the zero-based page that lists a response under the current filters
   * and the default "id DESC" sort, by counting the responses with a higher id.
   *
   * @param {string|number} responseId Response to locate.
   * @returns {Promise<number>} Page index containing the response.
   */
  const findResponsePageIndex = async (responseId) => {
    const result = await responseService.getSurveyResponses(surveyId, {
      pagination: { pageIndex: 0, pageSize: 1 },
      filters: {
        ...filters,
        locateResponse: {
          keys: ['id'],
          filterMethod: 'greaterThan',
          value: responseId,
        },
      },
      // Counted under the same filter the table shows, or the page would be
      // computed against a larger result set than the one being paged.
      filterSet,
      sorting: [],
    })
    const precedingCount = result?._meta?.pagination?.totalItems ?? 0

    return Math.floor(precedingCount / pagination.pageSize)
  }

  return {
    responses: error ? previous : payload,
    error,
    isFetching,
    refetch,
    mutateOperations,
    exportResponses: exportMutation.mutateAsync,
    isExporting: exportMutation.isPending,
    findResponsePageIndex,
  }
}
