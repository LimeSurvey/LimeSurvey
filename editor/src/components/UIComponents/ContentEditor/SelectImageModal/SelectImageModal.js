import React, { useCallback, useEffect, useId, useRef, useState } from 'react'
import { Form, Modal } from 'react-bootstrap'
import { useDropzone } from 'react-dropzone'
import {
  Lock,
  Pencil,
  Trash,
  Unlock,
  Upload as UploadIcon,
} from 'react-bootstrap-icons'
import classNames from 'classnames'
import { format } from 'util'

import { CloseIcon } from 'components/icons'
import { Button } from 'components/UIComponents'
import { TooltipContainer } from 'components/TooltipContainer/TooltipContainer'

import {
  buildImageHtml,
  fitToMaxSize,
  getOtherSide,
  getSizeError,
  IMAGE_ACCEPT,
  IMAGE_MAX_FILE_SIZE,
  IMAGE_MAX_SIZE,
} from './imageHtml'

const STATUS_EMPTY = 'empty'
const STATUS_UPLOADING = 'uploading'
const STATUS_READY = 'ready'
const STATUS_ERROR = 'error'

/**
 * Returns the message for a file the upload area refused.
 *
 * @param {{errors: {code: string, message: string}[]}[]} fileRejections the
 *   refused files, as react-dropzone reports them
 * @returns {string} the message
 */
const getRejectionMessage = (fileRejections) => {
  const error = fileRejections[0]?.errors[0]
  switch (error?.code) {
    case 'file-invalid-type':
      return t(
        'This file type is not supported. Choose a PNG, JPEG or WebP image.'
      )
    case 'file-too-large':
      return format(
        t('This file is too large. Images can have at most %s MB.'),
        IMAGE_MAX_FILE_SIZE / 1024 / 1024
      )
    case 'too-many-files':
      return t('Choose only one image.')
    default:
      return error?.message || t('The upload failed. Please try again.')
  }
}

/**
 * Loads an image to read its size.
 *
 * @param {string} url URL of the image
 * @returns {Promise<{width: number, height: number}>} the natural size,
 *   rejected if the image can't be loaded
 */
const loadImageSize = (url) =>
  new Promise((resolve, reject) => {
    const image = new Image()
    image.onload = () =>
      image.naturalWidth && image.naturalHeight
        ? resolve({ width: image.naturalWidth, height: image.naturalHeight })
        : reject(new Error('Empty image'))
    image.onerror = () => reject(new Error('Image not loaded'))
    image.src = url
  })

/**
 * Dialog to upload an image and insert it into HTML: The author sets its
 * size, with a lock for the aspect ratio, and its alternative text, or marks
 * it as decorative. The image is saved in the survey's images folder, like
 * the images of the classic editor.
 *
 * @param {boolean} show whether the dialog is open
 * @param {Function} onHide called when the dialog is closed without
 *   inserting (Cancel, close button, Escape)
 * @param {Function} onInsert called with the HTML of the img element
 * @param {Function} onExited called when the dialog has closed, to move the
 *   focus back
 * @param {import('services').FileService} fileService uploads the image
 * @returns {JSX.Element} the dialog
 */
export const SelectImageModal = ({
  show = false,
  onHide = () => {},
  onInsert = () => {},
  onExited = () => {},
  fileService,
}) => {
  const idPrefix = useId()
  const ids = {
    title: `${idPrefix}-title`,
    description: `${idPrefix}-description`,
    uploadLabel: `${idPrefix}-upload-label`,
    uploadStatus: `${idPrefix}-upload-status`,
    width: `${idPrefix}-width`,
    widthError: `${idPrefix}-width-error`,
    height: `${idPrefix}-height`,
    heightError: `${idPrefix}-height-error`,
    sizeNote: `${idPrefix}-size-note`,
    altText: `${idPrefix}-alt-text`,
    altTextHint: `${idPrefix}-alt-text-hint`,
    decorative: `${idPrefix}-decorative`,
  }

  const [status, setStatus] = useState(STATUS_EMPTY)
  const [errorMessage, setErrorMessage] = useState('')
  // The uploaded image: path for the HTML, preview URL and natural size
  const [image, setImage] = useState(null)
  const [width, setWidth] = useState('')
  const [height, setHeight] = useState('')
  const [keepRatio, setKeepRatio] = useState(true)
  const [ratio, setRatio] = useState(1)
  const [isReduced, setIsReduced] = useState(false)
  const [altText, setAltText] = useState('')
  const [isDecorative, setIsDecorative] = useState(false)
  // Only the result of the latest upload counts
  const uploadCounter = useRef(0)

  const reset = useCallback(() => {
    uploadCounter.current++
    setStatus(STATUS_EMPTY)
    setErrorMessage('')
    setImage(null)
    setWidth('')
    setHeight('')
    setKeepRatio(true)
    setRatio(1)
    setIsReduced(false)
    setAltText('')
    setIsDecorative(false)
  }, [])

  useEffect(() => {
    if (show) {
      reset()
    }
  }, [show, reset])

  const showError = (message) => {
    setImage(null)
    setStatus(STATUS_ERROR)
    setErrorMessage(message)
  }

  const onDropAccepted = async ([file]) => {
    const uploadNumber = ++uploadCounter.current
    setImage(null)
    setStatus(STATUS_UPLOADING)
    setErrorMessage('')

    const formData = new FormData()
    formData.append('file', file, file.name)
    let response
    try {
      response = await fileService.uploadContentImage(formData)
    } catch {
      if (uploadNumber === uploadCounter.current) {
        showError(t('The upload failed. Please try again.'))
      }
      return
    }
    if (uploadNumber !== uploadCounter.current) {
      return
    }
    if (!response?.success || !response.uploaded?.filePath) {
      showError(
        response?.uploadResultMessage ||
          t('The upload failed. Please try again.')
      )
      return
    }

    let naturalSize
    try {
      naturalSize = await loadImageSize(response.uploaded.previewUrl)
    } catch {
      if (uploadNumber === uploadCounter.current) {
        showError(t('The image could not be loaded. It may be damaged.'))
      }
      return
    }
    if (uploadNumber !== uploadCounter.current) {
      return
    }
    const size = fitToMaxSize(naturalSize.width, naturalSize.height)
    setImage({
      src: response.uploaded.filePath,
      previewUrl: response.uploaded.previewUrl,
      naturalWidth: naturalSize.width,
      naturalHeight: naturalSize.height,
    })
    setWidth(String(size.width))
    setHeight(String(size.height))
    setRatio(naturalSize.width / naturalSize.height)
    setKeepRatio(true)
    setIsReduced(size.isReduced)
    setStatus(STATUS_READY)
  }

  const onDropRejected = (fileRejections) => {
    uploadCounter.current++
    showError(getRejectionMessage(fileRejections))
  }

  const { getRootProps, getInputProps, open, rootRef } = useDropzone({
    onDropAccepted,
    onDropRejected,
    accept: IMAGE_ACCEPT,
    maxSize: IMAGE_MAX_FILE_SIZE,
    maxFiles: 1,
    multiple: false,
    disabled: status === STATUS_UPLOADING,
  })

  const hasImage = status === STATUS_READY && !!image
  const widthError = hasImage ? getSizeError(width) : ''
  const heightError = hasImage ? getSizeError(height) : ''
  const hasAltText = isDecorative || altText.trim() !== ''
  const canInsert = hasImage && !widthError && !heightError && hasAltText

  const changeSize = (side, value) => {
    const setSide = side === 'width' ? setWidth : setHeight
    const setOtherSide = side === 'width' ? setHeight : setWidth
    setSide(value)
    if (keepRatio && !getSizeError(value)) {
      setOtherSide(String(getOtherSide(Number(value), side, ratio)))
    }
  }

  const toggleKeepRatio = () => {
    if (!keepRatio) {
      // The ratio of the current size is kept from now on
      const currentRatio =
        !getSizeError(width) && !getSizeError(height)
          ? Number(width) / Number(height)
          : image.naturalWidth / image.naturalHeight
      setRatio(currentRatio)
    }
    setKeepRatio(!keepRatio)
  }

  const changeDecorative = (checked) => {
    setIsDecorative(checked)
    if (checked) {
      setAltText('')
    }
  }

  const handleInsert = () => {
    if (!canInsert) {
      return
    }
    onInsert(
      buildImageHtml({
        src: image.src,
        alt: isDecorative ? '' : altText,
        width: Number(width),
        height: Number(height),
      })
    )
  }

  const ratioLabel = keepRatio
    ? t('Unlock aspect ratio')
    : t('Lock aspect ratio')

  const renderSizeInput = (side, value, error, label) => (
    <Form.Group className="select-image-size-field">
      <Form.Label htmlFor={ids[side]}>{label}</Form.Label>
      <Form.Control
        id={ids[side]}
        type="number"
        inputMode="numeric"
        min={1}
        max={IMAGE_MAX_SIZE}
        step={1}
        value={value}
        disabled={!hasImage}
        isInvalid={!!error}
        aria-invalid={!!error}
        aria-describedby={
          classNames({
            [ids[`${side}Error`]]: error,
            [ids.sizeNote]: isReduced,
          }) || undefined
        }
        onChange={(event) => changeSize(side, event.target.value)}
        data-testid={`select-image-${side}`}
      />
      <Form.Control.Feedback
        type="invalid"
        id={ids[`${side}Error`]}
        aria-live="polite"
      >
        {error}
      </Form.Control.Feedback>
    </Form.Group>
  )

  return (
    <Modal
      show={show}
      onHide={onHide}
      onEntered={() => rootRef.current?.focus()}
      onExited={onExited}
      // The opener decides where the focus goes: Back to the button, or to
      // the HTML editor after inserting
      restoreFocus={false}
      centered
      className="select-image-modal"
      backdropClassName="select-image-modal-backdrop"
      aria-labelledby={ids.title}
      aria-describedby={ids.description}
      data-testid="select-image-modal"
    >
      <Modal.Header className="border-none d-flex align-items-start justify-content-between">
        <div>
          <h2 id={ids.title} className="modal-title">
            {t('Select image')}
          </h2>
          <p id={ids.description} className="mb-0 mt-2">
            {t('Choose settings and properties for your image.')}
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
        <div id={ids.uploadLabel} className="form-label">
          {t('Upload image')}
        </div>
        <div
          {...getRootProps({
            'className': classNames('select-image-dropzone', {
              'is-disabled': status === STATUS_UPLOADING,
            }),
            'role': 'button',
            'aria-labelledby': ids.uploadLabel,
            'aria-describedby': `${ids.uploadLabel}-hint ${ids.uploadStatus}`,
            'data-testid': 'select-image-dropzone',
          })}
        >
          <input {...getInputProps()} data-testid="select-image-file-input" />
          <UploadIcon aria-hidden="true" size={18} />
          <div id={`${ids.uploadLabel}-hint`}>
            <div>{t('Drop image here, or choose a file')}</div>
            <div>{t('PNG, JPEG or WebP (max 10 MB)')}</div>
          </div>
        </div>
        <div id={ids.uploadStatus} aria-live="polite" className="mt-2">
          {status === STATUS_UPLOADING && (
            <span className="text-secondary">{t('Uploading...')}</span>
          )}
          {status === STATUS_ERROR && (
            <span className="text-danger" role="alert">
              {errorMessage}
            </span>
          )}
        </div>

        {hasImage && (
          <div className="mt-2">
            <div className="form-label">{t('Preview')}</div>
            <div className="d-flex align-items-start gap-2">
              <img
                src={image.previewUrl}
                alt={t('Preview of the uploaded image')}
                className="select-image-preview"
              />
              <TooltipContainer tip={t('Replace image')}>
                <Button
                  variant="outline-light"
                  className="image-handle-btn"
                  size="sm"
                  ariaLabel={t('Replace image')}
                  onClick={open}
                  testId="select-image-replace"
                >
                  <Pencil className="text-primary" aria-hidden="true" />
                </Button>
              </TooltipContainer>
              <TooltipContainer tip={t('Remove image')}>
                <Button
                  variant="outline-light"
                  className="image-handle-btn"
                  size="sm"
                  ariaLabel={t('Remove image')}
                  onClick={() => {
                    reset()
                    rootRef.current?.focus()
                  }}
                  testId="select-image-remove"
                >
                  <Trash className="text-primary" aria-hidden="true" />
                </Button>
              </TooltipContainer>
            </div>
          </div>
        )}

        <div
          className={classNames('select-image-size mt-3', {
            'is-ratio-locked': hasImage && keepRatio,
          })}
        >
          {renderSizeInput('width', width, widthError, t('Width'))}
          {renderSizeInput('height', height, heightError, t('Height'))}
          <TooltipContainer tip={hasImage ? ratioLabel : ''}>
            <Button
              variant="link"
              className="select-image-ratio-button p-1"
              ariaLabel={ratioLabel}
              onClick={toggleKeepRatio}
              disabled={!hasImage}
              testId="select-image-ratio"
            >
              {keepRatio ? (
                <Lock aria-hidden="true" size={18} />
              ) : (
                <Unlock aria-hidden="true" size={18} />
              )}
            </Button>
          </TooltipContainer>
        </div>
        {isReduced && (
          <Form.Text id={ids.sizeNote} className="d-block">
            {format(
              t(
                'The image was reduced to the maximum of %s pixels. It keeps its aspect ratio.'
              ),
              IMAGE_MAX_SIZE
            )}
          </Form.Text>
        )}

        <Form.Group className="mt-3">
          <Form.Label htmlFor={ids.altText}>{t('Alt text')}</Form.Label>
          <Form.Control
            id={ids.altText}
            type="text"
            value={altText}
            placeholder={isDecorative ? '' : t('Enter here')}
            disabled={!hasImage || isDecorative}
            aria-describedby={ids.altTextHint}
            onChange={(event) => setAltText(event.target.value)}
            data-testid="select-image-alt-text"
          />
          {hasImage && (
            <Form.Text id={ids.altTextHint} className="d-block">
              {isDecorative
                ? t('Alternative text is not required for decorative images.')
                : t(
                    'Describe the image for people who can’t see it, or mark it as decorative.'
                  )}
            </Form.Text>
          )}
        </Form.Group>
        <Form.Check
          id={ids.decorative}
          className="mt-2"
          type="checkbox"
          label={t('This image is decorative')}
          checked={isDecorative}
          disabled={!hasImage}
          onChange={(event) => changeDecorative(event.target.checked)}
          data-testid="select-image-decorative"
        />
      </Modal.Body>
      <Modal.Footer className="border-none d-flex justify-content-end gap-2">
        <Button variant="secondary" className="text-light" onClick={onHide}>
          {t('Cancel')}
        </Button>
        <Button
          variant="primary"
          onClick={handleInsert}
          disabled={!canInsert}
          testId="select-image-insert"
        >
          {t('Insert image')}
        </Button>
      </Modal.Footer>
    </Modal>
  )
}
