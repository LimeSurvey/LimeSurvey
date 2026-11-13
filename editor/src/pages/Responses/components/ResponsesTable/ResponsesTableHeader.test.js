import 'tests/mocks'

import { useState } from 'react'
import { render, screen, within } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { getCoreRowModel, useReactTable } from '@tanstack/react-table'

import { ResponsesTableHeader } from './ResponsesTableHeader'

jest.mock('components', () => ({
  ContentEditor: ({ value }) => <span>{value}</span>,
}))

const columns = [
  { id: 'id', accessorKey: 'id', header: 'ID' },
  { id: 'completed', accessorKey: 'completed', header: 'Completed' },
  {
    id: '23',
    accessorKey: '23',
    header: 'Favourite fruit',
    meta: { title: 'Q1' },
  },
]

let latestSorting

/**
 * Header with a real table and sorting state, the way ResponsesTable wires it.
 *
 * @param {object} props
 * @param {string[]} [props.sortableColumns] Column IDs the API can sort by
 * @returns {JSX.Element} A table with the header
 */
const HeaderWithTable = ({ sortableColumns }) => {
  const [sorting, setSorting] = useState([])
  latestSorting = sorting
  const table = useReactTable({
    data: [],
    columns,
    state: { sorting },
    onSortingChange: setSorting,
    getCoreRowModel: getCoreRowModel(),
    manualSorting: true,
  })

  return (
    <table>
      <ResponsesTableHeader
        table={table}
        pinnedColumns={[]}
        setPinnedColumns={() => {}}
        sortableColumns={sortableColumns}
      />
    </table>
  )
}

const getHeaderCell = (name) =>
  screen
    .getAllByRole('columnheader')
    .find((cell) => within(cell).queryByText(name))

describe('ResponsesTableHeader sorting', () => {
  const originalTranslate = globalThis.t

  beforeEach(() => {
    latestSorting = undefined
    globalThis.t = (text) => text
  })

  afterEach(() => {
    globalThis.t = originalTranslate
  })

  test('only the columns the API can sort by get a sort button', () => {
    render(<HeaderWithTable sortableColumns={['id', '23']} />)

    expect(
      screen.getByRole('button', { name: 'Sort by ID' })
    ).toBeInTheDocument()
    expect(
      screen.getByRole('button', { name: 'Sort by Q1 Favourite fruit' })
    ).toBeInTheDocument()
    expect(
      within(getHeaderCell('Completed')).queryByRole('button', {
        name: /Sort by/,
      })
    ).toBeNull()
  })

  test('without sortable columns, as in the overview preview, no column can be sorted', () => {
    render(<HeaderWithTable />)

    expect(screen.queryAllByRole('button', { name: /Sort by/ })).toHaveLength(0)
    expect(getHeaderCell('ID')).not.toHaveAttribute('aria-sort')
  })

  test('the default order shows as ID descending, and the first click sorts the ID ascending', async () => {
    render(<HeaderWithTable sortableColumns={['id', '23']} />)

    expect(getHeaderCell('ID')).toHaveAttribute('aria-sort', 'descending')
    expect(getHeaderCell('Favourite fruit')).not.toHaveAttribute('aria-sort')

    await userEvent.click(screen.getByRole('button', { name: 'Sort by ID' }))

    expect(latestSorting).toEqual([{ id: 'id', desc: false }])
    expect(getHeaderCell('ID')).toHaveAttribute('aria-sort', 'ascending')
  })

  test('a column switches between ascending and descending, without an unsorted step', async () => {
    render(<HeaderWithTable sortableColumns={['id', '23']} />)
    const button = screen.getByRole('button', {
      name: 'Sort by Q1 Favourite fruit',
    })

    await userEvent.click(button)
    expect(latestSorting).toEqual([{ id: '23', desc: false }])
    expect(getHeaderCell('Favourite fruit')).toHaveAttribute(
      'aria-sort',
      'ascending'
    )
    expect(getHeaderCell('ID')).not.toHaveAttribute('aria-sort')

    await userEvent.click(button)
    expect(latestSorting).toEqual([{ id: '23', desc: true }])
    expect(getHeaderCell('Favourite fruit')).toHaveAttribute(
      'aria-sort',
      'descending'
    )

    await userEvent.click(button)
    expect(latestSorting).toEqual([{ id: '23', desc: false }])
  })
})
