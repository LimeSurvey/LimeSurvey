// Import shared mocks
import 'tests/mocks'

import { renderWithProviders } from 'tests/testUtils'
import QuestionGroup from './QuestionGroup'
import { screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import surveyData from 'helpers/data/survey-detail.json'
import { sleep } from 'helpers'

describe('QuestionGroup', () => {
  test('Should be able to collapse question group body', async () => {
    const questionGroups = surveyData.survey.questionGroups

    await renderWithProviders(
      <QuestionGroup
        questionGroup={questionGroups[0]}
        update={() => {}}
        firstQuestionNumber={0}
        groupIndex={0}
        deleteGroup={() => {}}
        duplicateGroup={() => {}}
        language="en"
        surveySettings={{ showNoAnswer: true }}
      />
    )

    const collapseToggleButton = screen.getByTestId(
      'collapse-button-question-group'
    )

    await userEvent.click(collapseToggleButton)

    expect(screen.queryAllByTestId('question-container')).toHaveLength(0)
  })

  test('Should be able to unfold question group body', async () => {
    const questionGroup = surveyData.survey.questionGroups[0]
    if (!questionGroup) return

    await renderWithProviders(
      <QuestionGroup
        questionGroup={questionGroup}
        update={() => {}}
        firstQuestionNumber={0}
        groupIndex={0}
        deleteGroup={() => {}}
        duplicateGroup={() => {}}
        language="en"
        surveySettings={{ showNoAnswer: true }}
      />
    )

    await sleep(1000)
    await waitFor(() => screen.getByTestId('collapse-button-question-group'))

    const collapseToggleButton = screen.getByTestId(
      'collapse-button-question-group'
    )

    // Click to collapse first
    await userEvent.click(collapseToggleButton)
    await sleep(1000)

    // Then click to unfold
    await userEvent.click(collapseToggleButton)
    await sleep(1000)

    expect(screen.queryAllByTestId('question-container')).toHaveLength(4)
  })

  test('Should number the questions in the group', async () => {
    const questionGroup = surveyData.survey.questionGroups[0]
    if (!questionGroup) return

    await renderWithProviders(
      <QuestionGroup
        questionGroup={questionGroup}
        update={() => {}}
        firstQuestionNumber={0}
        groupIndex={0}
        deleteGroup={() => {}}
        duplicateGroup={() => {}}
        language="en"
        surveySettings={{ showNoAnswer: true }}
      />
    )

    await sleep(1000)
    await waitFor(() => screen.getByTestId('collapse-button-question-group'))

    const questions = screen.queryAllByTestId('question-number')
    expect(questions).toHaveLength(4)
    expect(questions[0]).toHaveTextContent('1')
    expect(questions[1]).toHaveTextContent('2')
    expect(questions[2]).toHaveTextContent('3')
    expect(questions[3]).toHaveTextContent('4')
  })
})
