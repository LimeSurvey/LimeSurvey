import React from 'react'
import classNames from 'classnames'
import Fade from 'react-bootstrap/Fade'

export const SideBar = ({ visible = false, className = '', children }) => {
  return (
    <Fade
      in={typeof visible === 'boolean' ? visible : false}
      onEntered={() => null}
      onExiting={() => null}
    >
      <div className={classNames('sidebar p-2', className)}>{children}</div>
    </Fade>
  )
}
