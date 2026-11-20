import { useState } from 'react'
import { useParams } from 'react-router-dom'
import classNames from 'classnames'

import { useAppState, useBuffer, useSurvey } from 'hooks'
import { Entities, STATES, createBufferOperation, getSiteUrl } from 'helpers'
import { getTooltipMessages } from 'helpers/options'
import { TooltipContainer } from 'components'
import { DeleteIcon, CopyIcon, DownloadIcon, EyeIcon } from 'components/icons'
import { ConfirmModal } from 'components/Modals'
import { SaveLabelSetModal } from 'components/LabelSets'
import {
  OptionQuestionEditMode,
  questionEditComponents,
  singleChoiceThemes,
} from 'components/QuestionTypes'

// Stable fallback, so the save modal's language options aren't rebuilt on every render
const NO_LANGUAGES = []

export const QuestionFooter = ({
  question,
  handleRemove,
  handleDuplicate,
  isFocused = false,
}) => {
  const [isSurveyActive] = useAppState(STATES.IS_SURVEY_ACTIVE, false)
  const [activeLanguage] = useAppState(STATES.ACTIVE_LANGUAGE)
  const { surveyId } = useParams()
  const { addToBuffer } = useBuffer()
  const [showDeleteModal, setShowDeleteModal] = useState(false)
  const [showSaveLabelSetModal, setShowSaveLabelSetModal] = useState(false)
  const { survey } = useSurvey(surveyId)
  const [permissions] = useAppState(STATES.USER_PERMISSIONS)
  const surveyLanguages = survey?.languages ?? NO_LANGUAGES

  // Resolved by the backend, incl. per label set update rights
  const canCreateLabelSet = !!permissions?.effective?.labelSetsCreate
  const canUpdateLabelSet = !!permissions?.effective?.labelSetsUpdate
  // Label sets are only supported for list/multiple choice style questions for now
  const supportsLabelSets =
    questionEditComponents[question.questionThemeName] ===
    OptionQuestionEditMode
  const canSaveLabelSet =
    supportsLabelSets && (canCreateLabelSet || canUpdateLabelSet)
  const isSingleChoiceTheme = singleChoiceThemes.includes(
    question.questionThemeName
  )
  const labelSetEntity = isSingleChoiceTheme
    ? Entities.answer
    : Entities.subquestion
  const labelSetChildren =
    (isSingleChoiceTheme ? question.answers : question.subquestions) || []
  // Neutral wording: users with only the update right can only overwrite a set
  const saveLabelSetLabel = isSingleChoiceTheme
    ? t('Save answers as label set')
    : t('Save subquestions as label set')

  const handleRemoveQuestion = () => {
    setShowDeleteModal(true)
  }

  const handleConfirmRemove = () => {
    const operation = createBufferOperation(question.qid).question().delete()
    addToBuffer(operation)
    handleRemove()
    setShowDeleteModal(false)
  }

  const openQuestionPreview = () => {
    const previewUrl = getSiteUrl(
      `/survey/index/action/previewquestion/sid/${surveyId}/gid/${question.gid}/qid/${question.qid}/lang/${activeLanguage}`
    )
    window.open(previewUrl, '_blank')
  }

  return (
    <>
      <ConfirmModal
        show={showDeleteModal}
        onHide={() => setShowDeleteModal(false)}
        onConfirm={handleConfirmRemove}
        title={t('Delete question')}
        description={t(
          'Are you sure you want to delete this question? This action cannot be reverted.'
        )}
        confirmButtonText={t('Delete')}
      />
      {canSaveLabelSet && (
        <SaveLabelSetModal
          show={showSaveLabelSetModal}
          onHide={() => setShowSaveLabelSetModal(false)}
          _children={labelSetChildren}
          entityType={labelSetEntity}
          surveyLanguages={surveyLanguages}
          canCreate={canCreateLabelSet}
          canUpdate={canUpdateLabelSet}
        />
      )}
      <div
        data-testid="question-footer"
        className="question-footer align-items-center justify-content-end d-flex gap-3 text-end pt-3 pe-3"
      >
        {isFocused && canSaveLabelSet && (
          <TooltipContainer tip={saveLabelSetLabel}>
            <button
              type="button"
              className="question-footer-icon-button pt-2 mt-1"
              aria-label={saveLabelSetLabel}
              disabled={!labelSetChildren.length}
              data-testid="question-footer-save-label-set-icon"
              onClick={() => setShowSaveLabelSetModal(true)}
            >
              <DownloadIcon
                className="footer-icon fill-current"
                width={20}
                height={20}
                aria-hidden="true"
              />
            </button>
          </TooltipContainer>
        )}
        <div
          className={classNames('cursor-pointer', {
            'disabled': !isFocused,
            'pointer-events-none': !isFocused,
          })}
          data-testid="question-footer-preview-icon"
          onClick={openQuestionPreview}
        >
          <EyeIcon
            className={classNames('footer-icon ', {
              'active-icon fill-current': isFocused,
              'd-none disabled': !isFocused,
            })}
            width={20}
            height={20}
          />
        </div>
        {isFocused && (
          <>
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
                id={'question-footer-delete-icon'}
              >
                <DeleteIcon
                  className={classNames('footer-icon ', {
                    'active-icon fill-current': isFocused,
                    'opacity-0 disabled': !isFocused,
                  })}
                />
              </div>
            </TooltipContainer>
          </>
        )}
      </div>
    </>
  )
}
