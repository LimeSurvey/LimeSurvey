import React, { useState } from 'react'

import { CopyIcon } from 'components/icons'

const CopyButton = ({ onClick }) => {
  const [copied, setCopied] = useState(false)

  const handleClick = () => {
    if (onClick) {
      onClick()
    }

    setCopied(true)

    setTimeout(() => {
      setCopied(false)
    }, 600)
  }

  return (
    <button
      onClick={handleClick}
      className={`btn btn-primary btn-sm copy-btn d-flex justify-content-center align-items-center gap-2`}
    >
      {copied ? null : <CopyIcon className="text-white fill-current" />}
      <p>{copied ? t('Copied!') : t('Copy')}</p>
    </button>
  )
}

export default CopyButton
