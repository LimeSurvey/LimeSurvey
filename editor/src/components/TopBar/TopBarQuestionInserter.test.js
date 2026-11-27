import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { cloneDeep } from 'lodash'

import surveyData from 'helpers/data/survey-detail.json'
import { TopBarQuestionInserter } from './TopBarQuestionInserter'

let mockSurvey = {}
const mockUpdate = jest.fn()
const mockAddToBuffer = jest.fn()

// Mock the defining files, not the hooks barrel
jest.mock('hooks/useAppState', () => ({
  // The permissions are loaded independently of the survey
  useAppState: (key, initValue) => [
    key === 'has_survey_update_permission' ? true : initValue,
    jest.fn(),
  ],
}))
jest.mock('hooks/useSurvey', () => ({
  useSurvey: () => ({ survey: mockSurvey, update: mockUpdate, language: 'en' }),
}))
jest.mock('hooks/useBuffer', () => ({
  useBuffer: () => ({ addToBuffer: mockAddToBuffer }),
}))
jest.mock('hooks/useFocused', () => ({
  useFocused: () => ({ setFocused: jest.fn(), groupIndex: undefined }),
}))
// The question types come from the API: Use the ones of a default installation
jest.mock('hooks/useQuestionTypes', () => {
  const { getDefaultQuestionTypes } = jest.requireActual(
    'helpers/getDefaultQuestionTypes'
  )
  return {
    useQuestionTypes: () => ({
      ...getDefaultQuestionTypes('en'),
      getTitle: () => '',
      isFetching: false,
    }),
  }
})

describe('TopBarQuestionInserter', () => {
  const originalT = global.t

  beforeEach(() => {
    global.t = (text) => text
    mockUpdate.mockReset()
    mockAddToBuffer.mockReset()
  })

  afterEach(() => {
    global.t = originalT
  })

  describe('While the survey is loading', () => {
    beforeEach(() => {
      // useSurvey returns an empty survey until the survey is loaded
      mockSurvey = {}
      render(<TopBarQuestionInserter surveyID={surveyData.survey.sid} />)
    })

    test('Should open with disabled question types', () => {
      expect(screen.getByTestId('question-type-listradio')).toHaveClass(
        'disabled'
      )
    })

    test('Should not add a question group', async () => {
      await userEvent.click(screen.getByText('Add question group'))

      expect(mockUpdate).not.toHaveBeenCalled()
      expect(mockAddToBuffer).not.toHaveBeenCalled()
    })
  })

  describe('Once the survey is loaded', () => {
    beforeEach(() => {
      // The inserter changes the survey object it adds to
      mockSurvey = cloneDeep(surveyData.survey)
      render(<TopBarQuestionInserter surveyID={surveyData.survey.sid} />)
    })

    test('Should enable the question types', () => {
      expect(screen.getByTestId('question-type-listradio')).not.toHaveClass(
        'disabled'
      )
    })

    test('Should add a question group', async () => {
      await userEvent.click(screen.getByText('Add question group'))

      expect(mockUpdate).toHaveBeenCalled()
      expect(mockAddToBuffer).toHaveBeenCalled()
    })
  })
})
