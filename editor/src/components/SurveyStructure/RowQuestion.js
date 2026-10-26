import { useState } from 'react'
import { useParams } from 'react-router-dom'
import classNames from 'classnames'

import { useBuffer, useFocused, useSurvey } from 'hooks'
import { createBufferOperation, Toast } from 'helpers'
import { ExclamationMark } from 'components/icons'
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
}) => {
  const { setFocused } = useFocused()
  const { surveyId } = useParams()
  const { survey, update } = useSurvey(surveyId)
  const { addToBuffer } = useBuffer()
  const areCodesVisible = !!survey.showQNumCode?.showCode
  const hasAnswerOptions = (question.answers?.length ?? 0) > 0

  const toggleQuestionCodes = () => {
    const operationAttr = {
      showQNumCode: {
        ...survey.showQNumCode,
        showCode: !survey.showQNumCode?.showCode,
      },
    }
    update(operationAttr)
    addToBuffer(createBufferOperation(surveyId).survey().update(operationAttr))
  }

  const handleAssignNumericCodes = () => {
    const toastOptions = { position: 'top-center', duration: 5000 }
    try {
      const changed = assignNumericAnswerCodes(question)
      Toast({
        ...toastOptions,
        message: changed
          ? t('Numeric answer codes assigned.')
          : t('Answer options already use numeric codes.'),
        className: 'import-survey-toast import-survey-toast--success',
        leftIcon: <SmallCheckMarkIcon />,
        rightIcon: '',
      })
    } catch (e) {
      Toast({
        ...toastOptions,
        message: t('Numeric answer codes could not be assigned.'),
        className: 'import-survey-toast import-survey-toast--error',
        leftIcon: <ExclamationMark />,
      })
    }
  }
  const [showLogicModal, setShowLogicModal] = useState(false)

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
            label: t('Show/hide question codes'),
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
