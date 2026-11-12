import React, {
  useCallback,
  useEffect,
  useState,
  useImperativeHandle,
  forwardRef,
} from 'react'
import { format } from 'util'
import { useDropzone } from 'react-dropzone'
import { Form, Button } from 'react-bootstrap'

import NoImageFound from 'assets/images/no-image-found.jpg'
import { UploadIcon, DeleteIcon, EditIcon } from 'components/icons'
import { errorToast } from 'helpers/Alert'
import { FILE_UPLOAD_MAX_SIZE } from 'helpers/constants'
import { EditedImage } from '../ImageEditor/EditedImage'
import classNames from 'classnames'

/**
 * Image upload area: Shows the uploaded image with buttons to edit and delete
 * it, or a drop area while there is no image.
 *
 * @param {Object} props see the defaults below for the available props
 * @param {Object} ref gets open(), which opens the file dialog
 * @returns {JSX.Element} the upload area
 */
export const DropZone = forwardRef(
  (
    {
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
      // When set, an edit button is shown next to the trash icon on hover.
      onEdit = null,
      // Name of the image (e.g. the option code), used to tell the buttons of
      // several dropzones apart for screen readers.
      imageName = '',
      disabled = false,
      previewCover = true,
      // When set, both the empty and the uploaded-image states use this
      // exact height. The uploaded image then fills the full width/height
      // of the dropzone and gets cropped (top/bottom) via object-fit: cover.
      fixedHeight,
      // Settings from the image editor: The preview shows the image with its
      // frame ratio, crop and corner radius, scaled to previewMaxHeight.
      imageSettings = null,
      // Alt text of the preview image.
      imageAlt = 'Image Select List',
      // When set, the edit and delete buttons are shown to the right of the
      // image instead of on it, in a space that is always kept free, so they
      // stay reachable however small the image is. Hovering the image or that
      // space shows them.
      buttonsBeside = false,
      // Extra classes of the box around the image in buttonsBeside mode, e.g.
      // a border that should not include the buttons.
      previewBoxClassName = '',
    },
    ref
  ) => {
    // The buttons are shown while the pointer is over the image or the focus
    // is inside the dropzone: Moving the mouse away doesn't hide a focused
    // button from keyboard users.
    const [isHovered, setIsHovered] = useState(false)
    const [hasFocus, setHasFocus] = useState(false)
    const [previewUrl, setPreviewUrl] = useState(null)
    const [isLoading, setIsLoading] = useState(false)

    const handleOnMouseHover = () => setIsHovered(true)
    const handleOnMouseLeave = () => setIsHovered(false)
    // Keyboard users reach the buttons by focusing the dropzone first.
    const handleOnFocus = () => setHasFocus(true)
    const handleOnBlur = (event) => {
      if (!event.currentTarget.contains(event.relatedTarget)) {
        setHasFocus(false)
      }
    }
    const hideImageButtons = () => {
      setIsHovered(false)
      setHasFocus(false)
    }

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
          onUploadFailed(
            url,
            previousPreviewUrl,
            uploadResultMessage || 'Upload failed'
          )
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
      hideImageButtons()
      changePreview(null)
      onChange('')
      onDelete()
    }

    const onError = (error) => {
      errorToast(error.message)
    }

    const { getRootProps, getInputProps, open } = useDropzone({
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

    useImperativeHandle(ref, () => ({ open }))

    const loadingSpinner = (
      <div className="position-absolute file-loading-btn-wrapper">
        <div style={{ width: 24, height: 24 }} className="loader"></div>
      </div>
    )

    const emptyDropzone = (
      <div
        className={classNames('dropzone', { disabled: disabled })}
        style={fixedHeight ? { height: fixedHeight } : undefined}
      >
        <UploadIcon className="icon" />
        <p className="label">{emptyZoneText}</p>
      </div>
    )

    const hoverHandlers = {
      onMouseOver: handleOnMouseHover,
      onMouseLeave: handleOnMouseLeave,
    }
    const areButtonsShown =
      !isLoading && (trashIconEnabled || !!onEdit) && (isHovered || hasFocus)

    const imageButtons = (
      <div
        className={classNames(
          'image-handle-btn-wrapper',
          // Beside the image, hidden buttons keep their space: The image
          // doesn't change its size on hover.
          buttonsBeside
            ? ['image-handle-btn-beside', { invisible: !areButtonsShown }]
            : ['position-absolute', { 'd-none': !areButtonsShown }]
        )}
      >
        {onEdit && (
          <Button
            variant="outline-light"
            className="image-handle-btn ms-1"
            size="sm"
            aria-label={
              imageName
                ? format(t('Edit image %s'), imageName)
                : t('Edit image')
            }
            data-testid={`${dataTestId}-edit`}
            onClick={(event) => {
              // Keep the click from opening the file dialog of the dropzone.
              event.stopPropagation()
              hideImageButtons()
              onEdit()
            }}
          >
            <EditIcon className="text-primary fill-current" />
          </Button>
        )}
        {trashIconEnabled && (
          <Button
            variant="outline-light"
            className="image-handle-btn ms-1"
            size="sm"
            aria-label={
              imageName
                ? format(t('Delete image %s'), imageName)
                : t('Delete image')
            }
            onClick={(event) => {
              handleDelete()
              event.stopPropagation()
            }}
          >
            <DeleteIcon className="text-primary fill-current" />
          </Button>
        )}
      </div>
    )

    const image = (
      <div
        {...(buttonsBeside ? {} : hoverHandlers)}
        className="position-relative dropzone-img"
        style={{
          height: fixedHeight || undefined,
          maxHeight: fixedHeight ? undefined : previewMaxHeight,
        }}
      >
        {isLoading ? loadingSpinner : null}
        <EditedImage
          src={isValidImg ? previewUrl : NoImageFound}
          alt={imageAlt}
          settings={isValidImg && !fixedHeight ? imageSettings : null}
          height={previewMaxHeight}
          fallbackStyle={
            fixedHeight
              ? {
                  width: '100%',
                  height: '100%',
                  objectFit: previewCover ? 'cover' : 'contain',
                }
              : {
                  maxHeight: previewMaxHeight,
                  maxWidth: previewMaxWidth,
                  objectFit: previewCover ? 'cover' : 'contain',
                }
          }
        />
        {!buttonsBeside && imageButtons}
      </div>
    )

    // Beside the image, the hover area covers the image and the buttons. The
    // image shrinks to the space left next to the buttons, never below it.
    const preview = buttonsBeside ? (
      <div
        {...hoverHandlers}
        className="d-inline-flex align-items-end mw-100"
        data-testid={`${dataTestId}-preview`}
      >
        <div className={previewBoxClassName} style={{ minWidth: 0 }}>
          {image}
        </div>
        {imageButtons}
      </div>
    ) : (
      image
    )

    return (
      <div
        style={{ cursor: 'pointer', minwidth: '200px' }}
        data-testid={dataTestId}
        {...getRootProps({
          onFocus: previewUrl ? handleOnFocus : undefined,
          onBlur: handleOnBlur,
        })}
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
)

DropZone.displayName = 'DropZone'
