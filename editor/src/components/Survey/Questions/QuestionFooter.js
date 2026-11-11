import { useState } from 'react'
import { useParams } from 'react-router-dom'
import classNames from 'classnames'

import { useAppState, useBuffer, useSurvey } from 'hooks'
import {
  Entities,
  STATES,
  createBufferOperation,
  getSiteUrl,
  getSurveyLanguages,
} from 'helpers'
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

  // Label sets are only supported for list/multiple choice style questions for now
  const supportsLabelSets =
    questionEditComponents[question.questionThemeName] ===
    OptionQuestionEditMode
  const isSingleChoiceTheme = singleChoiceThemes.includes(
    question.questionThemeName
  )
  const labelSetEntity = isSingleChoiceTheme
    ? Entities.answer
    : Entities.subquestion
  const labelSetChildren =
    (isSingleChoiceTheme ? question.answers : question.subquestions) || []

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
      `/index.php/survey/index/action/previewquestion/sid/${surveyId}/gid/${question.gid}/qid/${question.qid}/lang/${activeLanguage}`
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
      {supportsLabelSets && (
        <SaveLabelSetModal
          show={showSaveLabelSetModal}
          onHide={() => setShowSaveLabelSetModal(false)}
          _children={labelSetChildren}
          entityType={labelSetEntity}
          surveyLanguages={getSurveyLanguages(survey)}
        />
      )}
      <div
        data-testid="question-footer"
        className="question-footer align-items-center justify-content-end d-flex gap-3 text-end pt-3 pe-3"
      >
        {isFocused && supportsLabelSets && (
          <TooltipContainer
            tip={
              isSingleChoiceTheme
                ? t('Save answers as new label set')
                : t('Save subquestions as new label set')
            }
          >
            <div
              className={classNames({
                'cursor-pointer': labelSetChildren.length > 0,
                'opacity-50 pointer-events-none': !labelSetChildren.length,
              })}
              data-testid="question-footer-save-label-set-icon"
              onClick={() => setShowSaveLabelSetModal(true)}
            >
              <DownloadIcon
                className="footer-icon fill-current"
                width={20}
                height={20}
              />
            </div>
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
