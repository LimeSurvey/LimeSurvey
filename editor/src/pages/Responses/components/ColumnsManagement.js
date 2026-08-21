import classNames from 'classnames'
import { useMemo, useState } from 'react'
import { DragDropContext, Draggable, Droppable } from 'react-beautiful-dnd'

import { Button, TooltipContainer } from 'components'
import {
  ArrowDownIcon,
  ArrowUpIcon,
  CheckIcon,
  DragIcon,
  XIconWithBorder,
} from 'components/icons'
import { completedColumnKey, idColumnKey } from '../utils/generateColumns'
import { ActionsColumnId, SelectColumnId } from '../utils/getDefaultColumns'

const isSpecialColumn = ({ id }) =>
  id === SelectColumnId || id === ActionsColumnId

const hasQuestionId = (column, qid) =>
  qid != null && column.qid?.toString() === qid.toString()

const orderColumns = (columns, normalColumns, timingColumns) => [
  ...columns.filter(({ id }) => id === SelectColumnId),
  ...normalColumns,
  ...timingColumns,
  ...columns.filter(({ id }) => id === ActionsColumnId),
]

const ColumnManagerLabel = ({ column }) => {
  const question = column.questionLabel

  if (!question) {
    return column.header
  }

  return (
    <>
      {column.isTiming && <span>{t('Question time')}: </span>}
      <span className="column-question-code">{question.code}</span>{' '}
      <span>{question.text}</span>
    </>
  )
}

const ColumnControl = ({ column, updateSelection }) => (
  <>
    <input
      className="form-check-input me-2"
      type="checkbox"
      id={`column-${column.id}`}
      checked={column.checked}
      onChange={({ target: { checked } }) =>
        updateSelection(column.id, checked)
      }
      disabled={column.isLocked}
    />
    <label className="form-check-label" htmlFor={`column-${column.id}`}>
      <ColumnManagerLabel column={column} />
    </label>
  </>
)

const DraggableColumn = ({ column, index, updateSelection }) => (
  <Draggable
    index={index}
    draggableId={`${column.id}${index}-column-management-item`}
    isDragDisabled={column.isLocked}
  >
    {(provided, snapshot) => (
      <div
        {...provided.draggableProps}
        ref={provided.innerRef}
        className={classNames('mb-1 reg16 column-item', {
          'focus-element': snapshot.isDragging,
        })}
        style={provided.draggableProps.style}
      >
        <div
          {...provided.dragHandleProps}
          className={classNames('ms-2 me-2 pb-1 p-0', {
            'opacity-0': column.isLocked,
          })}
        >
          <DragIcon className="cm-drag-icon" />
        </div>
        <ColumnControl column={column} updateSelection={updateSelection} />
      </div>
    )}
  </Draggable>
)

const TimingColumn = ({ column, updateSelection }) => (
  <div className="mb-1 reg16 column-item timing-column-item">
    <div className="column-item-spacer" aria-hidden="true" />
    <ColumnControl column={column} updateSelection={updateSelection} />
  </div>
)

export const ColumnsManagement = ({
  table = {},
  onHide = () => {},
  handleOnColumnsManagementConfirm = () => {},
}) => {
  const [showTimings, setShowTimings] = useState(false)
  const [columns, setColumns] = useState(() =>
    table.getAllLeafColumns().map((column, index) => {
      return {
        checked: column.getIsVisible(),
        id: column.id,
        index,
        header: column.columnDef.header,
        isLocked: column.id === idColumnKey || column.id === completedColumnKey,
        isTiming: column.columnDef.meta?.columnCategory === 'timing',
        qid: column.columnDef.meta?.qid,
        questionLabel: column.columnDef.meta?.questionLabel,
      }
    })
  )

  const normalColumns = useMemo(
    () =>
      columns.filter((column) => !column.isTiming && !isSpecialColumn(column)),
    [columns]
  )
  const timingColumns = useMemo(
    () => columns.filter(({ isTiming }) => isTiming),
    [columns]
  )

  const updateColumnSelection = (id, checked) => {
    setColumns((currentColumns) => {
      const updatedColumn = currentColumns.find(
        ({ id: columnId }) => columnId === id
      )

      return currentColumns.map((column) => {
        if (column.id === id) {
          return { ...column, checked }
        }

        const isRelatedQuestionTiming =
          !checked &&
          updatedColumn?.qid != null &&
          !updatedColumn.isTiming &&
          column.isTiming &&
          hasQuestionId(column, updatedColumn.qid)

        return isRelatedQuestionTiming ? { ...column, checked: false } : column
      })
    })
  }

  const handleSelectAll = () => {
    const currentColumns = columns.map((column) => {
      return column.isTiming ? column : { ...column, checked: true }
    })

    setColumns([...currentColumns])
  }

  const handleClearAll = () => {
    const currentColumns = columns.map((column) => {
      if (column.isLocked || isSpecialColumn(column)) {
        return column
      }

      return { ...column, checked: false }
    })

    setColumns([...currentColumns])
  }

  const handleDragEnd = (dropResult) => {
    // dropped outside the list
    if (!dropResult.destination) {
      return
    }

    const updatedColumns = [...normalColumns]

    const startIndex = dropResult.source.index
    const endIndex = dropResult.destination.index

    const [removed] = updatedColumns.splice(startIndex, 1)
    updatedColumns.splice(endIndex, 0, removed)
    setColumns(orderColumns(columns, updatedColumns, timingColumns))
  }

  const handleConfirm = () => {
    handleOnColumnsManagementConfirm(
      orderColumns(columns, normalColumns, timingColumns)
    )
    onHide()
  }

  return (
    <div className="column-manager">
      <h1 className="mb-3 reg24">{t('Organize columns')}</h1>
      <p className="reg14">
        {t(
          'Select which columns you want to display inside this table. You can order the columns by drag and drop.'
        )}
      </p>
      <div className="d-flex gap-3 my-4">
        <Button
          className="med14-c d-flex justify-content-center align-content-center gap-2"
          variant="outline-none"
          onClick={handleSelectAll}
        >
          <div>
            <CheckIcon />
          </div>
          <div className="d-flex align-items-center">{t('Select all')}</div>
        </Button>
        <Button
          className="med14-c d-flex justify-content-center align-content-center gap-2"
          variant="outline-none"
          onClick={handleClearAll}
        >
          <div>
            <XIconWithBorder />
          </div>
          <div className="d-flex align-items-center">
            {t('Clear selection')}
          </div>
        </Button>
      </div>
      <DragDropContext onDragEnd={handleDragEnd}>
        <Droppable
          direction={'vertical'}
          droppableId={'droppable-columns-management'}
          key={'droppable-columns-management'}
        >
          {(provided) => (
            <div ref={provided.innerRef} {...provided.droppableProps}>
              <div
                data-testid="normal-columns-container"
                className={classNames('columns-container mb-3', {
                  'has-timing-columns': timingColumns.length,
                })}
              >
                {normalColumns.map((column, index) => (
                  <DraggableColumn
                    key={`${column.id}-${column.index}`}
                    column={column}
                    index={index}
                    updateSelection={updateColumnSelection}
                  />
                ))}
                {provided.placeholder}
              </div>
            </div>
          )}
        </Droppable>
      </DragDropContext>
      {!!timingColumns.length && (
        <div className="timing-columns-section mb-3">
          <button
            type="button"
            className="timing-columns-header"
            aria-expanded={showTimings}
            onClick={() => setShowTimings((isVisible) => !isVisible)}
          >
            <span className="med14 timing-columns-title">
              {t('Timings')}
              <TooltipContainer
                placement="right"
                tooltipClassName="timing-columns-info-tooltip"
                tip={t(
                  'For timings to be enabled, activate them first in Survey settings > Notifications & data.'
                )}
              >
                <span
                  className="timing-columns-info-icon"
                  data-testid="timings-info-icon"
                  onClick={(event) => event.stopPropagation()}
                >
                  <i className="ri-information-line" aria-hidden="true" />
                </span>
              </TooltipContainer>
            </span>
            {showTimings ? <ArrowUpIcon /> : <ArrowDownIcon />}
          </button>
          {showTimings && (
            <div
              className="columns-container timing-columns-container"
              data-testid="timing-columns-container"
            >
              {timingColumns.map((column) => (
                <TimingColumn
                  key={`${column.id}-${column.index}`}
                  column={column}
                  updateSelection={updateColumnSelection}
                />
              ))}
            </div>
          )}
        </div>
      )}
      <div className="border-none d-flex align-items-center justify-content-end gap-2">
        <Button
          size="lg"
          className="text-light"
          variant="secondary"
          onClick={onHide}
        >
          {t('Cancel')}
        </Button>
        <Button
          size="lg"
          className="text-light"
          variant="primary"
          onClick={handleConfirm}
        >
          {t('Confirm')}
        </Button>
      </div>
    </div>
  )
}
