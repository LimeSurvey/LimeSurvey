import { useMemo } from 'react'
import { format } from 'util'

import {
  ImportIcon,
  MergeIcon,
  RadioSelectedIcon,
  RadioUnselectedIcon,
  ResponseTableIcon,
} from 'components/icons'

import { SURVEY_STATUS_SWITCH_TYPES } from './SurveyStatusSwitchConfig'
import SurveyStatusSwitchPopup from './surveyStatusSwitchPopup'

const SurveyReactivationPopup = ({
  surveyId,
  onConfirm,
  responseTableSelectionOptions,
}) => {
  const title = useMemo(
    () => (
      <span
        dangerouslySetInnerHTML={{
          __html: format(
            t('What do you want to do with the %sexisting%s Responses?'),
            '<b>',
            '</b>'
          ),
        }}
      />
    ),
    []
  )

  return (
    <SurveyStatusSwitchPopup
      surveyId={surveyId}
      title={title}
      getCards={getReactivationCards}
      onConfirm={onConfirm}
      responseTableSelectionOptions={responseTableSelectionOptions}
    />
  )
}

export const getSurveyReactivationPopupOptions = ({
  surveyId,
  onConfirm,
  responseTableSelectionOptions,
}) => {
  return {
    html: (
      <SurveyReactivationPopup
        surveyId={surveyId}
        onConfirm={onConfirm}
        responseTableSelectionOptions={responseTableSelectionOptions}
      />
    ),
  }
}

const getReactivationCards = ({ selectedChoice }) => [
  {
    choice: SURVEY_STATUS_SWITCH_TYPES.KEEP_RESPONSES,
    elements: [
      {
        Icon:
          selectedChoice === SURVEY_STATUS_SWITCH_TYPES.KEEP_RESPONSES
            ? RadioSelectedIcon
            : RadioUnselectedIcon,
        label: (
          <div>
            <b>{t('Keep existing responses')}</b>
            <br />
            <span>
              {t('Import archived responses in your new response table.')}
            </span>
          </div>
        ),
        isMainElement: true,
      },
      {
        Icon: ImportIcon,
        label: (
          <div>
            {t(
              'Import an existing responses table again and start from where you left off.'
            )}
          </div>
        ),
      },
      {
        Icon: MergeIcon,
        label: (
          <div>
            {t(
              'Questions and edits that were done in the meantime will be merged with your archived table as far as possible.'
            )}
          </div>
        ),
      },
    ],
  },
  {
    choice: SURVEY_STATUS_SWITCH_TYPES.START_FROM_SCRATCH,
    elements: [
      {
        Icon:
          selectedChoice === SURVEY_STATUS_SWITCH_TYPES.START_FROM_SCRATCH
            ? RadioSelectedIcon
            : RadioUnselectedIcon,
        label: (
          <div>
            <b>{t('Start from scratch')}</b>
            <br />
            <span>
              {t('Keep them archived and create a new response table.')}
            </span>
          </div>
        ),
        isMainElement: true,
      },
      {
        Icon: ResponseTableIcon,
        label: (
          <div>
            {t(
              'Start with a brand new responses table without importing an existing one.'
            )}
          </div>
        ),
      },
    ],
  },
]
