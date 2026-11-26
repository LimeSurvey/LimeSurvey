import { useState } from 'react'
import { useParams } from 'react-router-dom'
import classNames from 'classnames'

import { useFocused } from 'hooks'
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
  areCodesVisible = false,
  toggleQuestionCodes,
}) => {
  const { setFocused } = useFocused()
  const { surveyId } = useParams()
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
            icon: 'ri-file-copy-2-line',
            onClick: duplicateQuestion,
            testId: 'duplicate-button',
            disabled: { state: isSurveyActive },
          },
          {
            type: 'item',
            label: t('Delete question'),
            icon: 'ri-delete-bin-fill',
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
        onRowClick={() =>
          setFocused({ ...question }, groupIndex, questionIndex)
        }
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
