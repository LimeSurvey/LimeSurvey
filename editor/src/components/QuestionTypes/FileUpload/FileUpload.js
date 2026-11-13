import React, { useState } from 'react'
import { useDropzone } from 'react-dropzone'
import { cloneDeep } from 'lodash'

import { UploadIcon } from 'components/icons'
import { FileRejectionItems } from './FileRejectionItems'
import { FileUploadItem } from './FileUploadItem'
import { FileUploadParticipantView } from './FileUploadParticipantView'
import categorizeFileTypes from 'helpers/categorizeFileTypes'

/**
 * File upload question: The uploaded files with their title and comment, or
 * the participant view. The files can't be edited with the image editor: It
 * only supports the options of the image select themes.
 *
 * @param {Object} question the question
 * @param {Object[]} values the uploaded files of the participant
 * @param {boolean} participantMode whether the question is answered by a
 *   participant
 * @param {Function} onValueChange called with the files as JSON and their key
 * @returns {JSX.Element} the question
 */
export const FileUpload = ({
  question,
  values = [],
  participantMode,
  onValueChange = () => {},
}) => {
  const [uploadedImages, setUploadedImages] = useState([])
  const [files, setFiles] = useState(cloneDeep(values))

  const allowedFileTypes = question?.attributes?.allowedFileTypes?.value
  const accept = categorizeFileTypes(allowedFileTypes)
  const maxFileSize = question?.attributes?.maximumFileSizeAllowed?.value

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
    </div>
  )
}
