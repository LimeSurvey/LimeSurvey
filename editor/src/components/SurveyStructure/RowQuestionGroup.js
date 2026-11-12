import { useState } from 'react'
import { useParams } from 'react-router-dom'

import { SideBarRow } from 'components/SideBar/SideBarRow'
import { RowQuestionsList } from './RowQuestionsList'
import { ClipBoardIcon } from 'components/icons'
import { getQuestionGroupMenuItems, LANGUAGE_CODES, STATES } from 'helpers'
import { useAppState, useFocused } from 'hooks'

import { SurveyLogicModal } from './SurveyLogicModal'

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
  isSurveyActive,
}) => {
  const { setFocused, focused } = useFocused()
  const { surveyId } = useParams()
  const [showLogicModal, setShowLogicModal] = useState(false)
  const [hasSurveyUpdatePermission] = useAppState(
    STATES.HAS_SURVEY_UPDATE_PERMISSION
  )

  const handleQuestionsUpdate = (questions) => {
    update({ ...questionGroup, questions })
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
    <>
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
        menuId="group-meatball-menu"
        menuToggleId="meatball-menu-button"
        menuItems={[
          ...getQuestionGroupMenuItems({
            onDuplicate: () => duplicateGroup(),
            onDelete: () => deleteGroup(),
            isSurveyActive,
            hasSurveyUpdatePermission,
          }),
          {
            type: 'item',
            label: t('Check logic'),
            onClick: () => setShowLogicModal(true),
            testId: 'show-logic-button',
          },
        ]}
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
      <SurveyLogicModal
        show={showLogicModal}
        onHide={() => setShowLogicModal(false)}
        sid={surveyId}
        gid={gid}
        language={language}
      />
    </>
  )
}
