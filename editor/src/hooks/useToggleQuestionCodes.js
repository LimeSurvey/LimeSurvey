import { createBufferOperation } from 'helpers'

import { useBuffer } from './useBuffer'
import { useSurvey } from './useSurvey'

/**
 * Shows or hides question codes for the whole survey.
 * Only showCode is flipped; showNumber is sent back unchanged because the
 * API needs both to build the single showqnumcode setting (B/N/C/X).
 */
export const useToggleQuestionCodes = (surveyId) => {
  const { survey, update } = useSurvey(surveyId)
  const { addToBuffer } = useBuffer()
  const areCodesVisible = !!survey?.showQNumCode?.showCode

  const toggleQuestionCodes = () => {
    const operationAttr = {
      showQNumCode: {
        showCode: !areCodesVisible,
        showNumber: !!survey?.showQNumCode?.showNumber,
      },
    }
    update(operationAttr)
    addToBuffer(createBufferOperation(surveyId).survey().update(operationAttr))
  }

  return { areCodesVisible, toggleQuestionCodes }
}
