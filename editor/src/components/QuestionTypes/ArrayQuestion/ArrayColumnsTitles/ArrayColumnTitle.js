import classNames from 'classnames'
import { useParams } from 'react-router-dom'
import { ContentEditor } from 'components'
import { CloseCircleFillIcon, DragIcon } from 'components/icons'
import { STATES } from 'helpers'
import { useAppState, useSurvey } from 'hooks'
import { SubquestionCodeInput } from '../../subquestionCodeComponents'

export const ArrayColumnTitle = ({
  isFocused,
  removeItem,
  dragIconSize,
  provided = {},
  highestHeight,
  index,
  title,
  handleUpdateL10ns,
  placeholder = 'Answer option',
  itemsKey,
  isNoAnswer = false,
  code,
  handleChildCodeUpdate,
}) => {
  const [isSurveyActive] = useAppState(STATES.IS_SURVEY_ACTIVE)
  const { surveyId } = useParams()
  const { survey } = useSurvey(surveyId)
  const showQNumCode = survey.showQNumCode

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
              'disabled opacity-0': !provided.dragHandleProps,
            },
            'd-flex align-items-center action-item-button drag column'
          )}
        >
          {isFocused && <DragIcon className="text-secondary fill-current" />}
        </div>
        {!(isSurveyActive && itemsKey === 'subquestions') && (
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
            paddingLeft:
              isFocused && showQNumCode?.showNumber ? '40px' : dragIconSize,
            paddingRight:
              isFocused && showQNumCode?.showNumber ? '40px' : dragIconSize,
            marginTop:
              isNoAnswer && isFocused && showQNumCode?.showNumber ? '28px' : 0,
          }}
        >
          {isFocused && showQNumCode?.showNumber && !isNoAnswer && (
            <SubquestionCodeInput
              isColumnTitle={true}
              isSurveyActive={isSurveyActive}
              onChange={(e) => handleChildCodeUpdate(e.target.value, index)}
              code={code}
            />
          )}
          <ContentEditor
            className={classNames(
              'text-start choice array-answer-content-editor'
            )}
            placeholder={placeholder}
            value={title}
            update={(value) => handleUpdateL10ns(value, index)}
            disabled={isNoAnswer}
          />
        </div>
      </div>
    </div>
  )
}
