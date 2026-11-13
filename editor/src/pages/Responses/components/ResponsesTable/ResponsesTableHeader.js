import { format } from 'util'
import classNames from 'classnames'
import { flexRender } from '@tanstack/react-table'

import { ContentEditor } from 'components'

import {
  ActionsColumnId,
  getCommonPinningStyles,
  idColumnKey,
  SelectColumnId,
} from '../../utils'

const ariaSortValues = { asc: 'ascending', desc: 'descending' }

/**
 * Name of a column for screen reader labels.
 *
 * @param {object} header TanStack header of the column
 * @returns {string} Question code and text, the header text, or the column ID
 */
const getColumnName = (header) => {
  const { header: label, meta } = header.column.columnDef
  const text = typeof label === 'string' ? label : ''

  return [meta?.title, text].filter(Boolean).join(' ') || header.id
}

/**
 * Render the header row of the responses table, with pin and sort controls.
 * A column sorts in two modes: The first click sorts ascending, the next ones
 * switch between ascending and descending. Without a sort, the table shows the
 * newest responses first, which the ID column shows as sorted descending.
 *
 * @param {object} props
 * @param {object} props.table TanStack table instance
 * @param {string[]} props.pinnedColumns IDs of the pinned columns
 * @param {Function} props.setPinnedColumns Setter for the pinned columns
 * @param {string[]} [props.sortableColumns] Column IDs the API can sort by, none when missing (e.g. the overview preview)
 * @returns {JSX.Element} The table head
 */
export const ResponsesTableHeader = ({
  table,
  pinnedColumns,
  setPinnedColumns,
  sortableColumns = [],
}) => {
  const isDefaultSort = !table.getState().sorting?.length

  const handleColumnPin = ({ column, id }) => {
    if (id === SelectColumnId) {
      setPinnedColumns([...pinnedColumns])
      return
    }

    const isPinned = column.getIsPinned()
    if (!isPinned) {
      setPinnedColumns([...pinnedColumns, id])
    } else {
      const index = pinnedColumns.indexOf(id)
      pinnedColumns.splice(index, 1)
      setPinnedColumns([...pinnedColumns])
    }
  }

  return (
    <thead>
      {table.getHeaderGroups().map((headerGroup) => (
        <tr key={headerGroup.id}>
          {headerGroup.headers.map((header, index) => {
            const canSort = sortableColumns.includes(header.id)
            const sortDir =
              header.column.getIsSorted() ||
              (isDefaultSort && header.id === idColumnKey ? 'desc' : false)
            const toggleSorting = () =>
              table.setSorting([{ id: header.id, desc: sortDir === 'asc' }])
            const questionTitle = header.column.columnDef.meta?.title

            // to remove the border
            const isColumnBeforeActions =
              index === headerGroup.headers.length - 2
            const isLastColumn = index === headerGroup.headers.length - 1

            return (
              <th
                key={header.id}
                aria-sort={canSort ? ariaSortValues[sortDir] : undefined}
                className={classNames(`${header.id} table-header`, {
                  'highlight-cell': header.column.getIsSorted(),
                  'columnBeforeActions': isColumnBeforeActions,
                })}
                style={{
                  ...getCommonPinningStyles(header.column),
                }}
              >
                <div
                  className={classNames(
                    `d-flex flex-column text-fade-bottom-right align-items-start  position-relative ${header.id}`,
                    {
                      'columnBeforeActions': isColumnBeforeActions,
                      'justify-content-end': isLastColumn,
                    }
                  )}
                  style={{
                    width: header.getSize(),
                  }}
                >
                  {questionTitle && (
                    <>
                      <div className="question-title text-fade-bottom-right">
                        {questionTitle}
                      </div>
                    </>
                  )}
                  {(() => {
                    const renderedHeader = flexRender(
                      header.column.columnDef.header,
                      header.getContext()
                    )

                    return typeof renderedHeader === 'string' ||
                      typeof renderedHeader === 'number' ? (
                      <ContentEditor
                        value={String(renderedHeader)}
                        disabled={true}
                      />
                    ) : (
                      renderedHeader
                    )
                  })()}
                </div>

                <div
                  className={classNames('header-actions-container', {
                    'd-none':
                      header.id === SelectColumnId ||
                      header.id === ActionsColumnId,
                  })}
                >
                  <div
                    onClick={() => handleColumnPin(header)}
                    className={classNames('pin-icon', {
                      'ri-pushpin-2-line': !header.column.getIsPinned(),
                      'ri-unpin-line': header.column.getIsPinned(),
                    })}
                  ></div>
                  {canSort && (
                    <button
                      type="button"
                      onClick={toggleSorting}
                      aria-label={format(
                        t('Sort by %s'),
                        getColumnName(header)
                      )}
                      className={classNames('sort-icon', {
                        'is-sorted': sortDir,
                        'ri-arrow-up-down-line': !sortDir,
                        'ri-sort-asc': sortDir === 'asc',
                        'ri-sort-desc': sortDir === 'desc',
                      })}
                    ></button>
                  )}
                  <div
                    onMouseDown={header.getResizeHandler()}
                    onTouchStart={header.getResizeHandler()}
                    className={`resizer ${
                      header.column.getIsResizing() ? 'isResizing' : ''
                    }`}
                  ></div>
                </div>
              </th>
            )
          })}
        </tr>
      ))}
    </thead>
  )
}
