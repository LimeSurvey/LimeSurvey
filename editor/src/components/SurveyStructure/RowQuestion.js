import { useState } from 'react'
import { useParams } from 'react-router-dom'
import classNames from 'classnames'

import { queryClient } from 'queryClient'
import { useFocused, useOperationCallback } from 'hooks'
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

export const RowQuestion = ({
  question,
  language,
  provided,
  duplicateQuestion,
  deleteQuestion,
  groupIndex,
  questionIndex,
  snapshot,
  focused,
  isSurveyActive,
  assignNumericAnswerCodes,
  areCodesVisible = false,
  toggleQuestionCodes,
}) => {
  const { setFocused } = useFocused()
  const { surveyId } = useParams()
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
      changed = assignNumericAnswerCodes(question)
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
        'Answer codes will be renumbered 1, 2, 3, … Conditions, expressions, default answers and quotas that use the current codes are not updated and may stop working.'
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
        'focus-element': snapshot.isDragging,
        'opacity-25': question.attributes?.hide_question?.value,
      })}
    >
      <SideBarRow
        titlePlaceholder={t("What's your question?")}
        provided={provided}
        menuToggleId="meatball-menu-button"
        title={question.l10ns[language]?.question}
        isFocused={focused?.qid === question?.qid}
        menuItems={[
          {
            type: 'header',
            label: t('Question actions'),
          },
          {
            type: 'item',
            label: t('Duplicate question'),
            icon: 'ri-file-copy-line',
            onClick: duplicateQuestion,
            testId: 'duplicate-button',
            disabled: { state: isSurveyActive },
          },
          {
            type: 'item',
            label: t('Delete question'),
            icon: 'ri-delete-bin-line',
            onClick: deleteQuestion,
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
            // The label states the action so screen readers know the current state
            label: areCodesVisible
              ? t('Hide question codes')
              : t('Show question codes'),
            onClick: toggleQuestionCodes,
            testId: 'toggle-question-codes-button',
            trailingIcon: areCodesVisible ? 'ri-eye-line' : 'ri-eye-off-line',
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
        onRowClick={() =>
          setFocused({ ...question }, groupIndex, questionIndex)
        }
      />
      <ConfirmModal
        show={numericCodesConfirm.show}
        onHide={() => setNumericCodesConfirm({ show: false, references: [] })}
        onConfirm={confirmAssignNumericCodes}
        title={t('Assign numeric answer codes')}
        description={numericCodesWarning}
        confirmButtonText={t('Assign codes')}
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
}
