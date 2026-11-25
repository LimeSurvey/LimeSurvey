import React, { useCallback, useEffect, useMemo, useRef, useState } from 'react'
import classNames from 'classnames'
import { PlusLg } from 'react-bootstrap-icons'

import { Button, Input } from 'components'
import { useBuffer, useFocused, useConditionDesigner } from 'hooks'
import { SideBarHeader } from 'components/SideBar'
import { ArrowLeftIcon } from 'components/icons'
import { QUESTION_RELEVANCE_DEFAULT_VALUE } from 'helpers'

import { previousQuestionsHandler } from './handlers/previousQuestions/previousQuestionsHandler'
import { ConditionItem } from './ConditionItem'
import {
  buildScenarioConditionsForUpdate,
  getConditionState,
  getDefaultCondition,
  hasUnsavedChanges,
  isScenarioDraftValid,
  runIfConditionUnchanged,
  showConditionScriptOverwriteConfirmationOverlay,
  showUnsavedChangesOverlay,
  showWarningMessage,
} from './utils'

const scenarioActions = {
  CREATE: 'create', // when creating a new scenario
  UPDATE: 'update', // when updating an existing scenario
}

export const ConditionDesigner = ({
  question,
  groupIndex,
  questionIndex,
  isFirstQuestion = false,
  survey,
  update,
  language,
  scenarioToPatch = null,
  setPendingScenarioName,
  onNavigateBack = () => {},
}) => {
  const { focused } = useFocused()
  const { addToBuffer } = useBuffer()

  const [scid, setScid] = useState(scenarioToPatch ? scenarioToPatch.scid : 1)

  const previousQuestions = useMemo(() => {
    return previousQuestionsHandler(language, survey, groupIndex, questionIndex)
      .cQuestions
  }, [language, survey.sid, groupIndex, questionIndex])

  const scenarioAction = scenarioToPatch
    ? scenarioActions.UPDATE
    : scenarioActions.CREATE
  const initialConditions = scenarioToPatch
    ? buildScenarioConditionsForUpdate(scenarioToPatch, previousQuestions)
    : [getDefaultCondition()]

  const [conditions, setConditions] = useState(initialConditions)
  const [isApplyButtonDisabled, setIsApplyButtonDisabled] = useState(true)

  const shouldConfirmOverwrite =
    !!question?.relevance &&
    !question.scenarios?.length &&
    question.relevance !== QUESTION_RELEVANCE_DEFAULT_VALUE

  const hasShownWarningRef = useRef(false)

  useEffect(() => {
    if (shouldConfirmOverwrite && !hasShownWarningRef.current) {
      showWarningMessage(
        t(
          'Switching to the visual condition builder will overwrite the condition written in ExpressionScript mode.'
        ),
        'bottom-center'
      )
      hasShownWarningRef.current = true
    }
  }, [question])

  const {
    removeCondition,
    addCondition,
    updateCondition,
    handleSavingScenario,
    handleScenarioNameChange,
  } = useConditionDesigner({
    question,
    scenarioToPatch,
    update,
    setPendingScenarioName,
    onNavigateBack,
    scid,
    setScid,
    setConditions,
    conditions,
    addToBuffer,
  })

  useEffect(() => {
    if (scenarioToPatch && scenarioToPatch.qid !== focused.qid) {
      onNavigateBack()
    }
  }, [focused.qid])

  const isValidScenario = useMemo(
    () => isScenarioDraftValid(conditions, scid),
    [conditions, scid]
  )

  const isUpdateAction = useCallback(() => {
    return scenarioAction === scenarioActions.UPDATE
  }, [scenarioAction])

  const handleBackClick = useCallback(() => {
    if (hasUnsavedChanges(conditions, scenarioToPatch, scid, isUpdateAction)) {
      showUnsavedChangesOverlay(onNavigateBack)
    } else {
      onNavigateBack()
    }
  }, [conditions, scenarioToPatch, scid, isUpdateAction, onNavigateBack])

  const handleApplyClick = () => {
    const save = () => handleSavingScenario()
    if (shouldConfirmOverwrite) {
      // The confirmation only applies to the script it shows
      const shownCondition = getConditionState(question)
      showConditionScriptOverwriteConfirmationOverlay({
        script: question.relevance,
        onConfirm: () =>
          runIfConditionUnchanged(question.qid, shownCondition, save),
        onCancel: onNavigateBack,
      })
    } else {
      save()
    }
  }

  useEffect(() => {
    const shouldDisable =
      !isValidScenario ||
      (isUpdateAction() &&
        !hasUnsavedChanges(conditions, scenarioToPatch, scid, isUpdateAction))

    setIsApplyButtonDisabled(shouldDisable)
  }, [isValidScenario, conditions, scenarioAction])

  if (!question) {
    return null
  }

  return (
    <>
      <div
        className={classNames('survey-settings')}
        data-testid="condition-designer"
      >
        <SideBarHeader className="condition-designer-sidebar right-side-bar-header primary">
          <div className="d-flex gap-2 fw-bold">
            <Button
              className="d-flex align-items-center p-0"
              variant="btn bg-transparent border-0 shadow-none p-0"
              onClick={handleBackClick}
            >
              <ArrowLeftIcon className="text-black" />
            </Button>
            <span className="text-start">{t('Condition designer')}</span>
          </div>
        </SideBarHeader>
      </div>
      <div className="scenario-container">
        <div className="scenario-header">
          <span className="text-muted fw-bold">
            {scid === 1 ? t('Default scenario') : t('Scenario')}
          </span>
          <div className="scenario-input mt-2">
            <Input
              type="number"
              onChange={handleScenarioNameChange}
              placeholder={t('Scenario')}
              value={scid}
              min="1"
            />
          </div>
        </div>
        <div className="conditions-container">
          {conditions.map((condition, index) => (
            <ConditionItem
              key={`condition-item-${condition.cid ?? index}`}
              index={index}
              condition={condition}
              pervCondition={conditions[index - 1]}
              previousQuestions={previousQuestions}
              updateCondition={updateCondition}
              removeCondition={removeCondition}
              isFirstQuestion={isFirstQuestion}
              survey={survey}
              isUpdateAction={isUpdateAction()}
            />
          ))}
        </div>
        <div className="d-flex justify-content-center">
          <Button
            className="mb-3"
            onClick={addCondition}
            variant="outline-primary"
            style={{ border: 'none' }}
            disabled={!isValidScenario}
          >
            <PlusLg /> {t('Add condition')}
          </Button>
        </div>
      </div>
      {conditions.length > 0 && (
        <div className="p-2 m-2 d-flex justify-content-center">
          <Button
            onClick={handleApplyClick}
            className={`mt-3 condition-apply-button`}
            variant={isApplyButtonDisabled ? 'secondary' : 'primary'}
            disabled={isApplyButtonDisabled}
          >
            {t('Apply')}
          </Button>
        </div>
      )}
    </>
  )
}
