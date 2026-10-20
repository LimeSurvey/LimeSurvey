import { ExclamationMark } from 'components/icons'
import { getSurveyDatabaseAlertMessage } from './SurveyDatabaseAlert'

export const getSurveyDatabaseToastOptions = ({ isSuperAdmin }) => ({
  message: getSurveyDatabaseAlertMessage(isSuperAdmin, true),
  className:
    'generic-toast error-left-mark toast-constrained toast-compact toast-fixed-icon toast-below-top-bar reg14',
  position: 'top-center',
  leftIcon: <ExclamationMark />,
  duration: Infinity,
})
