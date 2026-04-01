import { Button } from 'react-bootstrap'
import { PlusLg } from 'react-bootstrap-icons'
import classNames from 'classnames'
import { RankingQuestionAnswer } from './RankingQuestionAnswer'
import { DragAndDrop } from 'components/UIComponents'
import { Draggable } from 'react-beautiful-dnd'
import { Entities, L10ns, STATES } from 'helpers'
import { useAppState } from 'hooks'

export const RankingQuestionAnswers = ({
  question: { answers = [] } = {},
  isFocused,
  handleChildAdd,
  handleAnswerUpdate,
  handleOnDragEnd,
  handleRemovingAnswers,
}) => {
  const [activeLanguage] = useAppState(STATES.ACTIVE_LANGUAGE)

  const getAnswerStyle = (draggableStyle) => ({
    userSelect: 'none',
    margin: `0 0 8px 0`,
    ...draggableStyle,
  })

  return (
    <>
      {isFocused && (
        <div className="mb-4">
          <h5>{t('You can add texts or upload images')}</h5>
        </div>
      )}
      <DragAndDrop onDragEnd={handleOnDragEnd} droppableId={'droppable'}>
        {answers.map((answer, index) => (
          <Draggable
            key={`ranking-${answer.qid}-${answer.aid}`}
            draggableId={`ranking-${answer.qid}-${answer.aid}`}
            index={index}
          >
            {(provided, snapshot) => (
              <div
                ref={provided.innerRef}
                {...provided.draggableProps}
                style={getAnswerStyle(provided.draggableProps.style)}
                className={classNames(
                  {
                    'focus-element': snapshot.isDragging,
                  },
                  'mb-2'
                )}
              >
                <RankingQuestionAnswer
                  answer={L10ns({
                    l10ns: answer.l10ns,
                    language: activeLanguage,
                    prop: 'answer',
                  })}
                  aid={answer.aid}
                  isFocused={isFocused}
                  index={index}
                  onChange={(value) => handleAnswerUpdate(value, index)}
                  provided={provided}
                  handleRemovingAnswers={handleRemovingAnswers}
                />
              </div>
            )}
          </Draggable>
        ))}
      </DragAndDrop>
      <div>
        <Button
          onClick={() => handleChildAdd(answers, Entities.answer)}
          variant={'outline'}
          className={classNames('text-primary add-choice-button px-0 mt-2', {
            'd-none disabled': !isFocused,
          })}
          data-testid="single-choice-add-answer-button"
        >
          <PlusLg /> {t('Add answer')}
        </Button>
      </div>
    </>
  )
}
