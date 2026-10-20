import parse from 'html-react-parser'

import { ExclamationMark } from 'components/icons'
import { useAppState } from 'hooks'
import { STATES } from 'helpers'
import { format } from 'util'
import { getSiteUrl } from 'helpers'

export const getSurveyDatabaseAlertMessage = (isSuperAdmin) => {
  const linkOpen = `<a class="grape-link" href="${getSiteUrl('/index.php/admin/checkintegrity')}">`

  const action = isSuperAdmin
    ? format(
        t(
          'You can resolve this issue by %sperforming a data integrity check%s or by deactivating and reactivating your survey to reset the response database.'
        ),
        linkOpen,
        '</a>'
      )
    : t(
        'Please contact an administrator to perform a database integrity check or deactivate and reactivate your survey to reset the response database.'
      )

  return (
    <div className="reg14">
      {parse(
        `${t('A problem has been detected with the survey database.')} ${action}`
      )}
    </div>
  )
}

export const SurveyDatabaseAlert = () => {
  const [userDetail] = useAppState(STATES.USER_DETAIL)

  return (
    <div
      className="generic-toast error-left-mark toast-compact toast-fixed-icon"
      role="alert"
    >
      <ExclamationMark />
      {getSurveyDatabaseAlertMessage(userDetail?.isSuperAdmin)}
    </div>
  )
}
