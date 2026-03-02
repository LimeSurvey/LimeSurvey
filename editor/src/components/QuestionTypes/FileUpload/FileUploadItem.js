import React from 'react'
import { Button, Form } from 'react-bootstrap'
import { DeleteIcon, EditIcon } from 'components/icons'

export const FileUploadItem = ({
  file,
  index,
  onEdit,
  onDelete,
  onMetadataChange,
  question,
}) => {
  const showTitle = question?.attributes?.showTitle?.value
  const showComment = question?.attributes?.showComment?.value

  // Get file metadata from file object
  const fileTitle = file.title || ''
  const fileComment = file.comment || ''

  const handleTitleChange = (e) => {
    onMetadataChange(index, 'title', e.target.value)
  }

  const handleCommentChange = (e) => {
    onMetadataChange(index, 'comment', e.target.value)
  }

  return (
    <div className="mb-3">
      <div
        className="position-relative image-wrapper"
        style={{
          maxWidth: '300px',
          background: 'rgba(0, 0, 0, 0.05)',
        }}
      >
        <img
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
            onClick={() => onEdit(index)}
          >
            <EditIcon className="text-primary fill-current" />
          </Button>
          <Button
            variant="outline-light"
            className="image-handle-btn ms-1"
            size="sm"
            onClick={() => onDelete(index)}
          >
            <DeleteIcon className="text-primary fill-current" />
          </Button>
        </div>
      </div>

      {showTitle && (
        <div>
          <Form.Label>{t('Title')}</Form.Label>
          <Form.Control
            placeholder={st('Enter your answer here')}
            data-testid="text-question-answer-input"
            value={fileTitle}
            onChange={handleTitleChange}
          />
        </div>
      )}
      {showComment && (
        <div className="mt-1">
          <Form.Label>{st('Comment')}</Form.Label>
          <Form.Control
            placeholder={st('Enter your answer here')}
            as="textarea"
            rows={4}
            maxLength={Infinity}
            data-testid="text-question-answer-input"
            value={fileComment}
            onChange={handleCommentChange}
          />
        </div>
      )}
    </div>
  )
}
