import React, {
  useMemo,
  useState,
  useEffect,
  useLayoutEffect,
  useRef,
} from 'react'
import { Form, Image } from 'react-bootstrap'
import classNames from 'classnames'

import {
  isTempId,
  IMAGE_PREVIEW_HEIGHT,
  getNoAnswerLabel,
  docUrls,
  getImageChoiceSrc,
  getImageChoiceAlt,
  setImageChoiceImage,
} from 'helpers'
import NoImageFound from 'assets/images/no-image-found.jpg'
import { ContentEditor, DropZone, FormCheck } from 'components/UIComponents'
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
  groupName,
  checked = null,
  defaultChecked = false,
  errors,
  setErrors = () => {},
}) => {
  const [isLoading, setIsLoading] = useState(false)
  const { fileService } = useFileService()
  // The choice text holds the image as HTML (or as a bare image URL in older surveys)
  const imageSrc = useMemo(() => getImageChoiceSrc(value), [value])
  const imageAlt = useMemo(() => getImageChoiceAlt(value), [value])
  // An upload finishes after later renders: Use the latest text and update
  // callback then, so changes made meanwhile (e.g. alternative texts) are kept
  const latest = useRef({ value, update })
  useLayoutEffect(() => {
    latest.current = { value, update }
  })
  // REACT_APP_SITE_URL is needed in development environments
  // - because the react-app runs on a different port to the core app
  const getPreviewUrl = (src) =>
    src && src.startsWith('/')
      ? (process.env.REACT_APP_SITE_URL || '') + src
      : src
  const [previewUrl, setPreviewUrl] = useState(getPreviewUrl(imageSrc))

  const onChange = (filePath) => {
    const { value: latestValue, update: latestUpdate } = latest.current
    latestUpdate(
      filePath
        ? setImageChoiceImage(latestValue, { src: encodeURI(filePath) })
        : ''
    )
  }

  const onChangeAlt = (event) => {
    update(setImageChoiceImage(value, { alt: event.target.value }))
  }

  const onChangePreview = (previewUrl) => {
    setPreviewUrl(previewUrl)
  }

  const initLoading = useMemo(() => isTempId(id), [id])

  const isValidImg = !value || imageSrc !== null

  const idSuffix = idPrefix + id + '-i' + index

  const showLoader = (isFocused || isLoading) && !initLoading

  useEffect(() => {
    setPreviewUrl(getPreviewUrl(imageSrc))
  }, [imageSrc])

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
            name={'image-choice-' + idSuffix}
            data-testid={'image-choice-' + idSuffix}
            checked={checked}
            defaultChecked={defaultChecked}
            groupName={groupName}
            update={(value) => update(value)}
            label={
              <ContentEditor
                className="choice"
                value={isNoAnswer ? getNoAnswerLabel(true) : ''}
                disabled={true}
              />
            }
          />
        )}
        {isFocused && <div>&nbsp;</div>}
        {!isNoAnswer && !initLoading && (
          <div className="border border-3 border-secondary rounded">
            <div className={classNames({ 'd-none': showLoader })}>
              <Image
                src={previewUrl && isValidImg ? previewUrl : NoImageFound}
                alt={imageAlt || 'Image Select List'}
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
      {isFocused && !isNoAnswer && !initLoading && imageSrc && (
        <Form.Group className="mt-2" controlId={'image-choice-alt-' + idSuffix}>
          <Form.Label className="mb-1">{t('Alternative text')}</Form.Label>
          <Form.Control
            type="text"
            value={imageAlt}
            onChange={onChangeAlt}
            aria-describedby={'image-choice-alt-help-' + idSuffix}
            data-testid={'image-choice-alt-' + idSuffix}
          />
          <Form.Text id={'image-choice-alt-help-' + idSuffix}>
            {t('Describes the image for participants who cannot see it.')}
          </Form.Text>
        </Form.Group>
      )}
    </div>
  )
}
