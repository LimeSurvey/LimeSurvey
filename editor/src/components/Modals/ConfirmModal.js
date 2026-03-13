import * as React from 'react'
import { Modal } from 'react-bootstrap'

import { Button } from 'components/UIComponents'

export const ConfirmModal = ({
  show = false,
  onHide = () => {},
  onConfirm,
  title = t('Confirm Action'),
  description = t('Are you sure you want to perform this action?'),
  confirmButtonText = t('Confirm'),
  ...props
}) => {
  return (
    <Modal
      {...props}
      show={show}
      centered
      onHide={onHide}
      className="confirm-modal"
    >
      <Modal.Header
        className="border-none d-flex align-items-center gap-2 text-center"
        closeButton
      ></Modal.Header>
      <Modal.Body>
        <h1 className="reg24">{title}</h1>
        <h3 className="reg14 description">{description}</h3>
      </Modal.Body>
      <Modal.Footer className="border-none  p-0 d-flex justify-content-end text-center">
        <Button
          size="lg"
          className="cancel-button"
          variant="secondary"
          onClick={onHide}
        >
          {t('Cancel')}
        </Button>
        <Button
          size="lg"
          className="text-light confirm-button"
          variant="danger"
          onClick={onConfirm}
          testId="confirm-modal-confirm-button"
        >
          {confirmButtonText}
        </Button>
      </Modal.Footer>
    </Modal>
  )
}
