import classNames from 'classnames'
import {
  ActionsColumnId,
  getCommonPinningStyles,
  idColumnKey,
  SelectColumnId,
} from '../../utils'
import { flexRender } from '@tanstack/react-table'

export const ResponsesTableHeader = ({
  table,
  pinnedColumns,
  setPinnedColumns,
}) => {
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
            const canSort = header.id === idColumnKey
            const sortDir = header.column.getIsSorted()
            const questionTitle = header.column.columnDef.meta?.title

            // to remove the border
            const isColumnBeforeActions =
              index === headerGroup.headers.length - 2
            const isLastColumn = index === headerGroup.headers.length - 1

            return (
              <th
                key={header.id}
                className={classNames(`${header.id}`, {
                  'highlight-cell': sortDir,
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
                  {flexRender(
                    header.column.columnDef.header,
                    header.getContext()
                  )}
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
                  <div
                    onClick={
                      canSort
                        ? header.column.getToggleSortingHandler()
                        : undefined
                    }
                    className={classNames('sort-icon', {
                      'ri-sort-desc': !sortDir,
                      'ri-sort-asc': sortDir === 'desc',
                      'ri-format-clear': sortDir === 'asc',
                      'd-none': !canSort,
                    })}
                  ></div>
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
