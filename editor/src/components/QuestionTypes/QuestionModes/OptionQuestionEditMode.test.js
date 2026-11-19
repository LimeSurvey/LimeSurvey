// Import shared mocks
import 'tests/mocks'

import { act } from 'react'
import { screen } from '@testing-library/react'

import { renderWithProviders } from 'tests/testUtils'
import { getQuestionTypeInfo } from 'components/QuestionTypes'
import { STATES } from 'helpers'
import { queryClient } from 'queryClient'

import { OptionQuestionEditMode } from './OptionQuestionEditMode'

// The onLoad callback of every render of the load modal, oldest first
const mockOnLoadCallbacks = []

jest.mock('components/LabelSets', () => ({
  LoadLabelSetModal: ({ onLoad }) => {
    mockOnLoadCallbacks.push(onLoad)
    return null
  },
}))

// React Query notifies subscribers on a setTimeout(0), so wait a tick for the re-render
const setAppState = (key, value) =>
  act(async () => {
    queryClient.setQueryData(['appState', key], value)
    await new Promise((resolve) => setTimeout(resolve, 0))
  })

describe('OptionQuestionEditMode label sets', () => {
  const question = {
    qid: 42,
    gid: 7,
    questionThemeName: getQuestionTypeInfo().SINGLE_CHOICE_LIST_RADIO.theme,
  }

  const handleLabelSetLoad = jest.fn()
  const labels = [{ code: 'A1', assessmentValue: 0, l10ns: { en: 'Yes' } }]

  afterEach(async () => {
    await setAppState(STATES.IS_SURVEY_ACTIVE, false)
    mockOnLoadCallbacks.length = 0
    handleLabelSetLoad.mockReset()
  })

  const renderEditMode = (isFocused) =>
    renderWithProviders(
      <OptionQuestionEditMode
        question={question}
        isFocused={isFocused}
        handleLabelSetLoad={handleLabelSetLoad}
        language="en"
        _children={[]}
        handleChildAdd={() => {}}
        handleChildDelete={() => {}}
        handleOnChildDragEnd={() => {}}
        handleChildLUpdate={() => {}}
      />
    )

  test('Shows the load label set link when the user may edit the question', async () => {
    await renderEditMode(true)

    expect(
      await screen.findByTestId('load-label-set-button')
    ).toBeInTheDocument()
  })

  test('Hides the load label set link without survey update permission', async () => {
    // QuestionBody passes isFocused=false when the user lacks update permission
    await renderEditMode(false)

    expect(screen.getByTestId('add-child-button')).toBeInTheDocument()
    expect(
      screen.queryByTestId('load-label-set-button')
    ).not.toBeInTheDocument()
  })

  test('Disables the load label set link on an active survey', async () => {
    await renderEditMode(true)
    await setAppState(STATES.IS_SURVEY_ACTIVE, true)

    expect(screen.getByTestId('load-label-set-button')).toBeDisabled()
  })

  test('Appends a loaded label set to the question', async () => {
    await renderEditMode(true)

    act(() => mockOnLoadCallbacks.at(-1)(labels))

    expect(handleLabelSetLoad).toHaveBeenCalledTimes(1)
    expect(handleLabelSetLoad.mock.calls[0][2]).toEqual(labels)
  })

  test('Ignores a label set that arrives after the survey was activated', async () => {
    await renderEditMode(true)
    // The modal still holds the callback of the render in which the user confirmed
    const onLoadAtConfirmation = mockOnLoadCallbacks.at(-1)
    await setAppState(STATES.IS_SURVEY_ACTIVE, true)

    act(() => onLoadAtConfirmation(labels))

    expect(handleLabelSetLoad).not.toHaveBeenCalled()
  })
})
