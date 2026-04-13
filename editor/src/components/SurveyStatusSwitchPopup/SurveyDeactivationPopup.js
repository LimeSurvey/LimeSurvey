import { useMemo } from 'react'
import parse from 'html-react-parser'

import {
  ExclamationIcon,
  InformationIcon,
  RadioSelectedIcon,
  RadioUnselectedIcon,
  EditPossibleIcon,
  ResponsesAvailableIcon,
  EditNotPossibleIcon,
  InvisibleIcon,
  ResponsesNotAvailableIcon,
  RecoverIcon,
} from 'components/icons'

import SurveyStatusSwitchPopup from './surveyStatusSwitchPopup'
import { SURVEY_STATUS_SWITCH_TYPES } from './SurveyStatusSwitchConfig'
import { format } from 'util'

const SurveyDeactivationPopup = ({
  surveyId,
  onConfirm,
  surveyIsExpired,
  navigateToPublication,
}) => {
  const title = useMemo(
    () => (
      <>
        {t('You want to stop your survey?')} <b>({surveyId})</b>
      </>
    ),
    [surveyId]
  )

  return (
    <SurveyStatusSwitchPopup
      surveyId={surveyId}
      title={title}
      getCards={getDeactivationCards}
      onConfirm={onConfirm}
      surveyIsExpired={surveyIsExpired}
      navigateToPublication={navigateToPublication}
    />
  )
}

export const getSurveyDeactivationPopupOptions = ({
  surveyId,
  onConfirm,
  surveyIsExpired,
  navigateToPublication,
}) => {
  return {
    html: (
      <SurveyDeactivationPopup
        surveyId={surveyId}
        onConfirm={onConfirm}
        surveyIsExpired={surveyIsExpired}
        navigateToPublication={navigateToPublication}
      />
    ),
  }
}

const getDeactivationCards = ({ selectedChoice, navigateToPublication }) => [
  {
    choice: SURVEY_STATUS_SWITCH_TYPES.PAUSE,
    elements: [
      {
        Icon:
          selectedChoice === SURVEY_STATUS_SWITCH_TYPES.PAUSE
            ? RadioSelectedIcon
            : RadioUnselectedIcon,
        label: (
          <div>
            <b>{t('Stop / pause survey')}</b>
            <span className={'d-block'}>
              {t('Stop your survey and still access your data for statistics')}
            </span>
          </div>
        ),
        isMainElement: true,
      },
      {
        Icon: EditNotPossibleIcon,
        label: (
          <div>
            {t(
              'After the survey was stopped, modifications to questions and settings are very limited.'
            )}
          </div>
        ),
      },
      {
        Icon: ResponsesAvailableIcon,
        label: (
          <div>
            {t(
              'Current responses and participant information will be kept and is still available for analysis.'
            )}
          </div>
        ),
      },
      {
        Icon: InvisibleIcon,
        label: (
          <div>
            {t(
              'Cannot be accessed by participants anymore. Stops data collection and sets expiry date.'
            )}
          </div>
        ),
      },
      {
        Icon: InformationIcon,
        label: <div>{t('This survey is already stopped.')}</div>,
        onlyDisabled: true,
      },
      {
        label: (
          <div className="already-paused-alert yellow-left-mark">
            <div className="already-paused-alert-icon">
              <ExclamationIcon />
            </div>
            <p className="label-m already-paused-alert-label">
              {parse(
                format(
                  t(
                    'Reopen the survey by changing or removing the expiration date in the %sPublication & access panel%s'
                  ),
                  '<span class="green-link">',
                  '</span>'
                ),
                {
                  replace: (domNode) => {
                    if (
                      domNode.name === 'span' &&
                      domNode.attribs.class === 'green-link'
                    ) {
                      return (
                        <span
                          className="green-link"
                          onClick={navigateToPublication}
                        >
                          {domNode.children[0].data}
                        </span>
                      )
                    }
                  },
                }
              )}
            </p>
          </div>
        ),
        onlyDisabled: true,
      },
    ],
  },
  {
    choice: SURVEY_STATUS_SWITCH_TYPES.DEACTIVATE,
    elements: [
      {
        Icon:
          selectedChoice === SURVEY_STATUS_SWITCH_TYPES.DEACTIVATE
            ? RadioSelectedIcon
            : RadioUnselectedIcon,
        label: (
          <div>
            <b>{t('Deactivate survey')}</b>
            <span className={'d-block'}>
              {t('Adjust questions or modify the survey structure')}
            </span>
          </div>
        ),
        isMainElement: true,
      },
      {
        Icon: EditPossibleIcon,
        label: (
          <div>
            {t('Add, delete or edit questions and modify survey structure.')}
          </div>
        ),
      },
      {
        Icon: ResponsesNotAvailableIcon,
        label: (
          <div>
            {t(
              'Current responses and participant information are not available for analysis anymore.'
            )}
          </div>
        ),
      },
      {
        Icon: InvisibleIcon,
        label: (
          <div>
            {t(
              'Cannot be accessed by participants anymore. Stops data collection.'
            )}
          </div>
        ),
      },
      {
        Icon: RecoverIcon,
        label: (
          <div>
            {t(
              'When reactivating you can import your archived responses and continue collecting responses.'
            )}
          </div>
        ),
      },
    ],
  },
]
