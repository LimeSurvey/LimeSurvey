import React from 'react'

import { ComponentModal } from 'components'

export const StatisticsDetailModal = ({
  show,
  onHide,
  title,
  modalClassname = '',
  children,
}) => (
  <ComponentModal
    show={show}
    onHide={onHide}
    modalClassname={`statistics-detail-modal ${modalClassname}`.trim()}
    componentClassname="statistics-detail-modal-body"
    Component={
      <>
        {title && <h2 className="statistics-detail-modal-title">{title}</h2>}
        {children}
      </>
    }
  />
)
