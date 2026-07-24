import React, { useCallback, useEffect, useState } from 'react'
import { useDropzone } from 'react-dropzone'
import { Form, Button, Image } from 'react-bootstrap'

import NoImageFound from 'assets/images/no-image-found.jpg'
import { UploadIcon, DeleteIcon } from 'components/icons'
import { errorToast } from 'helpers/Alert'
import { FILE_UPLOAD_MAX_SIZE } from 'helpers/constants'
import classNames from 'classnames'

export const DropZone = ({
  fileService,
  onChange = () => {},
  onLoading = () => {},
  onChangePreview = () => {},
  onDelete = () => {},
  previewUrlInit = null,
  cleanPreviewOnInitValueChange = false,
  isValidImg = true,
  labelText = '',
  labelCommentText,
  emptyZoneText = t('Drop image here'),
  previewMaxHeight = '300px',
  previewMaxWidth = '100%',
  dataTestId = 'dropzone',
  trashIconEnabled = true,
  disabled = false,
  previewCover = true,
}) => {
  const [showTrashIcon, setShowTrashIcon] = useState(false)
  const [previewUrl, setPreviewUrl] = useState(null)
  const [isLoading, setIsLoading] = useState(false)

  const handleOnMouseHover = () => setShowTrashIcon(trashIconEnabled && true)
  const handleOnMouseLeave = () => setShowTrashIcon(false)

  const setLoading = (value) => {
    onLoading(value)
    setIsLoading(value)
  }

  useEffect(() => {
    if (previewUrlInit || cleanPreviewOnInitValueChange) {
      setPreviewUrl(previewUrlInit)
    }
  }, [previewUrlInit])

  const changePreview = useCallback(
    (previewUrl) => {
      setPreviewUrl(previewUrl)
      onChangePreview(previewUrl)
    },
    [setPreviewUrl, onChangePreview]
  )

  const onUploadFailed = (failedBlobUrl, previousPreviewUrl, message) => {
    URL.revokeObjectURL(failedBlobUrl)
    changePreview(previousPreviewUrl)
    setLoading(false)
    errorToast(message)
  }

  const onDropAccepted = (acceptedFiles) => {
    acceptedFiles.forEach(async (file) => {
      setLoading(true)

      const previousPreviewUrl = previewUrl
      const url = URL.createObjectURL(file)
      changePreview(url)

      const formData = new FormData()
      formData.append('file', file, file.name)

      let response = {}
      try {
        response = await fileService.uploadSurveyImage(formData)
      } catch (error) {
        const message = error.response
          ? 'Upload failed with status code: ' + error.response.status
          : error.message
        onUploadFailed(url, previousPreviewUrl, message)
        return
      }

      const { success, uploadResultMessage } = response
      if (!success) {
        onUploadFailed(url, previousPreviewUrl, uploadResultMessage || 'Upload failed')
        return
      }

      onChange(response.uploaded.filePath)
      changePreview(response.uploaded.previewUrl)
      setLoading(false)
    })
  }

  const onDropRejected = useCallback((fileRejections) => {
    fileRejections.forEach((fileRejection) => {
      fileRejection.errors.forEach((error) => {
        errorToast(fileRejection.file.name + ': ' + error.message)
      })
    })
  }, [])

  const handleDelete = () => {
    // Deleting - does not delete the file from the server
    // - we just remove the reference in the related data
    setShowTrashIcon(false)
    changePreview(null)
    onChange('')
    onDelete()
  }

  const onError = (error) => {
    errorToast(error.message)
  }

  const { getRootProps, getInputProps } = useDropzone({
    onDropAccepted,
    onDropRejected,
    onError,
    maxFiles: 1,
    maxSize: FILE_UPLOAD_MAX_SIZE,
    accept: {
      'image/png': ['.png'],
      'image/jpeg': ['.jpg'],
      'image/gif': ['.gif'],
    },
    disabled: disabled,
  })

  const loadingSpinner = (
    <div className="position-absolute file-loading-btn-wrapper">
      <div style={{ width: 24, height: 24 }} className="loader"></div>
    </div>
  )

  const emptyDropzone = (
    <div className={classNames('dropzone', { disabled: disabled })}>
      <UploadIcon className="icon" />
      <p className="label">{emptyZoneText}</p>
    </div>
  )

  const preview = (
    <div
      onMouseOver={handleOnMouseHover}
      onMouseLeave={handleOnMouseLeave}
      className="position-relative"
      style={{
        maxHeight: previewMaxHeight,
      }}
    >
      {isLoading ? loadingSpinner : null}
      <Image
        src={isValidImg ? previewUrl : NoImageFound}
        alt="Image Select List"
        style={{
          maxHeight: previewMaxHeight,
          maxWidth: previewMaxWidth,
          objectFit: previewCover ? 'cover' : 'container',
        }}
      />
      <div
        className={`position-absolute image-handle-btn-wrapper ${
          !isLoading && showTrashIcon ? '' : 'd-none'
        }`}
      >
        <Button
          variant="outline-light"
          className="image-handle-btn ms-1"
          size="sm"
          onClick={(event) => {
            handleDelete()
            event.stopPropagation()
          }}
        >
          <DeleteIcon className="text-primary fill-current" />
        </Button>
      </div>
    </div>
  )

  return (
    <div
      style={{ cursor: 'pointer', minwidth: '200px' }}
      data-testid={dataTestId}
      {...getRootProps()}
    >
      <input {...getInputProps()} />
      {labelText.length ? (
        <Form.Label>
          {labelText}
          {labelCommentText && (
            <span className="fw-normal ps-1">({labelCommentText})</span>
          )}
        </Form.Label>
      ) : (
        <></>
      )}
      {previewUrl ? preview : emptyDropzone}
    </div>
  )
}
