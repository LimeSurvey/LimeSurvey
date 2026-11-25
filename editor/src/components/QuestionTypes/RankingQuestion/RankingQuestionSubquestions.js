import { Button } from 'react-bootstrap'
import { PlusLg } from 'react-bootstrap-icons'
import classNames from 'classnames'
import { RankingQuestionSubquestion } from './RankingQuestionSubquestion'
import { DragAndDrop } from 'components/UIComponents'
import { Draggable } from '@hello-pangea/dnd'
import {
  canUpdateChildren,
  Entities,
  getTooltipMessages,
  L10ns,
  STATES,
} from 'helpers'
import { useAppState } from 'hooks'
import { TooltipContainer } from 'components'

export const RankingQuestionSubquestions = ({
  question: { subquestions = [] } = {},
  isFocused,
  handleChildAdd,
  handleSubquestionUpdate,
  handleOnDragEnd,
  handleRemovingSubquestions,
  handleChildCodeUpdate,
}) => {
  const [activeLanguage] = useAppState(STATES.ACTIVE_LANGUAGE)
  const [isSurveyActive] = useAppState(STATES.IS_SURVEY_ACTIVE)
  const isChildUpdateAllowed = canUpdateChildren(
    Entities.subquestion,
    isSurveyActive
  )

  const getSubquestionStyle = (draggableStyle) => ({
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
        {subquestions.map((subquestion, index) => (
          <Draggable
            key={`ranking-${subquestion.appKey || subquestion.qid}`}
            draggableId={`ranking-${subquestion.appKey || subquestion.qid}`}
            index={index}
          >
            {(provided, snapshot) => (
              <div
                ref={provided.innerRef}
                {...provided.draggableProps}
                style={getSubquestionStyle(provided.draggableProps.style)}
                className={classNames(
                  {
                    'focus-element': snapshot.isDragging,
                  },
                  'mb-2'
                )}
              >
                <RankingQuestionSubquestion
                  subquestion={L10ns({
                    l10ns: subquestion.l10ns,
                    language: activeLanguage,
                    prop: 'question',
                  })}
                  qid={subquestion.qid}
                  isFocused={isFocused}
                  index={index}
                  onChange={(value) => handleSubquestionUpdate(value, index)}
                  provided={provided}
                  handleRemovingSubquestions={handleRemovingSubquestions}
                  handleCodeUpdate={(value, index) =>
                    handleChildCodeUpdate({
                      newCode: value,
                      childIndex: index,
                      childArray: subquestions,
                      entityType: Entities.subquestion,
                      entityTitleKey: 'subquestion',
                    })
                  }
                  title={subquestion.title}
                />
              </div>
            )}
          </Draggable>
        ))}
      </DragAndDrop>
      <div>
        <TooltipContainer
          tip={getTooltipMessages().ACTIVE_DISABLED}
          showTip={!isChildUpdateAllowed}
        >
          <Button
            onClick={() => handleChildAdd(subquestions, Entities.subquestion)}
            variant={'outline'}
            className={classNames('text-primary add-choice-button px-0 mt-2', {
              'd-none disabled': !isFocused,
            })}
            data-testid="single-choice-add-subquestion-button"
            disabled={!isChildUpdateAllowed}
          >
            <PlusLg /> {t('Add subquestion')}
          </Button>
        </TooltipContainer>
      </div>
    </>
  )
}
