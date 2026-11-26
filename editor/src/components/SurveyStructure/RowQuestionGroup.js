import { memo, useState } from 'react'

import { SideBarRow } from 'components/SideBar/SideBarRow'
import { RowQuestionsList } from './RowQuestionsList'
import { ClipBoardIcon } from 'components/icons'
import { getQuestionGroupMenuItems, LANGUAGE_CODES, STATES } from 'helpers'
import { useAppState } from 'hooks'

import { SurveyLogicModal } from './SurveyLogicModal'

const noop = () => {}

/**
 * Structure row of a question group with its question list. Memoized, so it
 * only renders again when its own props change: Not on every pointer move of
 * a drag, and not for changes to other groups, the focus or the URL. That
 * needs props that keep their identity, so the handlers get the group and its
 * index as arguments instead of being created for each row.
 */
export const RowQuestionGroup = memo(function RowQuestionGroup({
  questionGroup: { l10ns = {}, questions = [], gid },
  questionGroup = {},
  language,
  surveyId,
  dragHandleProps,
  groupIndex,
  /* The focus is on the group or one of its questions */
  isOpen = false,
  /* The focus is on the group itself */
  isFocused = false,
  isSurveyActive,
  /* (updatedGroup, groupIndex) */
  update = noop,
  /* (questionGroup, groupIndex) */
  duplicateGroup = noop,
  /* (questionGroup, groupIndex) */
  deleteGroup = noop,
  /* (questionGroup, groupIndex) */
  onTitleClick = noop,
}) {
  const [showLogicModal, setShowLogicModal] = useState(false)
  const [hasSurveyUpdatePermission] = useAppState(
    STATES.HAS_SURVEY_UPDATE_PERMISSION
  )

  const handleQuestionsUpdate = (questions) => {
    update({ ...questionGroup, questions }, groupIndex)
  }

  const languageTitle = l10ns[language]?.groupName
  const englishLanguageTitle = l10ns[LANGUAGE_CODES.EN]?.groupName

  const title = languageTitle
    ? languageTitle
    : englishLanguageTitle
      ? englishLanguageTitle
      : ''

  return (
    <>
      <SideBarRow
        onTitleClick={() => onTitleClick(questionGroup, groupIndex)}
        testId={`survey-structure-question-group`}
        title={title}
        titlePlaceholder={t('What is your question group about?')}
        provided={{ dragHandleProps }}
        icon={<ClipBoardIcon />}
        isQuestionGroup={true}
        menuId="group-meatball-menu"
        menuToggleId="meatball-menu-button"
        menuItems={[
          ...getQuestionGroupMenuItems({
            onDuplicate: () => duplicateGroup(questionGroup, groupIndex),
            onDelete: () => deleteGroup(questionGroup, groupIndex),
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
        isOpen={isOpen}
        isFocused={isFocused}
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
})
