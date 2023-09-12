import classNames from 'classnames'
import { useEffect, useRef, useState } from 'react'
import { Button } from 'react-bootstrap'

import { useFocused } from 'hooks'
import { IsTrue, ScrollToElement } from 'helpers'
import {
  ArrowDownIcon,
  ArrowUpIcon,
  DeleteIcon,
  EditIcon,
} from 'components/icons'
import { ImageEditor } from 'components/UIComponents'

import { QuestionHeader } from './QuestionHeader'
import { QuestionBody } from './QuestionBody'
import { QuestionFooter } from './QuestionFooter'

export const Question = ({
  language,
  question,
  handleRemove,
  handleDuplicate,
  update,
  questionNumber,
  groupIndex,
  questionIndex,
  lastQuestionIndex,
  questionGroupIsOpen,
  handleSwapQuestionPosition,
}) => {
  const questionRef = useRef(null)
  const { focused = {}, setFocused } = useFocused()
  const [isHovered, setIsHovered] = useState(false)

  const [rgba, setRgba] = useState('')
  const [, setHasErrors] = useState(false)
  const [show, setShow] = useState(false)
  const [selectedFile, setSelectedFile] = useState(null)

  const handleClose = () => setShow(false)

  const handleChange = (file) => {
    const attributes = {
      image: file,
    }
    handleUpdate({ attributes })
  }
  const handleEditImage = () => {
    setShow(true)
    setSelectedFile({
      ...{
        ...question.attributes.image,
        origin: question.attributes.image.origin
          ? question.attributes.image.origin
          : question.attributes.image.preview,
        preview: question.attributes.image.preview,
        zoom: [1],
        rotate: [0],
        radius: [0],
      },
    })
  }

  const handleFocusQuestion = () => {
    const questionIsNotFocused = question.qid !== focused.qid
    if (questionIsNotFocused) {
      setFocused(question, groupIndex, questionIndex)
    }
  }

  const handleOnErrors = (errors) => setHasErrors(errors)

  useEffect(() => {
    const questionIsNotFocused = focused.qid !== question.qid
    if (questionIsNotFocused) {
      return
    }

    if (questionGroupIsOpen) {
      ScrollToElement(questionRef.current)
    }

    if (question.tempFocusTitle) {
      setTimeout(() => {
        delete question.tempFocusTitle
        update({ ...question })
      }, 1000)
    }

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [focused, question.qid])

  useEffect(() => {
    const imageBrightness = question.attributes?.imageBrightness || 0
    const rgba =
      imageBrightness > 0
        ? `rgba(255, 255, 255, ${imageBrightness / 100})`
        : `rgba(0, 0, 0, ${-imageBrightness / 100})`

    setRgba(rgba)
  }, [question.attributes?.imageBrightness])

  const handleUpdate = (change) => {
    const updateQuestion = {
      ...question,
      ...change,
    }

    update(updateQuestion)
  }

  const handleDeleteImage = () => {
    const attributes = {
      image: '',
    }
    handleUpdate({ attributes })
  }

  if (!question.qid) {
    return <></>
  }

  return (
    <div
      onClick={handleFocusQuestion}
      id={`${question.qid}-question`}
      className={classNames(
        'question d-flex position-relative',
        question.attributes?.cssclass?.value,
        {
          'focus-element': focused.qid === question.qid,
          'opacity-25': IsTrue(question.attributes?.hide_question?.value),
          'flex-row-reverse': question.attributes?.imageAlign === 'right',
        }
      )}
      key={`question-${question.qid}`}
      ref={questionRef}
      style={{
        backgroundImage: `linear-gradient(${rgba}, ${rgba}), url(${
          question.attributes?.image &&
          question.attributes?.imageAlign === 'center' &&
          question.attributes?.image
        })`,
        backgroundSize: 'cover',
        backgroundPosition: 'center',
        backgroundRepeat: 'no-repeat',
      }}
    >
      {focused.qid === question.qid && (
        <div className="position-absolute question-scroll">
          <div>
            <Button
              variant="secondary"
              onClick={() => handleSwapQuestionPosition(-1)}
              size="sm"
              disabled={questionIndex === 0}
              className="question-scroll-button"
            >
              <ArrowUpIcon className="text-white fill-current" />
            </Button>
          </div>
          <div className="mt-1">
            <Button
              onClick={() => handleSwapQuestionPosition(+1)}
              variant="secondary"
              size="sm"
              disabled={questionIndex === lastQuestionIndex}
              className="question-scroll-button"
            >
              <ArrowDownIcon className="text-white fill-current" />
            </Button>
          </div>
        </div>
      )}
      {question.attributes?.image &&
        question.attributes?.imageAlign !== 'center' && (
          <div className="position-relative image-wrapper">
            <img
              src={question.attributes.image.preview}
              alt="question attributes"
              width="100%"
              height="100%"
              style={{
                borderRadius: `${question.attributes?.image?.radius * 2}px`,
              }}
            />
            <div className="position-absolute image-handle-btn-wrapper">
              <Button
                variant="outline-light"
                className="image-handle-btn ms-1"
                size="sm"
                onClick={handleEditImage}
              >
                <EditIcon className="text-primary fill-current" />
              </Button>
              <Button
                variant="outline-light"
                className="image-handle-btn ms-1"
                size="sm"
                onClick={handleDeleteImage}
              >
                <DeleteIcon className="text-primary fill-current" />
              </Button>
            </div>
          </div>
        )}
      <div
        className={classNames(
          'w-100 d-flex flex-column justify-content-between',
          {
            'w-50': question.attributes?.image,
          }
        )}
        onMouseEnter={() => setIsHovered(true)}
        onMouseLeave={() => setIsHovered(false)}
      >
        <div>
          <QuestionHeader
            handleUpdate={handleUpdate}
            language={language}
            question={question}
            questionNumber={questionNumber}
            onError={(errors) => handleOnErrors(errors)}
            isFocused={focused.qid === question.qid}
          />
          <QuestionBody
            language={language}
            question={question}
            handleUpdate={handleUpdate}
            questionNumber={questionNumber}
            isFocused={focused.qid === question.qid}
            isHovered={isHovered}
          />
        </div>
        <div>
          <QuestionFooter
            question={question}
            isFocused={focused.qid === question.qid}
            handleUpdate={handleUpdate}
            handleRemove={handleRemove}
            handleDuplicate={handleDuplicate}
          />
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
    </div>
  )
}
