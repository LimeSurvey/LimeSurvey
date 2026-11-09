import React, { useEffect } from 'react'
import { useParams } from 'react-router-dom'
import { format } from 'util'
import { queryClient } from 'queryClient'

import {
  useAppState,
  useBuffer,
  useErrors,
  useFocused,
  useInFlightConditionQids,
  useSurvey,
} from 'hooks'
import {
  Entities,
  EntitiesType,
  Operations,
  OperationsBuffer,
  STATES,
  errorToast,
  applyQuestionRelevance,
  clearResolvedTempIds,
  getConditionLogicQids,
  getRefreshedFocus,
  processValidationErrorsAndUpdateOperations,
  rememberResolvedTempIds,
  removeConditionsWithUnknownTempIdsFromSurveyAndBuffer,
  replaceTempIdsInSurveyAndBuffer,
  dayJsHelper,
  RandomNumber,
} from 'helpers'
import { removeConditionOpsOfFailedCreates } from 'helpers/Buffer/operationsHandlers'

/**
 * Render children while loading the route's survey and saving buffered edits.
 * Reconcile patch results with local survey state, surface errors, and invalidate
 * response queries after receiving a patch result.
 */
export const SurveyLogicProvider = ({ children }) => {
  const { surveyId } = useParams()
  const { setFocused, groupIndex, questionIndex } = useFocused()

  const { surveyPatch, fetchSurvey, update, refetchQuestionsFieldNamesMap } =
    useSurvey(surveyId)
  const { operationsBuffer, addToBuffer, setBuffer, clearBuffer } = useBuffer()
  const { setErrorsFromPatchResponse, clearErrors } = useErrors()

  const [surveyRefreshRequired, setSurveyRefreshRequired] = useAppState(
    STATES.SURVEY_REFRESH_REQUIRED
  )
  const [isPatchSurveyRunning, setIsPatchSurveyRunning] = useAppState(
    STATES.IS_PATCH_SURVEY_RUNNING,
    false
  )
  const [, setSaveState] = useAppState(STATES.SAVE_STATE, '')
  const [, setHelpersSettings] = useAppState(STATES.HELPER_SETTINGS, {})
  const [, setIsAddingQuestionOrGroup] = useAppState(
    STATES.IS_ADDING_QUESTION_OR_GROUP,
    false
  )
  const [, setInFlightConditionQids] = useInFlightConditionQids()

  // Refresh the focused question's data from the survey. Only the data
  // changes, so no navigation: It would add browser history entries.
  // Read the focus now, it may have changed while a request was running.
  const refreshFocusedQuestion = (survey) => {
    const focusData = getRefreshedFocus(
      queryClient.getQueryData([STATES.FOCUSED_ENTITY]),
      survey
    )
    if (focusData) queryClient.setQueryData([STATES.FOCUSED_ENTITY], focusData)
  }

  /**
   * Clean the condition operations that could never be sent, before each save
   * (only when no save is running, so no create can be on its way):
   * - the conditions of, or using as source, an unsaved question, subquestion
   *   or group that was deleted: Its temp ID will never be replaced. They are
   *   removed from the survey (shown in the editor) and from the buffer, and
   *   this is their only cleanup;
   * - the operations still on a temp cid without a buffered create (see
   *   removeConditionOpsOfFailedCreates): For example the delete of a
   *   condition whose create was refused, which would otherwise block every
   *   save.
   * @returns {boolean} whether the buffer changed
   */
  const cleanConditionOperations = () => {
    // Read only: The buffer's own copy, getOperations() would copy it again.
    const operations = operationsBuffer.operations
    const result = removeConditionsWithUnknownTempIdsFromSurveyAndBuffer(
      queryClient.getQueryData([STATES.SURVEY])?.survey,
      operations
    )

    if (result.changedQids.length) {
      update({ ...result.survey })
      refreshFocusedQuestion(result.survey)
    }

    const cleanedOperations = removeConditionOpsOfFailedCreates(
      result.operations
    )
    if (cleanedOperations === operations) return false

    setBuffer(cleanedOperations, RandomNumber())
    return true
  }

  // Mount / ID Change Logic
  useEffect(() => {
    clearBuffer()
    clearErrors()
    setHelpersSettings({})
    setIsPatchSurveyRunning(false)
    setIsAddingQuestionOrGroup(false)
    setInFlightConditionQids([])
    clearResolvedTempIds()

    if (surveyId) {
      fetchSurvey(surveyId)
      refetchQuestionsFieldNamesMap()
    }
  }, [surveyId])

  // Window Unload Safety
  useEffect(() => {
    const handleBeforeUnload = (event) => {
      if (operationsBuffer.getOperations()?.length > 0) {
        event.preventDefault()
      }
    }
    window.addEventListener('beforeunload', handleBeforeUnload)
    return () => {
      window.removeEventListener('beforeunload', handleBeforeUnload)
    }
  }, [operationsBuffer])

  // Auto-Save / Patch Loop
  useEffect(() => {
    if (process.env.REACT_APP_DEMO_MODE === 'true' || isPatchSurveyRunning) {
      return
    }

    // The buffer changed: This effect runs again with the cleaned buffer.
    if (cleanConditionOperations()) return

    const readyOperations = operationsBuffer.getOperations({ ready: true })
    // surveyRefreshRequired is only updated on the next render, too late for finallyCallback
    let refreshRequired = false

    const beforeCallback = () => {
      // Clear ready buffer items
      // - items that are not ready may be waiting for tempId to be resolved
      operationsBuffer.clearBuffer({ ready: true })
      clearBuffer({ ready: true })
      setInFlightConditionQids(getConditionLogicQids(readyOperations))
      setIsPatchSurveyRunning(true)
    }

    // currentBuffer
    /**
     * Apply a patch result's ID mappings and errors to the survey and buffer,
     * update save status, and invalidate responses even if no operations applied.
     */
    const thenCallback = (result) => {
      const survey = queryClient.getQueryData([STATES.SURVEY]).survey
      const focused = queryClient.getQueryData([STATES.FOCUSED_ENTITY]).focused
      const currentBuffer = new OperationsBuffer(
        queryClient.getQueryData([STATES.BUFFER]),
        RandomNumber()
      )

      const questionsTempIdMapping = result.tempIdMapping?.questionsMap
      const subquestionsTempIdMapping = result.tempIdMapping?.subquestionsMap
      const groupsTempIdMapping = result.tempIdMapping?.questionGroupsMap
      const answersTempIdMapping = result.tempIdMapping?.answersMap
      const conditionsTempIdMapping = result.tempIdMapping?.conditionsMap

      ;[
        groupsTempIdMapping,
        questionsTempIdMapping,
        subquestionsTempIdMapping,
        answersTempIdMapping,
        conditionsTempIdMapping,
      ].forEach(rememberResolvedTempIds)

      let mapResult = replaceTempIdsInSurveyAndBuffer(
        groupsTempIdMapping,
        EntitiesType.group,
        survey,
        currentBuffer,
        focused
      )

      if (mapResult.focused && groupsTempIdMapping) {
        setFocused(mapResult.focused, groupIndex, questionIndex)
      }

      mapResult = replaceTempIdsInSurveyAndBuffer(
        questionsTempIdMapping,
        EntitiesType.question,
        mapResult.survey,
        mapResult.operationsBuffer,
        focused
      )

      if (mapResult.focused && questionsTempIdMapping) {
        setFocused(mapResult.focused, groupIndex, questionIndex)
      }

      mapResult = replaceTempIdsInSurveyAndBuffer(
        subquestionsTempIdMapping,
        EntitiesType.subquestion,
        mapResult.survey,
        mapResult.operationsBuffer
      )

      mapResult = replaceTempIdsInSurveyAndBuffer(
        answersTempIdMapping,
        EntitiesType.answer,
        mapResult.survey,
        mapResult.operationsBuffer
      )

      mapResult = replaceTempIdsInSurveyAndBuffer(
        conditionsTempIdMapping,
        EntitiesType.condition,
        mapResult.survey,
        mapResult.operationsBuffer
      )

      mapResult = applyQuestionRelevance(
        mapResult,
        result.extras?.questionRelevance
      )

      // The operations that were sent to the API.
      const readyOperationsBuffer = new OperationsBuffer(readyOperations)

      let validationErrors = processValidationErrorsAndUpdateOperations(
        result.validationErrors || [],
        readyOperationsBuffer,
        mapResult.operationsBuffer
      )

      let exceptionErrors = processValidationErrorsAndUpdateOperations(
        result.exceptionErrors || [],
        readyOperationsBuffer,
        mapResult.operationsBuffer
      )

      // A refused delete already removed the item locally: Drop the operation
      // and reload the survey to show the item again.
      const refusedDeletes = exceptionErrors.filter(
        (error) => error.op === Operations.delete
      )
      refusedDeletes.forEach((error) => {
        mapResult.operationsBuffer.removeOperation(
          error.id,
          error.op,
          error.entity
        )
      })
      // A refused condition change is not sent again: The editor shows it,
      // reload the survey to show the conditions the server has.
      const hasRefusedConditionChange = [
        ...validationErrors,
        ...exceptionErrors,
      ].some((error) => error.entity === Entities.questionCondition)
      if (refusedDeletes.length || hasRefusedConditionChange) {
        refreshRequired = true
        setSurveyRefreshRequired(true)
      }

      // A refused survey status change (e.g. activation blocked by a plugin) is
      // not retried automatically: Drop it, so the user can trigger it again.
      exceptionErrors
        .filter((error) => error.entity === Entities.surveyStatus)
        .forEach((error) => {
          mapResult.operationsBuffer.removeOperation(
            error.id,
            error.op,
            error.entity
          )
        })

      update({ ...mapResult.survey })
      refetchQuestionsFieldNamesMap()
      // A condition operation still on a temp cid without a buffered create
      // now belongs to a condition creation that failed or was refused.
      mapResult.operationsBuffer.setOperations(
        removeConditionOpsOfFailedCreates(
          mapResult.operationsBuffer.getOperations()
        )
      )

      setBuffer(mapResult.operationsBuffer.getOperations(), RandomNumber())

      // Its URL was already updated with any new ID above.
      refreshFocusedQuestion(mapResult.survey)

      setSaveState(
        format(
          t('Saved at %s'),
          dayJsHelper(new Date().getTime()).format('hh:mm')
        )
      )
      setErrorsFromPatchResponse([...validationErrors, ...exceptionErrors])

      // Refresh responses after survey metadata changes.
      queryClient.invalidateQueries({
        queryKey: [STATES.SURVEY_RESPONSES, surveyId],
      })

      // Specific errors are already shown as toasts, see SurveyWorkspace
      const hasErrorMessages =
        result.validationErrors?.length || result.exceptionErrors?.length
      if (!result.operationsApplied && !hasErrorMessages) {
        errorToast(
          t(
            'Sorry, we encountered an issue while saving the changes. Please try refreshing the page!'
          )
        )
      }
    }

    const catchCallback = (error) => {
      const updatedOperation = readyOperations.map((operation) => ({
        ...operation,
        error: true,
      }))

      updatedOperation.forEach((operation) => {
        addToBuffer(operation, false)
      })

      // No condition create got its real ID: Drop the operations waiting for
      // one, except those of the creates put back.
      setBuffer(
        removeConditionOpsOfFailedCreates(
          queryClient.getQueryData([STATES.BUFFER])
        ),
        RandomNumber()
      )

      setErrorsFromPatchResponse([...updatedOperation])
      setSaveState(
        `Error happened ${dayJsHelper(new Date().getTime()).format('hh:mm')}`
      )

      errorToast(error.message)
    }

    const finallyCallback = () => {
      setIsPatchSurveyRunning(false)
      setInFlightConditionQids([])

      // maybe there's some delayed operations.
      const pendingOperations = new OperationsBuffer(
        queryClient.getQueryData([STATES.BUFFER])
      )
      if (
        pendingOperations.isEmpty() &&
        (surveyRefreshRequired || refreshRequired)
      ) {
        fetchSurvey(surveyId)
        setSurveyRefreshRequired(false)
      }
    }

    surveyPatch(
      readyOperations,
      beforeCallback,
      thenCallback,
      finallyCallback,
      catchCallback
    )
  }, [operationsBuffer.bufferHash, isPatchSurveyRunning])

  return <>{children}</>
}
