import { useQuery } from '@tanstack/react-query'
import { cloneDeep } from 'lodash'
import { useLocation, useNavigate } from 'react-router-dom'

import { getQuestionTypeInfo } from 'components'
import { EntitiesType, STATES, resolveTempIds } from 'helpers'
import { queryClient } from 'queryClient'

// Lets an open panel with unsaved changes (Condition Designer, ExpressionScript)
// ask the user before the focus moves away from its question
let focusLeaveGuard = null

/**
 * Registers the guard asked before the focus leaves the focused question.
 *
 * @param {Function} guard Called with `proceed`: Calls it right away, or after the user confirmed leaving.
 * @returns {Function} Removes the guard again.
 */
export const setFocusLeaveGuard = (guard) => {
  focusLeaveGuard = guard
  return () => {
    if (focusLeaveGuard === guard) focusLeaveGuard = null
  }
}

/**
 * Whether the target is the focused question, also when the focused question
 * still has the temp ID the server just replaced with the target's real ID.
 *
 * @param {object|null} target The element to focus, null to unfocus.
 * @returns {boolean} True if the focus stays on the same question.
 */
const isSameFocusedQuestion = (target) => {
  const current = queryClient.getQueryData([STATES.FOCUSED_ENTITY])?.focused
  if (!current?.qid || !target?.qid) return false

  return (
    String(current.qid) === String(target.qid) ||
    String(resolveTempIds(current.qid)) === String(target.qid)
  )
}

/**
 * Applies a focus change, after asking the registered guard if the focus
 * leaves the focused question.
 *
 * @param {object|null} target The element to focus, null to unfocus.
 * @param {Function} apply Applies the focus change.
 * @returns {*} The result of apply when it ran right away.
 */
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
