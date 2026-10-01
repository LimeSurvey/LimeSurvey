import 'tests/mocks'

import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'

import { AssessmentValueBadge } from './AssessmentValueBadge'
import { AssessmentValueInput } from './AssessmentValueInput'

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
