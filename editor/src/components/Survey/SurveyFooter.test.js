// Import shared mocks
import 'tests/mocks'

import { renderWithProviders } from 'tests/testUtils'
import { SurveyFooter } from './SurveyFooter'
import { screen } from '@testing-library/react'
import surveyData from 'helpers/data/survey-detail.json'

describe('SurveyFooter', () => {
  beforeEach(async () => {})

  test('Survey footer should have the correct finish button', async () => {
    await renderWithProviders(
      <SurveyFooter
        language="en"
        update={() => {}}
        isEmpty={!surveyData.survey.questionGroups?.length}
        survey={surveyData.survey}
      />
    )

    const finishButton = screen.getByTestId('survey-footer-finish-button')

    expect(finishButton.innerHTML).toBe(
      surveyData.survey.languageSettings.en.urlDescription || 'Finish'
    )
  })
})
