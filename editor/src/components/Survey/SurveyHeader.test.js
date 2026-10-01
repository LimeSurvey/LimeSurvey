// Import shared mocks
import 'tests/mocks'

import { screen } from '@testing-library/react'

import { renderWithProviders } from 'tests/testUtils'
import surveyData from 'helpers/data/survey-detail.json'

import { SurveyHeader } from './SurveyHeader'

describe('SurveyHeader', () => {
  let Survey

  beforeEach(async () => {
    Survey = surveyData.survey
    await renderWithProviders(
      <SurveyHeader
        update={() => {}}
        survey={Survey}
        allLanguages={{
          en: {
            description: 'English',
            nativedescription: 'English',
          },
        }}
        activeLanguage="en"
      />
    )
  })

  test('Should have language switch select', async () => {
    const languageSelect = screen.getByTestId('language-change-select')
    expect(languageSelect).toBeInTheDocument()
  })
})
