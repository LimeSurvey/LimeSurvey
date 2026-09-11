// Import shared mocks
import 'tests/mocks'

import { renderWithProviders } from 'tests/testUtils'
import { Editor } from './Editor'
import { screen, waitFor, within } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { getSurveyPanels } from 'helpers/options'
import surveyData from 'helpers/data/survey-detail.json'

describe('Editor interactions', () => {
  beforeEach(async () => {
    await renderWithProviders(<Editor />)

    // Ensure editor is fully rendered before tests run
    await waitFor(() =>
      expect(screen.getByTestId('editor')).toBeInTheDocument()
    )
    await waitFor(() =>
      expect(screen.getAllByTestId('question-group').length).toBeGreaterThan(0)
    )
  })

  test('Should show all question groups and questions', async () => {
    const structureButton = screen.getByTestId(
      `btn-${getSurveyPanels().structure.panel}-open`
    )
    await userEvent.click(structureButton)

    const questionGroups = screen.getAllByTestId('question-group')
    const questions = screen.getAllByTestId('question')

    expect(questionGroups).toHaveLength(surveyData.survey.questionGroups.length)
    const numberOfQuestions = surveyData.survey.questionGroups.reduce(
      (acc, group) => acc + group.questions.length,
      0
    )
    expect(questions).toHaveLength(numberOfQuestions)
  })

  test('Should show question settings when focus the question', async () => {
    const firstQuestionGroup = screen.getAllByTestId('question-group')[0]
    const questionHeaders =
      await within(firstQuestionGroup).findAllByTestId('question')
    await userEvent.click(questionHeaders[0])
    expect(await screen.findByTestId('question-settings')).toBeInTheDocument()
  })

  test('Should show question footer when focus the question', async () => {
    const firstQuestionGroup = screen.getAllByTestId('question-group')[0]
    const questionHeaders =
      await within(firstQuestionGroup).findAllByTestId('question')
    await userEvent.click(questionHeaders[0])
    const footers = await screen.findAllByTestId('question-footer')
    expect(footers[0]).toBeInTheDocument()
  })

  test('Should be able to duplicate a question group from the group header', async () => {
    const numberOfQuestionGroups =
      screen.getAllByTestId('question-group').length

    const meatballMenus = screen.getAllByTestId('question-group-meatball-menu')
    const meatballMenuToggleButton = within(meatballMenus[0]).getByTestId(
      'meatball-menu-button'
    )
    await userEvent.click(meatballMenuToggleButton)
    await userEvent.click(await screen.findByTestId('duplicate-button'))

    const numberOfQuestionGroupsAfterUpdate =
      screen.getAllByTestId('question-group').length

    expect(numberOfQuestionGroupsAfterUpdate).toBe(numberOfQuestionGroups + 1)
  })

  test('Should be able to duplicate a question group from the structure panel', async () => {
    const numberOfQuestionGroups =
      screen.getAllByTestId('question-group').length

    const meatballMenusButtons = await screen.findAllByTestId(
      'meatball-menu-button'
    )
    await userEvent.click(meatballMenusButtons[0])
    const duplicateButton = screen.getByTestId('duplicate-button')
    await userEvent.click(duplicateButton)

    const numberOfQuestionGroupsAfterUpdate =
      screen.getAllByTestId('question-group').length

    expect(numberOfQuestionGroupsAfterUpdate).toBe(numberOfQuestionGroups + 1)
  })

  // todo: make sure the right question group was deleted
  test('Should be able to delete a question group from the group header', async () => {
    const meatballMenus = screen.getAllByTestId('question-group-meatball-menu')
    const numberOfMeatballMenus = meatballMenus.length
    const meatballMenuToggleButton = within(meatballMenus[0]).getByTestId(
      'meatball-menu-button'
    )
    await userEvent.click(meatballMenuToggleButton)
    await userEvent.click(await screen.findByTestId('delete-button'))
    await userEvent.click(
      await screen.findByTestId('confirm-modal-confirm-button')
    )

    await expect(
      screen.getAllByTestId('question-group-meatball-menu')
    ).toHaveLength(numberOfMeatballMenus - 1)
  })

  // todo: make sure the right question group was duplicated with all data
  test('Should be able to delete a question group from the structure panel', async () => {
    const structurePanel = screen.getByTestId('editor-structure-panel')
    const numberOfQuestionGroups = within(structurePanel).getAllByTestId(
      'survey-structure-question-group'
    ).length

    const sideBarTogglerButtons = within(structurePanel).getAllByTestId(
      'meatball-menu-button'
    )
    await userEvent.click(sideBarTogglerButtons[0])
    await userEvent.click(await screen.findByTestId('delete-button'))
    await userEvent.click(
      await screen.findByTestId('confirm-modal-confirm-button')
    )
    const numberOfQuestionGroupsAfterUpdate = within(
      structurePanel
    ).getAllByTestId('survey-structure-question-group').length

    await expect(numberOfQuestionGroupsAfterUpdate).toBe(
      numberOfQuestionGroups - 1
    )
  })

  test('Should move question up and down using the arrows', async () => {
    const questionGroups = screen.getAllByTestId('question-group')
    const firstQuestionGroupWithTwoQuestions = questionGroups[0]

    const questions = await within(
      firstQuestionGroupWithTwoQuestions
    ).findAllByTestId('question')
    const oldFirstQuestion = questions[0]
    const oldSecondQuestion = questions[1]

    await userEvent.click(oldFirstQuestion)

    const oldFirstQuestionTitle = (
      await within(oldFirstQuestion).findByTestId('question-content-editor')
    ).innerHTML
    const oldSecondQuestionTitle = (
      await within(oldSecondQuestion).findByTestId('question-content-editor')
    ).innerHTML

    const moveDownButton = await screen.findByTestId(
      'question-arrow-down-button'
    )
    await userEvent.click(moveDownButton)

    const updatedQuestionsAfterMovingDown = await within(
      firstQuestionGroupWithTwoQuestions
    ).findAllByTestId('question')

    const firstQuestionTitleAfterMovingDown = (
      await within(updatedQuestionsAfterMovingDown[0]).findByTestId(
        'question-content-editor'
      )
    ).innerHTML
    const secondQuestionTitleAfterMovingDown = (
      await within(updatedQuestionsAfterMovingDown[1]).findByTestId(
        'question-content-editor'
      )
    ).innerHTML

    expect(firstQuestionTitleAfterMovingDown).toEqual(oldSecondQuestionTitle)
    expect(secondQuestionTitleAfterMovingDown).toEqual(oldFirstQuestionTitle)

    const moveUpButton = await screen.findByTestId('question-arrow-up-button')
    await userEvent.click(moveUpButton)

    const updatedQuestionsAfterMovingUp = await within(
      firstQuestionGroupWithTwoQuestions
    ).findAllByTestId('question')

    const firstQuestionTitleAfterMovingUp = (
      await within(updatedQuestionsAfterMovingUp[0]).findByTestId(
        'question-content-editor'
      )
    ).innerHTML
    const secondQuestionTitleAfterMovingUp = (
      await within(updatedQuestionsAfterMovingUp[1]).findByTestId(
        'question-content-editor'
      )
    ).innerHTML

    expect(firstQuestionTitleAfterMovingUp).toEqual(oldFirstQuestionTitle)
    expect(secondQuestionTitleAfterMovingUp).toEqual(oldSecondQuestionTitle)
  })

  test('Should be able to focus questions from the sidebar', async () => {
    const structureButton = screen.getByTestId(
      `btn-${getSurveyPanels().structure.panel}-open`
    )
    await userEvent.click(structureButton)

    const structurePanel = screen.getByTestId('editor-structure-panel')
    const questionGroups = within(structurePanel).getAllByTestId(
      'survey-structure-question-group'
    )

    // Click the group title (triggers onTitleClick → setFocused → expands group)
    const groupTitle = questionGroups[0].querySelector('.sidebar-row-title')
    if (groupTitle) {
      await userEvent.click(groupTitle)
    } else {
      const toggler = within(structurePanel).getAllByTestId(
        'sidebar-row-toggler-button'
      )[0]
      if (toggler) await userEvent.click(toggler)
    }

    const sidebarQuestionsRows = screen.getAllByTestId('sidebar-row-question')
    await userEvent.click(sidebarQuestionsRows[1])
    const questions = screen.getAllByTestId('question')
    expect(questions[1]).toHaveClass('focus-element')

    await userEvent.click(sidebarQuestionsRows[0])
    expect(questions[0]).toHaveClass('focus-element')
  })
})
