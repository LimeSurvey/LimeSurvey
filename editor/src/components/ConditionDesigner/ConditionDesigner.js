import React, { useCallback, useEffect, useMemo, useRef, useState } from 'react'
import classNames from 'classnames'
import { PlusLg } from 'react-bootstrap-icons'

import { Button, Input } from 'components'
import { useBuffer, useFocused, useConditionDesigner } from 'hooks'
import { SideBarHeader } from 'components/SideBar'
import { ArrowLeftIcon } from 'components/icons'
import { getQuestionById, QUESTION_RELEVANCE_DEFAULT_VALUE } from 'helpers'

import { previousQuestionsHandler } from './handlers/previousQuestions/previousQuestionsHandler'
import { ConditionItem } from './ConditionItem'
import {
  buildScenarioConditionsForUpdate,
  getAllowedMethods,
  getConditionTypeInfo,
  getDefaultCondition,
  hasUnsavedChanges,
  showConditionScriptOverwriteConfirmationOverlay,
  showUnsavedChangesOverlay,
  showWarningMessage,
} from './utils'

const conditionTypeInfo = getConditionTypeInfo()

const scenarioActions = {
  CREATE: 'create', // when creating a new scenario
  UPDATE: 'update', // when updating an existing scenario
}

export const ConditionDesigner = ({
  survey,
  update,
  language,
  scenarioToPatch = null,
  setPendingScenarioName,
  onNavigateBack = () => {},
}) => {
  const { focused, groupIndex, questionIndex } = useFocused()
  const { addToBuffer } = useBuffer()

  const question = useMemo(() => {
    return getQuestionById(focused.qid, survey)?.question
  }, [focused.qid, survey])

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
    question.scenarios.length === 0 &&
    question.relevance !== QUESTION_RELEVANCE_DEFAULT_VALUE

  const hasShownWarningRef = useRef(false)

  useEffect(() => {
    if (shouldConfirmOverwrite && !hasShownWarningRef.current) {
      showWarningMessage(
        t(
          'Switching to the visual condition builder will overwrite the condition written in expression script mode.'
        )
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
    survey,
    groupIndex,
    questionIndex,
    update,
    focused,
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

  const isValidScenario = useMemo(() => {
    return conditions.every((condition) => {
      const { method, cqid, targetType, value, sourceType, cfieldname } =
        condition

      const isMethodValid = Object.values(getAllowedMethods()).includes(method)
      const isValueValid =
        targetType === conditionTypeInfo.TARGET.CONSTANT || value !== null
      const isScidValid = scid !== '' && !isNaN(scid)

      const isSourceValid =
        sourceType === conditionTypeInfo.SOURCE.QUESTION
          ? typeof cqid === 'number' && cqid > 0
          : Boolean(cfieldname)

      return isMethodValid && isValueValid && isScidValid && isSourceValid
    })
  }, [conditions, scid])

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
      showConditionScriptOverwriteConfirmationOverlay({
        script: question.relevance,
        onConfirm: save,
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
              focused={focused}
              survey={survey}
              isUpdateAction={isUpdateAction()}
            />
          ))}
        </div>
        <div className="text-center">
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
        <div className="p-2 m-2 text-center">
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
