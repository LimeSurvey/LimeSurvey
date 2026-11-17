import { act, fireEvent, render, waitFor } from '@testing-library/react'

import { htmlPopup, SwalAlert } from 'helpers'

import { getConditionPopupClass } from '../utils'
import { ExpressionScriptInputBox } from './ExpressionScriptInputBox'

// The popups' open / replace / close behavior is what is tested here, so use
// the real SweetAlert instead of the shared stubs (__mocks__/sweetalert2.js
// and the sweetalert2-react-content mock in setupTests.js)
jest.unmock('sweetalert2')
jest.unmock('sweetalert2-react-content')

const mockAddToBuffer = jest.fn()
const mockUpdate = jest.fn()

jest.mock('react-router-dom', () => ({
  ...jest.requireActual('react-router-dom'),
  useParams: () => ({ surveyId: '1' }),
}))

jest.mock('hooks', () => ({
  ...jest.requireActual('hooks'),
  useBuffer: () => ({ addToBuffer: mockAddToBuffer }),
  useSurvey: () => ({ update: mockUpdate }),
  useExpressionScriptValidation: () => jest.fn(),
}))

// CodeMirror can't be typed into in jsdom. Mock the defining file only:
// spreading the components/UIComponents barrel copies exports that are not
// defined yet due to circular imports.
jest.mock(
  'components/UIComponents/ExpressionScriptEditor/ExpressionScriptEditor',
  () => {
    const React = require('react')
    return {
      ExpressionScriptEditor: ({ value, onChange }) =>
        React.createElement('textarea', {
          'data-testid': 'expression-script-editor',
          value,
          'onChange': (event) => onChange(event.target.value),
        }),
    }
  }
)

const question = {
  qid: 11,
  relevance: 'Q1 == "A"',
  scenarios: [{ scid: 1, conditions: [] }],
}

// SwalAlert.isVisible() needs layout, which jsdom doesn't have; getPopup()
// returns the popup element while a popup is shown
const getVisiblePopup = () => SwalAlert.getPopup()

const renderInputBox = () =>
  render(
    <ExpressionScriptInputBox
      question={question}
      onShowPanel={jest.fn()}
      onNavigateBack={jest.fn()}
    />
  )

describe('ExpressionScriptInputBox popups', () => {
  afterEach(async () => {
    await act(async () => SwalAlert.close())
    jest.clearAllMocks()
  })

  it('closes the overwrite confirmation that replaced the editor popup when the question is removed', async () => {
    const { container, unmount } = renderInputBox()

    // Open the editor popup (the expand button)
    fireEvent.click(container.querySelectorAll('button')[0])
    const editor = await waitFor(() => {
      const element = document.querySelector(
        '[data-testid="expression-script-editor"]'
      )
      expect(element).toBeInTheDocument()
      return element
    })
    expect(getVisiblePopup()).toHaveClass(getConditionPopupClass(question.qid))

    // Apply a changed script: the question has scenarios, so the overwrite
    // confirmation replaces the editor popup
    fireEvent.change(editor, { target: { value: 'Q1 == "B"' } })
    fireEvent.click(
      document.querySelector('.condition-designer-overlay-primary-button')
    )
    await waitFor(() =>
      expect(
        document.querySelector('[data-testid="expression-script-editor"]')
      ).not.toBeInTheDocument()
    )
    expect(getVisiblePopup()).toHaveClass(getConditionPopupClass(question.qid))

    // The question is removed: its panel unmounts
    await act(async () => unmount())

    await waitFor(() => expect(getVisiblePopup()).toBeNull())
    expect(mockAddToBuffer).not.toHaveBeenCalled()
    expect(mockUpdate).not.toHaveBeenCalled()
  })

  it('leaves a popup opened for something else open on unmount', async () => {
    const { unmount } = renderInputBox()

    act(() => {
      htmlPopup({ html: 'Other popup', popupClass: getConditionPopupClass(99) })
    })
    await waitFor(() =>
      expect(getVisiblePopup()).toHaveClass(getConditionPopupClass(99))
    )

    await act(async () => unmount())

    expect(getVisiblePopup()).toHaveClass(getConditionPopupClass(99))
  })
})
