import { DragDropContext, Droppable } from 'react-beautiful-dnd'
import classNames from 'classnames'
import { PlusLg } from 'react-bootstrap-icons'
import { Button } from 'react-bootstrap'
import { useState } from 'react'
import { cloneDeep } from 'lodash'

import { Entities, getInfoFromObjectByIndex, Toast } from 'helpers'

import { RankingAdvancedQuestionAnswersPlaceholder } from './RankingAdvancedQuestionAnswersPlaceholder'
import { RankingAdvancedQuestionAnswers } from './RankingAdvancedQuestionAnswers'

const ANSWERS_DROPPALE_ID = 'ranking-advanced-answers'
const PLACEHOLDERS_DROPPALE_ID = 'ranking-advanced-placeholders'

export const RankingAdvancedQuestion = ({
  question: { answers: questionAnswers = [], qid } = {},
  isFocused,
  handleChildLUpdate,
  handleChildAdd,
  handleChildDelete,
  handleOnChildDragEnd,
  values = [],
  participantMode,
  language,
  onValueChange = () => {},
}) => {
  const [answersHeight, setAnswersHeight] = useState([])
  const [answersValue, setAnswersValue] = useState(cloneDeep(values))

  const handleOnDragEnd = (dropResult) => {
    if (participantMode) {
      handleResponseAnswer(dropResult)
      return
    }

    handleOnChildDragEnd(dropResult, questionAnswers, Entities.answer)
  }

  const handleResponseAnswer = (dropResult) => {
    const sourceIndex = +dropResult.source.index
    const destinationIndex = +dropResult.destination.droppableId
    const newAnswerValue = { ...questionAnswers[sourceIndex] }

    let answerAlreadyExists = false
    answersValue.forEach((answer) => {
      if (answer.aid === newAnswerValue.aid) {
        answerAlreadyExists = true
      }
    })

    if (answerAlreadyExists) {
      Toast({
        message: t('Answer already exists'),
        position: 'bottom-center',
        duration: 3000,
      })

      return
    }

    answersValue[destinationIndex] = {
      ...answersValue[destinationIndex],
      aid: newAnswerValue.aid,
      value: newAnswerValue.code,
      answerTitle: newAnswerValue.l10ns[language].answer,
      subquestionTitle: newAnswerValue.code,
    }

    setAnswersValue([...answersValue])
    onValueChange(newAnswerValue.code, answersValue[destinationIndex].key)
  }

  const clearAnswer = (index) => {
    const answerInfo = getInfoFromObjectByIndex(answersValue[index])
    answersValue[index][answerInfo.key] = ''

    answersValue[index] = {
      ...answersValue[index],
      aid: '',
      value: '',
      answerTitle: '',
      subquestionTitle: '',
    }

    setAnswersValue([...answersValue])
    onValueChange('', answersValue[index].key)
  }

  const handleAnswerUpdate = (value, index) => {
    handleChildLUpdate(value, index, questionAnswers, Entities.answer)
  }

  const handleRemoveAnswer = (answer) => {
    handleChildDelete(answer.aid, questionAnswers, Entities.answer)
  }

  return (
    <DragDropContext onDragEnd={handleOnDragEnd}>
      <div
        data-testid="ranking-advanced-question"
        className={'ranking-advanced-question d-flex flex-row'}
      >
        <Droppable key={ANSWERS_DROPPALE_ID} droppableId={ANSWERS_DROPPALE_ID}>
          {(provided) => (
            <div ref={provided.innerRef} {...provided.droppableProps}>
              <RankingAdvancedQuestionAnswers
                isFocused={isFocused}
                handleAnswerUpdate={handleAnswerUpdate}
                handleRemoveAnswer={handleRemoveAnswer}
                answers={questionAnswers}
                qid={qid}
                setAnswersHeight={setAnswersHeight}
                language={language}
              />
              {provided.placeholder}
            </div>
          )}
        </Droppable>
        <Droppable droppableId={PLACEHOLDERS_DROPPALE_ID}>
          {(provided) => (
            <div ref={provided.innerRef} {...provided.droppableProps}>
              {!isFocused && (
                <RankingAdvancedQuestionAnswersPlaceholder
                  isFocused={isFocused}
                  answers={questionAnswers}
                  answersHeight={answersHeight}
                  answersValue={answersValue}
                  clearAnswer={clearAnswer}
                />
              )}
              {provided.placeholder}
            </div>
          )}
        </Droppable>
      </div>
      <div>
        <Button
          onClick={() => handleChildAdd(questionAnswers, Entities.answer)}
          variant={'outline'}
          className={classNames('text-primary add-choice-button px-0 mt-2', {
            'd-none disabled': !isFocused,
          })}
          data-testid="single-choice-add-answer-button"
        >
          <PlusLg /> {t('Add answer')}
        </Button>
      </div>
    </DragDropContext>
  )
}
