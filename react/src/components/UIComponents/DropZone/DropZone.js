import React, { useCallback, useEffect } from 'react'
import { useDropzone } from 'react-dropzone'
import { Form } from 'react-bootstrap'
import { UploadIcon } from 'components/icons'

export const DropZone = ({
  onReaderResult = () => {},
  image,
  labelText = '',
}) => {
  const [styleWithImage, setStyleWithImage] = React.useState({})

  // should be updated like file upload editor
  const onDrop = useCallback(
    (acceptedFiles) => {
      acceptedFiles.forEach((file) => {
        Object.assign(file, {
          preview: URL.createObjectURL(file),
        })

        onReaderResult({
          origin: URL.createObjectURL(file),
          preview: URL.createObjectURL(file),
        })
        setStyleWithImage({
          backgroundImage: `url(${file.preview})`,
          backgroundRepeat: 'no-repeat',
          backgroundSize: 'cover',
          backgroundPosition: 'center',
        })
      })
    },
    [onReaderResult]
  )

  const { getRootProps, getInputProps } = useDropzone({
    onDrop,
  })

  useEffect(() => {
    setStyleWithImage(
      image
        ? {
            backgroundImage: `url(${image})`,
            backgroundRepeat: 'no-repeat',
            backgroundSize: 'cover',
            backgroundPosition: 'center',
          }
        : {}
    )
  }, [image])

  return (
    <div style={{ cursor: 'pointer' }}>
      {labelText.length ? <Form.Label>{labelText}</Form.Label> : <></>}
      <div style={styleWithImage} {...getRootProps({ className: 'dropzone' })}>
        <input {...getInputProps()} />
        <div className="">
          <UploadIcon className="icon" />
        </div>
        <p className="label">Click or drop a file here</p>
      </div>
    </div>
  )
}
