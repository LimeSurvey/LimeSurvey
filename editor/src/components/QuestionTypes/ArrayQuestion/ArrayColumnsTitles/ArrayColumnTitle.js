import classNames from 'classnames'
import { ContentEditor } from 'components'
import { CloseCircleFillIcon } from 'components/icons'
import { canUpdateChildren, Entities, hasTempId, STATES } from 'helpers'
import { useAppState, useToggleQuestionCodes } from 'hooks'
import { CodeInput } from '../../codeComponents'
import { AssessmentValueInput } from '../../AssessmentValueInput'
import { ChildDragIcon } from '../../ChildDragIcon'

export const ArrayColumnTitle = ({
  isFocused,
  removeItem,
  dragIconSize,
  provided = {},
  highestHeight,
  index,
  title,
  handleUpdateL10ns,
  placeholder,
  itemsKey,
  isNoAnswer = false,
  code,
  handleChildCodeUpdate,
  showAssessmentValue = false,
  assessmentValue,
  assessmentScaleNumber,
  handleAssessmentValueUpdate = () => {},
  qid = '',
  isTitleFocused,
  id,
}) => {
  const [isSurveyActive] = useAppState(STATES.IS_SURVEY_ACTIVE)
  const isAnswer = itemsKey === 'answers'
  const isChildUpdateAllowed = canUpdateChildren(
    isAnswer ? Entities.answer : Entities.subquestion,
    isSurveyActive
  )
  const { areCodesVisible } = useToggleQuestionCodes()

  return (
    <div>
      <div
        className={classNames(
          'd-flex array-question-item position-relative remove-option-button-parent'
        )}
      >
        <div
          {...provided.dragHandleProps}
          className={classNames(
            {
              'disabled opacity-0': isNoAnswer,
              'cursor-not-allowed': !isNoAnswer && !isChildUpdateAllowed,
            },
            'd-flex align-items-center action-item-button drag column'
          )}
        >
          {isFocused && (
            <ChildDragIcon
              isLocked={!isChildUpdateAllowed}
              className="text-secondary fill-current"
            />
          )}
        </div>
        {isChildUpdateAllowed && (
          <div
            className="cursor-pointer remove-option-button action-item-button remove column"
            onClick={() => removeItem(index)}
            data-testid="remove-horizontal-option-button"
          >
            <CloseCircleFillIcon
              className={classNames('text-danger fill-current', {
                'd-none disabled': !isFocused || isNoAnswer,
              })}
            />
          </div>
        )}
        <div
          style={{
            minHeight: highestHeight,
            paddingLeft: isFocused && areCodesVisible ? '40px' : dragIconSize,
            paddingRight: isFocused && areCodesVisible ? '40px' : dragIconSize,
            marginTop: isNoAnswer && isFocused && areCodesVisible ? '28px' : 0,
          }}
        >
          {!isNoAnswer && (
            <div className="array-answer-metadata">
              {isFocused && areCodesVisible && (
                <CodeInput
                  isAnswerCode={isAnswer}
                  isColumnTitle={true}
                  isEditable={isChildUpdateAllowed}
                  onChange={(e) => handleChildCodeUpdate(e.target.value, index)}
                  code={code}
                />
              )}
              {showAssessmentValue && (
                <AssessmentValueInput
                  assessmentValue={assessmentValue}
                  answerCode={code}
                  scaleNumber={assessmentScaleNumber}
                  onChange={(e) => handleAssessmentValueUpdate(e.target.value)}
                />
              )}
            </div>
          )}
          <ContentEditor
            className={classNames(
              'text-center choice array-answer-content-editor'
            )}
            placeholder={placeholder ?? t('Answer option')}
            value={title}
            update={(value) => handleUpdateL10ns(value, index)}
            disabled={isNoAnswer}
            showToolbar={true}
            focus={hasTempId(id) && !hasTempId(qid) && !isTitleFocused}
          />
        </div>
      </div>
    </div>
  )
}
