import React, { useEffect, useState } from 'react'

import { useDropzone } from 'react-dropzone'
import { Button, Form } from 'react-bootstrap'
import categorizeFileTypes from 'helpers/categorizeFileTypes'
import { DeleteIcon, UploadIcon, EditIcon } from 'components/icons'
import { ImageEditor } from 'components/UIComponents/ImageEditor/ImageEditor'
import { FileExceedItems } from './FileExceedItems'
import { FileRejectionItems } from './FileRejectionItems'

export const FileUpload = ({ question, handleUpdate }) => {
  const [uploadedImages, setUploadedImages] = useState([])
  const [show, setShow] = useState(false)
  const [selectedIndex, setSelectedIndex] = useState(-1)
  const [selectedFile, setSelectedFile] = useState(null)
  const [exceededMaxFiles, setExceededMaxFiles] = useState(false)

  const allowedFileTypes = question?.attributes?.allowedFileTypes?.value
  const accept = categorizeFileTypes(allowedFileTypes)
  const maxFileSize = question?.attributes?.maximumFileSizeAllowed?.value
  const minFiles = question.attributes?.minNumberFiles?.value || 1
  const maxFiles = question?.attributes?.maxNumberFiles?.value || 100

  const handleClose = () => setShow(false)
  const handleShow = () => setShow(true)

  const handleChange = (file) => {
    uploadedImages[selectedIndex] = { ...file }
    setUploadedImages([...uploadedImages])
  }

  const { getRootProps, getInputProps, fileRejections, acceptedFiles } =
    useDropzone({
      onDrop: (acceptedFiles) => {
        if (uploadedImages.length >= maxFiles) {
          setExceededMaxFiles(true)
          return
        }
        setUploadedImages((prev) => [
          ...prev,
          ...acceptedFiles.map((file) => ({
            ...file,
            origin: URL.createObjectURL(file),
            preview: URL.createObjectURL(file),
            zoom: [1],
            rotate: [0],
            radius: [0],
          })),
        ])
        setExceededMaxFiles(false)
      },
      accept,
      maxFiles: 1,
      maxSize: maxFileSize ? maxFileSize * 1024 : 1024 * 1000000,
    })

  useEffect(() => {
    if (uploadedImages.length < maxFiles) {
      setExceededMaxFiles(false)
      return
    }
  }, [uploadedImages.length, maxFiles])

  const handleDeleteImage = (index) => {
    uploadedImages.splice(index, 1)
    setUploadedImages([...uploadedImages])
  }
  const handleEditImage = (index) => {
    handleShow(true)
    setSelectedFile(uploadedImages[index])
    setSelectedIndex(index)
  }

  return (
    <div className="">
      {uploadedImages.length > 0 &&
        uploadedImages?.map((file, index) => {
          return (
            <div className="mb-3 ">
              <div
                className="position-relative image-wrapper"
                style={{
                  maxWidth: '300px',
                  background: 'rgba(0, 0, 0, 0.05)',
                }}
              >
                <img
                  key={index}
                  src={file.preview}
                  alt={file.name}
                  className="bg-light"
                  style={{
                    width: '100%',
                    minHeight: '100px',
                    borderRadius: `${file.radius * 1.5}px`,
                  }}
                />
                <div className="position-absolute image-handle-btn-wrapper">
                  <Button
                    variant="outline-light"
                    className="image-handle-btn ms-1"
                    size="sm"
                    onClick={() => handleEditImage(index)}
                  >
                    <EditIcon className="text-primary fill-current" />
                  </Button>
                  <Button
                    variant="outline-light"
                    className="image-handle-btn ms-1"
                    size="sm"
                    onClick={() => handleDeleteImage(index)}
                  >
                    <DeleteIcon className="text-primary fill-current" />
                  </Button>
                </div>
              </div>

              {question.attributes.showTitle?.value && (
                <div>
                  <Form.Label>Title</Form.Label>
                  <Form.Control
                    placeholder="Enter your answer here."
                    data-testid="text-question-answer-input"
                  />
                </div>
              )}
              {question.attributes.showComment?.value && (
                <div className="mt-1">
                  <Form.Label>Comment</Form.Label>
                  <Form.Control
                    placeholder="Enter your answer here."
                    as="textarea"
                    rows={4}
                    maxLength={Infinity}
                    data-testid="text-question-answer-input"
                  />
                </div>
              )}
            </div>
          )
        })}
      <FileRejectionItems fileRejections={fileRejections} />
      {/*  as there is no minFiles option for library, added custom validation */}
      {(uploadedImages.length < minFiles || exceededMaxFiles) && (
        <FileExceedItems
          files={acceptedFiles}
          minFiles={minFiles}
          maxFiles={maxFiles}
          exceededMaxFiles={exceededMaxFiles}
        />
      )}
      <div
        style={{ cursor: 'pointer' }}
        {...getRootProps({ className: 'dropzone' })}
      >
        <input {...getInputProps()} />
        <div className="">
          <UploadIcon className="icon" />
        </div>
        <p className="label">Click or drop a file here</p>
      </div>
      {selectedFile && (
        <ImageEditor
          showModal={show}
          onClose={handleClose}
          onChange={handleChange}
          file={selectedFile}
        />
      )}
    </div>
  )
}
