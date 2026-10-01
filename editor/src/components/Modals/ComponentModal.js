import * as React from 'react'
import { Modal } from 'react-bootstrap'

import { CloseIcon } from 'components/icons'
import { Button } from 'components/UIComponents'

export const ComponentModal = ({
  show = false,
  onHide = () => {},
  Component,
  title = null,
  componentClassname = '',
  modalClassname = '',
  headerClassname = '',
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
        className={`border-none d-flex align-items-center gap-2 ${
          title ? 'justify-content-between' : 'justify-content-end'
        } ${headerClassname}`}
        closeButton={false}
      >
        {title}
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
        <Modal.Footer className="border-none d-block text-end">
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
            variant="primary"
            onClick={onConfirm}
          >
            {t('Confirm')}
          </Button>
        </Modal.Footer>
      )}
    </Modal>
  )
}
