import classNames from 'classnames'
import { useDropzone } from 'react-dropzone'

import { Button } from 'components/UIComponents/Buttons/Button'
import { DeleteIcon, UploadIcon } from 'components/icons'

export const FileDropZone = ({
  accept,
  disabled = false,
  error = '',
  file = null,
  helperText = '',
  id = 'file-drop-zone',
  label = '',
  maxSize,
  onChange = () => {},
  onReject = () => {},
  prompt = t('Drop file here'),
}) => {
  const { getInputProps, getRootProps, isDragActive } = useDropzone({
    accept,
    disabled,
    maxFiles: 1,
    maxSize,
    multiple: false,
    onDropAccepted: ([acceptedFile]) => onChange(acceptedFile),
    onDropRejected: onReject,
  })

  const removeFile = (event) => {
    event.stopPropagation()
    onChange(null)
  }

  return (
    <div className="file-drop-zone-field">
      {label && (
        <label className="form-label fw-semibold" htmlFor={id}>
          {label}
        </label>
      )}
      <div
        {...getRootProps({
          className: classNames('file-drop-zone', {
            'file-drop-zone--active': isDragActive,
            'file-drop-zone--disabled': disabled,
            'file-drop-zone--invalid': error,
            'file-drop-zone--selected': file,
          }),
        })}
      >
        <input
          {...getInputProps({ id, 'aria-describedby': `${id}-message` })}
        />
        <UploadIcon className="file-drop-zone__icon" aria-hidden="true" />
        {file ? (
          <div className="file-drop-zone__selection">
            <span className="file-drop-zone__filename">{file.name}</span>
            <Button
              ariaLabel={t('Remove selected file')}
              className="file-drop-zone__remove p-0"
              disabled={disabled}
              onClick={removeFile}
              variant="link"
            >
              <DeleteIcon aria-hidden="true" />
            </Button>
          </div>
        ) : (
          <span>{isDragActive ? t('Drop file here') : prompt}</span>
        )}
      </div>
      <div
        className={classNames('file-drop-zone__message', {
          'text-danger': error,
          'text-muted': !error,
        })}
        id={`${id}-message`}
        role={error ? 'alert' : undefined}
      >
        {error || helperText}
      </div>
    </div>
  )
}
