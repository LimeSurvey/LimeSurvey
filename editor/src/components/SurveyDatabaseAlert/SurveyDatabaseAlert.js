import { ExclamationIcon } from 'components/icons'
import SurveyDatabaseAlertContent from './SurveyDatabaseAlertContent'

const SurveyDatabaseAlert = ({ isSuperAdmin }) => {

  return (
    <div className="generic-toast error-left-mark" role="alert">
      <ExclamationIcon />
      <SurveyDatabaseAlertContent isSuperAdmin={isSuperAdmin} />
    </div>
  )
}

export default SurveyDatabaseAlert
