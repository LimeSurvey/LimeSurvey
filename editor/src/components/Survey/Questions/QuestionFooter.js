import { useState } from 'react'
import classNames from 'classnames'

import { useAppState, useBuffer } from 'hooks'
import { STATES, createBufferOperation } from 'helpers'
import { getTooltipMessages } from 'helpers/options'
import { TooltipContainer } from 'components'
import { DeleteIcon, CopyIcon } from 'components/icons'
import { ConfirmModal } from 'components/Modals'

export const QuestionFooter = ({
  question,
  handleRemove,
  handleDuplicate,
  isFocused = false,
}) => {
  const [isSurveyActive] = useAppState(STATES.IS_SURVEY_ACTIVE, false)
  const { addToBuffer } = useBuffer()
  const [showDeleteModal, setShowDeleteModal] = useState(false)

  const handleRemoveQuestion = () => {
    setShowDeleteModal(true)
  }

  const handleConfirmRemove = () => {
    const operation = createBufferOperation(question.qid).question().delete()
    addToBuffer(operation)
    handleRemove()
    setShowDeleteModal(false)
  }

  return (
    <>
      <ConfirmModal
        show={showDeleteModal}
        onHide={() => setShowDeleteModal(false)}
        onConfirm={handleConfirmRemove}
        title={t('Delete question')}
        description={t(
          'Are you sure you want to delete this question? this action cannot be reverted.'
        )}
        confirmButtonText={t('Delete')}
      />
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
              pointerEvents: (isSurveyActive || !isFocused) && 'none',
            }}
            className={classNames({ disabled: !isFocused })}
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
    </>
  )
}
