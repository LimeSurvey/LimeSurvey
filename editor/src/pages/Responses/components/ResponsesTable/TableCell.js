import { flexRender } from '@tanstack/react-table'
import { completedColumnKey, renderCellText } from '../../utils'
import { Badge } from 'react-bootstrap'

export const TableCell = ({ cell }) => {
  const cellValue = cell.getContext().getValue()
  let value = ''

  if (typeof cellValue === 'object') {
    value = cellValue?.map(
      (
        {
          value,
          comment,
          subquestionTitle,
          answerTitle,
          questionThemeName,
          checked,
        },
        index
      ) => {
        return (
          <Badge key={`cell-value-${index}${cell.column.id}`}>
            {renderCellText({
              value,
              comment,
              subquestionTitle,
              answerTitle,
              questionThemeName,
              checked,
              index,
            })}
          </Badge>
        )
      }
    )
  } else {
    value = flexRender(cell.column.columnDef.cell, cell.getContext())
  }

  return cell.column.id === completedColumnKey ? (
    <i className={cell.getContext().getValue()}></i>
  ) : (
    <>{value}</>
  )
}
