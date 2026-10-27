import { useEffect, useMemo } from 'react'
import { useQuery } from '@tanstack/react-query'

import { UserSettingsService } from 'services'
import {
  CODE_PREFIX_SETTING_NAMES,
  getApiUrl,
  resolveCodePrefix,
} from 'helpers'
import { showErrorMessage } from 'components/ConditionDesigner/utils'

import useAuth from './useAuth'

const useCodePrefixSetting = (settingName, service) =>
  useQuery({
    queryKey: ['userSettings', settingName],
    queryFn: () => service.getUserSettingValue(settingName),
    enabled: !!service,
    staleTime: Infinity,
    retry: 1,
  })

/**
 * Loads the user's answer option / subquestion code prefixes.
 * A prefix is '' while loading, when no setting is stored or when loading
 * failed, which produces purely numeric codes; there is no letter fallback.
 */
export const useCodePrefixes = () => {
  const auth = useAuth()
  const service = useMemo(
    () => (auth ? new UserSettingsService(auth, getApiUrl()) : null),
    [auth]
  )

  const answer = useCodePrefixSetting(CODE_PREFIX_SETTING_NAMES.answer, service)
  const subquestion = useCodePrefixSetting(
    CODE_PREFIX_SETTING_NAMES.subquestion,
    service
  )

  const isError = answer.isError || subquestion.isError
  useEffect(() => {
    if (isError) {
      showErrorMessage(
        t(
          'Could not load your answer option and subquestion code prefix settings. Numeric codes are used.'
        )
      )
    }
  }, [isError])

  return {
    answerPrefix: resolveCodePrefix(answer.data),
    subquestionPrefix: resolveCodePrefix(subquestion.data),
    isLoading: answer.isLoading || subquestion.isLoading,
    isError,
  }
}
