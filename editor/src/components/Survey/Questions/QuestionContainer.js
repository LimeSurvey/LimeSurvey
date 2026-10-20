import React from 'react'
import classNames from 'classnames'

export const QuestionContainer = ({ questionImageObject, children }) => {
  const hasQuestionImage = questionImageObject.hasQuestionImage
  const hasImageAsBackground = questionImageObject.hasQuestionImageAsBackground

  // If no image, just render children
  if (!hasQuestionImage) {
    return children
  }

  // If image is used as background, use an actual img element instead of background-image
  if (hasImageAsBackground) {
    return (
      <div className="position-relative w-100 h-auto overflow-hidden p-0">
        {/* Container for the image */}
        <div className="position-relative w-100">
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
        <div className={'position-relative overflow-hidden w-100'}>
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
