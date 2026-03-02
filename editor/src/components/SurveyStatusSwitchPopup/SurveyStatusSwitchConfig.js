import parse from 'html-react-parser'

import { format } from 'util'
import { SURVEY_MENU_TITLES } from 'helpers'
import { getSurveyPanels } from 'helpers/options'
import { CheckIcon, ExclamationIcon } from 'components/icons'

export const SURVEY_STATUS_SWITCH_TYPES = {
  KEEP_RESPONSES: 'activate_and_keep_responses',
  START_FROM_SCRATCH: 'activate_and_start_from_scratch',
  DEACTIVATE: 'deactivate',
  PAUSE: 'pause',
}

export const getSurveyDeactivationToastOptions = () => {
  return {
    message: t('Your survey has been deactivated'),
    position: 'bottom-center',
    className: 'success-toast',
    leftIcon: (
      <CheckIcon className="fill-current text-success rounded-circle bg-dark me-1" />
    ),
  }
}

export const getPublicationPath = (surveyId) =>
  `/survey/${surveyId}/${getSurveyPanels().settings.panel}/${SURVEY_MENU_TITLES.publication}`

// eslint-disable-next-line no-unused-vars
export const getSurveyPauseToastOptions = (surveyId, navigate) => {
  const publicationPath = getPublicationPath(surveyId)

  return {
    message: (
      <p className="label-m">
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
                    onClick={() => navigate(publicationPath)}
                  >
                    {domNode.children[0].data}
                  </span>
                )
              }
            },
          }
        )}
      </p>
    ),
    position: 'bottom-center',
    className: 'generic-toast yellow-left-mark',
    leftIcon: <ExclamationIcon />,
    duration: Infinity,
  }
}
