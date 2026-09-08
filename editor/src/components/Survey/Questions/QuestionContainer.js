import React from 'react'
import classNames from 'classnames'

import { ImageWrapper } from 'components/UIComponents'

export const QuestionContainer = ({ questionImageObject, children }) => (
  <ImageWrapper
    imageObject={questionImageObject}
    className="w-100 p-0"
    imageContainerClassName={classNames('image-container w-50', {
      'pe-3': questionImageObject.imageAlign === 'left',
      'ps-3': questionImageObject.imageAlign === 'right',
    })}
    contentContainerClassName="w-50"
    overlayClassName="background-image-overlay position-absolute top-0 start-0 end-0 bottom-0 p-3 z-1"
    imageTestId="question-image"
    backgroundImageTestId="question-background-image"
  >
    {children}
  </ImageWrapper>
)
