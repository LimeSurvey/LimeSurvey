import * as React from 'react'
import { Modal } from 'react-bootstrap'

import { Button } from 'components/UIComponents'

export const ConfirmModal = ({
  show = false,
  onHide = () => {},
  onConfirm,
  title = t('Confirm action'),
  description = t('Are you sure you want to perform this action?'),
  confirmButtonText = t('Confirm'),
  confirmButtonVariant = 'danger',
  onEntered,
  ...props
}) => {
  const titleId = React.useId()
  const cancelButtonRef = React.useRef(null)

  return (
    <Modal
      {...props}
      show={show}
      centered
      onHide={onHide}
      aria-labelledby={titleId}
      onEntered={(...args) => {
        // Focus the safe choice once the modal is shown
        cancelButtonRef.current?.focus()
        onEntered?.(...args)
      }}
      className={`confirm-modal confirm-modal--${confirmButtonVariant}`}
    >
      <Modal.Header
        className="border-none d-flex align-items-center gap-2 text-center"
        closeButton
      ></Modal.Header>
      <Modal.Body>
        <h1 className="reg24" id={titleId}>
          {title}
        </h1>
        <h3 className="reg14 description">{description}</h3>
      </Modal.Body>
      <Modal.Footer className="border-none  p-0 d-flex justify-content-end text-center">
        <Button
          ref={cancelButtonRef}
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
          variant={confirmButtonVariant}
          onClick={onConfirm}
          testId="confirm-modal-confirm-button"
        >
          {confirmButtonText}
        </Button>
      </Modal.Footer>
    </Modal>
  )
}
