import React, { useState } from 'react'
import { useDropzone } from 'react-dropzone'
import { cloneDeep } from 'lodash'

import { UploadIcon } from 'components/icons'
import { ImageEditor } from 'components/UIComponents/ImageEditor/ImageEditor'
import { FileRejectionItems } from './FileRejectionItems'
import { FileUploadItem } from './FileUploadItem'
import { FileUploadParticipantView } from './FileUploadParticipantView'
import categorizeFileTypes from 'helpers/categorizeFileTypes'

export const FileUpload = ({
  question,
  values = [],
  participantMode,
  onValueChange = () => {},
}) => {
  const [uploadedImages, setUploadedImages] = useState([])
  const [show, setShow] = useState(false)
  const [selectedIndex, setSelectedIndex] = useState(-1)
  const [selectedFile, setSelectedFile] = useState(null)
  const [files, setFiles] = useState(cloneDeep(values))

  const allowedFileTypes = question?.attributes?.allowedFileTypes?.value
  const accept = categorizeFileTypes(allowedFileTypes)
  const maxFileSize = question?.attributes?.maximumFileSizeAllowed?.value

  const handleClose = () => {
    setShow(false)
    setSelectedFile(null)
    setSelectedIndex(-1)
  }

  const handleChange = (file) => {
    setUploadedImages((prev) => {
      const updated = [...prev]
      updated[selectedIndex] = { ...file }
      return updated
    })
  }

  const handleDrop = (acceptedFiles) => {
    setUploadedImages((prev) => [
      ...prev,
      ...acceptedFiles.map((file) => ({
        ...file,
        origin: URL.createObjectURL(file),
        preview: URL.createObjectURL(file),
        zoom: 1,
        rotate: 0,
        radius: 0,
      })),
    ])
  }

  const { getRootProps, getInputProps, fileRejections } = useDropzone({
    onDrop: handleDrop,
    disabled: true,
    accept,
    maxFiles: 1,
    maxSize: maxFileSize ? maxFileSize * 1024 : 1024 * 1000000,
  })

  const handleDeleteImage = (index) => {
    setUploadedImages((prev) => {
      const updated = [...prev]
      updated.splice(index, 1)
      return updated
    })
  }

  const handleEditImage = (index) => {
    setSelectedIndex(index)
    setSelectedFile(uploadedImages[index])
    setShow(true)
  }

  const handleFileMetadataChange = (index, keyToUpdate, newValue) => {
    // Update metadata in uploadedImages for edit mode
    setUploadedImages((prev) => {
      const updated = [...prev]
      if (updated[index]) {
        updated[index] = { ...updated[index], [keyToUpdate]: newValue }
      }
      return updated
    })

    // Also update files state for participant mode
    setFiles((prev) => {
      const updated = [...prev]
      if (!updated[index]) {
        updated[index] = {}
      }
      updated[index] = { ...updated[index], [keyToUpdate]: newValue }

      // Call onValueChange with updated files
      const fileKey = updated[index].key
      if (fileKey) {
        onValueChange(JSON.stringify(updated), fileKey)
      }

      return updated
    })
  }

  if (participantMode) {
    return (
      <FileUploadParticipantView
        files={files}
        question={question}
        onValueChange={onValueChange}
      />
    )
  }

  return (
    <div className="disable-select" data-testid="file-upload">
      {uploadedImages.length > 0 &&
        uploadedImages.map((file, index) => (
          <FileUploadItem
            key={index}
            file={file}
            index={index}
            onEdit={handleEditImage}
            onDelete={handleDeleteImage}
            onMetadataChange={handleFileMetadataChange}
            question={question}
          />
        ))}
      <FileRejectionItems fileRejections={fileRejections} />

      <div
        style={{ cursor: 'pointer' }}
        {...getRootProps({ className: 'dropzone' })}
      >
        <input {...getInputProps()} />
        <div className="">
          <UploadIcon className="icon" />
        </div>
        <p className="label">{t('Choose file or drop image here')}</p>
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
