import React, { useState, useMemo, useEffect } from 'react'
import classNames from 'classnames'
import { Button } from 'react-bootstrap'

import {
  STATES,
  getQuestionAttributesTitles,
  createBufferOperation,
  getDisabledQuestionTypes,
  errorToast,
} from 'helpers'
import { useAppState, useBuffer, useFocused, useSurvey } from 'hooks'
import { SideBarHeader } from 'components/SideBar'
import { CloseIcon } from 'components/icons'
import { ToggleButtons } from 'components/UIComponents'

import { Setting } from './Setting'
import { getQuestionSettings } from './getQuestionSettings'
import { getQuestionTypeInfo } from '../QuestionTypes'
import { ScenarioList } from 'components/ConditionDesigner/ScenarioList'
import { ConditionDesigner } from 'components/ConditionDesigner/ConditionDesigner'
import { AddScenario } from 'components/ConditionDesigner/AddScenario'

import { ExpressionScript } from '../ConditionDesigner/ExpressionScript'

import { ExpressionScriptInputBox } from '../ConditionDesigner/ExpressionScript/ExpressionScriptInputBox'

export const QuestionSettings = ({ surveyId }) => {
  const [questionSettingsOptions, setQuestionSettingsOptions] = useAppState(
    STATES.QUESTION_SETTINGS_OPTIONS,
    { isAdvanced: false }
  )
  const { survey, update, language } = useSurvey(surveyId)
  const { addToBuffer } = useBuffer()
  const [conditionDesignerPanels, setConditionDesignerPanels] = useState({
    isConditionPanelOpen: false,
    isExpressionScriptPanelOpen: false,
  })
  const [scenarioToPatch, setScenarioToPatch] = useState(null)

  // holds the scenario ID currently has a new conditions (waiting for temp condition IDs to be replaced)
  const [pendingScenarioName, setPendingScenarioName] = useState(null)

  const {
    focused = {},
    unFocus,
    setFocused,
    groupIndex,
    questionIndex,
  } = useFocused()

  useEffect(() => {
    closeConditionDesignerPanels()
  }, [focused?.qid])

  const questionSettings = useMemo(() => {
    if (!focused?.qid) {
      return []
    }

    return getQuestionSettings()[focused.questionThemeName]
  }, [focused?.questionThemeName])

  const handleOnQuestionCodeClick = () => {
    setFocused(focused, groupIndex, questionIndex)
  }

  const toggleConditionDesignerPanels = (
    scenario = null,
    isConditionPanelOpen = false,
    isExpressionScriptPanelOpen = false
  ) => {
    setScenarioToPatch(scenario)
    setConditionDesignerPanels({
      isConditionPanelOpen,
      isExpressionScriptPanelOpen,
    })
  }

  const closeConditionDesignerPanels = () => {
    toggleConditionDesignerPanels(null, false, false)
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
    const question =
      survey?.questionGroups?.[groupIndex]?.questions?.[questionIndex]

    if (!question) {
      errorToast('Invalid question group or question')
      return
    }

    const updatedQuestion = { ...question }

    if (isAttribute) {
      updatedQuestion.attributes = {
        ...updatedQuestion.attributes,
        ...value,
      }

      handleUpdate(updatedQuestion)
      const operation = createBufferOperation(updatedQuestion.qid)
        .questionAttribute()
        .update({
          ...value,
        })

      addToBuffer(operation)
    } else {
      handleUpdate({ ...updatedQuestion, ...value })

      const operation = createBufferOperation(updatedQuestion.qid)
        .question()
        .update({
          ...value,
        })

      addToBuffer(operation)
    }
  }

  if (!focused.qid) {
    return <></>
  }

  const isQuestionDisabled =
    getDisabledQuestionTypes().includes(focused.questionThemeName) ||
    !Object.values(getQuestionTypeInfo())
      .map((q) => q.theme)
      .includes(focused.questionThemeName)

  if (focused && typeof focused.qid === 'number') {
    if (conditionDesignerPanels.isConditionPanelOpen || scenarioToPatch) {
      return (
        <ConditionDesigner
          key={`condition-designer-${focused.qid}`}
          survey={survey}
          update={update}
          language={language}
          onNavigateBack={closeConditionDesignerPanels}
          scenarioToPatch={scenarioToPatch}
          setPendingScenarioName={setPendingScenarioName}
        />
      )
    }
    if (
      focused.relevance !== null &&
      conditionDesignerPanels.isExpressionScriptPanelOpen
    ) {
      return (
        <ExpressionScript
          key={`expression-script-${focused.qid}`}
          onNavigateBack={closeConditionDesignerPanels}
        />
      )
    }
  }

  return (
    <div
      className={classNames('right-sidebar-settings')}
      data-testid="question-settings"
      id="sidebar-question-settings"
    >
      <SideBarHeader className="right-side-bar-header primary">
        <div
          onClick={handleOnQuestionCodeClick}
          className="focused-question-code"
        >
          {t('Question settings')}
        </div>
        <Button variant="link" style={{ padding: 0 }} onClick={unFocus}>
          <CloseIcon className="text-black fill-current" />
        </Button>
      </SideBarHeader>
      {!isQuestionDisabled && (
        <>
          <div className="mb-3 advanced-toggle">
            <ToggleButtons
              toggleOptions={[
                { name: t('Simple'), value: false },
                { name: t('Advanced'), value: true },
              ]}
              value={!!questionSettingsOptions?.isAdvanced}
              onChange={(value) =>
                setQuestionSettingsOptions({ isAdvanced: value })
              }
              isSecondary
            />
          </div>

          {questionSettings?.map((setting, index) => {
            return (
              <Setting
                key={`${setting.title}-${index}`}
                question={focused}
                isAdvanced={!!questionSettingsOptions?.isAdvanced}
                simpleSettings={
                  getQuestionAttributesTitles().SIMPLE === setting.title
                }
                handleUpdate={updateAttribute}
                title={setting.title}
                attributes={setting.attributes}
                language={language}
              />
            )
          })}
        </>
      )}
      {isQuestionDisabled && (
        <>
          <p style={{ paddingLeft: '18px' }}>
            {t(
              'This question type isn’t supported in the new editor yet, but your responses will still be collected. If you want to make edits you can switch to a similar question type or edit it in the classic editor.'
            )}
          </p>
        </>
      )}
      <div id="condition-designer">
        {!focused ? null : (
          <>
            <AddScenario
              key={`add-scenario-${focused.qid}`}
              onShowPanel={toggleConditionDesignerPanels}
            />

            {focused.scenarios?.length ? (
              <ScenarioList
                key={`scenario-list-${focused.qid}-${focused.scenarios.length}`}
                setScenarioToPatch={setScenarioToPatch}
                pendingScenarioName={pendingScenarioName}
                setPendingScenarioName={setPendingScenarioName}
              />
            ) : null}

            {focused.relevance != null && (
              <ExpressionScriptInputBox
                key={`condition-expression-trigger-${focused.qid}`}
                onShowPanel={toggleConditionDesignerPanels}
                onNavigateBack={closeConditionDesignerPanels}
              />
            )}
          </>
        )}
      </div>
    </div>
  )
}
