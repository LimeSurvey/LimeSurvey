import { expect, userEvent, waitFor, within } from '@storybook/test'

import { SurveyFooter } from './SurveyFooter'

export default {
  title: 'General/Survey/Footer',
  component: SurveyFooter,
}

let Survey

export const Footer = ({ survey, update }) => {
  Survey = survey

  return (
    <SurveyFooter
      language="en"
      update={(languageSettings) => update(languageSettings)}
      isEmpty={!survey.questionGroups?.length}
      survey={survey}
    />
  )
}

Footer.play = async ({ canvasElement, step }) => {
  const { getByTestId } = within(canvasElement)
  await waitFor(() => getByTestId('survey-footer-section'))

  await step('Should have the correct endText ', async () => {
    const endTextContentEditor = getByTestId(
      'survey-footer-end-text-content-editor'
    )

    expect(endTextContentEditor.innerHTML).toBe(
      Survey.languageSettings.en.endText
    )
  })

  await step('Should be able to update the endText ', async () => {
    const endTextContentEditor = getByTestId(
      'survey-footer-end-text-content-editor'
    )

    await userEvent.type(endTextContentEditor, 'Hello World')

    expect(endTextContentEditor.innerHTML).toBe(
      Survey.languageSettings.en.endText
    )
  })

  await step(
    'Survey footer should have the correct finish button',
    async () => {
      const finishButton = getByTestId('survey-footer-finish-button')

      expect(finishButton.innerHTML).toBe(
        Survey.languageSettings.en.urlDescription || 'Finish'
      )
    }
  )
}
