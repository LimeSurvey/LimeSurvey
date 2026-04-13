// Shared mocks for Jest tests
// These mocks are used across multiple test files to avoid duplication

jest.mock('components/UIComponents', () => {
  const React = require('react')
  const actual = jest.requireActual('components/UIComponents')
  return {
    ...actual,
    ContentEditor: ({
      testId = 'content-editor',
      value = '',
      className = '',
      onClick,
    }) =>
      React.createElement('div', {
        'data-testid': testId,
        className,
        onClick,
        'role': 'button',
        'dangerouslySetInnerHTML': { __html: value },
      }),
  }
})

jest.mock('services', () => {
  const actual = jest.requireActual('services')
  const surveyData = require('helpers/data/survey-detail.json')
  return {
    ...actual,
    SurveyService: class MockSurveyService {
      getSurveyDetail = async () => surveyData
      patchSurvey = async () => ({ operationsApplied: true, tempIdMapping: {} })
      getSurveyArchives = async () => []
      getSurveyArchivesByBaseTable = async () => []
    },
  }
})
