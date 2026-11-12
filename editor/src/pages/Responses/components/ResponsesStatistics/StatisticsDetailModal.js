import React from 'react'

import { ComponentModal } from 'components'

export const StatisticsDetailModal = ({
  show,
  onHide,
  title = null,
  modalClassname = '',
  children,
}) => (
  <ComponentModal
    show={show}
    onHide={onHide}
    title={title}
    modalClassname={`responses-statistics-modal ${modalClassname}`.trim()}
    componentClassname="responses-statistics-modal-body"
    Component={children}
  />
)
