import React, { useEffect, useRef, useState } from 'react'
import { Button, Form } from 'react-bootstrap'
import { Direction } from 'react-range'

import {
  AlignButtons,
  DropZone,
  ImageEditor,
  InputRange,
  ToggleButtons,
} from 'components/UIComponents'
import { DeleteIconFilled, EditIcon } from 'components/icons'
import { useFileService } from 'hooks'
import {
  getQuestionImageObjectFromImageAttribute,
  getAndGenerateImageStyles,
  getClearedQuestionImageObject,
} from 'helpers/questionImage'
import {getYesNoOptions, isTrue} from "helpers"

export const ImageAttributes = ({
  update,
  value = {},
  isSimpleSettings = false,
  disabled = false,
}) => {
  const charLimit = 125
  const [show, setShow] = useState(false)
  const [remainingChars, setRemainingChars] = useState(charLimit) // Init with all remaining.
  const [imageState, setImageState] = useState({})
  const [previewUrl, setPreviewUrl] = useState(null)
  const [forceUpdateKey, setForceUpdateKey] = useState(0)
  const [showAltText, setShowAltText] = useState(false)
  const dropzoneRef = useRef(null)
  const { fileService } = useFileService()

  // Initialize or update image state when value changes
  useEffect(() => {
    const imageObject = getQuestionImageObjectFromImageAttribute(value)
    setImageState(imageObject)
    setRemainingChars(charLimit - (imageObject.imageAltText?.length || 0))
    setShowAltText(!!imageObject.imageAltText)

    // Reset preview URL when the question/value changes
    if (imageObject && imageObject.imagePath) {
      setPreviewUrl(imageObject.imagePreviewUrl)
    } else {
      setPreviewUrl(null)
    }
  }, [value])

  // Only update the key when the image path changes when switching questions
  useEffect(() => {
    if (imageState.imagePath) {
      setForceUpdateKey((prev) => prev + 1)
    }
  }, [imageState.imagePath])

  const updateImageState = (changes) => {
    // Create new state with changes
    const newImageState = {
      ...imageState,
      ...changes,
    }

    // Generate styles if needed
    if (
      changes.imageBrightness !== undefined ||
      changes.imageRadius !== undefined
    ) {
      newImageState.imageStyles = getAndGenerateImageStyles(newImageState)
    }
    // Update component state
    setImageState(newImageState)

    // Create object for saving to backend
    const saveObject = {
      image_path: newImageState.imagePath || '',
      image_align: newImageState.imageAlign || 'left',
      image_brightness: newImageState.imageBrightness || 0,
      image_radius: newImageState.imageRadius || 0,
      image_alt_text: newImageState.imageAltText || '',
      image_styles: getAndGenerateImageStyles(newImageState, true),
    }

    // Save to backend
    update(JSON.stringify(saveObject))
  }

  const handleImageChange = (imagePath) => {
    const encodedPath = encodeURI(imagePath)
    updateImageState({ imagePath: encodedPath })
  }

  const handleAlignChange = (alignValue) => {
    updateImageState({ imageAlign: alignValue })
  }

  const handleBrightnessChange = (brightnessValue) => {
    updateImageState({ imageBrightness: brightnessValue[0] })
  }

  const handleRadiusChange = (radiusValue) => {
    updateImageState({ imageRadius: radiusValue[0] })
  }

  const handleAltTextChange = (altTextValue) => {
    updateImageState({ imageAltText: altTextValue })
  }

  const handleAltTextToggle = (toggleValue) => {
    const isYes = isTrue(toggleValue)
    setShowAltText(isYes)
    if (!isYes) {
      setRemainingChars(charLimit)
      handleAltTextChange('')
    }
  }

  const onChangePreview = (previewUrl) => {
    setPreviewUrl(previewUrl)
  }

  const handleDeleteImage = () => {
    updateImageState(getClearedQuestionImageObject())
    setPreviewUrl(null)
  }

  const handleEditModalSave = (imageObject) => {
    updateImageState({
      imageZoom: imageObject.imageZoom,
      imageRotate: imageObject.imageRotate,
      imageRadius: imageObject.imageRadius,
      imagePositionX: imageObject.imagePositionX,
      imagePositionY: imageObject.imagePositionY,
      imageStyles: imageObject.imageStyles,
    })
  }

  const handleClose = () => setShow(false)

  return (
    <>
      <div className="mb-3">
        {isSimpleSettings && <hr className="mb-3" />}
        <DropZone
          ref={dropzoneRef}
          key={`dropzone-${previewUrl ? 'with-image' : 'empty'}`}
          previewUrlInit={previewUrl}
          onChangePreview={onChangePreview}
          fileService={fileService}
          onChange={handleImageChange}
          labelText={t('Background image')}
          image={previewUrl}
          dataTestId="add-image-or-video"
          trashIconEnabled={false}
          disabled={disabled}
        />

        {previewUrl && (
          <>
            <div
              className="mt-2 d-flex justify-content-end"
              data-testid="image-or-video-edit-delete"
            >
              <Button
                className="ms-2 btn-sm-sidebar"
                onClick={() => dropzoneRef.current?.open()}
                variant="secondary"
                aria-label={t('Replace image')}
              >
                <EditIcon className="fill-current" width={16} height={16} />
              </Button>
              <Button
                onClick={handleDeleteImage}
                variant="secondary"
                className="ms-2 btn-sm-sidebar"
                aria-label={t('Delete image')}
              >
                <DeleteIconFilled width={16} height={16} />
              </Button>
            </div>
            <div className={'qe-input-group multi-settings'}>
              <AlignButtons
                update={handleAlignChange}
                labelText={t('Alignment')}
                value={imageState.imageAlign}
              />
            </div>
            <div
              className={'qe-input-group image-attributes-range multi-settings'}
            >
              <InputRange
                key={`brightness-${forceUpdateKey}`}
                onChange={handleBrightnessChange}
                labelText={t('Brightness')}
                min={-100}
                max={100}
                value={imageState.imageBrightness}
                step={1}
                direction={Direction.Right}
              />
            </div>
            <div
              className={'qe-input-group image-attributes-range multi-settings'}
            >
              <InputRange
                key={`radius-${forceUpdateKey}`}
                onChange={handleRadiusChange}
                labelText={t('Radius')}
                min={0}
                max={50}
                value={imageState.imageRadius}
                step={1}
                direction={Direction.Right}
              />
            </div>
            <div className="qe-input-group multi-settings">
              <Form.Label>{t('Alt text')}</Form.Label>
              <ToggleButtons
                id="alt-text-toggle"
                toggleOptions={getYesNoOptions()}
                value={showAltText ? '1' : '0'}
                onChange={handleAltTextToggle}
              />
              {showAltText && (
                <div className="position-relative mt-2">
                  <Form.Control
                    value={imageState.imageAltText}
                    className="textarea"
                    maxLength={charLimit}
                    placeholder={t('Image description')}
                    as="textarea"
                    rows={6}
                    data-testid="alt-text"
                    onChange={(e) => {
                      if (charLimit) {
                        let remains = charLimit - e.target.value.length
                        setRemainingChars(remains) // Update characters remaining every change.
                      }
                      handleAltTextChange(e.target.value)
                    }}
                  />
                  <p
                    className="bottom-0 position-absolute"
                    style={{
                      right: '10px',
                      color: getCharactersColor(remainingChars, charLimit),
                    }}
                  >
                    {remainingChars}/{charLimit}
                  </p>
                </div>
              )}
            </div>
            <ImageEditor
              showModal={show}
              onClose={handleClose}
              onChange={(imageObject) => handleEditModalSave(imageObject)}
              imageObject={imageState}
            />
          </>
        )}
      </div>
    </>
  )
}

export const getCharactersColor = (remainingChars, maxChars) => {
  const ratio = remainingChars / maxChars
  if (ratio >= 0.2) return '#1A7A47'
  if (ratio >= 0.06) return '#8F5A00'
  if (ratio >= 0 || ratio < 0) return '#D12323'
}
