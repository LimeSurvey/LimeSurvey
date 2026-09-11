import React from 'react'
import { Button } from 'react-bootstrap'
import { Input } from 'components/UIComponents'

export const FileUploadParticipantView = ({
  files,
  question,
  onValueChange,
}) => {
  const handleOnValueChange = (newValue, keyToUpdate, index) => {
    const updatedFiles = [...files]
    updatedFiles[index] = { ...updatedFiles[index], [keyToUpdate]: newValue }
    onValueChange(JSON.stringify(updatedFiles), files[index].key)
  }

  if (!files.length) {
    return (
      <>
        <h1>{t('No files uploaded')}</h1>
      </>
    )
  }

  return (
    <>
      {files?.map((file, index) => {
        return (
          <div className="w-50" key={`${file.size}-${index}`}>
            <div className="d-flex justify-content-between my-2 align-items-center">
              {t('File name')}:
              <Input
                update={(value) => handleOnValueChange(value, 'name', index)}
                className="w-75"
                value={file.name}
              />
            </div>
            <div className="d-flex justify-content-between my-2 align-items-center">
              {t('Title')}:
              <Input
                update={(value) => handleOnValueChange(value, 'title', index)}
                className="w-75"
                value={file.title}
              />
            </div>
            <div className="d-flex justify-content-between my-2 align-items-center">
              {t('Comment')}:
              <Input
                update={(value) => handleOnValueChange(value, 'comment', index)}
                className="w-75"
                value={file.comment}
              />
            </div>
            <Button
              disabled={file.isDeleted}
              href={`${location.protocol}//${location.hostname}/responses/downloadfile?surveyId=${question.sid}&responseId=${file.responseId}&qid=${question.qid}&index=${index}`}
              target="_blank"
              rel="noreferrer"
              variant={file.isDeleted ? 'outline-danger' : 'outline-info'}
            >
              {file.isDeleted ? (
                'File has been deleted'
              ) : (
                <>
                  {t('Download file')} {Math.max(file.approxFileSizeInMB, 0.1)}
                  {t('MB')}
                </>
              )}
            </Button>
            {index !== files.length - 1 && <hr />}
          </div>
        )
      })}
    </>
  )
}
