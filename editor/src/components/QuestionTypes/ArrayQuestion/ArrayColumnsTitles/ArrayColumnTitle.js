import classNames from 'classnames'
import { useParams } from 'react-router-dom'

import { ContentEditor } from 'components'
import { CloseCircleFillIcon, DragIcon } from 'components/icons'
import { STATES } from 'helpers'
import { useAppState, useSurvey } from 'hooks'

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
  entity,
}) => {
  const [isSurveyActive] = useAppState(STATES.IS_SURVEY_ACTIVE)
  const type = itemsKey === 'answers' ? 'code' : 'title'
  const { surveyId } = useParams()
  const { survey } = useSurvey(surveyId)
  const showQNumCode = survey.showQNumCode

  return (
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
          'd-flex align-items-center'
        )}
        style={{
          position: 'absolute',
          top: '50%',
          left: '10px',
          transform: 'translate(-50%, -50%)',
        }}
      >
        {isFocused && <DragIcon className="text-secondary fill-current" />}
      </div>
      {!(isSurveyActive && itemsKey === 'subquestions') && (
        <div
          className="cursor-pointer remove-option-button remove-item-button"
          onClick={() => removeItem(index)}
          style={{
            position: 'absolute',
            top: '-10px',
            left: '50%',
            transform: 'translate(-50%, -50%)',
          }}
          data-testid="remove-horizontal-option-button"
        >
          <CloseCircleFillIcon
            className={classNames('text-danger fill-current', {
              'd-none disabled': !isFocused || isNoAnswer,
            })}
          />
        </div>
      )}
      <div>
        {isFocused && showQNumCode?.showNumber && entity && entity[type] && (
          <div className="question-code-tag" style={{ marginLeft: '20px' }}>
            {entity[type]}
          </div>
        )}
      </div>
      <div
        style={{
          minHeight: highestHeight,
          paddingLeft: isFocused && showQNumCode?.showNumber ? 0 : dragIconSize,
          paddingRight:
            isFocused && showQNumCode?.showNumber ? 0 : dragIconSize,
          marginLeft: isNoAnswer && isFocused ? '40px' : 0,
        }}
      >
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
  )
}
