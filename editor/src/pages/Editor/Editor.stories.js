import { expect, userEvent, waitFor, within, screen } from '@storybook/test'

import { sleep } from 'helpers'
import { getSurveyPanels } from 'helpers/options'
import { Editor as EditorComponent } from './Editor'

const meta = {
  title: 'Page/Editor',
  component: EditorComponent,
  argTypes: {
    structurePanel: {
      name: 'Toggle structure panel',
      control: { type: 'boolean' },
    },
    settingsPanel: {
      name: 'Toggle settings panel',
      control: { type: 'boolean' },
    },
  },
  args: {
    structurePanel: true,
    settingsPanel: true,
  },
}

export default meta

let Survey

export const Editor = (args) => {
  Survey = args.survey

  return <EditorComponent />
}

Editor.play = async ({ canvasElement, step }) => {
  const { getAllByTestId, getByTestId } = within(canvasElement)
  await waitFor(() => getAllByTestId('question-group'))

  const structureButton = await getByTestId(
    `btn-${getSurveyPanels().structure.panel}-open`
  )

  await userEvent.click(structureButton)

  await step('Should show all question groups and questions', async () => {
    const questionGroups = getAllByTestId('question-group')
    const questions = getAllByTestId('question')
    expect(questionGroups).toHaveLength(Survey.questionGroups.length)
    const numberOfQuestions = Survey.questionGroups.reduce(
      (acc, group) => acc + group.questions.length,
      0
    )
    expect(numberOfQuestions).toBe(questions.length)
  })

  // // todo: make sure the right question group was duplicated with all data
  await step(
    'Should be able to duplicate a question group from the group header',
    async () => {
      await sleep(1000)
      const numberOfQuestionGroups = Survey.questionGroups.length

      const meatballMenus = getAllByTestId('question-group-meatball-menu')
      const meatballMenuToggleButton = await within(
        meatballMenus[0]
      ).getByTestId('meatball-menu-button')
      userEvent.click(meatballMenuToggleButton)
      userEvent.click(await screen.findByTestId('duplicate-button'))

      await sleep(1000)
      await expect(Survey.questionGroups).toHaveLength(
        numberOfQuestionGroups + 1
      )
    }
  )

  await step(
    'Should show question footer / toggle preview mode when focus the question',
    async () => {
      const firstQuestionGroup = getAllByTestId('question-group')[0]
      const questionHeaders =
        await within(firstQuestionGroup).findAllByTestId('question')
      await userEvent.click(questionHeaders[0])
      expect(await screen.findByTestId('question-footer')).toBeInTheDocument()
    }
  )

  await step('Should move question up and down using the arrows', async () => {
    await sleep(2000)
    const firstQuestionGroup = await getAllByTestId('question-group')[0]
    const oldFirstQuestion = (
      await within(firstQuestionGroup).findAllByTestId('question')
    )[0]
    const oldSecondQuestion = (
      await within(firstQuestionGroup).findAllByTestId('question')
    )[1]
    await userEvent.click(oldFirstQuestion)

    const oldFirstQuestionTitle = (
      await within(oldFirstQuestion).findByTestId('question-content-editor')
    ).innerHTML
    const oldSecondQuestionTitle = (
      await within(oldSecondQuestion).findByTestId('question-content-editor')
    ).innerHTML

    const moveDownButton = await screen.getByTestId(
      'question-arrow-down-button'
    )

    await userEvent.click(moveDownButton)
    const updatedQuestionsAfterMovingDown =
      await within(firstQuestionGroup).findAllByTestId('question')

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

    await userEvent.click(await screen.getByTestId('question-arrow-up-button'))

    const updatedQuestionsAfterMovingUp =
      await within(firstQuestionGroup).findAllByTestId('question')

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

  await step('Should be able to focus questions from the sidebar', async () => {
    await sleep(1000)
    const sidebarQuestionsRows = await getAllByTestId('sidebar-row-question')
    await userEvent.click(sidebarQuestionsRows[1])

    const questions = getAllByTestId('question')
    expect(questions[1]).toHaveClass('focus-element')

    await sleep(1000)
    await userEvent.click(sidebarQuestionsRows[0])
    expect(questions[0]).toHaveClass('focus-element')
  })
}
