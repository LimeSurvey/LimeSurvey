import 'tests/mocks'

import { fireEvent, render, screen } from '@testing-library/react'

import { DateTimeBreakdownTable } from './DateTimeBreakdownTable.js'

global.t = (key) => key

const makeRows = (count) =>
  Array.from({ length: count }, (_, index) => ({
    date: '2026-01-14',
    time: `10:${String(index % 60).padStart(2, '0')}:${String(
      Math.floor(index / 60)
    ).padStart(2, '0')}`,
    count: 1,
  }))

const dataRows = () => screen.getAllByRole('row').length - 1
const loadMore = () => screen.queryByRole('button', { name: 'Load more' })

describe('DateTimeBreakdownTable', () => {
  test('a short list has no Load more', () => {
    render(<DateTimeBreakdownTable rows={makeRows(50)} hasTime />)

    expect(dataRows()).toBe(50)
    expect(loadMore()).not.toBeInTheDocument()
  })

  test('Load more draws the rows in steps and then goes away', () => {
    render(<DateTimeBreakdownTable rows={makeRows(120)} hasTime />)
    expect(dataRows()).toBe(50)

    fireEvent.click(loadMore())
    expect(dataRows()).toBe(100)

    fireEvent.click(loadMore())
    expect(dataRows()).toBe(120)
    expect(loadMore()).not.toBeInTheDocument()
  })

  test('the focus stays on the table when the button goes away', () => {
    const { container } = render(
      <DateTimeBreakdownTable rows={makeRows(60)} hasTime />
    )

    const button = loadMore()
    button.focus()
    fireEvent.click(button)

    expect(
      container.querySelector('.responses-statistics-date-table')
    ).toHaveFocus()
  })
})
