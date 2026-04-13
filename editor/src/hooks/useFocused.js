import { useQuery } from '@tanstack/react-query'
import { cloneDeep } from 'lodash'
import { useLocation, useNavigate } from 'react-router-dom'

import { getQuestionTypeInfo } from 'components'
import { EntitiesType, STATES } from 'helpers'
import { queryClient } from 'queryClient'

export const useFocused = (focused, groupIndex, questionIndex) => {
  const navigate = useNavigate()
  const location = useLocation()

  const { data } = useQuery({
    queryKey: [STATES.FOCUSED_ENTITY],
    queryFn: () => {
      return {
        focused,
        groupIndex,
        questionIndex,
      }
    },
    staleTime: Infinity,
    meta: {
      persist: true,
    },
  })

  const setFocused = (
    focusElement,
    groupIndex,
    questionIndex,
    unfocus = true
  ) => {
    if (unfocus) unFocus()

    const focused = cloneDeep(focusElement) || {}

    const newParams = buildFocusParams(
      location.search,
      focused,
      groupIndex,
      questionIndex
    )
    const newSearch = newParams.toString()

    navigate({ pathname: location.pathname, search: newSearch })

    queryClient.setQueryData([STATES.FOCUSED_ENTITY], {
      focused,
      groupIndex,
      questionIndex,
    })
  }

  const unFocus = () => {
    navigate(location.pathname)
    return queryClient.setQueryData([STATES.FOCUSED_ENTITY], {})
  }

  const buildFocusParams = (
    currentSearch,
    focused,
    groupIndex,
    questionIndex
  ) => {
    const params = new URLSearchParams(currentSearch)

    const focusKeys = [
      EntitiesType.question,
      EntitiesType.group,
      EntitiesType.welcomeScreen,
      EntitiesType.endScreen,
    ]
    focusKeys.forEach((key) => params.delete(key))

    if (questionIndex !== undefined) {
      params.set(EntitiesType.question, focused.qid)
    } else if (groupIndex !== undefined) {
      params.set(EntitiesType.group, focused.gid)
    } else if (
      focused.info?.type === getQuestionTypeInfo().WELCOME_SCREEN.type
    ) {
      params.set(EntitiesType.welcomeScreen, 'true')
    } else if (focused.info?.type === getQuestionTypeInfo().END_SCREEN.type) {
      params.set(EntitiesType.endScreen, 'true')
    }

    return params
  }

  return {
    setFocused,
    unFocus,
    ...data,
  }
}
