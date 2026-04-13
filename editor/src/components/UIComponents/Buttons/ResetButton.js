import ResetIcon from 'components/icons/ResetIcon'
import { RESET_TYPES } from 'helpers'
import React, { useCallback } from 'react'

const ResetButton = ({ resetType }) => {
  const handleReset = useCallback((type) => {
    switch (type) {
      case RESET_TYPES.ThemeOptions:
        resetThemeOptions()
        break
      default:
        break
    }
  }, [])

  const resetThemeOptions = useCallback(() => {
    // api call
  }, [])

  return (
    <div
      onClick={() => handleReset(resetType)}
      className="cursor-pointer d-flex align-items-center gap-2 px-2 fit mb-2"
    >
      <ResetIcon />
      <span className="text-primary">{t('Reset')}</span>
    </div>
  )
}

export default ResetButton
