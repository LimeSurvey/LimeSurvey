import React from 'react'
import { expect, userEvent, waitFor, within } from '@storybook/test'

import { URLS } from 'helpers'

import { TopBar as TopbarComponent } from './TopBar'

export default {
  title: 'Page/Editor/TopBar',
  component: TopbarComponent,
}

let Survey
export const TopBar = ({ survey }) => {
  Survey = survey

  return <TopbarComponent surveyId={survey.sid} />
}

TopBar.play = async ({ canvasElement, step }) => {
  const { getByTestId } = within(canvasElement)

  await waitFor(() => getByTestId('add-question-button'))
  await waitFor(
    async () =>
      await expect(getByTestId('add-question-button')).not.toHaveAttribute(
        'disabled'
      )
  )

  await step(
    'Logo should links back to the dashboard of core app',
    async () => {
      const logoATag = getByTestId('logo-a-tag')

      await expect(logoATag).toHaveAttribute('href', `${URLS.ADMIN}`)
    }
  )

  await step(
    '+/x buttons should toggle the question type selector',
    async () => {
      const { getByTestId, queryByTestId } = within(canvasElement)
      const addQuestionButton = getByTestId('add-question-button')

      await expect(addQuestionButton).toBeInTheDocument()
      await userEvent.click(addQuestionButton)

      let questionTypeSelector = queryByTestId('topbar-question-inserter')

      if (questionTypeSelector) {
        // If selector is visible, clicking the button should hide it
        await userEvent.click(addQuestionButton)
        questionTypeSelector = queryByTestId('topbar-question-inserter')
        await expect(questionTypeSelector).not.toBeInTheDocument()

        // then testing if we can show it again
        await userEvent.click(addQuestionButton)
        questionTypeSelector = queryByTestId('topbar-question-inserter')
        await expect(questionTypeSelector).toBeInTheDocument()
      } else {
        // If selector is not visible, clicking the button should show it
        await userEvent.click(addQuestionButton)
        questionTypeSelector = queryByTestId('topbar-question-inserter')
        await expect(questionTypeSelector).toBeInTheDocument()

        // then testing if we can hide it again
        await userEvent.click(addQuestionButton)
        questionTypeSelector = queryByTestId('topbar-question-inserter')
        await expect(questionTypeSelector).not.toBeInTheDocument()
      }
    }
  )

  await step('Survey title should exist', async () => {
    const { getByTestId } = within(canvasElement)
    const surveyTitleContentEditor = getByTestId(
      'topbar-survey-title-content-editor'
    )

    await waitFor(
      async () =>
        await expect(surveyTitleContentEditor.innerHTML).toBe(
          Survey.languageSettings.en.title
        )
    )
  })

  await step('Publish settings should exist', async () => {
    const { getByTestId } = within(canvasElement)
    const publishSettings = getByTestId('publish-settings')

    await expect(publishSettings).toBeInTheDocument()
  })
}
