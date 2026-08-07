import React from 'react'
import classNames from 'classnames'

export const SideBarHeader = ({ children = null, className = '' }) => {
  return (
    <div
      className={classNames(
        'd-flex',
        'sidebar-header',
        'align-items-center',
        'justify-content-between',
        className
      )}
    >
      {children}
    </div>
  )
}
