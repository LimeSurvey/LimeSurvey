import 'tests/mocks'

import { render, screen } from '@testing-library/react'

import { CodeInput } from './codeComponents'

describe('CodeInput', () => {
  const originalTranslate = globalThis.t

  beforeAll(() => {
    globalThis.t = (text) => text
  })

  afterAll(() => {
    globalThis.t = originalTranslate
  })

  test('editable codes have unique accessible names and disable autocomplete', () => {
    render(
      <>
        <CodeInput code="SQ001" onChange={jest.fn()} />
        <CodeInput code="SQ002" onChange={jest.fn()} />
      </>
    )

    expect(
      screen.getByRole('textbox', { name: 'Subquestion code SQ001' })
    ).toHaveAttribute('autocomplete', 'off')
    expect(
      screen.getByRole('textbox', { name: 'Subquestion code SQ002' })
    ).toHaveAttribute('autocomplete', 'off')
  })

  test('answer option codes are labelled as answer codes', () => {
    render(<CodeInput isAnswerCode={true} code="A2" onChange={jest.fn()} />)

    expect(screen.getByRole('textbox', { name: 'Answer code A2' })).toHaveValue(
      'A2'
    )
  })

  test('codes that are not editable are shown as plain text', () => {
    render(<CodeInput isEditable={false} code="SQ001" onChange={jest.fn()} />)

    expect(screen.queryByRole('textbox')).toBeNull()
    expect(screen.getByText('SQ001')).toHaveClass('question-code-tag')
  })
})
