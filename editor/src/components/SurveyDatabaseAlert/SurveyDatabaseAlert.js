import { ExclamationIcon } from 'components/icons'
import { useTranslation } from 'react-i18next'
import { getSiteUrl } from 'helpers'

const SurveyDatabaseAlert = ({ isSuperAdmin }) => {
  const { t } = useTranslation()

  return (
    <div className="already-paused-alert yellow-left-mark" role="alert">
      <div className="already-paused-alert-icon">
        <ExclamationIcon />
      </div>

      <p className="label-m already-paused-alert-label">
        {isSuperAdmin ? (
          <>
            {t('A problem has been detected with the survey database. Please ')}
            <a href={getSiteUrl('/index.php/admin/checkintegrity')}>
              {t('perform data integrity')}
            </a>
            {t('.')}
          </>
        ) : (
          t(
            'A problem has been detected with the survey database. Please contact an administrator to perform a database integrity check.'
          )
        )}
      </p>
    </div>
  )
}

export default SurveyDatabaseAlert
