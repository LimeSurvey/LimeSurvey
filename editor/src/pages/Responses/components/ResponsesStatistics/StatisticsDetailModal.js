import React from 'react'

import { ComponentModal } from 'components'

export const StatisticsDetailModal = ({
  show,
  onHide,
  modalClassname = '',
  children,
}) => (
  <ComponentModal
    show={show}
    onHide={onHide}
    modalClassname={`responses-statistics-modal ${modalClassname}`.trim()}
    componentClassname="responses-statistics-modal-body"
    Component={children}
  />
)
