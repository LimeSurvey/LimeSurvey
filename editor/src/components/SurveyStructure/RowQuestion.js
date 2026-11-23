import { memo, useState } from 'react'
import classNames from 'classnames'

import { queryClient } from 'queryClient'
import { useOperationCallback } from 'hooks'
import {
  Entities,
  findQuestionCodeReferences,
  hasNumericAnswerCodes,
  Operations,
  STATES,
  Toast,
} from 'helpers'
import { ConfirmModal } from 'components/Modals'
import { ExclamationMark, InformationIcon } from 'components/icons'
import SmallCheckMarkIcon from 'components/icons/SmallCheckMarkIcon'
import { SideBarRow } from 'components/SideBar/SideBarRow'
import { QuestionListIcon } from 'components/icons'

import { SurveyLogicModal } from './SurveyLogicModal'

/**
 * Structure row of a question. Memoized, so it only renders again when its own
 * props change: Not on every pointer move of a drag, and not for changes to
 * other questions, the focus or the URL. That needs props that keep their
 * identity, so the handlers get the question and its index as arguments
 * instead of being created for each row.
 */
export const RowQuestion = memo(function RowQuestion({
  question,
  language,
  surveyId,
  dragHandleProps,
  questionIndex,
  isDragging = false,
  isFocused = false,
  isSurveyActive,
  areCodesVisible = false,
  /* (question, questionIndex) */
  onSelect,
  /* (question, questionIndex) */
  duplicateQuestion,
  /* (questionIndex) */
  deleteQuestion,
  /* (question, questionIndex), returns whether the codes changed */
  assignNumericAnswerCodes,
  toggleQuestionCodes,
}) {
  const { subscribeToOperationFinish } = useOperationCallback()
  const hasAnswerOptions = (question.answers?.length ?? 0) > 0
  const [numericCodesConfirm, setNumericCodesConfirm] = useState({
    show: false,
    references: [],
  })

  const showToast = (message, variant, icon) =>
    Toast({
      position: 'top-center',
      duration: 5000,
      message,
      className: `import-survey-toast import-survey-toast--${variant}`,
      leftIcon: icon,
      rightIcon: '',
    })

  const showAssignError = (error) => {
    // eslint-disable-next-line no-console
    console.error('Assigning numeric answer codes failed', error)
    showToast(
      t('Numeric answer codes could not be assigned.'),
      'error',
      <ExclamationMark />
    )
  }

  const handleAssignNumericCodes = () => {
    if (hasNumericAnswerCodes(question.answers)) {
      showToast(
        t('Answer options already use numeric codes.'),
        'info',
        <InformationIcon />
      )
      return
    }
    // Read at click time so rows do not subscribe to the whole survey
    const survey = queryClient.getQueryData([STATES.SURVEY])?.survey
    setNumericCodesConfirm({
      show: true,
      references: findQuestionCodeReferences(survey, question),
    })
  }

  const confirmAssignNumericCodes = () => {
    setNumericCodesConfirm({ show: false, references: [] })
    let changed
    try {
      changed = assignNumericAnswerCodes(question, questionIndex)
    } catch (error) {
      showAssignError(error)
      return
    }
    if (!changed) {
      return
    }
    // The change is only queued here (the save is debounced), so subscribing
    // afterwards still catches its result and leaves no stale subscription
    subscribeToOperationFinish({
      entity: Entities.answer,
      operation: Operations.update,
      id: question.qid,
      match: (operation) =>
        Array.isArray(operation.props) &&
        hasNumericAnswerCodes(operation.props),
      callback: () =>
        showToast(
          t('Numeric answer codes assigned.'),
          'success',
          <SmallCheckMarkIcon />
        ),
      onError: showAssignError,
    })
  }

  const numericCodesWarning = (
    <>
      {t(
        'The answer codes of this question will be renumbered to use “1, 2, 3…”. Conditions, expressions, default answers and quotas that use the current codes are not updated and may stop working.'
      )}
      {numericCodesConfirm.references.length > 0 && (
        <>
          <br />
          <br />
          {t('This question is used in:')}{' '}
          <strong data-testid="numeric-codes-references">
            {numericCodesConfirm.references.join(', ')}
          </strong>
        </>
      )}
    </>
  )
  const [showLogicModal, setShowLogicModal] = useState(false)

  return (
    <div
      className={classNames({
        'focus-element': isDragging,
        'opacity-25': question.attributes?.hide_question?.value,
      })}
    >
      <SideBarRow
        titlePlaceholder={t("What's your question?")}
        provided={{ dragHandleProps }}
        menuToggleId="meatball-menu-button"
        title={question.l10ns[language]?.question}
        isFocused={isFocused}
        menuItems={[
          {
            type: 'header',
            label: t('Question actions'),
          },
          {
            type: 'item',
            label: t('Duplicate question'),
            icon: 'ri-file-copy-2-line',
            onClick: () => duplicateQuestion(question, questionIndex),
            testId: 'duplicate-button',
            disabled: { state: isSurveyActive },
          },
          {
            type: 'item',
            label: t('Delete question'),
            icon: 'ri-delete-bin-fill',
            onClick: () => deleteQuestion(questionIndex),
            className: 'text-danger',
            testId: 'delete-button',
            disabled: { state: isSurveyActive },
          },
          { type: 'divider' },
          {
            type: 'item',
            label: t('Check logic'),
            onClick: () => setShowLogicModal(true),
            testId: 'show-logic-button',
            hideIconSpace: true,
          },
          {
            type: 'item',
            // The label and icon show the action, not the current state
            label: areCodesVisible
              ? t('Hide question codes')
              : t('Show question codes'),
            onClick: toggleQuestionCodes,
            testId: 'toggle-question-codes-button',
            trailingIcon: areCodesVisible ? 'ri-eye-off-line' : 'ri-eye-line',
          },
          {
            type: 'item',
            label: t('Assign numeric answer codes'),
            onClick: handleAssignNumericCodes,
            testId: 'assign-numeric-codes-button',
            hideIconSpace: true,
            disabled: {
              state: !hasAnswerOptions || isSurveyActive,
              tooltip: isSurveyActive
                ? t('Not available while the survey is active.')
                : t('This question type has no answer options.'),
            },
          },
        ]}
        icon={<QuestionListIcon />}
        code={question.title}
        testId={`sidebar-row-question`}
        showCode={areCodesVisible}
        onRowClick={() => onSelect(question, questionIndex)}
      />
      <ConfirmModal
        show={numericCodesConfirm.show}
        onHide={() => setNumericCodesConfirm({ show: false, references: [] })}
        onConfirm={confirmAssignNumericCodes}
        title={t('Assign numeric answer codes')}
        description={numericCodesWarning}
        confirmButtonText={t('Assign codes')}
        confirmButtonVariant="primary"
      />
      <SurveyLogicModal
        show={showLogicModal}
        onHide={() => setShowLogicModal(false)}
        sid={surveyId}
        qid={question.qid}
        language={language}
      />
    </div>
  )
})
