// Import shared mocks
import 'tests/mocks'

import { RowQuestionGroup } from './RowQuestionGroup'
import { waitFor, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import surveyData from 'helpers/data/survey-detail.json'
import { DragDropContext } from 'react-beautiful-dnd'
import { renderWithProviders } from 'tests/testUtils'
import { act } from 'react'
import { STATES } from 'helpers'
import { queryClient } from 'queryClient'

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

  describe('Group actions menu', () => {
    const renderRow = () =>
      renderWithProviders(
        <DragDropContext onDragEnd={() => {}} onDragUpdate={() => {}}>
          <RowQuestionGroup
            questionGroup={surveyData.survey.questionGroups[0]}
            language={surveyData.survey.language}
            onTitleClick={() => {}}
            groupIndex={0}
          />
        </DragDropContext>
      )

    const setPermission = async (value) => {
      await act(async () => {
        queryClient.setQueryData(
          ['appState', STATES.HAS_SURVEY_UPDATE_PERMISSION],
          value
        )
      })
    }

    afterEach(async () => {
      await setPermission(true)
    })

    test('Shows Duplicate/Delete when the user has survey update permission', async () => {
      await renderRow()

      await userEvent.click(
        (await screen.findAllByTestId('meatball-menu-button'))[0]
      )

      expect(await screen.findByTestId('duplicate-button')).toBeInTheDocument()
      expect(screen.getByTestId('delete-button')).toBeInTheDocument()
      expect(screen.getByTestId('show-logic-button')).toBeInTheDocument()
    })

    test('Hides Duplicate/Delete when the user lacks survey update permission', async () => {
      // ComponentWrapper resets the permission to true on mount, so revoke it after render
      await renderRow()
      await setPermission(false)

      await userEvent.click(
        (await screen.findAllByTestId('meatball-menu-button'))[0]
      )

      expect(await screen.findByTestId('show-logic-button')).toBeInTheDocument()
      expect(screen.queryByTestId('duplicate-button')).not.toBeInTheDocument()
      expect(screen.queryByTestId('delete-button')).not.toBeInTheDocument()
    })
  })
})
