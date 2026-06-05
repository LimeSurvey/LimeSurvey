import React from 'react'
import { Button } from 'react-bootstrap'

import { SideBarHeader } from 'components/SideBar'
import { CloseIcon } from 'components/icons'

export const SharingSideBar = ({ title, children, onClose }) => {
  return (
    <div
      id="sidebar-embed"
      className="right-side-bar bg-white sidebar active-side-bar"
    >
      <div className="right-sidebar-settings">
        <SideBarHeader className="right-side-bar-header primary">
          <div>{title}</div>
          {onClose && (
            <Button variant="link" style={{ padding: 0 }} onClick={onClose}>
              <CloseIcon className="text-black fill-current" />
            </Button>
          )}
        </SideBarHeader>
        {children}
      </div>
    </div>
  )
}
