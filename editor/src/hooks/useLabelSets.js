import { useMemo } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'

import { getApiUrl } from 'helpers'
import { LabelSetService } from 'services'

import useAuth from './useAuth'

const LABEL_SETS_QUERY_KEY = 'labelSets'

export const useLabelSetService = () => {
  const auth = useAuth()

  return useMemo(() => new LabelSetService(auth, getApiUrl()), [auth])
}

/**
 * List label sets.
 *
 * @param {object} options
 * @param {string[]} options.languages Only list sets that contain all of these languages
 * @param {boolean} options.updatable Only list sets the user may overwrite
 * @param {boolean} options.enabled Fetch only while true, e.g. while a modal is open
 */
export const useLabelSets = ({
  languages = [],
  updatable = false,
  enabled = true,
} = {}) => {
  const labelSetService = useLabelSetService()

  const {
    data: labelSets = [],
    isFetching,
    isError,
    refetch,
  } = useQuery({
    queryKey: [LABEL_SETS_QUERY_KEY, languages, updatable],
    queryFn: () => labelSetService.getLabelSets({ languages, updatable }),
    enabled,
    // The list can change in the classic editor, so refetch whenever a modal opens
    staleTime: 0,
  })

  return { labelSets, isFetching, isError, refetch }
}

export const useSaveLabelSet = () => {
  const labelSetService = useLabelSetService()
  const queryClient = useQueryClient()

  const { mutateAsync, isPending } = useMutation({
    mutationFn: (labelSet) => labelSetService.saveLabelSet(labelSet),
    onSuccess: () =>
      queryClient.invalidateQueries({ queryKey: [LABEL_SETS_QUERY_KEY] }),
  })

  return { saveLabelSet: mutateAsync, isSaving: isPending }
}
