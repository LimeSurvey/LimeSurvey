// Import shared mocks
import 'tests/mocks'

import { renderWithProviders } from 'tests/testUtils'
import { SurveyFooter } from './SurveyFooter'
import { screen } from '@testing-library/react'
import surveyData from 'helpers/data/survey-detail.json'

describe('SurveyFooter', () => {
  beforeEach(async () => {})

  test('Survey footer should render the finish button', async () => {
    await renderWithProviders(
      <SurveyFooter
        language="en"
        update={() => {}}
        isEmpty={!surveyData.survey.questionGroups?.length}
        survey={surveyData.survey}
      />
    )

    const finishButton = await screen.findByTestId(
      'survey-footer-finish-button'
    )

    expect(finishButton).toBeInTheDocument()
  })

  test('Survey footer end text editor should use rich text toolbar mode', async () => {
    await renderWithProviders(
      <SurveyFooter
        language="en"
        update={() => {}}
        isEmpty={!surveyData.survey.questionGroups?.length}
        survey={surveyData.survey}
      />
    )

    const endTextEditor = await screen.findByTestId(
      'survey-footer-end-text-content-editor'
    )

    expect(endTextEditor.getAttribute('data-show-toolbar')).toBe('true')
    expect(endTextEditor.getAttribute('data-use-rich-text-editor')).toBe('true')
  })
})
