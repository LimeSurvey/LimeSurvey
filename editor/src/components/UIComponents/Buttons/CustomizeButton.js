import React from 'react'
import classNames from 'classnames'

import pencilIcon from 'assets/icons/pencil-icon.svg'

export const CustomizeButton = ({ text, onClick, isDisabled }) => {
  return (
    <span
      onClick={onClick}
      className={classNames('customize-button-container med14-c gap-1', {
        'disable-settings': isDisabled,
      })}
    >
      <img src={pencilIcon} /> {text}
    </span>
  )
}
