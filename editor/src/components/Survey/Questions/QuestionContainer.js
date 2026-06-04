import React, { useState } from 'react'
import classNames from 'classnames'
import { DeleteIcon } from '../../icons'
import { Button } from '../../UIComponents'
import { getClearedQuestionImageObject } from 'helpers/questionImage'
import { createBufferOperation } from 'helpers'
import { useBuffer } from 'hooks'

export const QuestionContainer = ({
  questionImageObject,
  children,
  update,
  qid,
}) => {
  const [showDeleteIcon, setShowDeleteIcon] = useState(false)
  const { addToBuffer } = useBuffer()

  const hasQuestionImage = questionImageObject.hasQuestionImage
  const hasImageAsBackground = questionImageObject.hasQuestionImageAsBackground

  const handleDeleteImage = () => {
    const clearedImageData = {
      ['image']: {
        ['']: JSON.stringify(getClearedQuestionImageObject()),
      },
    }

    const operation = createBufferOperation(qid)
      .questionAttribute()
      .update(clearedImageData)

    update(clearedImageData)
    addToBuffer(operation)
  }

  // If no image, just render children
  if (!hasQuestionImage) {
    return children
  }

  // If image is used as background, use an actual img element instead of background-image
  if (hasImageAsBackground) {
    return (
      <div className="position-relative w-100 h-auto overflow-hidden p-0">
        {/* Container for the image */}
        <div
          className="position-relative w-100"
          onMouseEnter={() => setShowDeleteIcon(true)}
          onMouseLeave={() => setShowDeleteIcon(false)}
        >
          {handleDeleteImage && (
            <Button
              onClick={handleDeleteImage}
              variant="light"
              className={`position-absolute bottom-0 end-0 m-4 z-2 ${showDeleteIcon ? '' : 'd-none'}`}
            >
              <DeleteIcon className="fill-current" />
            </Button>
          )}
          <img
            className={'w-100 h-auto d-block'}
            src={questionImageObject.imagePreviewUrl}
            alt={questionImageObject.imageAltText || ''}
            style={questionImageObject.imageStyles}
            data-testid="question-background-image"
          />

          {/* Content overlay */}
          <div className="background-image-overlay position-absolute top-0 start-0 end-0 bottom-0 p-3 z-1">
            {children}
          </div>
        </div>
      </div>
    )
  }

  // If image exists and not used as background, render flex layout
  return (
    <div
      className={classNames('d-flex flex-row', {
        'flex-row-reverse': questionImageObject.imageAlign === 'right',
      })}
    >
      <div
        className={classNames('image-container w-50', {
          'pe-3': questionImageObject.imageAlign === 'left',
          'ps-3': questionImageObject.imageAlign === 'right',
        })}
      >
        <div
          className={'position-relative overflow-hidden w-100'}
          onMouseEnter={() => setShowDeleteIcon(true)}
          onMouseLeave={() => setShowDeleteIcon(false)}
        >
          {handleDeleteImage && (
            <Button
              onClick={handleDeleteImage}
              variant="light"
              className={`position-absolute bottom-0 end-0 m-4 z-2 ${showDeleteIcon ? '' : 'd-none'}`}
            >
              <DeleteIcon className="fill-current" />
            </Button>
          )}
          <img
            className={'w-100 h-auto d-block'}
            src={questionImageObject.imagePreviewUrl}
            alt={questionImageObject.imageAltText || ''}
            style={questionImageObject.imageStyles}
            data-testid="question-image"
          />
        </div>
      </div>
      <div className="w-50">{children}</div>
    </div>
  )
}
