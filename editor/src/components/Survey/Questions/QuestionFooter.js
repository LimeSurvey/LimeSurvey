import classNames from 'classnames'

import { useAppState, useBuffer } from 'hooks'
import { STATES, createBufferOperation, confirmAlert } from 'helpers'
import { getTooltipMessages } from 'helpers/options'
import { TooltipContainer } from 'components'
import { DeleteIcon, CopyIcon } from 'components/icons'

export const QuestionFooter = ({
  question,
  handleRemove,
  handleDuplicate,
  isFocused = false,
}) => {
  const [isSurveyActive] = useAppState(STATES.IS_SURVEY_ACTIVE, false)
  const { addToBuffer } = useBuffer()

  const handleRemoveQuestion = () => {
    confirmAlert({ icon: 'warning' }).then(({ isConfirmed }) => {
      if (!isConfirmed) {
        return
      }
      const operation = createBufferOperation(question.qid).question().delete()

      addToBuffer(operation)
      handleRemove()
    })
  }

  if (!isFocused) return <></>

  return (
    <div
      data-testid="question-footer"
      className="question-footer align-items-center justify-content-end d-flex gap-3 text-end pt-3 pe-3"
    >
      <TooltipContainer
        tip={getTooltipMessages().ACTIVE_DISABLED}
        showTip={isSurveyActive}
      >
        <div
          style={{
            opacity: isSurveyActive && 0.3,
            cursor: !isSurveyActive && 'pointer',
            pointerEvents: isSurveyActive && 'none',
          }}
          data-testid="question-footer-copy-icon"
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
        tip={getTooltipMessages().ACTIVE_DISABLED}
        showTip={isSurveyActive}
      >
        <div
          style={{
            opacity: isSurveyActive && 0.3,
            cursor: !isSurveyActive && 'pointer',
            pointerEvents: isSurveyActive && 'none',
          }}
          onClick={handleRemoveQuestion}
          data-testid="question-footer-delete-icon"
          id="question-footer-delete-icon"
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
