import React, { useCallback, useEffect, useState } from 'react'
import { useDropzone } from 'react-dropzone'
import { Form, Button, Image } from 'react-bootstrap'

import NoImageFound from 'assets/images/no-image-found.jpg'
import { UploadIcon, DeleteIcon } from 'components/icons'
import { errorToast } from 'helpers/Alert'
import { FILE_UPLOAD_MAX_SIZE } from 'helpers/constants'
import classNames from 'classnames'

const handleError = (errorMessage) => {
  errorToast(errorMessage)
}

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

  const onDropAccepted = (acceptedFiles) => {
    acceptedFiles.forEach(async (file) => {
      setLoading(true)

      const previewUrlOld = previewUrl
      const url = URL.createObjectURL(file)
      changePreview(url)
      if (previewUrlOld) {
        URL.revokeObjectURL(previewUrlOld)
      }

      const formData = new FormData()
      formData.append('file', file, file.name)

      let response = {}
      try {
        response = await fileService.uploadSurveyImage(formData)
      } catch (error) {
        changePreview(previewUrlOld)
        setLoading(false)
        if (error.response) {
          handleError(
            'Upload failed with status code: ' + error.response.status
          )
        } else {
          handleError(error.message)
        }
        return
      }

      onChange(response.uploaded.filePath)
      changePreview(response.uploaded.previewUrl)
      setLoading(false)
    })
  }

  const onDropRejected = useCallback(
    (fileRejections) => {
      fileRejections.forEach((fileRejection) => {
        fileRejection.errors.forEach((error) => {
          handleError(fileRejection.file.name + ': ' + error.message)
        })
      })
    },
    [handleError]
  )

  const handleDelete = () => {
    // Deleting - does not delete the file from the server
    // - we just remove the reference in the related data
    setShowTrashIcon(false)
    changePreview(null)
    onChange('')
    onDelete()
  }

  const onError = (error) => {
    handleError(error.message)
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
    <div
      className={`position-absolute file-loading-btn-wrapper ${
        isLoading ? '' : 'd-none'
      }`}
    >
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
      {loadingSpinner}
      <Image
        src={isValidImg ? previewUrl : NoImageFound}
        alt="Image Select List"
        style={{
          height: previewMaxHeight,
          width: previewMaxWidth,
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
