import { fireEvent, render, screen } from '@testing-library/react'

import { QuestionBodyPreview } from './QuestionPreviewBody'

// A stand-in question type that reports a valid or an invalid (unsavable) input
jest.mock('../../../QuestionTypes/questionPreviewComponents', () => ({
  questionPreviewComponents: {
    stub: ({ onValueChange }) => (
      <>
        <button onClick={() => onValueChange('2001-02-03 00:00:00', 'k')}>
          valid
        </button>
        <button onClick={() => onValueChange(null, 'k', true)}>invalid</button>
      </>
    ),
  },
}))

jest.mock('helpers', () => ({
  ...jest.requireActual('helpers'),
  Toast: jest.fn(),
}))

global.t = (key) => key

const renderBody = (props = {}) =>
  render(
    <QuestionBodyPreview
      question={{ questionThemeName: 'stub', answers: [], subquestions: [] }}
      language="en"
      valueInfo={{ values: [{ key: 'k', value: '' }] }}
      {...props}
    />
  )

// The buttons row is hidden with opacity-0 while there is nothing to confirm or cancel
const buttonsAreShown = () =>
  !screen
    .getByRole('button', { name: 'Cancel' })
    .parentElement.classList.contains('opacity-0')

describe('QuestionBodyPreview', () => {
  it('keeps Cancel available after an invalid input, but not Confirm', () => {
    const onSave = jest.fn()
    renderBody({ onSave })
    expect(buttonsAreShown()).toBe(false)

    fireEvent.click(screen.getByText('valid'))
    fireEvent.click(screen.getByText('invalid'))

    expect(buttonsAreShown()).toBe(true)
    const confirm = screen.getByRole('button', { name: 'Confirm' })
    expect(confirm.disabled).toBe(true)
    fireEvent.click(confirm)
    expect(onSave).not.toHaveBeenCalled()
  })

  it('allows confirming again once the input is valid', () => {
    const onSave = jest.fn()
    renderBody({ onSave })

    fireEvent.click(screen.getByText('invalid'))
    fireEvent.click(screen.getByText('valid'))

    const confirm = screen.getByRole('button', { name: 'Confirm' })
    expect(confirm.disabled).toBe(false)
    fireEvent.click(confirm)
    expect(onSave).toHaveBeenCalledWith({ k: '2001-02-03 00:00:00' })
  })
})
