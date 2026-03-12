import React, { useMemo, useState, useEffect } from 'react'
import { Image } from 'react-bootstrap'
import classNames from 'classnames'

import {
  isTempId,
  IMAGE_PREVIEW_HEIGHT,
  getNoAnswerLabel,
  docUrls,
} from 'helpers'
import NoImageFound from 'assets/images/no-image-found.jpg'
import { DropZone, FormCheck } from 'components/UIComponents'
import { useFileService } from 'hooks'

export const ImageChoice = ({
  id,
  idPrefix,
  index,
  inputType = 'checkbox',
  isFocused,
  update,
  value,
  isNoAnswer,
  errors,
  setErrors = () => {},
}) => {
  const [isLoading, setIsLoading] = useState(false)
  const { fileService } = useFileService()
  // REACT_APP_SITE_URL is needed in development environments
  // - because the react-app runs on a different port to the core app
  const [previewUrl, setPreviewUrl] = useState(
    value ? process.env.REACT_APP_SITE_URL + value : null
  )

  const onChange = (filePath) => {
    update(encodeURI(filePath))
  }
  const onChangePreview = (previewUrl) => {
    setPreviewUrl(previewUrl)
  }

  const initLoading = useMemo(() => isTempId(id), [id])

  const regexContainsHtml = /(<([^>]+)>)/i
  const isValidImg = useMemo(() => !regexContainsHtml.test(value), [value])

  const idSuffix = idPrefix + id + '-i' + index

  const showLoader = (isFocused || isLoading) && !initLoading

  useEffect(() => {
    setPreviewUrl(value ? process.env.REACT_APP_SITE_URL + value : null)
  }, [value])

  useEffect(() => {
    let errorMessage = null
    if (!isValidImg && !isNoAnswer) {
      errorMessage = (
        <div className="error-message">
          <a
            href={docUrls.imageQuestionHtmlImageValue}
            target="_blank"
            rel="noreferrer"
          >
            {t('This image is not valid. Click here for more info.')}
          </a>
        </div>
      )
    }
    setErrors({ invalidImage: errorMessage, ...errors })
  }, [isValidImg, isNoAnswer])

  return (
    <div className="pe-4">
      <div className="d-flex gap-2">
        {!isFocused && (
          <FormCheck
            type={inputType}
            className="pointer-events-none"
            name={'image-choice-' + idSuffix}
            data-testid={'image-choice-' + idSuffix}
            label={isNoAnswer ? getNoAnswerLabel(true) : ''}
          />
        )}
        {isFocused && <div>&nbsp;</div>}
        {!isNoAnswer && !initLoading && (
          <div className="border border-3 border-secondary rounded">
            <div className={classNames({ 'd-none': showLoader })}>
              <Image
                src={previewUrl && isValidImg ? previewUrl : NoImageFound}
                alt="Image Select List"
                height={'100%'}
                style={{
                  maxHeight: IMAGE_PREVIEW_HEIGHT,
                  backgroundSize: 'cover',
                }}
              />
            </div>
            <div className={classNames({ 'd-none': !showLoader })}>
              <DropZone
                dataTestId={'dropzone-' + idSuffix}
                previewUrlInit={previewUrl}
                fileService={fileService}
                previewMaxHeight={IMAGE_PREVIEW_HEIGHT}
                onChange={onChange}
                onChangePreview={onChangePreview}
                isValidImg={isValidImg}
                onLoading={(value) => setIsLoading(value)}
              />
            </div>
          </div>
        )}
        {isFocused && initLoading && !isNoAnswer && (
          <div style={{ width: 44, height: 44 }} className="loader ms-4"></div>
        )}
      </div>
    </div>
  )
}
