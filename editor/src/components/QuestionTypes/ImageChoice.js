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
import {
  ContentEditor,
  DropZone,
  FormCheck,
  ImageEditor,
} from 'components/UIComponents'
import { useFileService } from 'hooks'

export const ImageChoice = ({
  id,
  idPrefix,
  index,
  code = '',
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
  const [showImageEditor, setShowImageEditor] = useState(false)
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

  // Memoized: The image editor resets its state whenever this object changes.
  const imageEditorObject = useMemo(
    () => ({ imagePreviewUrl: previewUrl }),
    [previewUrl]
  )

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
          <div className="image-choice-preview border border-3 border-secondary rounded">
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
                onEdit={
                  previewUrl && isValidImg
                    ? () => setShowImageEditor(true)
                    : null
                }
                imageName={code}
              />
            </div>
          </div>
        )}
        {showImageEditor && (
          <ImageEditor
            showModal={showImageEditor}
            onClose={() => setShowImageEditor(false)}
            // The settings are not stored yet.
            onChange={() => {}}
            imageObject={imageEditorObject}
          />
        )}
        {isFocused && initLoading && !isNoAnswer && (
          <div style={{ width: 44, height: 44 }} className="loader ms-4"></div>
        )}
      </div>
    </div>
  )
}
