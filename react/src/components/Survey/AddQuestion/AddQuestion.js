import { useState } from 'react'
import { Button, Popover, OverlayTrigger } from 'react-bootstrap'

import { QuestionTypeSelector } from 'components/QuestionTypeSelector'
import { QuestionTypeInfo } from '../../QuestionTypes'
import { RandomNumber, LANGUAGE_CODES } from 'helpers'
import { useAppState } from 'hooks'
import { TooltipContainer } from 'components/TooltipContainer/TooltipContainer'
import { AddIcon, CloseIcon } from 'components/icons'

export const AddQuestion = ({
  questionGroup,
  placement = 'top',
  onToggle = () => {},
  handleAddQuestion = () => {},
  handleAddQuestionGroup = () => {},
  toggleDarkOnOpen = true,
  surveyId,
}) => {
  const [isAddingQuestionOrGroup, setIsAddingQuestionOrGroup] = useState(false)
  const [isSurveyActive] = useAppState('isSurveyActive', false)

  const addNewQuestion = (questionTypeInfo) => {
    if (
      (!questionGroup &&
        questionTypeInfo.type !== QuestionTypeInfo.QUESTION_GROUP.type) ||
      !questionTypeInfo.type
    ) {
      return
    }

    if (questionTypeInfo.type === QuestionTypeInfo.QUESTION_GROUP.type) {
      addQuestionGroup()
      return
    }

    const questionId = RandomNumber()

    const newQuestion = {
      title: `A${RandomNumber()}`,
      qid: questionId,
      gid: questionGroup.gid,
      sid: questionGroup.sid,
      type: questionTypeInfo.type,
      questionThemeName: questionTypeInfo.theme,
      tempFocusTitle: true,
      l10ns: {
        ...Object.values(LANGUAGE_CODES).reduce((l10ns, language) => {
          return {
            ...l10ns,
            [language]: {
              id: 1,
              qid: questionId,
              question: '',
              script: null,
              language: language,
            },
          }
        }, {}),
      },
      answers: [],
      attributes: [],
    }

    if (
      newQuestion.type === QuestionTypeInfo.LIST_RADIO.type ||
      newQuestion.type === QuestionTypeInfo.LIST_RADIO_WITH_COMMENT.type
    ) {
      newQuestion.answers = [
        {
          aid: RandomNumber(),
          qid: questionId,
          code: 'A1',
          assessmentValue: 'Column 1',
          sortorder: 1,
          scaleId: 0,
        },
        {
          aid: RandomNumber(),
          qid: questionId,
          code: 'A2',
          assessmentValue: 'Option 2',
          sortorder: 2,
          scaleId: 1,
        },
      ]
    } else if (newQuestion.type === QuestionTypeInfo.FIVE_POINT_CHOICE.type) {
      newQuestion.answers = {
        aid: RandomNumber(),
        qid: questionId,
        code: 'A1',
        scaleId: 0,
        assessmentValue: '',
      }
    } else if (
      [
        QuestionTypeInfo.ARRAY.type,
        QuestionTypeInfo.ARRAY_NUMBERS.type,
        QuestionTypeInfo.ARRAY_TEXT.type,
        QuestionTypeInfo.ARRAY_COLUMN.type,
      ].includes(newQuestion.type)
    ) {
      newQuestion.answers = [
        {
          aid: RandomNumber(),
          qid: questionId,
          code: 'A1',
          scaleId: 0,
          sortorder: 1,
          assessmentValue: 'Column 1',
        },
      ]

      let subQuestionId1 = RandomNumber()
      let subQuestionId2 = RandomNumber()

      newQuestion.subquestions = [
        {
          sortorder: 1,
          parentQid: questionId,
          qid: subQuestionId1,
          l10ns: {
            ...Object.values(LANGUAGE_CODES).reduce((l10ns, language) => {
              return {
                ...l10ns,
                [language]: {
                  id: RandomNumber(),
                  qid: subQuestionId1,
                  question: '',
                  script: null,
                  language: language,
                  help: '',
                },
              }
            }, {}),
          },
          gid: newQuestion.gid,
          sid: newQuestion.sid,
          type: newQuestion.type,
        },
        {
          sortorder: 2,
          parentQid: questionId,
          qid: subQuestionId2,
          l10ns: {
            ...Object.values(LANGUAGE_CODES).reduce((l10ns, language) => {
              return {
                ...l10ns,
                [language]: {
                  id: RandomNumber(),
                  qid: subQuestionId2,
                  question: '',
                  script: null,
                  language: language,
                  help: '',
                },
              }
            }, {}),
          },
          gid: newQuestion.gid,
          sid: newQuestion.sid,
          type: newQuestion.type,
        },
      ]
    } else if (QuestionTypeInfo.ARRAY_DUAL_SCALE.type === newQuestion.type) {
      newQuestion.answers = {
        scale1: [
          {
            aid: RandomNumber(),
            qid: questionId,
            code: 'A1',
            scaleId: 0,
            sortorder: 1,
            assessmentValue: 'Column 1',
          },
        ],
        scale2: [
          {
            aid: RandomNumber(),
            qid: questionId,
            code: 'A1',
            scaleId: 1,
            sortorder: 1,
            assessmentValue: 'Column 1',
          },
        ],
      }

      let subQuestionId1 = RandomNumber()
      let subQuestionId2 = RandomNumber()

      newQuestion.subquestions = [
        {
          sortorder: 1,
          parentQid: questionId,
          qid: subQuestionId1,
          l10ns: {
            ...Object.values(LANGUAGE_CODES).reduce((l10ns, language) => {
              return {
                ...l10ns,
                [language]: {
                  id: RandomNumber(),
                  qid: subQuestionId1,
                  question: '',
                  script: null,
                  language: language,
                  help: '',
                },
              }
            }, {}),
          },
          gid: newQuestion.gid,
          sid: newQuestion.sid,
          type: newQuestion.type,
        },
        {
          sortorder: 2,
          parentQid: questionId,
          qid: subQuestionId2,
          l10ns: {
            ...Object.values(LANGUAGE_CODES).reduce((l10ns, language) => {
              return {
                ...l10ns,
                [language]: {
                  id: RandomNumber(),
                  qid: subQuestionId2,
                  question: '',
                  script: null,
                  language: language,
                  help: '',
                },
              }
            }, {}),
          },
          gid: newQuestion.gid,
          sid: newQuestion.sid,
          type: newQuestion.type,
        },
      ]
    } else if (newQuestion.type === QuestionTypeInfo.RATING.type) {
      newQuestion.answers = [
        { value: -1, ratingType: 'star', displayCounter: 5 },
      ]
    } else if (QuestionTypeInfo.RANKING_ADVANCED.type === newQuestion.type) {
      newQuestion.firstAnswers = []
      newQuestion.secondAnswers = []
    }

    handleAddQuestion(newQuestion)
    setIsAddingQuestionOrGroup(false)
  }

  const addQuestionGroup = () => {
    const groupId = RandomNumber()
    const newQuestionGroup = {
      gid: groupId,
      sid: surveyId,
      type: QuestionTypeInfo.QUESTION_GROUP.type,
      theme: QuestionTypeInfo.QUESTION_GROUP.theme,
      tempFocusTitle: true,
      l10ns: {
        ...Object.values(LANGUAGE_CODES).reduce((l10ns, language) => {
          return {
            ...l10ns,
            [language]: {
              id: 1,
              groupName: '',
              description: '',
              language: language,
              gid: groupId,
            },
          }
        }, {}),
      },
      questions: [],
    }

    handleAddQuestionGroup(newQuestionGroup)
    setIsAddingQuestionOrGroup(false)
  }

  const questionTypeSelectorPopover = (
    <Popover
      className="question-type-selector-container"
      style={{
        width: 280,
        borderRadius: '2px',
        border: ' 1px solid #1e1e1e',
        boxShadow: 'none',
      }}
    >
      <Popover.Body>
        <QuestionTypeSelector
          disableAddingQuestions={!questionGroup}
          callBack={addNewQuestion}
        />
      </Popover.Body>
    </Popover>
  )

  return (
    <OverlayTrigger
      trigger="click"
      placement={placement}
      overlay={questionTypeSelectorPopover}
      show={isAddingQuestionOrGroup}
      onToggle={(show) => {
        setIsAddingQuestionOrGroup(show)
        onToggle(show)
      }}
      rootClose
    >
      {isSurveyActive ? (
        <TooltipContainer
          placement="bottom"
          tip={isSurveyActive && 'Disabled while survey is published.'}
        >
          <Button
            disabled={true}
            variant={'primary'}
            style={{ color: 'white' }}
            className="m-1 add-question-button"
          >
            <AddIcon />
          </Button>
        </TooltipContainer>
      ) : (
        <Button
          variant={
            isAddingQuestionOrGroup && toggleDarkOnOpen ? 'dark' : 'primary'
          }
          style={{ color: 'white' }}
          className="m-1 add-question-button"
        >
          {isAddingQuestionOrGroup ? (
            <CloseIcon className="text-white fill-current" />
          ) : (
            <AddIcon className="text-white fill-current" />
          )}
        </Button>
      )}
    </OverlayTrigger>
  )
}
