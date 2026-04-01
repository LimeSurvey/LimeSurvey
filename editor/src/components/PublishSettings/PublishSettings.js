import { useMemo } from 'react'
import { useParams } from 'react-router-dom'
import classNames from 'classnames'

import { useAppState, useSurveyArchive } from 'hooks'
import { STATES } from 'helpers'
import { getTooltipMessages } from 'helpers/options'
import { Button, TooltipContainer } from 'components'
import { CheckIcon, StopIcon } from 'components/icons'

export const PublishSettings = ({
  operationsLength = 0,
  triggerPublish,
  className = '',
}) => {
  const { surveyId } = useParams()
  const { surveyArchives } = useSurveyArchive(surveyId)
  const [surveyPublishRunning] = useAppState(
    STATES.SURVEY_PUBLISH_RUNNING,
    false
  )
  const [numberOfQuestions] = useAppState(STATES.NUMBER_OF_QUESTIONS)
  const [isSurveyActive] = useAppState(STATES.IS_SURVEY_ACTIVE, false)
  const [hasSurveyUpdatePermission] = useAppState(
    STATES.HAS_SURVEY_UPDATE_PERMISSION
  )
  const surveyHasArchivedResponses = useMemo(
    () => surveyArchives.length > 0,
    [surveyArchives]
  )

  const getActivateTip = () => {
    if (isSurveyActive) {
      return t(
        'When deactivated, responses will be archived. You will then be able to add or delete questions, question groups, and settings again.'
      )
    }

    if (numberOfQuestions === 0) {
      return t('You must have at least one question.')
    }

    if (operationsLength) {
      return t('Waiting for changes to be saved...')
    }

    if (!hasSurveyUpdatePermission) {
      return getTooltipMessages().NO_PERMISSION
    }

    return surveyHasArchivedResponses
      ? t(
          'When reactivated, you will not be able to add or delete questions, question groups, or sub-questions. However, you can still edit text.'
        )
      : t(
          'When activated, you will not be able to add or delete questions, question groups, or sub-questions. However, you can still edit text.'
        )
  }

  const isButtonDisabled =
    surveyPublishRunning ||
    operationsLength ||
    numberOfQuestions === 0 ||
    !hasSurveyUpdatePermission

  const buttonText = isSurveyActive
    ? t('Deactivate')
    : surveyHasArchivedResponses
      ? t('Reactivate')
      : t('Activate')

  return (
    <span data-testid="publish-settings">
      <TooltipContainer
        placement="bottom"
        showTip={true}
        tip={getActivateTip()}
      >
        <Button
          variant={isSurveyActive ? 'danger' : 'success'}
          className={classNames(
            'publish-button align-items-center d-flex ml-auto me-2',
            className
          )}
          onClick={() => triggerPublish()}
          disabled={isButtonDisabled}
          id="activate-survey-button"
        >
          {surveyPublishRunning ? (
            <div style={{ width: 24, height: 24 }} className="loader"></div>
          ) : (
            <>
              <div
                className="d-flex align-items-center"
                style={{ width: '20px' }}
              >
                {isSurveyActive ? (
                  <StopIcon className="fill-current text-white me-1" />
                ) : (
                  <CheckIcon className="fill-current text-white me-1" />
                )}
              </div>
              <p className="m-0 text-white">{buttonText}</p>
            </>
          )}
        </Button>
      </TooltipContainer>
    </span>
  )
}
