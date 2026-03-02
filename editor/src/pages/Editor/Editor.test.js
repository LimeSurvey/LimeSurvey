import { renderWithProviders } from 'tests/jestWrapWithProviders'
import { Editor } from './Editor'
import { screen, waitFor, within } from '@testing-library/dom'
import userEvent from '@testing-library/user-event'

describe('Editor interactions', () => {
  beforeEach(async () => {
    await renderWithProviders(<Editor />)

    // Ensure editor is fully rendered before tests run
    await waitFor(() =>
      expect(screen.getByTestId('editor')).toBeInTheDocument()
    )
  })

  test('Should show question settings when focus the question', async () => {
    const firstQuestionGroup = screen.getAllByTestId('question-group')[0]
    const questionHeaders =
      await within(firstQuestionGroup).findAllByTestId('question')
    await userEvent.click(questionHeaders[0])
    expect(await screen.findByTestId('question-settings')).toBeInTheDocument()
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

    const numberOfQuestionGroupsAfterUpdate = within(
      structurePanel
    ).getAllByTestId('survey-structure-question-group').length

    await expect(numberOfQuestionGroupsAfterUpdate).toBe(
      numberOfQuestionGroups - 1
    )
  })
})
