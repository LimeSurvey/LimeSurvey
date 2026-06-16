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

const SurveyListExample = ({
  data = sampleData,
  selectable = false,
  selectedRows,
  onSelectionChange,
  sortBy,
  sortDirection,
  onSortChange,
  emptyMessage = '',
}) => {
  const onEdit = (row) => console.log('Edit', row)
  const onDuplicate = (row) => console.log('Duplicate', row)
  const onDelete = (row) => console.log('Delete', row)

  return (
    <LSTable
      rowId="id"
      columns={[
        { key: 'id', title: 'ID', sortable: true },
        { key: 'title', title: 'Survey Name', sortable: true },
        {
          key: 'actions',
          title: '',
          align: 'right',
          render: (row) => (
            <RowActions
              actions={[
                { label: 'Edit', onClick: () => onEdit(row) },
                { label: 'Duplicate', onClick: () => onDuplicate(row) },
                {
                  label: 'Delete',
                  danger: true,
                  onClick: () => onDelete(row),
                },
              ]}
            />
          ),
        },
      ]}
      data={data}
      emptyMessage={emptyMessage}
      selectable={selectable}
      selectedRows={selectedRows}
      onSelectionChange={onSelectionChange}
      sortBy={sortBy}
      sortDirection={sortDirection}
      onSortChange={onSortChange}
    />
  )
}

export const Default = () => <SurveyListExample />

export const Empty = () => (
  <SurveyListExample data={[]} emptyMessage="No data here.." />
)

export const Selectable = () => {
  const [selectedRows, setSelectedRows] = useState([1])

  return (
    <SurveyListExample
      selectable
      selectedRows={selectedRows}
      onSelectionChange={setSelectedRows}
    />
  )
}

export const Sortable = () => {
  const [sortBy, setSortBy] = useState(null)
  const [sortDirection, setSortDirection] = useState('asc')

  return (
    <SurveyListExample
      sortBy={sortBy}
      sortDirection={sortDirection}
      onSortChange={(column, direction) => {
        setSortBy(column)
        setSortDirection(direction ?? 'asc')
      }}
    />
  )
}
