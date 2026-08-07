import React, { useEffect } from 'react'
import { useParams } from 'react-router-dom'
import { format } from 'util'
import { queryClient } from 'queryClient'

import { useAppState, useBuffer, useErrors, useFocused, useSurvey } from 'hooks'
import {
  EntitiesType,
  OperationsBuffer,
  STATES,
  errorToast,
  processValidationErrorsAndUpdateOperations,
  replaceTempIdsInSurveyAndBuffer,
  updateSurveyEntitiesData,
  dayJsHelper,
  RandomNumber,
} from 'helpers'

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

    const readyOperations = operationsBuffer.getOperations({ ready: true })

    const beforeCallback = () => {
      // Clear ready buffer items
      // - items that are not ready may be waiting for tempId to be resolved
      operationsBuffer.clearBuffer({ ready: true })
      clearBuffer({ ready: true })
      setIsPatchSurveyRunning(true)
    }

    // currentBuffer
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

      if (!result.operationsApplied) {
        errorToast(
          'Sorry, we encountered an issue while saving the changes. Please try refreshing the page!'
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

      setErrorsFromPatchResponse([...updatedOperation])
      setSaveState(
        `Error happened ${dayJsHelper(new Date().getTime()).format('hh:mm')}`
      )

      errorToast(error.message)
    }

    const finallyCallback = () => {
      setIsPatchSurveyRunning(false)

      // maybe there's some delayed operations.
      if (operationsBuffer.isEmpty() && surveyRefreshRequired) {
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
