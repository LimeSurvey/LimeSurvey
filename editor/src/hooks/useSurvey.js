import { useCallback } from 'react'
import { debounce } from 'lodash'
import { useQuery } from '@tanstack/react-query'

import surveyData from 'helpers/data/survey-detail.json'

import { queryClient } from 'queryClient'
import { STATES, SURVEY_NOT_MODIFIED } from 'helpers'

import {
  useAppState,
  useQueryRetry,
  useSurveyRequestTimestamp,
  useSurveyService,
} from './'
import { useOperationCallback } from './useOperationCallback'

const PATCH_DEBOUNCE_TIME = 1000

export const useSurvey = (id) => {
  const { triggerCallbacks } = useOperationCallback()
  const { surveyService } = useSurveyService()
  const { refetchInterval, handleRetry } = useQueryRetry({})
  const { getSurveyRequestTimestamp, setSurveyRequestTimestamp } =
    useSurveyRequestTimestamp()
  const [, setSurveyRefreshRequired] = useAppState(
    STATES.SURVEY_REFRESH_REQUIRED
  )
  const [surveyHash, setSurveyHash] = useAppState(STATES.SURVEY_HASH, {
    updateHash: 0,
    refetchHash: 0,
  })

  let { data } = useQuery({
    queryKey: [STATES.SURVEY],
    queryFn: async ({ signal }) => {
      return fetchSurvey(id, signal)
    },
    staleTime: Infinity,
    refetchOnWindowFocus: 'always',
    refetchInterval: refetchInterval,
    retry: handleRetry,
    meta: {
      persist: true,
    },
  })

  const {
    data: questionsFieldNamesMap,
    refetch: refetchQuestionsFieldNamesMap,
  } = useQuery({
    queryKey: [STATES.SURVEY_QUESTIONS_FIELDNAME],
    queryFn: async ({ signal }) => {
      return fetchSurveyQuestionsFieldnameMap(id, signal)
    },
    initialData: {},
    cacheTime: Infinity,
    staleTime: Infinity,
  })

  const fetchSurvey = async (id, signal) => {
    const currentSurveyId = data?.survey?.sid?.toString()
    const isSameSurvey = id?.toString() === currentSurveyId
    const isDemoMode = process.env.REACT_APP_DEMO_MODE === 'true'
    const isStorybook = process.env.STORYBOOK_DEV === 'true'
    let lastRequestedAt = getSurveyRequestTimestamp(id)

    if (!isSameSurvey) {
      setSurvey({}) // triggers the loading UI state.
      lastRequestedAt = null // Reset last requestedAt if survey ID changes to skipp timestamp in getSurveyDetail
    }

    if (isStorybook || isDemoMode) {
      setSurvey(surveyData.survey)
      return surveyData
    }

    const newData = await surveyService.getSurveyDetail(
      id,
      signal,
      lastRequestedAt
    )

    if (!newData) return {}

    if (newData?.survey === SURVEY_NOT_MODIFIED) {
      return queryClient.getQueryData([STATES.SURVEY]) || { survey: {} }
    }

    const operationsBuffer = queryClient.getQueryData([STATES.BUFFER])
    const isPatchSurveyRunning = queryClient.getQueryData([
      'appState',
      STATES.IS_PATCH_SURVEY_RUNNING,
    ])
    // Return currentData if the buffer is not empty.
    // We are also checking if the data is defined because when the app first loads the data or the survey is not defined yet.
    if ((operationsBuffer?.length || isPatchSurveyRunning) && data) {
      // we should schdule a refetch to update the survey data.
      setSurveyRefreshRequired(true)
      queryClient.cancelQueries({ queryKey: [STATES.SURVEY] })
      return data
    }
    setSurveyRequestTimestamp(currentSurveyId)
    setSurvey({
      ...newData.survey,
      themesettings: {
        ...newData.survey.themesettings,
      },
    })

    // Survey hash is used to keep track of the survey data.
    setSurveyHash({ ...surveyHash, refetchHash: Math.random() })
    return newData
  }

  let { data: surveyList } = useQuery({
    queryKey: ['surveyList'],
    queryFn: async () => {
      if (process.env.REACT_APP_DEMO_MODE !== 'true') {
        return surveyService.getSurveyList() || []
      }

      return []
    },
    staleTime: Infinity,
    refetchOnWindowFocus: 'always',
    refetchInterval,
    retry: handleRetry,
  })

  const clearSurvey = () => {
    if (
      process.env.REACT_APP_DEMO_MODE === 'true' ||
      process.env.STORYBOOK_DEV === 'true'
    ) {
      return
    }

    setSurvey({})
  }

  const setSurvey = (surveyData = {}) => {
    queryClient.setQueryData([STATES.SURVEY], {
      survey: {
        ...surveyData,
      },
    })
  }

  const updateSurvey = (updateData) => {
    setSurveyHash({ ...surveyHash, updateHash: Math.random() })

    queryClient.setQueryData([STATES.SURVEY], {
      survey: {
        ...data.survey,
        ...updateData,
      },
    })
  }

  const surveyPatch = useCallback(
    debounce(
      async (
        operations,
        beforeCallback = () => {},
        thenCallback = () => {},
        finallyCallback = () => {},
        catchCallBack = () => {}
      ) => {
        if (!operations.length) {
          return
        }

        if (
          // process.env.REACT_APP_DEMO_MODE === 'true' ||
          // Storybook dev context
          process.env.STORYBOOK_DEV
        ) {
          return
        }

        const finalCallback = (results) => {
          thenCallback(results)
          triggerCallbacks(operations, results)
        }

        beforeCallback()
        return surveyService
          .patchSurvey(operations)
          .then(finalCallback)
          .finally(finallyCallback)
          .catch(catchCallBack)
      },
      PATCH_DEBOUNCE_TIME
    ),
    [surveyService.surveyId, surveyService.auth?.restHeaders?.Authorization]
  )

  const fetchSurveyQuestionsFieldnameMap = async (sid, signal) => {
    if (!sid || process.env.REACT_APP_DEMO_MODE === 'true') {
      return
    }

    const fieldnames = await surveyService.getSurveyQuestionsFieldname(
      sid,
      signal
    )
    if (!fieldnames) {
      return
    }

    return fieldnames
  }

  return {
    survey: data?.survey || {},
    surveyList: surveyList?.surveys || [],
    update: updateSurvey,
    language: data?.survey?.language,
    surveyPatch,
    clearSurvey,
    fetchSurvey,
    surveyMenus: data?.survey?.surveyMenus,
    surveyHash,
    questionsFieldNamesMap: questionsFieldNamesMap || {},
    refetchQuestionsFieldNamesMap,
  }
}
