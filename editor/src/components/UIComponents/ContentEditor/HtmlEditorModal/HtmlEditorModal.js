import React, { useEffect, useId, useRef, useState } from 'react'
import { Modal } from 'react-bootstrap'
import { Image as ImageIcon } from 'react-bootstrap-icons'

import { CloseIcon } from 'components/icons'
import { Button } from 'components/UIComponents'

import { CodeEditor } from '../CodeEditor/CodeEditor'
import { insertHtml } from '../CodeEditor/insertHtml'
import { SelectImageModal } from '../SelectImageModal/SelectImageModal'

/**
 * Dialog to edit a text as HTML. With a file service, its toolbar has an
 * "Insert image" button that uploads an image and inserts it at the cursor.
 *
 * @param {boolean} show whether the dialog is open
 * @param {string} value the HTML when the dialog opens
 * @param {Function} onSave called with the edited HTML
 * @param {Function} onHide called when the dialog is closed without saving
 * @param {string} title title of the dialog
 * @param {string} description text below the title
 * @param {import('services').FileService|null} fileService uploads images,
 *   without it there is no "Insert image" button
 * @returns {JSX.Element} the dialog
 */
export const HtmlEditorModal = ({
  show = false,
  value = '',
  onSave = () => {},
  onHide = () => {},
  title = t('Edit question using HTML'),
  description = t('Use custom HTML to edit and format your question text.'),
  fileService = null,
}) => {
  const titleId = useId()
  const descriptionId = useId()
  const valueRef = useRef(value)
  const editorViewRef = useRef(null)
  const insertImageButtonRef = useRef(null)
  // Cursor position when the image dialog was opened
  const insertPositionRef = useRef(0)
  const hasInsertedRef = useRef(false)
  const [showSelectImage, setShowSelectImage] = useState(false)

  useEffect(() => {
    if (show) {
      valueRef.current = value
    }
  }, [show, value])

  const openSelectImage = () => {
    const view = editorViewRef.current
    insertPositionRef.current = view
      ? view.state.selection.main.head
      : valueRef.current.length
    hasInsertedRef.current = false
    setShowSelectImage(true)
  }

  const handleInsertImage = (imageHtml) => {
    const view = editorViewRef.current
    if (view) {
      insertHtml(view, imageHtml, insertPositionRef.current)
      hasInsertedRef.current = true
    }
    setShowSelectImage(false)
  }

  const handleSelectImageExited = () => {
    if (hasInsertedRef.current) {
      editorViewRef.current?.focus()
    } else {
      insertImageButtonRef.current?.focus()
    }
  }

  return (
    <>
      <Modal
        show={show}
        onHide={onHide}
        size="xl"
        centered
        className="html-editor-modal"
        aria-labelledby={titleId}
        aria-describedby={descriptionId}
        data-testid="html-editor-modal"
      >
        <Modal.Header className="border-none d-flex align-items-start justify-content-between">
          <div>
            <h2 id={titleId} className="modal-title">
              {title}
            </h2>
            <p id={descriptionId} className="mb-0 mt-2">
              {description}
            </p>
          </div>
          <Button
            className="modal-close-button p-0"
            variant="link"
            onClick={onHide}
            ariaLabel={t('Close')}
          >
            <CloseIcon className="text-black fill-current" />
          </Button>
        </Modal.Header>
        <Modal.Body>
          <div className="html-editor-modal-editor">
            {fileService && (
              <div
                className="html-editor-modal-toolbar"
                role="toolbar"
                aria-label={t('HTML editor toolbar')}
              >
                <Button
                  ref={insertImageButtonRef}
                  variant="outline-dark"
                  size="sm"
                  className="d-inline-flex align-items-center gap-2"
                  onClick={openSelectImage}
                  testId="html-editor-insert-image"
                >
                  <ImageIcon aria-hidden="true" />
                  {t('Insert image')}
                </Button>
              </div>
            )}
            <CodeEditor
              id="html-editor-code"
              className="text-start html-editor"
              value={value}
              width="100%"
              height="60vh"
              autoFocus
              onCreateEditor={(view) => {
                editorViewRef.current = view
              }}
              onChange={(newValue) => {
                valueRef.current = newValue
              }}
            />
          </div>
        </Modal.Body>
        <Modal.Footer className="border-none d-flex justify-content-end gap-2">
          <Button variant="secondary" className="text-light" onClick={onHide}>
            {t('Cancel')}
          </Button>
          <Button
            variant="primary"
            onClick={() => onSave(valueRef.current)}
            testId="html-editor-save"
          >
            {t('Save')}
          </Button>
        </Modal.Footer>
      </Modal>
      {fileService && (
        <SelectImageModal
          show={show && showSelectImage}
          onHide={() => setShowSelectImage(false)}
          onInsert={handleInsertImage}
          onExited={handleSelectImageExited}
          fileService={fileService}
        />
      )}
    </>
  )
}
