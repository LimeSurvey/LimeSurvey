import React from 'react'
import classNames from 'classnames'
import { Button } from 'react-bootstrap'

import { useFocused, useSurvey } from 'hooks'
import { SideBarHeader } from 'components/SideBar'
import { CloseIcon } from 'components/icons'
import { QuestionTypeInfo } from 'components/QuestionTypes'
import { ToggleButtons } from 'components/UIComponents'

import { LogicSettings } from './LogicSettings/LogicSettings'
import { InputSettings } from './InputSettings/InputSettings'
import { OtherSettings } from './OtherSettings/OtherSettings'
import { LayoutSettings } from './LayoutSettings/LayoutSettings'
import { SliderSettings } from './SliderSettings/SliderSettings'
import { DisplaySettings } from './DisplaySettings/DisplaySettings'
import { GeneralSettings } from './GeneralSettings/GeneralSettings'
import { StatisticsSettings } from './StatisticsSettings/StatisticsSettings'
import { FileMetaDataSettings } from './FileMetaDataSettings/FileMetaDataSettings'

export const QuestionSettings = ({ surveyId }) => {
  const { survey, update } = useSurvey(surveyId)

  const {
    focused = {},
    unFocus,
    setFocused,
    groupIndex,
    questionIndex,
  } = useFocused()

  const handleOnQuestionCodeClick = () => {
    setFocused(focused, groupIndex, questionIndex)
  }

  const handleUpdate = (question) => {
    const updatedQuestionGroups = [...survey.questionGroups]
    updatedQuestionGroups[groupIndex].questions[questionIndex] = question

    update({
      questionGroups: updatedQuestionGroups,
    })

    setFocused(question, groupIndex, questionIndex)
  }

  const updateAttribute = (value, isAttribute = true) => {
    const updatedQuestion = {
      ...survey.questionGroups[groupIndex].questions[questionIndex],
    }

    if (isAttribute) {
      updatedQuestion.attributes = {
        ...updatedQuestion.attributes,
        ...value,
      }
      handleUpdate(updatedQuestion)
    } else {
      handleUpdate({ ...updatedQuestion, ...value })
    }
  }

  if (!focused.qid) {
    return <></>
  }

  return (
    <div className={classNames('survey-settings')}>
      <SideBarHeader className="right-side-bar-header primary">
        <div
          onClick={handleOnQuestionCodeClick}
          className="focused-question-code"
        >
          {focused.qid && 'Question settings'}
          {!focused.qid && focused.gid && 'Group settings'}
        </div>
        <Button variant="link" style={{ padding: 0 }} onClick={unFocus}>
          <CloseIcon className="text-black fill-current" />
        </Button>
      </SideBarHeader>

      <div className="mb-3">
        <ToggleButtons
          toggleOptions={[
            { name: 'Simple', value: false },
            { name: 'Advanced', value: true },
          ]}
          value={survey.isAdvanced}
          onChange={(value) => update({ isAdvanced: value })}
          isSecondary
        />
      </div>

      {focused.qid && (
        <React.Fragment>
          <GeneralSettings
            question={focused}
            handleUpdate={updateAttribute}
            questionGroups={survey.questionGroups}
            language={survey.language}
            groupIndex={groupIndex}
            questionIndex={questionIndex}
            surveyUpdate={update}
            surveyId={surveyId}
            isAdvanced={survey.isAdvanced}
          />
          <LayoutSettings
            question={focused}
            handleUpdate={updateAttribute}
            isAdvanced={survey.isAdvanced}
          />
          <DisplaySettings
            question={focused}
            handleUpdate={updateAttribute}
            isAdvanced={survey.isAdvanced}
          />
          <OtherSettings
            question={focused}
            handleUpdate={updateAttribute}
            isAdvanced={survey.isAdvanced}
          />
          {focused.type === QuestionTypeInfo.DATE_TIME && (
            <InputSettings
              question={focused}
              handleUpdate={updateAttribute}
              isAdvanced={survey.isAdvanced}
            />
          )}
          <LogicSettings
            question={focused}
            handleUpdate={updateAttribute}
            isAdvanced={survey.isAdvanced}
          />
          <StatisticsSettings
            question={focused}
            handleUpdate={updateAttribute}
            isAdvanced={survey.isAdvanced}
          />
          {focused.questionThemeName ===
            QuestionTypeInfo.MULTIPLE_NUMERICAL_INPUTS.theme && (
            <SliderSettings
              question={focused}
              handleUpdate={updateAttribute}
              isAdvanced={survey.isAdvanced}
            />
          )}
          {focused.type === QuestionTypeInfo.FILE_UPLOAD.type && (
            <FileMetaDataSettings
              question={focused}
              handleUpdate={updateAttribute}
              isAdvanced={survey.isAdvanced}
            />
          )}
        </React.Fragment>
      )}
    </div>
  )
}
