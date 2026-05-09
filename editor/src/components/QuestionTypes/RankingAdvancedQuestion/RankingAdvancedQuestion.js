import { DragDropContext, Droppable } from 'react-beautiful-dnd'
import classNames from 'classnames'
import { PlusLg } from 'react-bootstrap-icons'
import { Button } from 'react-bootstrap'
import { useState } from 'react'
import { cloneDeep } from 'lodash'

import {
  Entities,
  getInfoFromObjectByIndex,
  getTooltipMessages,
  STATES,
  Toast,
} from 'helpers'
import { useAppState } from 'hooks'
import { TooltipContainer } from 'components'

import { RankingAdvancedQuestionSubquestionsPlaceholder } from './RankingAdvancedQuestionSubquestionsPlaceholder'
import { RankingAdvancedQuestionSubquestions } from './RankingAdvancedQuestionSubquestions'

const SUBQUESTIONS_DROPPALE_ID = 'ranking-advanced-subquestions'
const PLACEHOLDERS_DROPPALE_ID = 'ranking-advanced-placeholders'

export const RankingAdvancedQuestion = ({
  question: { subquestions: rankingSubquestions = [], qid } = {},
  isFocused,
  handleChildLUpdate,
  handleChildAdd,
  handleChildDelete,
  handleOnChildDragEnd,
  values = [],
  participantMode,
  language,
  onValueChange = () => {},
  handleChildCodeUpdate,
}) => {
  const [isSurveyActive] = useAppState(STATES.IS_SURVEY_ACTIVE)

  const [subquestionsHeight, setSubquestionsHeight] = useState([])
  const [subquestionsValue, setSubquestionsValue] = useState(cloneDeep(values))

  const handleOnDragEnd = (dropResult) => {
    if (participantMode) {
      handleResponseAnswer(dropResult)
      return
    }

    handleOnChildDragEnd(dropResult, rankingSubquestions, Entities.subquestion)
  }

  const handleResponseAnswer = (dropResult) => {
    const sourceIndex = +dropResult.source.index
    const destinationIndex = +dropResult.destination.droppableId
    const newSubquestionValue = { ...rankingSubquestions[sourceIndex] }

    let subquestionAlreadyExists = false
    subquestionsValue.forEach((subquestion) => {
      if (subquestion.qid === newSubquestionValue.qid) {
        subquestionAlreadyExists = true
      }
    })

    if (subquestionAlreadyExists) {
      Toast({
        message: t('Subquestion already exists'),
        position: 'bottom-center',
        duration: 3000,
      })

      return
    }

    subquestionsValue[destinationIndex] = {
      ...subquestionsValue[destinationIndex],
      qid: newSubquestionValue.qid,
      value: newSubquestionValue.title,
      answerTitle: newSubquestionValue.title,
      subquestionTitle: newSubquestionValue.l10ns[language].question,
    }

    setSubquestionsValue([...subquestionsValue])
    onValueChange(
      newSubquestionValue.title,
      subquestionsValue[destinationIndex].key
    )
  }

  const clearSubquestion = (index) => {
    const subquestionInfo = getInfoFromObjectByIndex(subquestionsValue[index])
    subquestionsValue[index][subquestionInfo.key] = ''

    subquestionsValue[index] = {
      ...subquestionsValue[index],
      qid: '',
      value: '',
      answerTitle: '',
      subquestionTitle: '',
    }

    setSubquestionsValue([...subquestionsValue])
    onValueChange('', subquestionsValue[index].key)
  }

  const handleSubquestionUpdate = (value, index) => {
    handleChildLUpdate(value, index, rankingSubquestions, Entities.subquestion)
  }

  const handleRemoveSubquestion = (subquestion) => {
    handleChildDelete(
      subquestion.qid,
      rankingSubquestions,
      Entities.subquestion
    )
  }

  return (
    <DragDropContext onDragEnd={handleOnDragEnd}>
      <div
        data-testid="ranking-advanced-question"
        className={'ranking-advanced-question d-flex flex-row'}
      >
        <Droppable
          key={SUBQUESTIONS_DROPPALE_ID}
          droppableId={SUBQUESTIONS_DROPPALE_ID}
        >
          {(provided) => (
            <div ref={provided.innerRef} {...provided.droppableProps}>
              <RankingAdvancedQuestionSubquestions
                isFocused={isFocused}
                handleSubquestionUpdate={handleSubquestionUpdate}
                handleRemoveSubquestion={handleRemoveSubquestion}
                subquestions={rankingSubquestions}
                qid={qid}
                setSubQuestionsHeight={setSubquestionsHeight}
                language={language}
                handleCodeUpdate={handleChildCodeUpdate}
              />
              {provided.placeholder}
            </div>
          )}
        </Droppable>
        <Droppable droppableId={PLACEHOLDERS_DROPPALE_ID}>
          {(provided) => (
            <div ref={provided.innerRef} {...provided.droppableProps}>
              {!isFocused && (
                <RankingAdvancedQuestionSubquestionsPlaceholder
                  isFocused={isFocused}
                  subquestions={rankingSubquestions}
                  subquestionsHeight={subquestionsHeight}
                  subquestionsValue={subquestionsValue}
                  clearSubquestion={clearSubquestion}
                />
              )}
              {provided.placeholder}
            </div>
          )}
        </Droppable>
      </div>
      <div className="add-child-button-container">
        <TooltipContainer
          tip={getTooltipMessages().ACTIVE_DISABLED}
          showTip={isSurveyActive}
        >
          <Button
            onClick={() =>
              handleChildAdd(rankingSubquestions, Entities.subquestion)
            }
            variant={'outline'}
            className={classNames(
              'text-primary add-choice-button gap-2 p-0 mt-4 border-none',
              {
                'd-none disabled': !isFocused,
              }
            )}
            data-testid="single-choice-add-subquestion-button"
            disabled={isSurveyActive}
          >
            <PlusLg /> {t('Add subquestion')}
          </Button>
        </TooltipContainer>
      </div>
    </DragDropContext>
  )
}
