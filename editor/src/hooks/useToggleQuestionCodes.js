import { useCallback } from 'react'

import { STATES } from 'helpers'

import { useAppState } from './useAppState'

// Shows or hides question codes in the editor view
export const useToggleQuestionCodes = () => {
  const [areCodesVisible = false, setAreCodesVisible] = useAppState(
    STATES.SHOW_QUESTION_CODES_IN_EDITOR,
    false
  )

  const toggleQuestionCodes = useCallback(() => {
    setAreCodesVisible(!areCodesVisible)
  }, [areCodesVisible, setAreCodesVisible])

  return { areCodesVisible: !!areCodesVisible, toggleQuestionCodes }
}
