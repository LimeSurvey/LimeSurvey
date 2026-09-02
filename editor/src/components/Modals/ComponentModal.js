import * as React from 'react'
import { Modal } from 'react-bootstrap'

import { CloseIcon } from 'components/icons'
import { Button } from 'components/UIComponents'

export const ComponentModal = ({
  show = false,
  onHide = () => {},
  Component,
  componentClassname = '',
  modalClassname = '',
  headerClassname = '',
  title = '',
  confirmButtonText = t('Confirm'),
  cancelButtonText = t('Cancel'),
  onConfirm = () => {},
  useFooter = false,
  ...props
}) => {
  return (
    <Modal
      className={`component-modal ${modalClassname}`}
      size="lg"
      show={show}
      centered
      onHide={onHide}
      {...props}
    >
      <Modal.Header
        className={`border-none d-flex align-items-center ${title ? 'justify-content-between' : 'gap-2'} text-center ${headerClassname}`}
        closeButton={false}
      >
        {title && <h2 className="modal-title h5 mb-0">{title}</h2>}
        <Button
          className="modal-close-button p-0"
          variant="link"
          onClick={onHide}
          aria-label="Close"
        >
          <CloseIcon className="text-black fill-current" />
        </Button>
      </Modal.Header>
      <div className={componentClassname}>{Component}</div>
      {useFooter && (
        <Modal.Footer className="border-none d-flex justify-content-end gap-2">
          <Button
            size="lg"
            className="text-light"
            variant="secondary"
            onClick={onHide}
          >
            {cancelButtonText}
          </Button>
          <Button
            size="lg"
            className="text-light"
            variant="primary"
            onClick={onConfirm}
          >
            {confirmButtonText}
          </Button>
        </Modal.Footer>
      )}
    </Modal>
  )
}
