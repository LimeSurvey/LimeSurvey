import { ExclamationIcon } from 'components/icons'
import { getSurveyDatabaseAlertMessage } from './SurveyDatabaseAlert'

export const getSurveyDatabaseToastOptions = ({ isSuperAdmin }) => ({
  message: getSurveyDatabaseAlertMessage(isSuperAdmin, true),
  className: 'generic-toast yellow-left-mark survey-database-toast',
  position: 'top-center',
  leftIcon: <ExclamationIcon />,
  duration: Infinity,
})
