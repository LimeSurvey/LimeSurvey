import { useEffect, useState } from 'react'
import { useIdleTimer } from 'react-idle-timer'

import { SwalAlert } from 'helpers'

export const useQueryRetry = ({
  normalFetchInterval = 1000 * 60,
  idleFetchInterval = 1000 * 60 * 5,
  // if user is not active for 5min, then set idle
  idleTimeOut = 1000 * 60 * 5,
  // stop refetching if user is not active for 1 hour
  refetchStopTime = 1000 * 60 * 60,
  // check every 5 minutes to detect if user is idle for configured stop time and stop refetching
  detectUserIdleInterval = 1000 * 60 * 5,
}) => {
  const [refetchInterval, setRefetchInterval] = useState(normalFetchInterval)

  const onIdle = () => {
    setRefetchInterval(idleFetchInterval)
  }

  const onActive = () => {
    setRefetchInterval(normalFetchInterval)
  }

  const onAction = () => {}

  const { getTotalIdleTime } = useIdleTimer({
    onIdle,
    onActive,
    onAction,
    timeout: idleTimeOut,
  })

  const handleRetry = (failureCount, error) => {
    if (failureCount < 10) {
      return true
    }

    SwalAlert.fire({
      title: <strong>{error.message}</strong>,
      html: (
        <i>
          {t('Sorry, we encountered an issue while fetching the data.')}{' '}
          {t('Please try again later!')}
        </i>
      ),
      icon: 'error',
      width: 400,
    })
    return false
  }

  useEffect(() => {
    const interval = setInterval(() => {
      if (getTotalIdleTime() > refetchStopTime) {
        setRefetchInterval(false)
      }
    }, detectUserIdleInterval)

    return () => {
      clearInterval(interval)
    }
  }, [getTotalIdleTime, refetchStopTime, detectUserIdleInterval])

  return {
    refetchInterval,
    handleRetry,
  }
}
