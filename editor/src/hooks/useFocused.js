import { useQuery } from '@tanstack/react-query'
import { cloneDeep } from 'lodash'
import { useLocation, useNavigate } from 'react-router-dom'

import { getQuestionTypeInfo } from 'components'
import { EntitiesType, STATES, isResolvedTempId } from 'helpers'
import { queryClient } from 'queryClient'

// Lets an open panel with unsaved changes (e.g. the Condition Designer) ask the
// user before the focus moves away from its question. Called with
// (proceed, cancel), it either calls proceed right away, or asks the user and
// then calls proceed (leave) or cancel (stay).
let focusLeaveGuard = null
// The focus change waiting for the user's answer, while the guard is asking.
let pendingFocusChange = null
// True while running an action the user already agreed to leave for.
let isLeaveConfirmed = false

/**
 * Register a guard for leaving the focused question.
 * @param {Function} guard (proceed) => void
 * @returns {Function} unregister
 */
export const setFocusLeaveGuard = (guard) => {
  focusLeaveGuard = guard
  return () => {
    if (focusLeaveGuard === guard) {
      focusLeaveGuard = null
      pendingFocusChange = null
    }
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
  if (!focusLeaveGuard || isLeaveConfirmed || isSameFocusedQuestion(target)) {
    return apply()
  }

  // The user is already being asked (one click can request several focus
  // changes): don't ask again, apply the latest request if they leave.
  if (pendingFocusChange) {
    pendingFocusChange = apply
    return
  }

  pendingFocusChange = apply
  focusLeaveGuard(
    () => {
      const applyPending = pendingFocusChange
      pendingFocusChange = null
      applyPending?.()
    },
    () => {
      pendingFocusChange = null
    }
  )
}

/**
 * Run an action without asking the focus leave guard again, e.g. because the
 * user already confirmed leaving the focused question for it.
 */
export const runWithoutFocusLeaveGuard = (action) => {
  const wasLeaveConfirmed = isLeaveConfirmed
  isLeaveConfirmed = true
  try {
    return action()
  } finally {
    isLeaveConfirmed = wasLeaveConfirmed
  }
}

/**
 * Run an action that leaves the focused question (e.g. deleting or duplicating
 * it) only once the focus leave guard allows it, i.e. right away when there are
 * no unsaved changes, otherwise after the user chose to leave without saving.
 * Focus changes made by the action are not guarded again.
 */
export const confirmFocusLeave = (action) =>
  guardFocusChange(null, () => runWithoutFocusLeaveGuard(action))

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
