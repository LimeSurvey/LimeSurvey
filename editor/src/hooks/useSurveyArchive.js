import { useMemo } from 'react'
import { useQuery } from '@tanstack/react-query'

import { queryClient } from 'queryClient'
import { getApiUrl, STATES, SURVEY_ARCHIVES_QUERY_KEY_PREFIX } from 'helpers'
import { SurveyArchiveService } from 'services'

import useAuth from './useAuth'

export const useSurveyArchive = (sid) => {
  const auth = useAuth()

  const surveyArchiveService = useMemo(() => {
    return new SurveyArchiveService(auth, sid, getApiUrl())
  }, [auth, sid])

  const { data: surveyArchives, refetch } = useQuery({
    queryKey: [STATES.SURVEY_ARCHIVES, sid],
    queryFn: async ({ signal }) => {
      const archives = await surveyArchiveService.getSurveyArchives(sid, signal)
      if (!archives) return []
      const archivesArray = Array.isArray(archives) ? archives : []
      return archivesArray
        .filter((archive) => archive.newformat && archive.count > 0)
        .sort((a, b) => b.timestamp - a.timestamp)
    },
    enabled: !!sid && !process.env.REACT_APP_DEMO_MODE,
    initialData: [],
    staleTime: Infinity,
    cacheTime: Infinity,
  })

  const fetchFilteredSurveyArchivesByBase = async (
    sid,
    baseTable = 'tokens',
    signal
  ) => {
    if (!sid) return []

    try {
      const response = await surveyArchiveService.getSurveyArchivesByBaseTable(
        sid,
        baseTable,
        signal
      )

      const archivesArray = Array.isArray(response)
        ? response
        : Object.values(response || {})

      const filteredArchives = archivesArray.sort(
        (a, b) => b.timestamp - a.timestamp
      )
      const SURVEY_ARCHIVED_TOKENS_QUERY_KEY = `${SURVEY_ARCHIVES_QUERY_KEY_PREFIX}${baseTable}`

      queryClient.setQueryData(
        [SURVEY_ARCHIVED_TOKENS_QUERY_KEY],
        filteredArchives
      )
      return filteredArchives
    } catch (error) {
      return []
    }
  }

  return {
    surveyArchives,
    fetchSurveyArchives: refetch,
    fetchFilteredSurveyArchivesByBase,
  }
}
