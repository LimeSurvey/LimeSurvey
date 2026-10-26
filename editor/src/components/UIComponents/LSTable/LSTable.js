import React, { useState } from 'react'
import { Table } from 'react-bootstrap'
import classNames from 'classnames'
import {
  useReactTable,
  getCoreRowModel,
  getSortedRowModel,
  flexRender,
} from '@tanstack/react-table'

const MIN_COLUMN_WIDTH = 60

const defaultGetRowId = (row, index) => row?.id ?? String(index)

const SortIcon = ({ sorted, ariaLabel }) => (
  <i
    className={classNames('ls-table__sort-icon', {
      'ri-arrow-up-s-line': sorted === 'asc',
      'ri-arrow-down-s-line': sorted === 'desc',
      'ls-table__sort-icon--active': !!sorted,
    })}
    aria-label={sorted ? ariaLabel : undefined}
    aria-hidden={!sorted}
  />
)

const ariaSort = (canSort, sorted) => {
  if (!canSort) return undefined
  if (sorted === 'desc') return 'descending'
  if (sorted === 'asc') return 'ascending'
  return 'none'
}

export const LSTable = ({
  columns,
  data,
  getRowId = defaultGetRowId,
  emptyMessage = '',
  resizable = false,
  maxHeight,
  sorting: sortingProp,
  onSortingChange,
  manualSorting = false,
  enableRowSelection = false,
  rowSelection: rowSelectionProp,
  onRowSelectionChange,
}) => {
  const [internalSorting, setInternalSorting] = useState([])
  const [internalRowSelection, setInternalRowSelection] = useState({})

  const sorting = sortingProp ?? internalSorting
  const rowSelection = rowSelectionProp ?? internalRowSelection

  const table = useReactTable({
    data,
    columns,
    getRowId,
    state: { sorting, rowSelection },
    onSortingChange: onSortingChange ?? setInternalSorting,
    onRowSelectionChange: onRowSelectionChange ?? setInternalRowSelection,
    enableRowSelection,
    manualSorting,
    columnResizeMode: 'onChange',
    enableColumnResizing: resizable,
    defaultColumn: { enableSorting: false, minSize: MIN_COLUMN_WIDTH },
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
  })

  const rows = table.getRowModel().rows
  const leafColumns = table.getVisibleLeafColumns()
  const columnSizing = table.getState().columnSizing

  return (
    <div className="ls-table-wrapper">
      <div
        className="ls-table-container"
        style={
          maxHeight
            ? { maxHeight, overflowY: 'auto', paddingTop: 0 }
            : undefined
        }
      >
        <Table
          hover
          className={classNames('ls-table align-middle', {
            'ls-table--resizable': resizable,
          })}
        >
          {resizable && (
            <colgroup>
              {leafColumns.map((column) => (
                <col
                  key={column.id}
                  style={
                    columnSizing[column.id]
                      ? { width: columnSizing[column.id] }
                      : undefined
                  }
                />
              ))}
            </colgroup>
          )}
          <thead>
            {table.getHeaderGroups().map((headerGroup) => (
              <tr key={headerGroup.id}>
                {headerGroup.headers.map((header, index) => {
                  const canSort = header.column.getCanSort()
                  const sorted = header.column.getIsSorted()
                  const align = header.column.columnDef.meta?.align
                  const showResizer =
                    resizable &&
                    header.column.getCanResize() &&
                    index < headerGroup.headers.length - 1
                  const headerContent = header.isPlaceholder
                    ? null
                    : flexRender(
                        header.column.columnDef.header,
                        header.getContext()
                      )

                  return (
                    <th
                      key={header.id}
                      className={classNames('ls-table__header-cell', {
                        'ls-table__header-cell--sortable': canSort,
                        'highlight-cell': !!sorted,
                        'ls-table__actions-cell': align === 'right',
                      })}
                      aria-sort={ariaSort(canSort, sorted)}
                    >
                      {canSort ? (
                        <button
                          type="button"
                          className="ls-table__sort-button"
                          onClick={header.column.getToggleSortingHandler()}
                        >
                          <span className="ls-table__header-content">
                            <span>{headerContent}</span>
                            <SortIcon
                              sorted={sorted}
                              ariaLabel={
                                sorted === 'desc'
                                  ? t('Sorted descending')
                                  : t('Sorted ascending')
                              }
                            />
                          </span>
                        </button>
                      ) : (
                        <span className="ls-table__header-content">
                          <span>{headerContent}</span>
                        </span>
                      )}
                      {showResizer && (
                        <div
                          className={classNames('resizer', {
                            isResizing: header.column.getIsResizing(),
                          })}
                          onMouseDown={header.getResizeHandler()}
                          onTouchStart={header.getResizeHandler()}
                          onClick={(event) => event.stopPropagation()}
                          role="separator"
                          aria-orientation="vertical"
                          aria-hidden="true"
                        />
                      )}
                    </th>
                  )
                })}
              </tr>
            ))}
          </thead>
          <tbody>
            {rows.length === 0 ? (
              <tr className="ls-table__row">
                <td colSpan={leafColumns.length} className="ls-table__empty">
                  {emptyMessage}
                </td>
              </tr>
            ) : (
              rows.map((row) => (
                <tr
                  key={row.id}
                  className={classNames('ls-table__row', {
                    'row-selected': row.getIsSelected(),
                  })}
                >
                  {row.getVisibleCells().map((cell) => {
                    const align = cell.column.columnDef.meta?.align
                    return (
                      <td
                        key={cell.id}
                        className={classNames('ls-table__cell', {
                          'highlight-cell': !!cell.column.getIsSorted(),
                          'ls-table__actions-cell': align === 'right',
                        })}
                      >
                        {flexRender(
                          cell.column.columnDef.cell,
                          cell.getContext()
                        )}
                      </td>
                    )
                  })}
                </tr>
              ))
            )}
          </tbody>
        </Table>
      </div>
    </div>
  )
}
