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
  showQuestionCode,
}) => {
  const { setFocused } = useFocused()
  const questionTitleWithoutHtmlTags = RemoveHTMLTagsInString(
    question.l10ns[language].question
  )

  const handleDuplicate = () => {
    duplicateQuestion()
  }

  const handleDelete = () => {
    deleteQuestion()
  }

  return (
    <>
      <SideBarRow
        titlePlaceholder={"What's your question?"}
        provided={provided}
        title={questionTitleWithoutHtmlTags}
        meatballButton={
          <MeatballMenu
            deleteText={'Delete question'}
            duplicateText={'Duplicate question'}
            handleDelete={handleDelete}
            handleDuplicate={handleDuplicate}
          />
        }
        icon={<QuestionListIcon />}
        showQuestionCode={showQuestionCode}
        code={question.title}
        testId={`row-question-group-question-${question.qid} row-question-group-question`}
        onRowClick={() =>
          setFocused({ ...question }, groupIndex, questionIndex)
        }
      />
    </>
  )
}
