import React, { useMemo, useState } from 'react'
import PropTypes from 'prop-types'
import { Table } from 'react-bootstrap'
import classNames from 'classnames'

const compareValues = (a, b) => {
  if (a == null && b == null) return 0
  if (a == null) return 1
  if (b == null) return -1
  if (typeof a === 'number' && typeof b === 'number') return a - b
  return String(a).localeCompare(String(b))
}

const getSortIconClass = (columnKey, sortBy, sortDirection) => {
  if (sortBy !== columnKey) return 'ri-sort-desc'
  if (sortDirection === 'desc') return 'ri-sort-asc'
  return 'ri-format-clear'
}

export const LSTable = ({
  columns,
  data,
  rowId,
  emptyMessage = '',
  selectable = false,
  onSelectionChange = () => {},
  selectedRows = [],
  sortBy: sortByProp,
  sortDirection: sortDirectionProp = 'asc',
  onSortChange,
}) => {
  const [internalSortBy, setInternalSortBy] = useState(
    sortByProp ?? rowId
  )
  const [internalSortDirection, setInternalSortDirection] =
    useState(sortDirectionProp)

  const isSortControlled = typeof onSortChange === 'function'
  const currentSortBy = isSortControlled ? sortByProp ?? rowId : internalSortBy
  const currentSortDirection = isSortControlled
    ? sortDirectionProp
    : internalSortDirection

  const sortedData = useMemo(() => {
    if (!currentSortBy) return data

    const sorted = [...data].sort((rowA, rowB) => {
      const result = compareValues(rowA[currentSortBy], rowB[currentSortBy])
      return currentSortDirection === 'desc' ? -result : result
    })

    return sorted
  }, [data, currentSortBy, currentSortDirection])

  const handleSortClick = (columnKey) => {
    let newDirection = 'asc'

    if (currentSortBy === columnKey) {
      newDirection = currentSortDirection === 'asc' ? 'desc' : 'asc'
    }

    if (isSortControlled) {
      onSortChange(columnKey, newDirection)
    } else {
      setInternalSortBy(columnKey)
      setInternalSortDirection(newDirection)
    }
  }

  const allVisibleSelected =
    sortedData.length > 0 &&
    sortedData.every((row) => selectedRows.includes(row[rowId]))

  const handleSelectAll = (event) => {
    const visibleIds = sortedData.map((row) => row[rowId])

    if (event.target.checked) {
      onSelectionChange([...new Set([...selectedRows, ...visibleIds])])
      return
    }

    const visibleIdSet = new Set(visibleIds)
    onSelectionChange(selectedRows.filter((id) => !visibleIdSet.has(id)))
  }

  const handleRowSelect = (id, checked) => {
    if (checked) {
      onSelectionChange([...selectedRows, id])
      return
    }

    onSelectionChange(selectedRows.filter((selectedId) => selectedId !== id))
  }

  const columnCount = columns.length + (selectable ? 1 : 0)

  return (
    <div className="ls-table-wrapper">
      <div className="ls-table-container">
        <Table hover className="ls-table align-middle">
          <thead>
            <tr>
              {selectable && (
                <th className="ls-table__header-cell ls-table__select-cell">
                  <input
                    type="checkbox"
                    className="form-check-input"
                    checked={allVisibleSelected}
                    onChange={handleSelectAll}
                    aria-label={t('Select all rows')}
                  />
                </th>
              )}
              {columns.map((column) => {
                const isSorted = currentSortBy === column.key

                return (
                  <th
                    key={column.key}
                    className={classNames('ls-table__header-cell', {
                      'ls-table__header-cell--sortable': column.sortable,
                      'highlight-cell': isSorted,
                      'ls-table__actions-cell': column.key === 'actions',
                    })}
                    onClick={
                      column.sortable
                        ? () => handleSortClick(column.key)
                        : undefined
                    }
                  >
                    <div className="ls-table__header-content">
                      <span>{column.title}</span>
                      {column.sortable && (
                        <i
                          className={classNames(
                            'ls-table__sort-icon',
                            getSortIconClass(
                              column.key,
                              currentSortBy,
                              currentSortDirection
                            )
                          )}
                          aria-hidden="true"
                        />
                      )}
                    </div>
                  </th>
                )
              })}
            </tr>
          </thead>
          <tbody>
            {sortedData.length === 0 ? (
              <tr className="ls-table__row">
                <td colSpan={columnCount} className="ls-table__empty">
                  {emptyMessage}
                </td>
              </tr>
            ) : (
              sortedData.map((row) => {
                const id = row[rowId]
                const isSelected = selectedRows.includes(id)

                return (
                  <tr
                    key={id}
                    className={classNames('ls-table__row', {
                      'row-selected': isSelected,
                    })}
                  >
                    {selectable && (
                      <td className="ls-table__cell ls-table__select-cell">
                        <input
                          type="checkbox"
                          className="form-check-input"
                          checked={isSelected}
                          onChange={(event) =>
                            handleRowSelect(id, event.target.checked)
                          }
                          aria-label={t('Select row')}
                        />
                      </td>
                    )}
                    {columns.map((column) => (
                      <td
                        key={`${id}-${column.key}`}
                        className={classNames('ls-table__cell', {
                          'highlight-cell': currentSortBy === column.key,
                          'ls-table__actions-cell': column.key === 'actions',
                        })}
                      >
                        {column.render
                          ? column.render(row)
                          : row[column.key]}
                      </td>
                    ))}
                  </tr>
                )
              })
            )}
          </tbody>
        </Table>
      </div>
    </div>
  )
}

LSTable.propTypes = {
  columns: PropTypes.arrayOf(
    PropTypes.shape({
      key: PropTypes.string.isRequired,
      title: PropTypes.string,
      sortable: PropTypes.bool,
      render: PropTypes.func,
    })
  ).isRequired,
  data: PropTypes.array.isRequired,
  rowId: PropTypes.string.isRequired,
  emptyMessage: PropTypes.string,
  selectable: PropTypes.bool,
  onSelectionChange: PropTypes.func,
  selectedRows: PropTypes.array,
  sortBy: PropTypes.string,
  sortDirection: PropTypes.oneOf(['asc', 'desc']),
  onSortChange: PropTypes.func,
}
