import { useQuery } from '@tanstack/react-query'
import { cloneDeep } from 'lodash'
import { useLocation, useNavigate } from 'react-router-dom'

import { getQuestionTypeInfo } from 'components'
import { EntitiesType, STATES, isResolvedTempId } from 'helpers'
import { queryClient } from 'queryClient'

// Lets an open panel with unsaved changes (e.g. the Condition Designer) ask the
// user before the focus moves away from its question. Called with `proceed`,
// it either calls it right away or after the user confirmed leaving.
let focusLeaveGuard = null

/**
 * Register a guard for leaving the focused question.
 * @param {Function} guard (proceed) => void
 * @returns {Function} unregister
 */
export const setFocusLeaveGuard = (guard) => {
  focusLeaveGuard = guard
  return () => {
    if (focusLeaveGuard === guard) focusLeaveGuard = null
  }
}

const isSameFocusedQuestion = (target) => {
  const current = queryClient.getQueryData([STATES.FOCUSED_ENTITY])?.focused
  if (!current?.qid || !target?.qid) return false

  return (
    String(current.qid) === String(target.qid) ||
    isResolvedTempId(current.qid, target.qid)
  )
}

const guardFocusChange = (target, apply) => {
  if (!focusLeaveGuard || isSameFocusedQuestion(target)) {
    return apply()
  }

  focusLeaveGuard(apply)
}

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

  const setFocused = (...args) =>
    guardFocusChange(args[0], () => applyFocused(...args))

  const applyFocused = (
    focusElement,
    groupIndex,
    questionIndex,
    unfocus = true
  ) => {
    if (unfocus) applyUnFocus()

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

  const unFocus = () => guardFocusChange(null, applyUnFocus)

  const applyUnFocus = () => {
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
    focused: data?.focused || {},
    ...data,
  }
}
