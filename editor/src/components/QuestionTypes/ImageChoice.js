import React, { useMemo, useState, useEffect } from 'react'
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
  EditedImage,
  FormCheck,
  ImageEditor,
} from 'components/UIComponents'
import { useFileService } from 'hooks'

/**
 * One option of the image select themes: The image with its upload drop zone,
 * and in edit mode the image editor modal.
 *
 * @param {number|string} id ID of the answer or subquestion
 * @param {string} idPrefix prefix for element IDs ('a' answers, 'q' subquestions)
 * @param {number} index position of the option
 * @param {string} code the option code, used as image name
 * @param {string} inputType 'checkbox' or 'radio'
 * @param {boolean} isFocused whether the question is in edit mode
 * @param {Function} update called with the new image path, or the checked state
 * @param {string} value the image path
 * @param {boolean} isNoAnswer whether this is the "No answer" option
 * @param {string} groupName name of the radio group
 * @param {boolean|null} checked controlled checked state
 * @param {boolean} defaultChecked initial checked state
 * @param {Object} errors errors of the option
 * @param {Function} setErrors called with the updated errors
 * @param {Object|null} imageSettings saved image editor settings of the option,
 *   the image is shown with them in view and edit mode
 * @param {string} imageAltText saved alt text in the current language
 * @param {Function} onImageSettingsSave called with the image editor settings,
 *   including the alt text, when they are saved
 * @returns {JSX.Element} the option
 */
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
  imageSettings = null,
  imageAltText = '',
  onImageSettingsSave = () => {},
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

  const handleImageEditorSave = (settings) => {
    onImageSettingsSave(settings)
    setShowImageEditor(false)
  }

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
    // The option and its image may shrink to the space they get, so that the
    // image never gets wider than the question.
    <div
      className="pe-4"
      style={{ minWidth: 0, maxWidth: '100%', flexShrink: 1 }}
    >
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
          <div
            className="image-choice-preview border border-3 border-secondary rounded"
            style={{ minWidth: 0, maxWidth: '100%' }}
          >
            <div className={classNames({ 'd-none': showLoader })}>
              <EditedImage
                src={previewUrl && isValidImg ? previewUrl : NoImageFound}
                alt={imageAltText || 'Image Select List'}
                settings={previewUrl && isValidImg ? imageSettings : null}
                height={IMAGE_PREVIEW_HEIGHT}
                fallbackStyle={{
                  height: '100%',
                  maxHeight: IMAGE_PREVIEW_HEIGHT,
                  maxWidth: '100%',
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
                imageSettings={imageSettings}
                imageAlt={imageAltText || 'Image Select List'}
              />
            </div>
          </div>
        )}
        {showImageEditor && (
          <ImageEditor
            showModal={showImageEditor}
            onClose={() => setShowImageEditor(false)}
            onSave={handleImageEditorSave}
            imageUrl={previewUrl}
            settings={imageSettings}
            altText={imageAltText}
          />
        )}
        {isFocused && initLoading && !isNoAnswer && (
          <div style={{ width: 44, height: 44 }} className="loader ms-4"></div>
        )}
      </div>
    </div>
  )
}
