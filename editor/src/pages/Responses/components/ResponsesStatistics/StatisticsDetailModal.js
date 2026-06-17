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
    modalClassname={`responses-statistics-modal ${modalClassname}`.trim()}
    componentClassname="responses-statistics-modal-body"
    title={
      title ? (
        <h2 className="responses-statistics-modal-title">{title}</h2>
      ) : null
    }
    Component={children}
  />
)
