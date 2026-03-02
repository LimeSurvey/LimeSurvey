import { keepPreviousData, useMutation, useQuery } from '@tanstack/react-query'
import { useMemo } from 'react'

import { getApiUrl, STATES } from 'helpers'
import { ResponseService } from 'services'
import { queryClient } from 'queryClient'

import useAuth from './useAuth'

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
    select: (data) => data.responses,
    placeholderData: keepPreviousData,
  })

  const invalidate = () => {
    queryClient.invalidateQueries({
      queryKey: ['surveyResponses', surveyId, pagination, filters, sorting],
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
