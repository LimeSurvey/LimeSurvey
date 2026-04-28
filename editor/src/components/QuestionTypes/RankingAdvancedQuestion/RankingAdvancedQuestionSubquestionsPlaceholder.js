import { Droppable } from 'react-beautiful-dnd'

import { CloseCircleFillIcon } from 'components/icons'
import { ContentEditor } from 'components/UIComponents'
import classNames from 'classnames'

export const RankingAdvancedQuestionSubquestionsPlaceholder = ({
  subquestions,
  subquestionsHeight = [],
  subquestionsValue = [],
  clearSubquestion,
}) => {
  return (
    <div>
      {subquestions.map((subquestion, index) => {
        const subquestionInfo = subquestionsValue[index]
        const hasValue = !!subquestionInfo?.value
        const subquestionTitle = subquestionInfo?.subquestionTitle

        return (
          <Droppable
            key={`ranked-advanced-placeholder-${subquestion.parentQid}-${index}`}
            droppableId={`${index}`}
            index={`ranked-advanced-placeholder-${index}`}
          >
            {(provided, snapshot) => (
              <div
                data-index={index}
                ref={provided.innerRef}
                {...provided.draggableProps}
              >
                <div className="position-relative subquestion-item-placeholder p-1 ms-3 d-flex align-items-center">
                  <div
                    className={classNames(
                      'cursor-pointer position-absolute remove-option-button',
                      { 'd-none': !hasValue }
                    )}
                    onClick={() => clearSubquestion(index)}
                  >
                    <CloseCircleFillIcon
                      className={classNames(
                        'text-danger close-circle-fill-icon fill-current d-block'
                      )}
                    />
                  </div>
                  <ContentEditor
                    value={hasValue ? subquestionTitle : index + 1}
                    placeholder={t('Subquestion option')}
                    className={classNames(
                      'choice placeholder-content-editor text-center px-3',
                      { 'text-success': hasValue }
                    )}
                    style={{
                      minHeight: subquestionsHeight[index],
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
