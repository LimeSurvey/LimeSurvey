import { useAppState } from 'hooks'
import classNames from 'classnames'

import { TooltipContainer } from 'components/TooltipContainer/TooltipContainer'
import { DeleteIcon, CopyIcon } from 'components/icons'

export const QuestionFooter = ({
  handleRemove,
  handleDuplicate,
  isFocused = false,
}) => {
  const [isSurveyActive] = useAppState('isSurveyActive', false)

  if (!isFocused) return <></>

  return (
    <div className="question-footer align-items-center justify-content-end d-flex gap-3 text-end p-3">
      <TooltipContainer
        tip={isSurveyActive && 'Disabled while survey is published.'}
      >
        <div
          style={{
            opacity: isSurveyActive && 0.3,
            cursor: !isSurveyActive && 'pointer',
            pointerEvents: isSurveyActive && 'none',
          }}
          onClick={handleDuplicate}
        >
          <CopyIcon
            className={classNames('footer-icon ', {
              'active-icon fill-current': isFocused,
              'd-none disabled': !isFocused,
            })}
          />
        </div>
      </TooltipContainer>
      <TooltipContainer
        tip={isSurveyActive && 'Disabled while survey is published.'}
      >
        <div
          style={{
            opacity: isSurveyActive && 0.3,
            cursor: !isSurveyActive && 'pointer',
            pointerEvents: isSurveyActive && 'none',
          }}
          onClick={handleRemove}
        >
          <DeleteIcon
            className={classNames('footer-icon ', {
              'active-icon fill-current': isFocused,
              'opacity-0 disabled': !isFocused,
            })}
          />
        </div>
      </TooltipContainer>
    </div>
  )
}
