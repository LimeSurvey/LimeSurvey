import { useState } from 'react'
import { useParams } from 'react-router-dom'
import classNames from 'classnames'

import { useFocused } from 'hooks'
import { SideBarRow } from 'components/SideBar/SideBarRow'
import { MeatballMenu } from 'components/MeatballMenu/MeatballMenu'
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
}) => {
  const { setFocused } = useFocused()
  const { surveyId } = useParams()
  const [showLogicModal, setShowLogicModal] = useState(false)

  const handleDuplicate = () => {
    duplicateQuestion()
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
        title={question.l10ns[language]?.question}
        meatballButton={
          <MeatballMenu
            deleteText={t('Delete question')}
            duplicateText={t('Duplicate question')}
            handleDelete={deleteQuestion}
            handleDuplicate={handleDuplicate}
            additionalItems={[
              {
                label: t('Check Logic'),
                testId: 'show-logic-button',
                onClick: () => setShowLogicModal(true),
              },
            ]}
          />
        }
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
