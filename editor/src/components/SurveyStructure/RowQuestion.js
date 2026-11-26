import { memo, useState } from 'react'
import classNames from 'classnames'

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
  toggleQuestionCodes,
}) {
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
        ]}
        icon={<QuestionListIcon />}
        code={question.title}
        testId={`sidebar-row-question`}
        onRowClick={() => onSelect(question, questionIndex)}
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
