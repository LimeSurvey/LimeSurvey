import 'tests/mocks'

import { fireEvent, render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'

import { AssessmentValueBadge } from './AssessmentValueBadge'
import {
  AssessmentValueInput,
  normalizeAssessmentValue,
} from './AssessmentValueInput'

describe('Assessment value accessibility', () => {
  const originalTranslate = globalThis.t

  beforeAll(() => {
    globalThis.t = (text) => text
  })

  afterAll(() => {
    globalThis.t = originalTranslate
  })

  test('inputs have unique accessible names', () => {
    render(
      <>
        <AssessmentValueInput answerCode="A1" assessmentValue="1" />
        <AssessmentValueInput
          answerCode="A1"
          scaleNumber={2}
          assessmentValue="2"
        />
      </>
    )

    expect(
      screen.getByRole('textbox', {
        name: 'Assessment value for answer option A1',
      })
    ).toBeInTheDocument()
    expect(
      screen.getByRole('textbox', {
        name: 'Assessment value for answer option A1 (scale 2)',
      })
    ).toBeInTheDocument()
  })

  test('badge is a keyboard-operable button that focuses the first input', async () => {
    const user = userEvent.setup()

    render(
      <div className="question">
        <AssessmentValueBadge />
        <AssessmentValueInput answerCode="A1" assessmentValue="1" />
        <AssessmentValueInput answerCode="A2" assessmentValue="2" />
      </div>
    )

    const badge = screen.getByRole('button', { name: 'Assessment value' })
    await user.tab()
    expect(badge).toHaveFocus()

    await user.keyboard('{Enter}')
    expect(
      screen.getByRole('textbox', {
        name: 'Assessment value for answer option A1',
      })
    ).toHaveFocus()
  })

  test('restores the last valid value when leaving a lone minus', async () => {
    const user = userEvent.setup()
    const onChange = jest.fn()

    const { rerender } = render(
      <AssessmentValueInput
        answerCode="A1"
        assessmentValue="5"
        onChange={onChange}
      />
    )

    rerender(
      <AssessmentValueInput
        answerCode="A1"
        assessmentValue="-"
        onChange={onChange}
      />
    )

    await user.click(screen.getByRole('textbox'))
    await user.tab()

    expect(onChange).toHaveBeenLastCalledWith({ target: { value: '5' } })
  })

  test('does not change a valid value on blur', async () => {
    const user = userEvent.setup()
    const onChange = jest.fn()

    render(
      <AssessmentValueInput
        answerCode="A1"
        assessmentValue="-3"
        onChange={onChange}
      />
    )

    await user.click(screen.getByRole('textbox'))
    await user.tab()

    expect(onChange).not.toHaveBeenCalled()
  })

  test.each([
    ['', '0'],
    ['007', '7'],
    ['-0', '0'],
    ['-007', '-7'],
  ])('normalizes "%s" to "%s" on blur', async (typed, expected) => {
    const user = userEvent.setup()
    const onChange = jest.fn()

    render(
      <AssessmentValueInput
        answerCode="A1"
        assessmentValue={typed}
        onChange={onChange}
      />
    )

    await user.click(screen.getByRole('textbox'))
    await user.tab()

    expect(onChange).toHaveBeenLastCalledWith({ target: { value: expected } })
  })

  test('displays a missing value as 0', () => {
    render(<AssessmentValueInput answerCode="A1" assessmentValue={null} />)

    expect(screen.getByRole('textbox')).toHaveValue('0')
  })

  test('limits input to 5 characters like the legacy editor', () => {
    const onChange = jest.fn()

    render(
      <AssessmentValueInput
        answerCode="A1"
        assessmentValue="1234"
        onChange={onChange}
      />
    )

    const input = screen.getByRole('textbox')
    expect(input).toHaveAttribute('maxLength', '5')

    fireEvent.change(input, { target: { value: '123456' } })
    expect(onChange).not.toHaveBeenCalled()

    fireEvent.change(input, { target: { value: '-1234' } })
    expect(onChange).toHaveBeenCalledTimes(1)
  })

  test.each([
    [undefined, '0'],
    [null, '0'],
    [' 12 ', '12'],
    ['-', '5'],
    [42, '42'],
  ])('normalizeAssessmentValue(%p) returns %p', (value, expected) => {
    expect(normalizeAssessmentValue(value, '5')).toBe(expected)
  })

  test('repeated badge clicks cycle through assessment value inputs', async () => {
    const user = userEvent.setup()

    render(
      <div className="question">
        <AssessmentValueBadge />
        <AssessmentValueInput answerCode="A1" assessmentValue="1" />
        <AssessmentValueInput answerCode="A2" assessmentValue="2" />
      </div>
    )

    const badge = screen.getByRole('button', { name: 'Assessment value' })
    const firstInput = screen.getByRole('textbox', {
      name: 'Assessment value for answer option A1',
    })
    const secondInput = screen.getByRole('textbox', {
      name: 'Assessment value for answer option A2',
    })

    await user.click(badge)
    expect(firstInput).toHaveFocus()

    await user.click(badge)
    expect(secondInput).toHaveFocus()

    await user.click(badge)
    expect(firstInput).toHaveFocus()
  })
})
