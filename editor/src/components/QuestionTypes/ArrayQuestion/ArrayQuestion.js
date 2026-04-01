import { useMemo, useState } from 'react'
import classNames from 'classnames'

import {
  SCALE_1,
  SCALE_2,
  createBufferOperation,
  STATES,
  isTrue,
} from 'helpers'
import { useAppState, useBuffer } from 'hooks'
import { getTooltipMessages } from 'helpers/options'
import { getDisplayAttributes } from 'components/QuestionSettings/attributes'
import { Button } from 'components/UIComponents'
import { AddIcon } from 'components/icons'
import { TooltipContainer } from 'components'

import { getQuestionTypeInfo } from '../getQuestionTypeInfo'
import { ArrayColumnsTitles, ArrayRows } from './'

const DRAG_ICON_SIZE = 22

export const ArrayQuestion = ({
  question,
  handleUpdate,
  language,
  isFocused,
  surveySettings,
  handleChildLUpdate,
  handleChildAdd,
  handleChildDelete,
  handleOnChildDragEnd,
}) => {
  const { addToBuffer } = useBuffer()
  const [highestHeight1, setHighestHeight1] = useState(0)
  const [highestHeight2, setHighestHeight2] = useState(0)
  const [hoirzontalInfo1, setHoirzontalInfo1] = useState({})
  const [hoirzontalInfo2, setHorizontalInfo2] = useState({})
  const [subQuestionsHeight, setSubQuestionsHeight] = useState([])
  const [highestSubquestionWidth, setHighestSubquestiontWidth] = useState(0)
  const [arrayAnswersWidthScale1, setArrayAnswersWidthScale1] = useState([])
  const [arrayAnswersWidthScale2, setArrayAnswersWidthScale2] = useState([])
  const [verticalEntitiesInfo, setVerticalEntitiesInfo] = useState({})
  const [headersHeight, setHeadersHeight] = useState([0, 0])

  const [numberOfHorizontalEntities1, setNumberOfHorizontalEntities1] =
    useState(0)
  const [numberOfHorizontalEntities2, setNumberOfHorizontalEntities2] =
    useState(0)
  const [isSurveyActive] = useAppState(STATES.IS_SURVEY_ACTIVE)

  const highestHeight = useMemo(() => {
    return highestHeight1 > highestHeight2 ? highestHeight1 : highestHeight2
  }, [highestHeight1, highestHeight2])

  const setHighestHeight = (height, scale) => {
    if (scale === SCALE_1) {
      setHighestHeight1(height)
    } else {
      setHighestHeight2(height)
    }
  }

  const isArrayDualScale =
    question.questionThemeName === getQuestionTypeInfo().ARRAY_DUAL_SCALE.theme
  const isArrayByColumn =
    question.questionThemeName === getQuestionTypeInfo().ARRAY_COLUMN.theme
  const isArrayPointChoice =
    question.questionThemeName === getQuestionTypeInfo().ARRAY.theme

  const showNoAnswer =
    (isArrayByColumn || isArrayDualScale || isArrayPointChoice) &&
    !isTrue(question.mandatory) &&
    surveySettings.showNoAnswer

  const colName = [
    getQuestionTypeInfo().ARRAY_NUMBERS.theme,
    getQuestionTypeInfo().ARRAY_TEXT.theme,
    getQuestionTypeInfo().ARRAY_COLUMN.theme,
  ].includes(question.questionThemeName)
    ? 'subquestion'
    : 'answer option'

  const handleOnDragEnd = (dropResult, entitiesInfo) => {
    if (!dropResult.destination) {
      return
    }

    const { itemsKey, scaleId, entity } = entitiesInfo

    handleOnChildDragEnd(dropResult, question[itemsKey], entity, { scaleId })
  }

  const addItem = (info) => {
    const { itemsKey, entity } = info
    const updatedItems = question[itemsKey] ? [...question[itemsKey]] : []

    handleChildAdd(updatedItems, entity, {
      scaleId: info.scaleId,
    })
  }

  const handleUpdateL10ns = (value, entitiesInfo, index) => {
    const { items, itemsKey, idKey, entity } = entitiesInfo

    let updatedItems = [...question[itemsKey]]

    const updatedItemIndex = question[itemsKey].findIndex(
      (questionItem) => items[index][idKey] === questionItem[idKey]
    )

    handleChildLUpdate(value, updatedItemIndex, updatedItems, entity)
  }

  const removeItem = (info, item) => {
    const { itemsKey, entity, idKey } = info
    const updatedItems = [...question[itemsKey]]

    handleChildDelete(item[idKey], updatedItems, entity)
  }

  const handleHeaderHeightChange = (height, index) => {
    headersHeight[index] = height

    setHeadersHeight(headersHeight)
  }

  const handleHeaderUpdate = (value, scaleId) => {
    const attributeName =
      scaleId === SCALE_1
        ? getDisplayAttributes().SCALE_HEADER_A.attributeName
        : getDisplayAttributes().SCALE_HEADER_B.attributeName

    question.attributes[attributeName] = {
      ...(question.attributes[attributeName] || {}),
      [language]: value,
    }

    const operation = createBufferOperation(question.qid)
      .questionAttribute()
      .update({
        [attributeName]: {
          [language]: value,
        },
      })

    addToBuffer(operation)
    handleUpdate(question)
  }

  return (
    <>
      <div className="array-question d-flex gap-5" data-testid="array-question">
        <div className="d-flex">
          <div>
            <ArrayColumnsTitles
              question={question}
              onDragEndCallback={handleOnDragEnd}
              highestSubquestionWidth={highestSubquestionWidth}
              isFocused={isFocused}
              arrayAnswersWidth={arrayAnswersWidthScale1}
              setArrayAnswersWidth={setArrayAnswersWidthScale1}
              dragIconSize={DRAG_ICON_SIZE}
              isArrayDualScale={isArrayDualScale}
              scaleId={SCALE_1}
              highestHeight={highestHeight}
              setHighestHeight={setHighestHeight}
              setNumberOfHorizontalEntities={setNumberOfHorizontalEntities1}
              setHorizontalInfo={setHoirzontalInfo1}
              handleUpdateL10ns={handleUpdateL10ns}
              removeItem={removeItem}
              handleHeaderUpdate={handleHeaderUpdate}
              handleHeaderHeightChange={handleHeaderHeightChange}
              headersHeight={headersHeight}
              showNoAnswer={showNoAnswer && isArrayPointChoice}
            />
            <ArrayRows
              onDragEndCallback={handleOnDragEnd}
              language={language}
              question={question}
              isFocused={isFocused}
              highestWidth={highestSubquestionWidth}
              setHighestWidth={setHighestSubquestiontWidth}
              handleUpdate={handleUpdate}
              arrayAnswersWidth={arrayAnswersWidthScale1}
              dragIconSize={DRAG_ICON_SIZE}
              scaleId={SCALE_1}
              subQuestionsHeight={subQuestionsHeight}
              setSubQuestionsHeight={setSubQuestionsHeight}
              numberOfHorizontalEntities={
                showNoAnswer && isArrayPointChoice
                  ? numberOfHorizontalEntities1 + 1
                  : numberOfHorizontalEntities1
              }
              removeItem={removeItem}
              handleUpdateL10ns={handleUpdateL10ns}
              setVerticalEntitiesInfo={setVerticalEntitiesInfo}
              showNoAnswer={showNoAnswer}
            />
          </div>
          <div
            className={classNames('pb-1 ms-2 me-4', {
              'd-none': !isFocused,
            })}
            style={{
              position: 'sticky',
              right: '0',
            }}
          >
            <TooltipContainer
              tip={getTooltipMessages().ACTIVE_DISABLED}
              showTip={isSurveyActive && colName === 'subquestion'}
              placement="left"
            >
              <Button
                onClick={() => addItem(hoirzontalInfo1)}
                style={{
                  height: '100%',
                  width: '50px',
                }}
                disabled={isSurveyActive && colName === 'subquestion'}
                className="add-item-button"
                testId="add-vertical-item-button"
              >
                <AddIcon />
              </Button>
            </TooltipContainer>
          </div>
        </div>
        {isArrayDualScale && (
          <div className="d-flex">
            <div>
              <ArrayColumnsTitles
                question={question}
                handleUpdate={handleUpdate}
                onDragEndCallback={handleOnDragEnd}
                highestSubquestionWidth={highestSubquestionWidth}
                isFocused={isFocused}
                arrayAnswersWidth={arrayAnswersWidthScale2}
                setArrayAnswersWidth={setArrayAnswersWidthScale2}
                dragIconSize={DRAG_ICON_SIZE}
                isArrayDualScale={isArrayDualScale}
                scaleId={SCALE_2}
                highestHeight={highestHeight}
                setHighestHeight={setHighestHeight}
                setNumberOfHorizontalEntities={setNumberOfHorizontalEntities2}
                setHorizontalInfo={setHorizontalInfo2}
                removeItem={removeItem}
                handleUpdateL10ns={handleUpdateL10ns}
                handleHeaderUpdate={handleHeaderUpdate}
                handleHeaderHeightChange={handleHeaderHeightChange}
                headersHeight={headersHeight}
                showNoAnswer={showNoAnswer}
              />
              <ArrayRows
                onDragEndCallback={handleOnDragEnd}
                language={language}
                question={question}
                isFocused={isFocused}
                highestWidth={highestSubquestionWidth}
                setHighestWidth={setHighestSubquestiontWidth}
                handleUpdate={handleUpdate}
                arrayAnswersWidth={arrayAnswersWidthScale2}
                dragIconSize={DRAG_ICON_SIZE}
                showContentEditor={false}
                showQuestionCode={false}
                scaleId={SCALE_1}
                subQuestionsHeight={subQuestionsHeight}
                numberOfHorizontalEntities={
                  showNoAnswer
                    ? numberOfHorizontalEntities2 + 1
                    : numberOfHorizontalEntities2
                }
                removeItem={removeItem}
                handleUpdateL10ns={handleUpdateL10ns}
                setVerticalEntitiesInfo={setVerticalEntitiesInfo}
              />
            </div>
            <div
              className={classNames('pb-1 ms-2 me-4', {
                'd-none': !isFocused,
              })}
            >
              <TooltipContainer
                tip={getTooltipMessages().ACTIVE_DISABLED}
                showTip={isSurveyActive && colName === 'subquestion'}
                placement={'left'}
              >
                <Button
                  onClick={() => addItem(hoirzontalInfo2)}
                  style={{ height: '100%', width: '50px' }}
                  disabled={isSurveyActive && colName === 'subquestion'}
                  className="add-item-button"
                  testId="add-vertical-item-button2"
                >
                  <AddIcon />
                </Button>
              </TooltipContainer>
            </div>
          </div>
        )}
      </div>
      <div
        style={{ marginLeft: highestSubquestionWidth + DRAG_ICON_SIZE }}
        className={classNames('mt-2 array-question array-question-footer', {
          'd-none': !isFocused,
        })}
      >
        <TooltipContainer
          tip={getTooltipMessages().ACTIVE_DISABLED}
          showTip={
            isSurveyActive && verticalEntitiesInfo.rowName === 'subquestion'
          }
          placement="left"
        >
          <Button
            className={classNames('add-item-button add-horizontal-item w-100')}
            onClick={() => addItem(verticalEntitiesInfo)}
            disabled={
              isSurveyActive && verticalEntitiesInfo.rowName === 'subquestion'
            }
            testId="add-horizontal-item-button"
          >
            <AddIcon />
          </Button>
        </TooltipContainer>
      </div>
    </>
  )
}
