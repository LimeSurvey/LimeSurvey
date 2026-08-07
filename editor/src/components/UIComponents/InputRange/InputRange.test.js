// Import shared mocks
import 'tests/mocks'

import { renderWithProviders } from 'tests/testUtils'
import { InputRange } from './InputRange'
import { screen } from '@testing-library/react'
import { Direction } from 'react-range'

describe('InputRange Basic', () => {
  let container
  let input

  beforeAll(async () => {
    await renderWithProviders(<InputRange direction={Direction.Down} />)
    container = screen.getByTestId('input-range')
    input = container.querySelector('input')
  })

  test('Should render Input Range without a label correctly', async () => {
    expect(input).toBeInTheDocument()
    expect(container).toBeInTheDocument()
    expect(container.querySelector('label')).toBeNull()
  })
})

describe('InputRange Horizontal', () => {
  let container
  let input
  let slider

  beforeAll(async () => {
    await renderWithProviders(
      <InputRange labelText={'Input Range'} direction={Direction.Right} />
    )
    container = screen.getByTestId('input-range')
    input = container.querySelector('input')
    slider = screen.getByRole('slider')
  })

  test('Should render Input Range with a label correctly', async () => {
    expect(container).toBeInTheDocument()
    expect(container.querySelector('label')).toBeDefined()
    expect(container.querySelector('label').innerHTML).toBe('Input Range')
    expect(input).toBeInTheDocument()
    expect(slider).toBeInTheDocument()
  })
})
