import { useEffect, useState } from 'react'
import dayjs from 'dayjs'

import { Form, Button, Popover, OverlayTrigger } from 'react-bootstrap'
import { useFocused, useSurvey } from 'hooks'
import { SettingsForm } from 'pages/Editor/SettingsForm/SettingsForm'
import { AddQuestion } from 'components/Survey/AddQuestion'
import { PublishSettings } from 'components/PublishSettings/PublishSettings'
import { MenuIcon, CheckIcon } from 'components/icons'

export const TopBar = ({ surveyId }) => {
  const { survey, update } = useSurvey(surveyId)
  const { focused, setFocused, groupIndex } = useFocused()
  const [focusedQuestionGroup, setFocusedQuestionGroup] = useState({})

  useEffect(() => {
    setFocusedQuestionGroup(null)
  }, [survey.sid])

  const settingsFormPopover = (
    <Popover>
      <Popover.Header as="h3">Settings</Popover.Header>
      <Popover.Body>
        <SettingsForm />
      </Popover.Body>
    </Popover>
  )

  const handleAddQuestionGroup = (newQuestionGroup, index) => {
    if (!survey.questionGroups) {
      update({ questionGroups: [newQuestionGroup] })
      setFocused(newQuestionGroup, 0)
      return
    }

    const newQuestionGroupIndex = index
      ? index + 1
      : survey.questionGroups.length

    const updatedQuestionGroups = [
      ...survey.questionGroups.slice(0, newQuestionGroupIndex),
      newQuestionGroup,
      ...survey.questionGroups.slice(newQuestionGroupIndex),
    ].map((questionGroup, index) => {
      questionGroup.groupOrder = index + 1
      return questionGroup
    })

    update({ questionGroups: updatedQuestionGroups })
    setFocused(newQuestionGroup, newQuestionGroupIndex)
  }

  const handleAddQuestion = (question) => {
    const _groupIndex =
      groupIndex !== undefined ? groupIndex : survey.questionGroups.length - 1

    const updatedQuestionGroups = [...survey.questionGroups]
    updatedQuestionGroups[_groupIndex] = {
      ...updatedQuestionGroups[_groupIndex],
      questions: [...updatedQuestionGroups[_groupIndex].questions, question],
    }

    update({ questionGroups: updatedQuestionGroups })

    setFocused(
      question,
      _groupIndex,
      updatedQuestionGroups[_groupIndex].questions.length - 1
    )
  }

  const onToggleAddQuestionOverlay = () => {
    if (!survey.questionGroups) {
      return
    }

    const questionGroup =
      survey.questionGroups.find(
        (questionGroup) => questionGroup.gid === focused?.gid
      ) || survey?.questionGroups[survey?.questionGroups?.length - 1]

    setFocusedQuestionGroup(questionGroup)
  }

  // Todo: Wait for survey loading in the editor page.
  if (!survey.sid) {
    return <>Loading survey...</>
  }

  return (
    <div className="top-bar d-flex py-1 mx-auto">
      <div className="top-bar-left d-flex ps-2">
        <div className="logo"></div>
        <span className="d-flex ms-auto">
          <AddQuestion
            setFocusGroup={() => {}}
            handleAddQuestion={handleAddQuestion}
            placement={'bottom'}
            onToggle={onToggleAddQuestionOverlay}
            handleAddQuestionGroup={handleAddQuestionGroup}
            questionGroup={focusedQuestionGroup?.gid && focusedQuestionGroup}
            surveyId={survey.sid}
          />
        </span>
      </div>

      <div className="top-bar-middle d-md-flex d-none align-items-center ">
        <Form.Select
          aria-label="Default select example"
          className="text-align-center top-bar-select"
        >
          <option value="1">My Survey One</option>
          <option value="2">My Survey Two</option>
          <option value="3">My Survey Three</option>
        </Form.Select>
      </div>

      <div className="top-bar-right d-xl-flex d-none align-items-center ms-auto pe-4">
        <div className="d-flex align-items-center me-2">
          <p
            className={`m-0 me-2 text-secondary auto-saved ${
              survey.isSaved ? 'text-success' : 'disabled text-secondary '
            }`}
          >
            Saved at{' '}
            {dayjs(
              survey.savedAt ? survey.savedAt : new Date().getTime()
            ).format('hh:mm')}
          </p>
          <p
            onClick={() => {
              update({ isSaved: true, savedAt: new Date().getTime() })
            }}
            className={`m-0 me-2 auto-saved cursor-pointer ${
              survey.isSaved ? 'disabled text-secondary' : 'text-success '
            }`}
          >
            Save now
          </p>
          <CheckIcon
            className={`fill-current ${
              survey.isSaved ? 'text-success' : 'disabled text-secondary'
            }`}
          />
        </div>

        <PublishSettings
          survey={survey}
          isActivated={survey?.isActivated}
          update={update}
        />
        <OverlayTrigger
          trigger="click"
          placement="left"
          overlay={settingsFormPopover}
          rootClose
        >
          <Button variant="link" className="">
            <MenuIcon />
          </Button>
        </OverlayTrigger>
      </div>
    </div>
  )
}
