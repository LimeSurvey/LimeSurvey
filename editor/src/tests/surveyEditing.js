import { act } from 'react'
import { queryClient } from 'queryClient'
import { STATES } from 'helpers'

// Sets whether the survey is active; React Query notifies subscribers on a setTimeout(0), so wait a tick for the re-render
export const setSurveyActive = (value) =>
  act(async () => {
    queryClient.setQueryData(['appState', STATES.IS_SURVEY_ACTIVE], value)
    await new Promise((resolve) => setTimeout(resolve, 0))
  })

// Shows answer and subquestion codes on the canvas
export const WithCodesShown = ({ children }) => {
  queryClient.setQueryData(
    ['appState', STATES.SHOW_QUESTION_CODES_IN_EDITOR],
    true
  )

  return children
}
