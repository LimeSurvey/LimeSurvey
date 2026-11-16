import React from 'react'

import SmallCheckMarkIcon from 'components/icons/SmallCheckMarkIcon'

const OverViewToast = ({ message }) => {
  return (
    <div className="overview-toast">
      <SmallCheckMarkIcon classname="overview-left-spacing" />
      <span className="reg14 overview-left-spacing">{message}</span>
    </div>
  )
}

export default OverViewToast
