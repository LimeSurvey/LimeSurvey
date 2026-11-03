import { flexRender } from '@tanstack/react-table'
import { isArrayQuestion } from 'helpers'
import { completedColumnKey, renderCellText } from '../../utils'
import { Badge } from 'react-bootstrap'

export const TableCell = ({ cell, question = {}, baseLanguage }) => {
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
          key,
          subquestion1,
          subquestion2,
        },
        index
      ) => {
        if (value === '-oth-') {
          return <></>
        }

        return (
          <Badge
            key={`cell-value-${index}${cell.column.id}`}
            className={
              isArrayQuestion(questionThemeName)
                ? 'array-response-badge'
                : undefined
            }
          >
            {renderCellText({
              value,
              comment,
              subquestionTitle,
              answerTitle,
              questionThemeName,
              checked,
              index,
              key,
              question,
              baseLanguage,
              subquestion1,
              subquestion2,
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
