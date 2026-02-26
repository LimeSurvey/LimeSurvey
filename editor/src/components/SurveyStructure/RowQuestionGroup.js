import { SideBarRow } from 'components/SideBar/SideBarRow'
import { RowQuestionsList } from './RowQuestionsList'
import { MeatballMenu } from 'components/MeatballMenu/MeatballMenu'
import { ClipBoardIcon } from 'components/icons'
import { LANGUAGE_CODES } from 'helpers'
import { useFocused } from 'hooks'

export const RowQuestionGroup = ({
  questionGroup: { l10ns = {}, questions = [], gid },
  questionGroup = {},
  language,
  update,
  provided,
  duplicateGroup,
  deleteGroup,
  groupIndex,
  onTitleClick = () => {},
}) => {
  const { setFocused, focused } = useFocused()
  const handleQuestionsUpdate = (questions) => {
    update({ ...questionGroup, questions })
  }

  const handleDuplicate = () => {
    duplicateGroup()
  }

  const handleDelete = () => {
    deleteGroup()
  }

  const languageTitle = l10ns[language]?.groupName
  const englishLanguageTitle = l10ns[LANGUAGE_CODES.EN]?.groupName

  const title = languageTitle
    ? languageTitle
    : englishLanguageTitle
      ? englishLanguageTitle
      : ''

  const isQuestionGroupFocused = focused?.gid === gid
  const shouldHighlightQuestionGroup =
    focused?.gid === gid && focused?.qid === undefined

  return (
    <SideBarRow
      onTitleClick={() => {
        setFocused(questionGroup, groupIndex, undefined, false)
        onTitleClick()
      }}
      testId={`survey-structure-question-group`}
      title={title}
      titlePlaceholder={t('What is your question group about?')}
      provided={provided}
      icon={<ClipBoardIcon />}
      isQuestionGroup={true}
      meatballButton={
        <MeatballMenu
          deleteText={t('Delete group')}
          duplicateText={t('Duplicate group')}
          handleDelete={handleDelete}
          handleDuplicate={handleDuplicate}
        />
      }
      isOpen={isQuestionGroupFocused}
      isFocused={shouldHighlightQuestionGroup}
    >
      <RowQuestionsList
        questions={questions}
        language={language}
        handleUpdate={handleQuestionsUpdate}
        groupIndex={groupIndex}
      />
    </SideBarRow>
  )
}
