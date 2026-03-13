import classNames from 'classnames'
import { useMemo, useState } from 'react'
import { DragDropContext, Draggable, Droppable } from 'react-beautiful-dnd'

import { Button } from 'components'
import { CheckIcon, DragIcon, XIconWithBorder } from 'components/icons'

export const ColumnsManagement = ({
  table = {},
  onHide = () => {},
  handleOnColumnsManagementConfirm = () => {},
}) => {
  const [columns, setColumns] = useState(() =>
    table.getAllLeafColumns().map((column, index) => {
      return {
        checked: column.getIsVisible(),
        id: column.id,
        index,
        header: column.columnDef.header,
      }
    })
  )

  const Columns = useMemo(() => {
    if (!columns?.length) {
      return null
    }

    const result = columns.map((column, index) => {
      if (!index || index === table.getAllLeafColumns().length - 1) {
        return null
      }

      return (
        <Draggable
          index={index}
          key={`${column.id}-${column.index}`}
          draggableId={`${column.id}${index}-column-management-item`}
          isDragDisabled={index < 3}
        >
          {(provided, snapshot) => (
            <div
              {...provided.draggableProps}
              ref={provided.innerRef}
              className={classNames('mb-1 reg16 column-item', {
                'focus-element': snapshot.isDragging,
              })}
              style={{ ...provided.draggableProps.style }}
            >
              <div
                {...provided.dragHandleProps}
                className={classNames('ms-2 me-2 pb-1 p-0', {
                  'opacity-0': index < 3,
                })}
              >
                <DragIcon className="cm-drag-icon" />
              </div>
              <input
                className="form-check-input me-2"
                type="checkbox"
                id={`column-${column.id}`}
                checked={columns[index]?.checked}
                onChange={({ target: { checked } }) => {
                  columns[index] = { ...columns[index], checked }
                  setColumns([...columns])
                }}
                disabled={index < 3}
              />
              <label
                className="form-check-label "
                htmlFor={`column-${column.id}`}
              >
                {column.header}
              </label>
            </div>
          )}
        </Draggable>
      )
    })

    return result
  }, [table, columns])

  const handleSelectAll = () => {
    const currentColumns = [...columns].map((column) => {
      return { ...column, checked: true }
    })

    setColumns([...currentColumns])
  }

  const handleClearAll = () => {
    const currentColumns = [...columns].map((column, index) => {
      if (index < 3 || index === columns.length - 1) {
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

    const updatedColumns = [...columns]

    const startIndex = dropResult.source.index
    const endIndex = dropResult.destination.index

    const [removed] = updatedColumns.splice(startIndex, 1)
    updatedColumns.splice(endIndex, 0, removed)
    updatedColumns.map((column, index) => {
      return { ...column, index }
    })

    setColumns([...updatedColumns])
  }

  const handleConfirm = () => {
    handleOnColumnsManagementConfirm(columns)
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
            <div
              ref={provided.innerRef}
              {...provided.droppableProps}
              {...provided.droppableProps}
            >
              <div className="columns-container mb-3">{Columns}</div>
              {provided.placeholder}
            </div>
          )}
        </Droppable>
      </DragDropContext>
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
