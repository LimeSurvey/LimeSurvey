import { createBufferOperation } from 'helpers'

import { useBuffer } from './useBuffer'
import { useSurvey } from './useSurvey'

/**
 * Shows or hides question codes for the whole survey.
 * showCode and showNumber are set together: the sidebar reads showCode while
 * the canvas (answer and subquestion codes) reads showNumber.
 */
export const useToggleQuestionCodes = (surveyId) => {
  const { survey, update } = useSurvey(surveyId)
  const { addToBuffer } = useBuffer()
  const areCodesVisible = !!survey?.showQNumCode?.showCode

  const toggleQuestionCodes = () => {
    const next = !areCodesVisible
    const operationAttr = {
      showQNumCode: {
        showCode: next,
        showNumber: next,
      },
    }
    update(operationAttr)
    addToBuffer(createBufferOperation(surveyId).survey().update(operationAttr))
  }

  return { areCodesVisible, toggleQuestionCodes }
}
