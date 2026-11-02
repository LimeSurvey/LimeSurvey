import classNames from 'classnames'

import { getQuestionTypeInfo } from 'components'
import { getCellInfo, getCommonPinningStyles } from '../../utils'
import { FileInfoCell } from './FileInfoCell'
import { TableCell } from './TableCell'

export const ResponsesTableBody = ({
  table,
  clickedRowRef,
  sortedColumnId,
  sid,
  setShowQuestionComponent,
  cellQuestionInfoRef,
  baseLanguage,
}) => {
  const handleOnCellClick = (cell, row) => {
    const question = cell.column.columnDef?.meta?.question
    clickedRowRef.current = row

    let cellInfo = getCellInfo(cell, row)

    cellQuestionInfoRef.current = {
      ...cellInfo,
      cell,
    }

    if (question) {
      setShowQuestionComponent(true)
    }
  }

  return (
    <tbody>
      {table.getRowModel().rows.map((row) => {
        const isRowSelected = row.getIsSelected()

        return (
          <tr
            className={`${isRowSelected ? 'row-selected' : ''} data-row`}
            key={row.id}
          >
            {row.getVisibleCells().map((cell, index) => {
              const isSortedColumn = cell.column.id === sortedColumnId
              const isFileUploadValue =
                cell.column.columnDef?.meta?.question?.questionThemeName ===
                getQuestionTypeInfo().FILE_UPLOAD.theme

              // to remove the border
              const isColumnBeforeActions =
                index === row.getVisibleCells().length - 2
              const rowId = row?.original?.id
              const questionId = cell?.column?.columnDef?.meta?.qid
              const filesInfo = cell.getContext().getValue()

              return (
                <td
                  key={cell.id}
                  onClick={() => handleOnCellClick(cell, row)}
                  style={{
                    ...getCommonPinningStyles(cell.column),
                    maxWidth: cell.column.getSize(),
                  }}
                  className={classNames(`${cell.column.id}`, {
                    'highlight-cell': isSortedColumn,
                    'columnBeforeActions': isColumnBeforeActions,
                  })}
                >
                  <div className="text-fade-bottom-right td-value-container">
                    {isFileUploadValue ? (
                      <FileInfoCell
                        filesInfo={filesInfo}
                        questionId={questionId}
                        rowId={rowId}
                        surveyId={sid}
                      />
                    ) : (
                      <TableCell
                        cell={cell}
                        question={cell.column.columnDef?.meta?.question}
                        baseLanguage={baseLanguage}
                      />
                    )}
                  </div>
                </td>
              )
            })}
          </tr>
        )
      })}
    </tbody>
  )
}
