import { useEffect, useMemo, useRef, useState } from 'react'
import { Draggable } from 'react-beautiful-dnd'
import { useParams } from 'react-router-dom'
import classNames from 'classnames'
import { max } from 'lodash'

import {
  Entities,
  getAttributeValue,
  L10ns,
  getNoAnswerLabel,
  SCALE_1,
  SCALE_2,
  STATES,
} from 'helpers'
import { useAppState, useSurvey } from 'hooks'
import { ContentEditor, DragAndDrop } from 'components'
import { getDisplayAttributes } from 'components/QuestionSettings/attributes'
import { getQuestionTypeInfo } from 'components/QuestionTypes/getQuestionTypeInfo'

import { ArrayColumnTitle } from './ArrayColumnTitle'

export const ArrayColumnsTitles = ({
  question: {
    answers = [],
    subquestions = [],
    attributes = {},
    qid,
    questionThemeName,
  } = {},
  onDragEndCallback,
  highestSubquestionWidth,
  isFocused,
  arrayAnswersWidth,
  setArrayAnswersWidth,
  dragIconSize,
  scaleId,
  highestHeight,
  setHighestHeight = () => {},
  setNumberOfHorizontalEntities,
  setHorizontalInfo = () => {},
  handleUpdateL10ns = () => {},
  removeItem = () => {},
  handleHeaderUpdate = () => {},
  isArrayDualScale = false,
  handleHeaderHeightChange = () => {},
  headersHeight,
  showNoAnswer = false,
}) => {
  const [isReorderingAnswers, setIsReorderingAnswers] = useState(false)
  const [headerValue, setHeaderValue] = useState('')
  const { surveyId } = useParams()
  const answersContainerRef = useRef(null)

  const [activeLanguage] = useAppState(STATES.ACTIVE_LANGUAGE)
  const { survey } = useSurvey(surveyId)

  const isArrayByColumn =
    questionThemeName === getQuestionTypeInfo().ARRAY_COLUMN.theme
  const isArrayByText =
    questionThemeName === getQuestionTypeInfo().ARRAY_TEXT.theme
  const isArrayByNumbers =
    questionThemeName === getQuestionTypeInfo().ARRAY_NUMBERS.theme
  const isArrayPointChoice =
    questionThemeName === getQuestionTypeInfo().ARRAY.theme

  const showQNumCode = survey.showQNumCode

  const handleHeaderChange = (value) => {
    setHeaderValue(value)
    handleHeaderUpdate(value, scaleId)
  }

  useEffect(() => {
    const attributeName =
      scaleId === SCALE_1
        ? getDisplayAttributes().SCALE_HEADER_A.attributeName
        : getDisplayAttributes().SCALE_HEADER_B.attributeName
    setHeaderValue(getAttributeValue(attributes[attributeName], activeLanguage))
  }, [activeLanguage])

  const entitiesInfo = useMemo(() => {
    let info = {}

    if (isArrayByText || isArrayByNumbers || isArrayByColumn) {
      const items = Array.isArray(subquestions)
        ? subquestions.filter(
            (subquestion) => subquestion.scaleId === SCALE_2 || isArrayByColumn
          )
        : []

      info = {
        items,
        itemsKey: 'subquestions',
        idKey: 'qid',
        sortKey: 'sortOrder',
        titleKey: 'question',
        rowName: 'subquestion',
        placeholder: t('Subquestion'),
        scaleId: isArrayByColumn ? undefined : SCALE_2,
        entity: Entities.subquestion,
      }
    } else {
      const items =
        questionThemeName === getQuestionTypeInfo().ARRAY_DUAL_SCALE.theme
          ? answers.filter((answer) => answer.scaleId === scaleId)
          : answers

      info = {
        items,
        itemsKey: 'answers',
        idKey: 'aid',
        sortKey: 'sortOrder',
        titleKey: 'answer',
        rowName: 'answer option',
        placeholder: t('Answer option'),
        scaleId: scaleId ? scaleId : SCALE_1,
        entity: Entities.answer,
      }
    }

    setTimeout(() => {
      setNumberOfHorizontalEntities(info.items?.length || 0)
      setHorizontalInfo(info)
    }, 0)

    return info
  }, [
    answers,
    subquestions,
    isArrayByText,
    isArrayByNumbers,
    isArrayByColumn,
    questionThemeName,
  ])

  const handleSizeUpdate = () => {
    let highestHeight = 0
    if (!answersContainerRef.current) {
      return
    }

    answersContainerRef.current
      .querySelectorAll('.array-answer-content-editor')
      .forEach((item, index) => {
        const height = item.offsetHeight
        arrayAnswersWidth[index] = item.clientWidth
        if (height > highestHeight) {
          highestHeight = height
        }
      })

    if (isArrayDualScale) {
      const header = answersContainerRef.current.querySelector(
        '.array-dual-scale-header-input'
      )

      handleHeaderHeightChange(header?.clientHeight, scaleId)
    }

    setArrayAnswersWidth([...arrayAnswersWidth])
    setHighestHeight(highestHeight, scaleId)
  }

  const handleOnDragEnd = (dragResult) => {
    setIsReorderingAnswers(false)

    onDragEndCallback(dragResult, entitiesInfo)

    setTimeout(() => {
      handleSizeUpdate()
    }, 1)
  }

  useEffect(() => {
    const heightObserver = new ResizeObserver(() => {
      handleSizeUpdate()
    })

    if (answersContainerRef.current) {
      answersContainerRef.current
        .querySelectorAll(
          '.array-answer-content-editor, .array-dual-scale-header-input'
        )
        .forEach((item) => {
          heightObserver.observe(item)
        })

      heightObserver.observe(answersContainerRef.current)
    }

    return () => {
      heightObserver.disconnect()
    }
  }, [])

  const getAnswerStyle = (draggableStyle) => ({
    userSelect: 'none',
    ...draggableStyle,
  })

  return (
    <div ref={answersContainerRef}>
      <DragAndDrop
        onDragEnd={handleOnDragEnd}
        onDragStart={() => {
          setIsReorderingAnswers(true)
        }}
        droppableId={'droppable-subquestions-answers'}
        className={classNames('d-flex')}
        direction="horizontal"
      >
        <div
          style={{
            minWidth:
              scaleId === SCALE_1
                ? highestSubquestionWidth +
                  dragIconSize +
                  (isFocused && showQNumCode?.showNumber ? 80 : 0)
                : 0,
          }}
        ></div>
        <div style={{ marginTop: '20px' }}>
          {isArrayDualScale && (
            <div
              style={{ minHeight: max(headersHeight) }}
              className="d-flex justify-content-center"
            >
              <ContentEditor
                placeholder={`Header ${scaleId + 1}`}
                value={headerValue}
                update={(value) => handleHeaderChange(value, scaleId)}
                style={{ width: 'max-content' }}
                className={'array-dual-scale-header-input'}
              />
            </div>
          )}
          <div className="d-flex position-relative">
            {entitiesInfo?.items?.map((entity, index) => {
              return (
                <Draggable
                  key={`${
                    entity[entitiesInfo.idKey]
                  }-${qid}-${index}-subQuestionAnswerTitle`}
                  draggableId={`${
                    entity[entitiesInfo.idKey]
                  }-${qid}-${index}-subQuestionAnswerTitle`}
                  index={index}
                >
                  {(provided, snapshot) => (
                    <div
                      ref={provided.innerRef}
                      {...provided.draggableProps}
                      style={getAnswerStyle(provided.draggableProps.style)}
                      className={classNames('text-center text-secondary', {
                        'focus-element':
                          snapshot.isDragging || isReorderingAnswers,
                        'bg-grey': !(index % 2) && isArrayByColumn,
                      })}
                    >
                      <ArrayColumnTitle
                        dragIconSize={dragIconSize}
                        removeItem={() => removeItem(entitiesInfo, entity)}
                        highestHeight={highestHeight}
                        index={index}
                        isFocused={isFocused}
                        provided={provided}
                        title={L10ns({
                          prop: entitiesInfo.titleKey,
                          language: activeLanguage,
                          l10ns: entity.l10ns,
                        })}
                        handleUpdateL10ns={(value, index) =>
                          handleUpdateL10ns(value, entitiesInfo, index)
                        }
                        placeholder={entitiesInfo.placeholder}
                        itemsKey={entitiesInfo.itemsKey}
                        entity={entity}
                      />
                    </div>
                  )}
                </Draggable>
              )
            })}
            {showNoAnswer && (isArrayDualScale || isArrayPointChoice) && (
              <div className={classNames('question-body-content')}>
                <ArrayColumnTitle
                  dragIconSize={dragIconSize}
                  removeItem={() => {}}
                  highestHeight={highestHeight}
                  index={answers.length}
                  isFocused={isFocused}
                  title={getNoAnswerLabel(true)}
                  handleUpdateL10ns={() => {}}
                  itemsKey={entitiesInfo.itemsKey}
                  isNoAnswer={true}
                />
              </div>
            )}
          </div>
        </div>
      </DragAndDrop>
    </div>
  )
}
