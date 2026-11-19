import { useMemo } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'

import { getApiUrl } from 'helpers'
import { LabelSetService } from 'services'

import useAuth from './useAuth'

const LABEL_SETS_QUERY_KEY = 'labelSets'

// Error code of an overwrite that was rejected because the set changed meanwhile
export const LABEL_SET_CONFLICT = 'LABEL_SET_CONFLICT'

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

/**
 * One label set with its labels and revision.
 *
 * @param {object} options
 * @param {number|null} options.lid Id of the label set
 * @param {boolean} options.enabled Fetch only while true, e.g. while a set is selected
 */
export const useLabelSet = ({ lid = null, enabled = true } = {}) => {
  const labelSetService = useLabelSetService()

  const {
    data: labelSet,
    isFetching,
    isError,
    refetch,
  } = useQuery({
    // Below the list key, so saving a label set invalidates both
    queryKey: [LABEL_SETS_QUERY_KEY, 'detail', lid],
    queryFn: () => labelSetService.getLabelSet(lid),
    enabled: enabled && !!lid,
    // The set can change in the classic editor, so refetch whenever it is selected again
    staleTime: 0,
  })

  return { labelSet, isFetching, isError, refetch }
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
