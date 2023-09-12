import { useState } from 'react'
import { Button } from 'react-bootstrap'

import { AddIcon } from 'components/icons'
import classNames from 'classnames'
import { LANGUAGE_CODES, RandomNumber } from 'helpers'

import { ArrayQuestionSubQuestions } from './ArrayQuestionSubQuestions'
import { ArrayQuestionAnswersTitles } from './ArrayQuestionAnswerTitles/ArrayQuestionAnswerTitles'

import { QuestionTypeInfo } from '../QuestionTypeInfo'

const DRAG_ICON_SIZE = 22
const SCALE_1 = 'scale1'
const SCALE_2 = 'scale2'

export const ArrayQuestion = ({
  question: { subquestions = [], answers = [], sid, qid, type, gid } = {},
  question,
  handleUpdate,
  language,
  isFocused,
}) => {
  const [highestSubquestionWidth, setHighestSubquestiontWidth] = useState(0)
  const [arrayAnswersWidthScale1, setArrayAnswersWidthScale1] = useState([])
  const [arrayAnswersWidthScale2, setArrayAnswersWidthScale2] = useState([])
  const [highestHeight1, setHighestHeight1] = useState(0)
  const [highestHeight2, setHighestHeight2] = useState(0)

  const [subQuestionsHeight, setSubQuestionsHeight] = useState([])

  const setHighestHeight = (height, scale) => {
    if (scale === SCALE_1) {
      setHighestHeight1(height)
    } else {
      setHighestHeight2(height)
    }
  }

  const isArrayDualScale =
    question.questionThemeName === QuestionTypeInfo.ARRAY_DUAL_SCALE.theme

  const handleOnDragEnd = (dropResult, scaleId) => {
    const _answers = scaleId ? answers[scaleId] : answers

    if (!dropResult.destination) {
      return
    }

    if (
      dropResult.destination.droppableId === 'droppable-subquestions-answers'
    ) {
      const updatedQuestionAnswers = reorderItems(
        _answers,
        dropResult.source.index,
        dropResult.destination.index,
        'sortOrder'
      )

      handleUpdate({
        answers: scaleId
          ? { ...answers, [scaleId]: updatedQuestionAnswers }
          : updatedQuestionAnswers,
      })
    } else {
      const updatedSubQuestions = reorderItems(
        subquestions,
        dropResult.source.index,
        dropResult.destination.index,
        'questionOrder'
      )

      handleUpdate({ subquestions: updatedSubQuestions })
    }
  }

  const reorderItems = (listItems, startIndex, endIndex, orderProp) => {
    const updatedList = [...listItems]
    const [removed] = updatedList.splice(startIndex, 1)
    updatedList.splice(endIndex, 0, removed)

    return updatedList.map((item, index) => {
      item[orderProp] = index + 1
      return item
    })
  }

  const addSubQuestion = () => {
    const updatedSubQuestions = [...subquestions]
    const lastSubQuestion = updatedSubQuestions[updatedSubQuestions.length - 1]

    const subQuestionId = RandomNumber()
    const newSubQuestion = {
      qid: subQuestionId,
      parentQid: qid,
      l10ns: {
        ...Object.values(LANGUAGE_CODES).reduce((l10ns, language) => {
          return {
            ...l10ns,
            [language]: {
              id: RandomNumber(),
              qid: subQuestionId,
              question: '',
              script: null,
              language: language,
              help: '',
            },
          }
        }, {}),
      },
      gid: gid,
      sid: sid,
      type: type,
      sortorder: lastSubQuestion ? lastSubQuestion.sortorder + 1 : 1,
    }

    updatedSubQuestions.push(newSubQuestion)
    handleUpdate({ subquestions: updatedSubQuestions })
  }

  const addAnswer = (scaleId) => {
    const updatedAnswers = isArrayDualScale
      ? [...answers[scaleId]]
      : [...answers]
    const lastAnswer = updatedAnswers[updatedAnswers.length - 1]

    const newAnswer = {
      aid: lastAnswer ? lastAnswer.aid + 1 : 1,
      assessmentValue: '',
      code: 'A1',
      qid: qid,
      scaleId: 0,
      sortorder: lastAnswer ? lastAnswer.sortorder + 1 : 1,
    }

    updatedAnswers.push(newAnswer)
    answers[scaleId] = updatedAnswers
    handleUpdate({
      answers: isArrayDualScale
        ? { ...answers, scaleId: updatedAnswers }
        : updatedAnswers,
    })
  }

  return (
    <div
      className={classNames('array-question p-1 mt-4', {
        'pb-2': !isFocused,
      })}
    >
      <div className="d-flex gap-5">
        <div className="d-flex">
          <div>
            <ArrayQuestionAnswersTitles
              question={question}
              handleUpdate={handleUpdate}
              onDragEndCallback={handleOnDragEnd}
              highestSubquestionWidth={highestSubquestionWidth}
              isFocused={isFocused}
              arrayAnswersWidth={arrayAnswersWidthScale1}
              setArrayAnswersWidth={setArrayAnswersWidthScale1}
              dragIconSize={DRAG_ICON_SIZE}
              isArrayDualScale={isArrayDualScale}
              scaleId={SCALE_1}
              highestHeight={
                highestHeight1 > highestHeight2
                  ? highestHeight1
                  : highestHeight2
              }
              setHighestHeight={setHighestHeight}
            />
            <ArrayQuestionSubQuestions
              onDragEndCallback={handleOnDragEnd}
              language={language}
              question={question}
              isFocused={isFocused}
              addSubQuestion={addSubQuestion}
              highestWidth={highestSubquestionWidth}
              setHighestWidth={setHighestSubquestiontWidth}
              handleUpdate={handleUpdate}
              arrayAnswersWidth={arrayAnswersWidthScale1}
              dragIconSize={DRAG_ICON_SIZE}
              scaleId={SCALE_1}
              subQuestionsHeight={subQuestionsHeight}
              setSubQuestionsHeight={setSubQuestionsHeight}
            />
          </div>
          <div
            onClick={() => addAnswer(SCALE_1)}
            className={classNames(
              'bg-light cursor-pointer d-flex align-items-center justify-content-center array-add-column',
              {
                'd-none disabled': !isFocused,
              }
            )}
          >
            <div>
              <AddIcon className="mx-auto text-primary fill-current text-center" />
            </div>
            <div className="text-primary m-0">Add Column</div>
          </div>
        </div>
        {question.questionThemeName ===
          QuestionTypeInfo.ARRAY_DUAL_SCALE.theme && (
          <div className="d-flex">
            <div>
              <ArrayQuestionAnswersTitles
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
                highestHeight={
                  highestHeight1 > highestHeight2
                    ? highestHeight1
                    : highestHeight2
                }
                setHighestHeight={setHighestHeight}
              />
              <ArrayQuestionSubQuestions
                onDragEndCallback={handleOnDragEnd}
                language={language}
                question={question}
                isFocused={isFocused}
                addSubQuestion={addSubQuestion}
                highestWidth={highestSubquestionWidth}
                setHighestWidth={setHighestSubquestiontWidth}
                handleUpdate={handleUpdate}
                arrayAnswersWidth={arrayAnswersWidthScale2}
                dragIconSize={DRAG_ICON_SIZE}
                showContentEditor={false}
                scaleId={SCALE_2}
                subQuestionsHeight={subQuestionsHeight}
              />
            </div>
            <div
              onClick={() => addAnswer(SCALE_2)}
              className={classNames(
                'bg-light cursor-pointer d-flex align-items-center justify-content-center array-add-column',
                {
                  'd-none disabled': !isFocused,
                }
              )}
            >
              <div>
                <AddIcon className="mx-auto text-primary fill-current text-center" />
              </div>
              <div className="text-primary m-0">Add Column</div>
            </div>
          </div>
        )}
      </div>
      <div style={{ width: '120px' }}>
        <Button
          onClick={addSubQuestion}
          className={classNames(
            'd-flex justify-content-center align-items-center text-primary bg-light array-add-row',
            {
              'd-none disabled': !isFocused,
            }
          )}
          style={{ minWidth: highestSubquestionWidth + DRAG_ICON_SIZE }}
          variant=""
        >
          <AddIcon className="text-primary fill-current" /> Add Row
        </Button>
      </div>
    </div>
  )
}
