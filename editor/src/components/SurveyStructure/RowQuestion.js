import classNames from 'classnames'

import { useFocused } from 'hooks'
import { RemoveHTMLTagsInString } from 'helpers'
import { SideBarRow } from 'components/SideBar/SideBarRow'
import { MeatballMenu } from 'components/MeatballMenu/MeatballMenu'
import { QuestionListIcon } from 'components/icons'

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
  const questionTitleWithoutHtmlTags = RemoveHTMLTagsInString(
    question.l10ns[language]?.question
  )

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
        title={questionTitleWithoutHtmlTags}
        meatballButton={
          <MeatballMenu
            deleteText={t('Delete question')}
            duplicateText={t('Duplicate question')}
            handleDelete={deleteQuestion}
            handleDuplicate={handleDuplicate}
          />
        }
        icon={<QuestionListIcon />}
        code={question.title}
        testId={`sidebar-row-question`}
        onRowClick={() =>
          setFocused({ ...question }, groupIndex, questionIndex)
        }
      />
    </div>
  )
}
