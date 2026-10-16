import parse from 'html-react-parser'

import { ExclamationMark } from 'components/icons'
import { useAppState } from 'hooks'
import { STATES } from 'helpers'
import { format } from 'util'
import { getSiteUrl } from 'helpers'

export const getSurveyDatabaseAlertMessage = (
  isSuperAdmin,
  isToast = false
) => {
  const linkOpen = `<a class="grape-link" href="${getSiteUrl('/index.php/admin/checkintegrity')}">`

  if (isToast) {
    return (
      <div className="reg14">
        {parse(
          format(
            t(
              'A problem has been detected in the survey database. You can resolve this by performing a %sdata integrity check%s or by deactivating and reactivating the survey to reset the response table.'
            ),
            isSuperAdmin ? linkOpen : '',
            isSuperAdmin ? '</a>' : ''
          )
        )}
      </div>
    )
  }

  const action = isSuperAdmin
    ? format(t('Please perform a %sdata integrity check%s.'), linkOpen, '</a>')
    : t(
        'Please contact an administrator to perform a database integrity check or deactivate and reactivate your survey to reset the response database.'
      )

  return (
    <div className="reg14">
      {parse(
        `${t('A problem has been detected in the survey database.')} ${action}`
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
