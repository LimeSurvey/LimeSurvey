import { act, useEffect } from 'react'
import { queryClient } from 'queryClient'
import { STATES } from 'helpers'

// Sets whether the survey is active; React Query notifies subscribers on a setTimeout(0), so wait a tick for the re-render
export const setSurveyActive = (value) =>
  act(async () => {
    queryClient.setQueryData(['appState', STATES.IS_SURVEY_ACTIVE], value)
    await new Promise((resolve) => setTimeout(resolve, 0))
  })

// Shows answer and subquestion codes on the canvas, also after the demo survey (re)loads
export const WithCodesShown = ({ survey, update, children }) => {
  const areCodesShown = !!survey?.showQNumCode?.showNumber

  useEffect(() => {
    if (!areCodesShown) {
      update({ showQNumCode: { showCode: true, showNumber: true } })
    }
  }, [areCodesShown])

  return areCodesShown ? children : null
}
