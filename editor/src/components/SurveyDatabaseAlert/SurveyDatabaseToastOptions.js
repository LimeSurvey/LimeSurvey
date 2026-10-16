import { ExclamationIcon } from 'components/icons'
import { getSurveyDatabaseAlertMessage } from './SurveyDatabaseAlert'

export const getSurveyDatabaseToastOptions = ({ isSuperAdmin }) => ({
  message: getSurveyDatabaseAlertMessage(isSuperAdmin, true),
  className:
    'generic-toast yellow-left-mark toast-constrained toast-fixed-icon toast-below-top-bar reg14',
  position: 'top-center',
  leftIcon: <ExclamationIcon />,
  duration: Infinity,
})
