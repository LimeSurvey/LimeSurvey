import { useState } from 'react'
import { useParams } from 'react-router-dom'
import classNames from 'classnames'

import { useBuffer, useFocused, useSurvey } from 'hooks'
import { createBufferOperation } from 'helpers'
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
}) => {
  const { setFocused } = useFocused()
  const { surveyId } = useParams()
  const [showLogicModal, setShowLogicModal] = useState(false)
  const { survey, update } = useSurvey(surveyId)
  const { addToBuffer } = useBuffer()
  const areCodesVisible = !!survey.showQNumCode?.showCode

  const toggleQuestionCodes = () => {
    const operationAttr = {
      showQNumCode: {
        showCode: !survey.showQNumCode?.showCode,
        showNumber: !survey.showQNumCode?.showNumber,
      },
    }
    update(operationAttr)
    addToBuffer(createBufferOperation(surveyId).survey().update(operationAttr))
  }

  return (
    <div
      className={classNames('question-body-content ', {
        'focus-element': snapshot.isDragging,
        'opacity-25': question.attributes?.hide_question?.value,
        'focus-bg-purple': focused.qid === question.qid,
        'text-white': focused.qid === question.qid,
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
          {
            type: 'divider',
          },
          {
            type: 'item',
            label: t('Check logic'),
            onClick: () => setShowLogicModal(true),
            testId: 'show-logic-button',
          },
          {
            type: 'item',
            label: t('Show/hide question codes'),
            trailingIcon: areCodesVisible ? 'ri-eye-line' : 'ri-eye-off-line',
            onClick: toggleQuestionCodes,
            testId: 'toggle-question-codes-button',
          },
          {
            // Placeholder until the numeric answer codes investigation is done
            type: 'item',
            label: t('Assign numeric answer codes'),
            testId: 'assign-numeric-codes-button',
            disabled: { state: true, tooltip: t('Coming soon') },
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
