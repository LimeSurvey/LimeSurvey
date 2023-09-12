import React from 'react'
import Image from 'react-bootstrap/Image'
import { FormCheck } from 'react-bootstrap'
import { DropZone } from 'components/UIComponents'

import NoImageFound from 'assets/images/no-image-found.jpg'

export const SingleChoiceImageAnswer = ({
  answer,
  isFocused,
  qid,
  onChange = () => {},
  isNoAnswer,
  value,
}) => {
  return (
    <div className="pe-4">
      <div className="mb-2 d-flex gap-2">
        {!isFocused && (
          <>
            <FormCheck
              type={'radio'}
              className="pointer-events-none"
              name={`${qid}-single-choice-image`}
              data-testid="single-choice-image-answer"
              label={isNoAnswer && 'No answer.'}
            />
            {!isNoAnswer && (
              <div className="border border-3 border-secondary rounded">
                <Image
                  src={value ? value : NoImageFound}
                  alt="Image Select List"
                  width={'200px'}
                  height={'150px'}
                  style={{
                    backgroundSize: 'cover',
                  }}
                />
              </div>
            )}
          </>
        )}
        {isFocused && (
          <DropZone
            onReaderResult={(result) => onChange(result)}
            image={answer.assessmentValue}
          />
        )}
      </div>
    </div>
  )
}
