/* eslint-disable no-console */
import { useState } from 'react'

import { LSTable, RowActions } from '../LSTable'

export default {
  title: 'UIComponents/LSTable',
  component: LSTable,
}

const sampleData = [
  { id: 1, title: 'Survey Name' },
  { id: 2, title: 'Survey Name the 2nd' },
  { id: 3, title: 'Customer Feedback Q1' },
]

// Native TanStack column defs.
const buildColumns = () => [
  {
    accessorKey: 'id',
    header: 'ID',
    enableSorting: true,
    cell: ({ getValue }) => getValue(),
  },
  {
    accessorKey: 'title',
    header: 'Survey Name',
    enableSorting: true,
    cell: ({ getValue }) => getValue(),
  },
  {
    id: 'actions',
    header: '',
    meta: { align: 'right' },
    cell: ({ row }) => (
      <RowActions
        actions={[
          { label: 'Edit', onClick: () => console.log('Edit', row.original) },
          {
            label: 'Duplicate',
            onClick: () => console.log('Duplicate', row.original),
          },
          {
            label: 'Delete',
            danger: true,
            onClick: () => console.log('Delete', row.original),
          },
        ]}
      />
    ),
  },
]

const selectColumn = {
  id: 'select',
  header: ({ table }) => (
    <input
      type="checkbox"
      className="form-check-input"
      checked={table.getIsAllRowsSelected()}
      onChange={table.getToggleAllRowsSelectedHandler()}
      aria-label="Select all rows"
    />
  ),
  cell: ({ row }) => (
    <input
      type="checkbox"
      className="form-check-input"
      checked={row.getIsSelected()}
      onChange={row.getToggleSelectedHandler()}
      aria-label="Select row"
    />
  ),
}

export const Default = () => (
  <LSTable columns={buildColumns()} data={sampleData} />
)

export const Empty = () => (
  <LSTable columns={buildColumns()} data={[]} emptyMessage="No data here.." />
)

// Sorting is built in — click a sortable header. This story wires controlled
// sorting state to show the API.
export const Sortable = () => {
  const [sorting, setSorting] = useState([])

  return (
    <LSTable
      columns={buildColumns()}
      data={sampleData}
      sorting={sorting}
      onSortingChange={setSorting}
    />
  )
}

export const Selectable = () => {
  const [rowSelection, setRowSelection] = useState({ 1: true })

  return (
    <LSTable
      columns={[selectColumn, ...buildColumns()]}
      data={sampleData}
      enableRowSelection
      rowSelection={rowSelection}
      onRowSelectionChange={setRowSelection}
    />
  )
}
