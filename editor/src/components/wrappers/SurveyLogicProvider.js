import React, { useEffect } from 'react'
import { useParams } from 'react-router-dom'
import { format } from 'util'
import { queryClient } from 'queryClient'

import { useAppState, useBuffer, useErrors, useFocused, useSurvey } from 'hooks'
import {
  Entities,
  EntitiesType,
  Operations,
  OperationsBuffer,
  STATES,
  errorToast,
  processValidationErrorsAndUpdateOperations,
  replaceTempIdsInSurveyAndBuffer,
  updateSurveyEntitiesData,
  dayJsHelper,
  RandomNumber,
  isTempId,
  removeSurveyConditions,
} from 'helpers'
import {
  getInsertedTempConditionIds,
  isConditionWaitingForTempId,
  isReplacedByNewerScript,
  referencesDeletedTempId,
  removeConditionsWithDeletedTempIds,
  settleTempConditionIds,
} from 'helpers/Buffer/operationsHandlers'

/**
 * Lists the temp IDs of the questions and subquestions in the survey.
 *
 * @param {object} survey The survey.
 * @returns {Set<string>} The temp IDs.
 */
const getSurveyTempIds = (survey) =>
  new Set(
    (survey?.questionGroups ?? [])
      .flatMap((group) => group.questions ?? [])
      .flatMap((question) => [
        question.qid,
        ...(question.subquestions ?? []).map((subquestion) => subquestion.qid),
      ])
      .filter((id) => isTempId(id))
      .map(String)
  )

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

  // Mount / ID Change Logic
  useEffect(() => {
    clearBuffer()
    clearErrors()
    setHelpersSettings({})
    setIsPatchSurveyRunning(false)
    setIsAddingQuestionOrGroup(false)

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

    // Conditions on questions or subquestions that were deleted before they
    // were saved would wait for their real ID forever: Drop them, also from
    // the questions. Without a loaded survey, every temp ID would look deleted.
    const bufferedOperations = operationsBuffer.getOperations()
    const currentSurvey = queryClient.getQueryData([STATES.SURVEY])?.survey
    if (
      currentSurvey?.questionGroups &&
      bufferedOperations.some(isConditionWaitingForTempId)
    ) {
      const existingTempIds = getSurveyTempIds(currentSurvey)
      const cleanedOperations = removeConditionsWithDeletedTempIds(
        bufferedOperations,
        existingTempIds
      )
      if (cleanedOperations !== bufferedOperations) {
        const cleanedSurvey = removeSurveyConditions(
          currentSurvey,
          (condition) => referencesDeletedTempId(condition, existingTempIds)
        )
        if (cleanedSurvey !== currentSurvey) {
          update({ questionGroups: cleanedSurvey.questionGroups })
        }
        setBuffer(cleanedOperations, RandomNumber())
        return
      }
    }

    const readyOperations = operationsBuffer.getOperations({ ready: true })
    // surveyRefreshRequired is only updated on the next render, too late for finallyCallback
    let deleteRefused = false

    const beforeCallback = () => {
      // Clear ready buffer items
      // - items that are not ready may be waiting for tempId to be resolved
      operationsBuffer.clearBuffer({ ready: true })
      clearBuffer({ ready: true })
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

      if (result.extras) {
        mapResult = updateSurveyEntitiesData(
          EntitiesType.question,
          mapResult.survey,
          mapResult.operationsBuffer,
          result.extras
        )
      }

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

      // A sent condition insert without a real ID in the response was
      // rejected, and is not always put back in the buffer: Settle the edits
      // and deletes that wait for its ID.
      const mappedCids = (conditionsTempIdMapping ?? []).map(
        ({ tempId }) => tempId
      )
      const rejectedCids = getInsertedTempConditionIds(readyOperations).filter(
        (cid) => !mappedCids.includes(cid)
      )
      if (rejectedCids.length) {
        mapResult.operationsBuffer.setOperations(
          settleTempConditionIds(
            mapResult.operationsBuffer.getOperations(),
            rejectedCids
          )
        )

        // A rejected insert that doesn't wait to be sent again is not on the
        // server: Remove it from the question too, it can't be edited.
        const pendingCids = getInsertedTempConditionIds(
          mapResult.operationsBuffer.getOperations()
        )
        const lostCids = rejectedCids.filter(
          (cid) => !pendingCids.includes(cid)
        )
        if (lostCids.length) {
          mapResult.survey = removeSurveyConditions(
            mapResult.survey,
            (condition) => lostCids.includes(condition.cid)
          )
          errorToast(t('Could not insert all conditions.'))
        }
      }

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
      if (refusedDeletes.length) {
        deleteRefused = true
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
      setBuffer(mapResult.operationsBuffer.getOperations(), RandomNumber())

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
      // A condition save is obsolete when the user saved an ExpressionScript
      // for the question while the request was running.
      const bufferedOperations = queryClient.getQueryData([STATES.BUFFER]) ?? []
      const isObsolete = (operation) =>
        isReplacedByNewerScript(operation, bufferedOperations, readyOperations)
      const updatedOperation = readyOperations
        .filter((operation) => !isObsolete(operation))
        .map((operation) => ({
          ...operation,
          error: true,
        }))
      // The conditions of an obsolete save are never inserted: Edits and
      // deletes waiting for their real ID are settled like rejected inserts.
      const droppedCids = getInsertedTempConditionIds(
        readyOperations.filter(isObsolete)
      )

      updatedOperation.forEach((operation) => {
        addToBuffer(operation, false)
      })

      if (droppedCids.length) {
        setBuffer(
          settleTempConditionIds(
            queryClient.getQueryData([STATES.BUFFER]) ?? [],
            droppedCids
          ),
          RandomNumber()
        )
      }

      setErrorsFromPatchResponse([...updatedOperation])
      setSaveState(
        `Error happened ${dayJsHelper(new Date().getTime()).format('hh:mm')}`
      )

      errorToast(error.message)
    }

    const finallyCallback = () => {
      setIsPatchSurveyRunning(false)

      // maybe there's some delayed operations.
      const pendingOperations = new OperationsBuffer(
        queryClient.getQueryData([STATES.BUFFER])
      )
      if (
        pendingOperations.isEmpty() &&
        (surveyRefreshRequired || deleteRefused)
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
