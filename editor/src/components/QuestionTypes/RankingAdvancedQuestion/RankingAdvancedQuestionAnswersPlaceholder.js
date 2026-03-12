import { Droppable } from 'react-beautiful-dnd'

import { CloseCircleFillIcon } from 'components/icons'
import { ContentEditor } from 'components/UIComponents'
import classNames from 'classnames'

export const RankingAdvancedQuestionAnswersPlaceholder = ({
  answers,
  answersHeight = [],
  answersValue = [],
  clearAnswer,
}) => {
  return (
    <div>
      {answers.map((answer, index) => {
        const answerInfo = answersValue[index]
        const hasValue = !!answerInfo?.value
        const answerTitle = answerInfo?.answerTitle

        return (
          <Droppable
            key={`ranked-advanced-placeholder-${answer.qid}-${index}`}
            droppableId={`${index}`}
            index={`ranked-advanced-placeholder-${index}`}
          >
            {(provided, snapshot) => (
              <div
                data-index={index}
                ref={provided.innerRef}
                {...provided.draggableProps}
              >
                <div className="position-relative answer-item-placeholder p-1 ms-3 d-flex align-items-center">
                  <div
                    className={classNames(
                      'cursor-pointer position-absolute remove-option-button',
                      { 'd-none': !hasValue }
                    )}
                    onClick={() => clearAnswer(index)}
                  >
                    <CloseCircleFillIcon
                      className={classNames(
                        'text-danger close-circle-fill-icon fill-current d-block'
                      )}
                    />
                  </div>
                  <ContentEditor
                    value={hasValue ? answerTitle : index + 1}
                    placeholder={t('Answer option')}
                    className={classNames(
                      'choice placeholder-content-editor text-center px-3',
                      { 'text-success': hasValue }
                    )}
                    style={{
                      minHeight: answersHeight[index],
                      outline: snapshot.isDraggingOver && '1px solid #14ae5c',
                    }}
                    disabled={true}
                  />
                </div>
              </div>
            )}
          </Droppable>
        )
      })}
    </div>
  )
}
