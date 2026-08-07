// Import shared mocks
import 'tests/mocks'

import { RowQuestionGroup } from './RowQuestionGroup'
import { waitFor, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import surveyData from 'helpers/data/survey-detail.json'
import { DragDropContext } from 'react-beautiful-dnd'
import { renderWithProviders } from 'tests/testUtils'

describe('RowQuestionGroup', () => {
  test('Should be able to toggle the list', async () => {
    const questionGroup = surveyData.survey.questionGroups[0]

    renderWithProviders(
      <DragDropContext onDragEnd={() => {}} onDragUpdate={() => {}}>
        <RowQuestionGroup
          questionGroup={questionGroup}
          language={surveyData.survey.language}
          onTitleClick={() => {}}
          groupIndex={0}
        />
      </DragDropContext>
    )

    const togglerButton = await waitFor(
      () => screen.getAllByTestId('sidebar-row-toggler-button')[0]
    )

    const questionsBefore = screen.queryAllByTestId('sidebar-row-question')

    await userEvent.click(togglerButton)

    await waitFor(() => {
      const questionsAfter = screen.queryAllByTestId('sidebar-row-question')
      if (questionsBefore.length === 0) {
        expect(questionsAfter.length).toBeGreaterThanOrEqual(
          questionGroup.questions.length
        )
      } else if (questionsBefore.length > 0) {
        expect(questionsAfter.length).toBe(0)
      }
    })
  })
})
