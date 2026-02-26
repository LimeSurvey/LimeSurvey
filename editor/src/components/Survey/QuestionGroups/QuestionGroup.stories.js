import { expect, userEvent, waitFor, within } from '@storybook/test'

import QuestionGroupComponent from './QuestionGroup'
import { sleep } from 'helpers'

export default {
  title: 'General/QuestionGroup',
  component: QuestionGroupComponent,
}

export const QuestionGroup = ({ survey, update }) => {
  return (
    <QuestionGroupComponent
      questionGroup={survey?.questionGroups[0]}
      update={update}
      firstQuestionNumber={0}
      groupIndex={0}
      deleteGroup={() => {}}
      duplicateGroup={() => {}}
      language="en"
      surveySettings={{ showNoAnswer: true }}
    />
  )
}

QuestionGroup.play = async ({ canvasElement, step }) => {
  await sleep(1000)
  const { getByTestId, queryAllByTestId } = within(canvasElement)
  await waitFor(() => getByTestId('collapse-button-question-group'))
  const collapseToggleButton = getByTestId('collapse-button-question-group')

  await step('Should be able to collapse question group body', async () => {
    await userEvent.click(collapseToggleButton)
    await sleep(1000)
    expect(queryAllByTestId('question-container')).toHaveLength(0)
  })

  await step('Should be able to unfold question group body', async () => {
    await userEvent.click(collapseToggleButton)
    await sleep(1000)
    expect(queryAllByTestId('question-container')).toHaveLength(4)
  })

  await step('Should number the questions in the group', async () => {
    const questions = queryAllByTestId('question-number')
    expect(questions).toHaveLength(4)
    expect(questions[0]).toHaveTextContent('1')
    expect(questions[1]).toHaveTextContent('2')
    expect(questions[2]).toHaveTextContent('3')
    expect(questions[3]).toHaveTextContent('4')
  })
}
