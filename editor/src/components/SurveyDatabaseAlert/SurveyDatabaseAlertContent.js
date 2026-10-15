import { useTranslation } from 'react-i18next'
import { getSiteUrl } from 'helpers'
import parse from 'html-react-parser'
import { format } from 'util'

const SurveyDatabaseAlertContent = ({ isSuperAdmin = false }) => {
  const { t } = useTranslation()

  return (
    <div className="survey-database-alert-content">
      <p>
        {t('A problem has been detected with the survey database.')}
      </p>

      {isSuperAdmin ? (
        <>
          <p>
            {parse(
              format(
                t(
                  'You can resolve this issue by %sperforming a data integrity check%s or by deactivating and reactivating your survey to reset the response database.'
                ),
                `<a href="${getSiteUrl('/index.php/admin/checkintegrity')}">`,
                '</a>'
              )
            )}
          </p>
        </>
      ) : (
        <p>
          {t(
            'Please contact an administrator to perform a database integrity check or deactivate and reactivate your survey to reset the response database.'
          )}
        </p>
      )}
    </div>
  )
}

export default SurveyDatabaseAlertContent
