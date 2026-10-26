import React from 'react'
import classNames from 'classnames'

export const SectionHeader = ({ children, style }) => {
  return (
    <h6
      className={classNames(
        'd-flex',
        'align-items-center',
        'justify-content-between',
        'section-header'
      )}
      style={style}
    >
      {children}
    </h6>
  )
}
