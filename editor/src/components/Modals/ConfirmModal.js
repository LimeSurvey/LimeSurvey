import * as React from 'react'
import { Modal } from 'react-bootstrap'

import { Button } from 'components/UIComponents'

export const ConfirmModal = ({
  show = false,
  onHide = () => {},
  onConfirm,
  title = t('Confirm Action'),
  description = t('Are you sure you want to perform this action?'),
  ...props
}) => {
  return (
    <Modal
      {...props}
      show={show}
      centered
      onHide={onHide}
      className="w-fit confirm-modal"
    >
      <Modal.Header
        className="border-none d-flex align-items-center gap-2 text-center"
        closeButton
      ></Modal.Header>
      <Modal.Body>
        <i className="ri-close-large-line body-icon"></i>
        <h1 className="my-4">{title}</h1>
        <h3>{description}</h3>
      </Modal.Body>
      <Modal.Footer className="border-none d-flex justify-content-end text-center">
        <Button
          size="lg"
          className="text-light"
          variant="secondary"
          onClick={onHide}
        >
          {t('Cancel')}
        </Button>
        <Button
          size="lg"
          className="text-light"
          variant="danger"
          onClick={onConfirm}
        >
          {t('Confirm')}
        </Button>
      </Modal.Footer>
    </Modal>
  )
}
