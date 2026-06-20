import React, { useMemo, useState } from 'react'
import { Table } from 'react-bootstrap'
import classNames from 'classnames'

const MIN_COLUMN_WIDTH = 60

const compareValues = (a, b) => {
  if (a == null && b == null) return 0
  if (a == null) return 1
  if (b == null) return -1
  if (typeof a === 'number' && typeof b === 'number') return a - b
  return String(a).localeCompare(String(b))
}

const getSortIconClass = (isSorted, sortDirection) => {
  if (!isSorted) return ''
  if (sortDirection === 'desc') return 'ri-arrow-down-s-line'
  return 'ri-arrow-up-s-line'
}

const getAriaSort = (sortable, isSorted, sortDirection) => {
  if (!sortable) return undefined
  if (!isSorted) return 'none'
  return sortDirection === 'desc' ? 'descending' : 'ascending'
}

const SortIcon = ({ isSorted, sortDirection, ariaLabel }) => (
  <i
    className={classNames(
      'ls-table__sort-icon',
      getSortIconClass(isSorted, sortDirection),
      { 'ls-table__sort-icon--active': isSorted }
    )}
    aria-label={isSorted ? ariaLabel : undefined}
    aria-hidden={!isSorted}
  />
)

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
  resizable = false,
}) => {
  // In uncontrolled mode (no `onSortChange`), `sortBy`/`sortDirection` are
  // only used to seed the initial state; later prop changes are ignored.
  const [internalSortBy, setInternalSortBy] = useState(sortByProp ?? null)
  const [internalSortDirection, setInternalSortDirection] =
    useState(sortDirectionProp)

  // Per-column widths set by dragging the resize handles (only when resizable);
  // `resizingKey` is the column currently being dragged.
  const [columnWidths, setColumnWidths] = useState({})
  const [resizingKey, setResizingKey] = useState(null)

  const startResize = (event, columnKey) => {
    event.preventDefault()
    event.stopPropagation()
    const th = event.currentTarget.closest('th')
    const getClientX = (e) => e.touches?.[0]?.clientX ?? e.clientX
    const startX = getClientX(event)
    const startWidth = th ? th.getBoundingClientRect().width : MIN_COLUMN_WIDTH

    setResizingKey(columnKey)
    const onMove = (moveEvent) => {
      const nextWidth = Math.max(
        MIN_COLUMN_WIDTH,
        startWidth + (getClientX(moveEvent) - startX)
      )
      setColumnWidths((prev) => ({ ...prev, [columnKey]: nextWidth }))
    }
    const onUp = () => {
      setResizingKey(null)
      document.removeEventListener('mousemove', onMove)
      document.removeEventListener('mouseup', onUp)
      document.removeEventListener('touchmove', onMove)
      document.removeEventListener('touchend', onUp)
      document.body.style.cursor = ''
      document.body.style.userSelect = ''
    }

    document.addEventListener('mousemove', onMove)
    document.addEventListener('mouseup', onUp)
    document.addEventListener('touchmove', onMove)
    document.addEventListener('touchend', onUp)
    document.body.style.cursor = 'col-resize'
    document.body.style.userSelect = 'none'
  }

  const isSortControlled = typeof onSortChange === 'function'
  const currentSortBy = isSortControlled ? (sortByProp ?? null) : internalSortBy
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
    if (currentSortBy !== columnKey) {
      if (isSortControlled) {
        onSortChange(columnKey, 'asc')
      } else {
        setInternalSortBy(columnKey)
        setInternalSortDirection('asc')
      }
      return
    }

    if (currentSortDirection === 'asc') {
      if (isSortControlled) {
        onSortChange(columnKey, 'desc')
      } else {
        setInternalSortDirection('desc')
      }
      return
    }

    if (isSortControlled) {
      onSortChange(null, null)
    } else {
      setInternalSortBy(null)
      setInternalSortDirection('asc')
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
        <Table
          hover
          className={classNames('ls-table align-middle', {
            'ls-table--resizable': resizable,
          })}
        >
          {resizable && (
            <colgroup>
              {selectable && <col className="ls-table__select-col" />}
              {columns.map((column) => (
                <col
                  key={column.key}
                  style={
                    columnWidths[column.key]
                      ? { width: columnWidths[column.key] }
                      : undefined
                  }
                />
              ))}
            </colgroup>
          )}
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
              {columns.map((column, columnIndex) => {
                const isSorted = currentSortBy === column.key
                // No handle on the last column — there's nothing to its right.
                const showResizer = resizable && columnIndex < columns.length - 1

                return (
                  <th
                    key={column.key}
                    className={classNames('ls-table__header-cell', {
                      'ls-table__header-cell--sortable': column.sortable,
                      'highlight-cell': isSorted,
                      'ls-table__actions-cell': column.align === 'right',
                    })}
                    aria-sort={getAriaSort(
                      column.sortable,
                      isSorted,
                      currentSortDirection
                    )}
                  >
                    {column.sortable ? (
                      <button
                        type="button"
                        className="ls-table__sort-button"
                        onClick={() => handleSortClick(column.key)}
                      >
                        <span className="ls-table__header-content">
                          <span>{column.title}</span>
                          <SortIcon
                            isSorted={isSorted}
                            sortDirection={currentSortDirection}
                            ariaLabel={
                              isSorted
                                ? currentSortDirection === 'asc'
                                  ? t('Sorted ascending')
                                  : t('Sorted descending')
                                : t('Sortable')
                            }
                          />
                        </span>
                      </button>
                    ) : (
                      <span className="ls-table__header-content">
                        <span>{column.title}</span>
                      </span>
                    )}
                    {showResizer && (
                      <div
                        className={classNames('resizer', {
                          isResizing: resizingKey === column.key,
                        })}
                        onMouseDown={(event) => startResize(event, column.key)}
                        onTouchStart={(event) => startResize(event, column.key)}
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
                          'ls-table__actions-cell': column.align === 'right',
                        })}
                      >
                        {column.render ? column.render(row) : row[column.key]}
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
