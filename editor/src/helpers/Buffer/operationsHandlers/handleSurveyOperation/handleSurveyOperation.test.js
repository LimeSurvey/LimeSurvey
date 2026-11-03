import { cloneDeep, merge } from 'lodash'

import { Operations } from 'helpers'

import { handleSurveyOperation } from './handleSurveyOperation'

describe('handleSurveyOperation Tests', () => {
  const surveyUpdateOperation = {
    entity: 'survey',
    op: Operations.update,
    props: {
      anonymized: false,
      language: 'en',
      additionalLanguages: ['de'],
      expires: '2001-03-20 13:28:00',
      template: 'fruity_twentythree',
      format: 'G',
    },
  }

  const surveyStatusUpdateOperation = {
    id: 571271,
    op: Operations.update,
    entity: 'surveyStatus',
    error: false,
    props: {
      anonymized: false,
      activate: true,
    },
  }

  const languageSettingUpdateOperation = {
    entity: 'languageSetting',
    op: Operations.update,
    id: null,
    props: {
      de: {
        title: 'Beispielfragebogen',
      },
      en: {
        title: 'Example Survey',
      },
    },
  }

  test('Handles survey update operation and merges props correctly', () => {
    const currentOperation = {
      entity: 'survey',
      op: Operations.update,
      props: {
        anonymized: true,
        language: 'de',
        additionalLanguages: ['en'],
        expires: '2000-01-01 00:00:00',
        template: 'minimal_twenty',
        format: 'A',
      },
    }

    const bufferOperations = [currentOperation]
    const result = handleSurveyOperation(
      bufferOperations,
      surveyUpdateOperation,
      currentOperation
    )

    expect(result.bufferOperations).toEqual(bufferOperations)
    expect(result.newOperation).toEqual({
      ...currentOperation,
      props: merge({}, currentOperation.props, surveyUpdateOperation.props),
    })
    expect(result.addToBuffer).toBe(false)
  })

  test('Handles surveyStatus update operation without a current operation', () => {
    const bufferOperations = []
    const result = handleSurveyOperation(
      bufferOperations,
      surveyStatusUpdateOperation,
      null
    )

    expect(result.bufferOperations).toEqual(bufferOperations)
    expect(result.newOperation).toEqual(surveyStatusUpdateOperation)
    expect(result.addToBuffer).toBe(true)
  })

  test('Handles languageSetting update operation and maintains structure', () => {
    const currentOperation = cloneDeep(languageSettingUpdateOperation)
    currentOperation.props.en.title = 'Old Title'

    const bufferOperations = [currentOperation]
    const result = handleSurveyOperation(
      bufferOperations,
      languageSettingUpdateOperation,
      currentOperation
    )

    expect(result.bufferOperations).toEqual(bufferOperations)
    expect(result.newOperation).toEqual({
      ...currentOperation,
      props: merge(
        {},
        currentOperation.props,
        languageSettingUpdateOperation.props
      ),
    })
    expect(result.addToBuffer).toBe(false)
  })

  test('Handles null operation gracefully', () => {
    const bufferOperations = [surveyUpdateOperation]
    const result = handleSurveyOperation(bufferOperations, null, null)

    expect(result.bufferOperations).toEqual(bufferOperations)
    expect(result.newOperation).toEqual({})
    expect(result.addToBuffer).toBe(false)
  })

  test('Handles a new operation without a current operation', () => {
    const bufferOperations = []
    const result = handleSurveyOperation(
      bufferOperations,
      surveyUpdateOperation,
      null
    )

    expect(result.bufferOperations).toEqual(bufferOperations)
    expect(result.newOperation).toEqual(surveyUpdateOperation)
    expect(result.addToBuffer).toBe(true)
  })
})
