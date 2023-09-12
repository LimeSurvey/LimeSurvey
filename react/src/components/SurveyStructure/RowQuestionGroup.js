import { SideBarRow } from 'components/SideBar/SideBarRow'
import { RowQuestionsList } from './RowQuestionsList'
import { MeatballMenu } from 'components/MeatballMenu/MeatballMenu'
import { ClipBoardIcon } from 'components/icons'

export const RowQuestionGroup = ({
  questionGroup,
  language,
  update,
  provided,
  duplicateGroup,
  deleteGroup,
  groupIndex,
  showQuestionCode,
  onTitleClick = () => {},
}) => {
  const handleQuestionsUpdate = (questions) => {
    update({ ...questionGroup, questions })
  }

  const handleDuplicate = () => {
    duplicateGroup()
  }

  const handleDelete = () => {
    deleteGroup()
  }

  return (
    <SideBarRow
      testId={`survey-structure-question-group-${questionGroup.gid}`}
      title={questionGroup.l10ns[language].groupName}
      titlePlaceholder={"What's your question group is about?"}
      provided={provided}
      icon={<ClipBoardIcon />}
      onTitleClick={onTitleClick}
      meatballButton={
        <MeatballMenu
          deleteText={'Delete group'}
          duplicateText={'Duplicate group'}
          handleDelete={handleDelete}
          handleDuplicate={handleDuplicate}
        />
      }
    >
      <RowQuestionsList
        questions={questionGroup.questions}
        language={language}
        handleUpdate={handleQuestionsUpdate}
        groupIndex={groupIndex}
        showQuestionCode={showQuestionCode}
      />
    </SideBarRow>
  )
}
