import { expect, userEvent, within, waitFor } from '@storybook/test'
import { SurveyHeader } from './SurveyHeader'
import { RemoveHTMLTagsInString } from 'helpers'
import { sleep } from 'helpers/sleep'

export default {
  title: 'General/Survey/Header',
  component: SurveyHeader,
}

let Survey

export const Header = ({ survey, update }) => {
  Survey = survey
  const allLanguages = {
    en: {
      description: 'English',
      nativedescription: 'English',
    },
  }

  return (
    <SurveyHeader
      update={(updated) => update(updated)}
      survey={survey}
      allLanguages={allLanguages}
      activeLanguage="en"
    />
  )
}

Header.play = async ({ canvasElement, step }) => {
  const { getByTestId } = within(canvasElement)

  await sleep(2000)
  await waitFor(() => getByTestId('survey-header-section'))

  await step('Should have the correct welcomeTitle ', async () => {
    const welcomeTitleContentEditor = getByTestId('survey-header-welcome-title')

    expect(RemoveHTMLTagsInString(welcomeTitleContentEditor.innerHTML)).toBe(
      RemoveHTMLTagsInString(Survey.languageSettings.en.welcomeText)
    )
  })

  await step('Should be able to update the welcomeTitle ', async () => {
    const welcomeTitleContentEditor = getByTestId('survey-header-welcome-title')

    userEvent.clear(welcomeTitleContentEditor)

    expect(RemoveHTMLTagsInString(welcomeTitleContentEditor.innerHTML)).toBe(
      RemoveHTMLTagsInString(Survey.languageSettings.en.welcomeText)
    )

    await userEvent.type(welcomeTitleContentEditor, 'Hello World')

    expect(RemoveHTMLTagsInString(welcomeTitleContentEditor.innerHTML)).toBe(
      RemoveHTMLTagsInString(Survey.languageSettings.en.welcomeText)
    )
  })

  await step('Should have the correct welcomeDescription ', async () => {
    const welcomeDescriptionContentEditor = getByTestId(
      'survey-header-welcome-description'
    )

    expect(
      RemoveHTMLTagsInString(welcomeDescriptionContentEditor.innerHTML)
    ).toBe(RemoveHTMLTagsInString(Survey.languageSettings.en.description))
  })

  await step('Should be able to update the welcomeDescription ', async () => {
    const welcomeTitleContentEditor = getByTestId(
      'survey-header-welcome-description'
    )

    userEvent.clear(welcomeTitleContentEditor)

    expect(RemoveHTMLTagsInString(welcomeTitleContentEditor.innerHTML)).toBe(
      RemoveHTMLTagsInString(Survey.languageSettings.en.description)
    )

    await userEvent.type(welcomeTitleContentEditor, 'Hello World')

    expect(RemoveHTMLTagsInString(welcomeTitleContentEditor.innerHTML)).toBe(
      RemoveHTMLTagsInString(Survey.languageSettings.en.description)
    )
  })

  await step('Should have language switch select', async () => {
    const languageSelect = getByTestId('language-change-select')
    expect(languageSelect).toBeInTheDocument()
  })
}
