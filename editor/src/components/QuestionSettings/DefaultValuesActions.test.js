import 'tests/mocks'

import { fireEvent, render, screen } from '@testing-library/react'

import { DefaultValuesActions } from './DefaultValuesActions'

global.t = (key) => key

describe('DefaultValuesActions', () => {
  test('saves defaults and hides clear when no defaults exist', async () => {
    const update = jest.fn()
    render(<DefaultValuesActions update={update} />)

    expect(screen.queryByText('Clear default values')).not.toBeInTheDocument()
    fireEvent.click(
      screen.getByRole('button', { name: 'Save as default values' })
    )

    expect(update).toHaveBeenCalledWith({ saveAsDefault: true })

    expect(
      screen.queryByText(/The current attribute values/)
    ).not.toBeInTheDocument()
    fireEvent.click(screen.getByRole('button', { name: 'Learn more' }))
    expect(screen.getByText(/The current attribute values/)).toBeInTheDocument()
    expect(
      screen.getByRole('button', { name: 'Hide details' })
    ).toBeInTheDocument()
  })

  test('shows and triggers clear when defaults exist', async () => {
    const update = jest.fn()
    render(
      <DefaultValuesActions update={update} hasDefaultAttributeValues={true} />
    )

    fireEvent.click(
      screen.getByRole('button', { name: 'Clear default values' })
    )

    expect(update).toHaveBeenCalledWith({ clearDefault: true })
  })
})
