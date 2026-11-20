import { useEffect, useRef } from 'react'

import { Button } from 'components'
import { STATES } from 'helpers'
import { useAppState } from 'hooks'

/**
 * Asks before an encrypted Date/time question's answers are shown, in any of
 * its views.
 */
export const EncryptedDateTimeGate = ({
  isEncrypted = true,
  calendarState = {},
  onCalendarStateChange,
  children,
}) => {
  const [canReadResponses] = useAppState(
    STATES.HAS_RESPONSES_READ_PERMISSION,
    false
  )
  const isDecrypted = !!calendarState.decrypted && canReadResponses
  const decryptedRef = useRef(null)
  const moveFocusRef = useRef(false)

  useEffect(() => {
    if (isDecrypted && moveFocusRef.current) {
      moveFocusRef.current = false
      decryptedRef.current?.focus()
    }
  }, [isDecrypted])

  if (!isEncrypted) {
    return children
  }

  if (!isDecrypted) {
    return (
      <div className="responses-statistics-calendar-locked">
        <p>
          {t(
            'The date/time data for this question was set up to be encrypted.'
          )}
        </p>
        {canReadResponses && (
          <Button
            variant="primary"
            onClick={() => {
              moveFocusRef.current = true
              onCalendarStateChange?.({ ...calendarState, decrypted: true })
            }}
          >
            {t('Decrypt response data and show statistics')}
          </Button>
        )}
      </div>
    )
  }

  return (
    <div
      ref={decryptedRef}
      tabIndex={-1}
      className="responses-statistics-calendar-decrypted"
    >
      {children}
    </div>
  )
}
