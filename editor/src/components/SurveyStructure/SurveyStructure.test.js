// Import shared mocks
import 'tests/mocks'

import { renderWithProviders } from 'tests/testUtils'
import { SurveyStructure } from './SurveyStructure'
import { screen } from '@testing-library/react'
import surveyData from 'helpers/data/survey-detail.json'

describe('SurveyStructure', () => {
  const Survey = surveyData.survey

  test('Should have the Header and Footer', async () => {
    await renderWithProviders(<SurveyStructure />)

    const header = await screen.findByTestId('survey-structure-header')
    const footer = await screen.findByTestId('survey-structure-footer')

    expect(header).toBeDefined()
    expect(footer).toBeDefined()
  })

  test('Should have all of the survey question groups', async () => {
    await renderWithProviders(<SurveyStructure />)

    const questionGroups = await screen.findAllByTestId(
      'survey-structure-question-group'
    )

    expect(questionGroups.length).toBe(Survey?.questionGroups.length)
  })
})
