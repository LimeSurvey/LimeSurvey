import { useEffect, useMemo, useRef } from 'react'
import { Draggable } from 'react-beautiful-dnd'
import classNames from 'classnames'

import { DragAndDrop } from 'components/UIComponents/DragAndDrop/DragAndDrop'
import { getQuestionTypeInfo } from 'components/QuestionTypes/getQuestionTypeInfo'
import { Entities, L10ns, getNoAnswerLabel } from 'helpers'

import { ArrayRow } from './ArrayRow'

export const ArrayRows = ({
  onDragEndCallback,
  question: { questionThemeName, answers = [], subquestions = [], qid } = {},
  language,
  handleUpdate,
  isFocused,
  highestWidth,
  setHighestWidth,
  arrayAnswersWidth,
  dragIconSize,
  showContentEditor = true,
  showQuestionCode = true,
  scaleId,
  subQuestionsHeight,
  setSubQuestionsHeight,
  numberOfHorizontalEntities = 0,
  removeItem = () => {},
  handleUpdateL10ns = () => {},
  setVerticalEntitiesInfo,
  showNoAnswer = false,
}) => {
  const subQuestionsContainerRef = useRef(null)
  const isArrayByColumn =
    questionThemeName === getQuestionTypeInfo().ARRAY_COLUMN.theme
  const isArrayByText =
    questionThemeName === getQuestionTypeInfo().ARRAY_TEXT.theme
  const isArrayByNumbers =
    questionThemeName === getQuestionTypeInfo().ARRAY_NUMBERS.theme

  const entitiesInfo = useMemo(() => {
    let items
    let idKey = 'qid'
    let itemsKey = 'subquestions'
    let sortKey = 'sortOrder'
    let titleKey = 'question'
    let rowName = 'subquestion'
    let placeholder = 'Subquestion'
    let entity = Entities.subquestion

    if (isArrayByText || isArrayByNumbers) {
      items = Array.isArray(subquestions)
        ? subquestions.filter((subquestion) => subquestion.scaleId === scaleId)
        : []
    } else if (isArrayByColumn) {
      items = answers
      idKey = 'aid'
      itemsKey = 'answers'
      sortKey = 'sortOrder'
      titleKey = 'answer'
      rowName = 'answer option'
      placeholder = 'Answer option'
      entity = Entities.answer
    } else {
      items = subquestions ?? []
    }

    const info = {
      items,
      idKey,
      itemsKey,
      sortKey,
      titleKey,
      rowName,
      placeholder,
      scaleId,
      entity,
    }

    setTimeout(() => {
      setVerticalEntitiesInfo(info)
    }, 0)

    return info
  }, [answers, subquestions, questionThemeName, setVerticalEntitiesInfo])

  const getAnswerStyle = (draggableStyle) => ({
    userSelect: 'none',
    minWidth: 'fit-content',
    ...draggableStyle,
  })

  const handleRemovingSubQuestion = (index) => {
    const updatedSubQuestions = [...subquestions]
    updatedSubQuestions.splice(index, 1)

    for (let i = index; i < updatedSubQuestions.length; i++) {
      updatedSubQuestions[i].sortOrder--
    }

    handleUpdate({ subquestions: updatedSubQuestions })
  }

  useEffect(() => {
    if (!setSubQuestionsHeight) {
      return
    }

    const observer = new ResizeObserver(() => {
      // minimum column width is 90
      let highestWidth = 90
      if (!subQuestionsContainerRef.current) {
        return
      }

      subQuestionsContainerRef.current
        .querySelectorAll('.array-subquestion-content-editor')
        .forEach((item, index) => {
          const width = item.clientWidth
          subQuestionsHeight[index] = item.offsetHeight
          if (width > highestWidth) {
            highestWidth = width
          }
        })

      setSubQuestionsHeight([...subQuestionsHeight])
      setHighestWidth(highestWidth)
    })

    if (subQuestionsContainerRef.current) {
      subQuestionsContainerRef.current
        .querySelectorAll('.array-subquestion-content-editor')
        .forEach((item) => {
          observer.observe(item)
        })
      observer.observe(subQuestionsContainerRef.current)
    }

    return () => {
      observer.disconnect()
    }
  }, [])

  if (
    showNoAnswer &&
    questionThemeName === getQuestionTypeInfo().ARRAY_DUAL_SCALE.theme
  ) {
    // add extra item for no answer option
    subQuestionsHeight.push(subQuestionsHeight[0])
  }

  return (
    <div className="array-vertical-titles" ref={subQuestionsContainerRef}>
      <DragAndDrop
        onDragEnd={(dropResult) => onDragEndCallback(dropResult, entitiesInfo)}
        droppableId={'droppable-subquestions'}
      >
        {entitiesInfo.items?.map((entity, index) => (
          <Draggable
            key={`${entity[entitiesInfo.idKey]}${index}-subquestion`}
            draggableId={`${entity[entitiesInfo.idKey]}${index}-subquestion`}
            index={index}
          >
            {(provided, snapshot) => (
              <div
                {...provided.draggableProps}
                ref={provided.innerRef}
                style={getAnswerStyle(provided.draggableProps.style)}
                className={classNames({
                  'focus-element': snapshot.isDragging,
                })}
              >
                <ArrayRow
                  questionThemeName={questionThemeName}
                  isFocused={isFocused}
                  highestWidth={highestWidth}
                  subQuestionsHeight={subQuestionsHeight}
                  arrayAnswersWidth={arrayAnswersWidth}
                  dragIconSize={dragIconSize}
                  provided={provided}
                  index={index}
                  handleUpdateL10ns={(value, index) =>
                    handleUpdateL10ns(value, entitiesInfo, index)
                  }
                  handleRemovingSubQuestion={handleRemovingSubQuestion}
                  showContentEditor={showContentEditor}
                  titleValue={L10ns({
                    prop: entitiesInfo.titleKey,
                    language,
                    l10ns: entity.l10ns,
                  })}
                  numberOfHorizontalEntities={numberOfHorizontalEntities}
                  qid={qid}
                  removeItem={() => removeItem(entitiesInfo, entity)}
                  placeholder={entitiesInfo.placeholder}
                  itemsKey={entitiesInfo.itemsKey}
                  entity={entity}
                  scaleId={scaleId}
                  showQuestionCode={showQuestionCode}
                />
              </div>
            )}
          </Draggable>
        ))}
      </DragAndDrop>
      {showNoAnswer && isArrayByColumn && (
        <div className={classNames('question-body-content')}>
          <ArrayRow
            questionThemeName={questionThemeName}
            isFocused={isFocused}
            highestWidth={highestWidth}
            subQuestionsHeight={subQuestionsHeight}
            arrayAnswersWidth={arrayAnswersWidth}
            dragIconSize={dragIconSize}
            index={answers.length}
            handleUpdateL10ns={() => {}}
            handleRemovingSubQuestion={() => {}}
            showContentEditor={showContentEditor}
            titleValue={getNoAnswerLabel(true)}
            numberOfHorizontalEntities={numberOfHorizontalEntities}
            qid={qid}
            removeItem={() => {}}
            itemsKey={entitiesInfo.itemsKey}
            isNoAnswer={true}
          />
        </div>
      )}
    </div>
  )
}
