import { within, userEvent } from '@storybook/test'
import { expect } from '@storybook/test'
import { DragDropContext } from 'react-beautiful-dnd'

import { useSurvey } from 'hooks'
import { waitFor } from 'sbook'
import { RowQuestionGroup as RowQuestionGroupComponent } from '../RowQuestionGroup'

export default {
  title: 'General/RowQuestionGroup',
  component: RowQuestionGroupComponent,
  args: { survey: {}, update: () => {} },
}

let Survey
export const RowQuestionGroup = () => {
  const { survey } = useSurvey()
  Survey = survey

  if (!survey.questionGroups) {
    return <></>
  }

  return (
    <DragDropContext onDragEnd={() => {}} onDragUpdate={() => {}}>
      <RowQuestionGroupComponent
        questionGroup={survey.questionGroups[0]}
        language={survey.language}
        onTitleClick={() => {}}
      />
    </DragDropContext>
  )
}

RowQuestionGroup.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)
  await waitFor(() => canvas.getAllByTestId('sidebar-row-toggler-button'))
  const togglerButton = canvas.getAllByTestId('sidebar-row-toggler-button')[0]

  await step('Should be able to toggle the list', async () => {
    const questions = canvas.queryAllByTestId('sidebar-row-question')

    await userEvent.click(togglerButton)

    if (questions.length === 0) {
      await expect(
        canvas.queryAllByTestId('sidebar-row-question').length
      ).toBeGreaterThanOrEqual(Survey.questionGroups[0].questions.length)
    } else if (questions.length > 0) {
      await expect(canvas.queryAllByTestId('sidebar-row-question').length).toBe(
        0
      )
    }
  })
}
