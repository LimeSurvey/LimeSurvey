import 'tests/mocks'

import { render, screen } from '@testing-library/react'

import { SubquestionCodeInput } from './subquestionCodeComponents'

describe('Subquestion code accessibility', () => {
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
        <SubquestionCodeInput code="SQ001" onChange={jest.fn()} />
        <SubquestionCodeInput code="SQ002" onChange={jest.fn()} />
      </>
    )

    expect(
      screen.getByRole('textbox', { name: 'Subquestion code SQ001' })
    ).toHaveAttribute('autocomplete', 'off')
    expect(
      screen.getByRole('textbox', { name: 'Subquestion code SQ002' })
    ).toHaveAttribute('autocomplete', 'off')
  })
})
