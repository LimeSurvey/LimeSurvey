import React, { useEffect, useId, useMemo, useRef, useState } from 'react'
import { format } from 'util'
import AvatarEditor from 'react-avatar-editor'
import { Direction } from 'react-range'
import Modal from 'react-bootstrap/Modal'
import { Button, Form } from 'react-bootstrap'
import { ExclamationTriangleFill } from 'react-bootstrap-icons'
import classNames from 'classnames'

import { AspectRatioIcon } from 'components/icons'
import { TooltipContainer } from 'components/TooltipContainer/TooltipContainer'
import { InputRange } from '../InputRange/InputRange'
import {
  ALT_TEXT_MAX_LENGTH,
  FRAME_MIN_SIZE,
  ZOOM_MAX,
  ZOOM_MIN,
  formatFileSize,
  getDefaultFrameSize,
  getFrameRatio,
  getMovableAxes,
  getVisibleSourceSize,
  resizeFrame,
  roundSetting,
} from './imageEditorUtils'

// Size of the preview area in the modal. Larger frames are shown scaled down.
const PREVIEW_MAX_WIDTH = 440
const PREVIEW_MAX_HEIGHT = 420
// Dimmed area around the frame, showing the parts of the image that are cut off.
const PREVIEW_BORDER = 24

/**
 * Returns the settings the editor starts with: The saved ones, or defaults
 * based on the image size.
 *
 * @param {Object|null} settings settings saved before, see ImageEditor's onSave
 * @param {{width: number, height: number}|null} naturalSize size of the image
 *   file, or null while it is loading
 * @returns {{width: number, height: number, ratio: number|null,
 *   keepRatio: boolean, zoom: number, positionX: number, positionY: number,
 *   radius: number}} the start settings. ratio is the frame ratio that "Keep
 *   aspect ratio" keeps, it is not saved.
 */
const getInitialSettings = (settings, naturalSize) => {
  const defaultFrame = getDefaultFrameSize(
    naturalSize?.width,
    naturalSize?.height
  )
  const frame = {
    width: settings?.width ?? defaultFrame.width,
    height: settings?.height ?? defaultFrame.height,
  }
  return {
    ...frame,
    ratio: getFrameRatio(frame),
    keepRatio: settings?.keepRatio ?? true,
    zoom: settings?.zoom || ZOOM_MIN,
    positionX: settings?.positionX ?? 0.5,
    positionY: settings?.positionY ?? 0.5,
    radius: settings?.radius || 0,
  }
}

/**
 * Number input for one side of the frame. Valid numbers are applied while
 * typing. An empty or invalid field shows the current value again when it
 * loses focus.
 *
 * @param {string} id id of the input, linked to its label
 * @param {string} label visible label
 * @param {number} value current size in px
 * @param {Function} onCommit called with the entered size as integer
 * @param {Object} inputRef ref to the input element, e.g. to focus it
 * @param {boolean} isLocked whether the aspect ratio is locked, shown with a
 *   highlighted border
 * @param {string} className extra class of the wrapper, e.g. for styling
 * @returns {JSX.Element} the labelled input
 */
const FrameSizeInput = ({
  id,
  label,
  value,
  onCommit,
  inputRef,
  isLocked = false,
  className = '',
}) => {
  const [draft, setDraft] = useState(null)

  const handleChange = (event) => {
    setDraft(event.target.value)
    const number = parseInt(event.target.value, 10)
    if (number >= FRAME_MIN_SIZE) {
      onCommit(number)
    }
  }

  const handleBlur = () => {
    const number = parseInt(draft, 10)
    if (!isNaN(number)) {
      onCommit(number)
    }
    setDraft(null)
  }

  return (
    <div className={classNames('flex-grow-1', className)}>
      {/* A block label starts at the top of the column, level with the
          preview. Inline, it would sit lower in its line box. */}
      <Form.Label htmlFor={id} className="d-block">
        {label}
      </Form.Label>
      <Form.Control
        id={id}
        ref={inputRef}
        className={classNames({ 'image-editor-ratio-locked': isLocked })}
        type="number"
        min={FRAME_MIN_SIZE}
        step={1}
        value={draft ?? value}
        onChange={handleChange}
        onBlur={handleBlur}
      />
    </div>
  )
}

/**
 * Modal to set how an image is displayed: Frame size, position and zoom of the
 * image inside the frame, corner radius and alt text. The image file itself is
 * not changed: The settings are applied with CSS when the image is shown.
 *
 * @param {boolean} showModal whether the modal is shown
 * @param {Function} onClose called when the modal is closed without saving
 * @param {Function} onSave called with the settings ({width, height, keepRatio,
 *   zoom, positionX, positionY, radius, crop, altText}) when saving
 * @param {string} imageUrl URL of the image
 * @param {Object} settings settings saved before, see onSave
 * @param {string} altText alt text saved before
 * @param {boolean} showAltText whether to show the alt text field
 * @param {boolean} showApplyToAll whether to show "Apply to all images of this
 *   question". onSave then also gets applyToAll.
 * @returns {JSX.Element|null} the modal, or null without an image
 */
export const ImageEditor = ({
  showModal,
  onClose = () => {},
  onSave = () => {},
  imageUrl,
  settings = null,
  altText = '',
  showAltText = true,
  showApplyToAll = false,
}) => {
  const editorRef = useRef(null)
  const widthInputRef = useRef(null)
  const idPrefix = useId()
  const [naturalSize, setNaturalSize] = useState(null)
  const [fileSize, setFileSize] = useState(null)
  const [current, setCurrent] = useState(() =>
    getInitialSettings(settings, null)
  )
  const [currentAltText, setCurrentAltText] = useState(altText)
  // Not saved: Each save with the box checked applies the settings again.
  const [applyToAll, setApplyToAll] = useState(false)
  // Sliders keep their own state: Remount them when the values are reset.
  const [resetCount, setResetCount] = useState(0)

  // Load the image to know its original size.
  useEffect(() => {
    if (!imageUrl) {
      return
    }
    let cancelled = false
    const image = new Image()
    image.onload = () => {
      if (cancelled) {
        return
      }
      const size = { width: image.naturalWidth, height: image.naturalHeight }
      setNaturalSize(size)
      if (settings?.width == null || settings?.height == null) {
        const frame = getDefaultFrameSize(size.width, size.height)
        setCurrent((prev) => ({
          ...prev,
          ...frame,
          ratio: getFrameRatio(frame),
        }))
      }
    }
    image.src = imageUrl

    fetch(imageUrl, { method: 'HEAD' })
      .then((response) => {
        const length = parseInt(response.headers.get('content-length'), 10)
        if (!cancelled && !isNaN(length)) {
          setFileSize(length)
        }
      })
      .catch(() => {})

    return () => {
      cancelled = true
    }
  }, [imageUrl])

  // Start from the saved values each time the modal is opened.
  useEffect(() => {
    if (showModal) {
      setCurrent(getInitialSettings(settings, naturalSize))
      setCurrentAltText(altText)
      setApplyToAll(false)
      setResetCount((count) => count + 1)
    }
  }, [showModal])

  const maxRadius = Math.floor(Math.min(current.width, current.height) / 2)
  const radius = Math.min(current.radius, maxRadius)

  const previewScale = Math.min(
    1,
    PREVIEW_MAX_WIDTH / current.width,
    PREVIEW_MAX_HEIGHT / current.height
  )

  const visibleSource = useMemo(
    () =>
      naturalSize
        ? getVisibleSourceSize(
            naturalSize.width,
            naturalSize.height,
            current,
            current.zoom
          )
        : null,
    [naturalSize, current.width, current.height, current.zoom]
  )
  const isBlurry =
    visibleSource &&
    (visibleSource.width < current.width ||
      visibleSource.height < current.height)
  const movableAxes = getMovableAxes(
    naturalSize?.width,
    naturalSize?.height,
    current,
    current.zoom
  )
  const isMovable = movableAxes.horizontal || movableAxes.vertical

  const update = (changes) => setCurrent((prev) => ({ ...prev, ...changes }))

  // With "Keep aspect ratio", the other side follows the stored ratio. It isn't
  // recalculated from the rounded sizes, which would change it while typing.
  // Without it, the ratio follows the frame, ready for when the box is checked.
  const handleFrameChange = (side, value) => {
    if (current.keepRatio) {
      update(resizeFrame(current, side, value, current.ratio))
      return
    }
    const frame = resizeFrame(current, side, value)
    update({ ...frame, ratio: getFrameRatio(frame) })
  }

  const handleReset = () => {
    setCurrent({
      ...getInitialSettings(null, naturalSize),
    })
    setResetCount((count) => count + 1)
  }

  const handleSave = () => {
    const rect = editorRef.current?.getCroppingRect()
    const round = roundSetting
    // eslint-disable-next-line no-unused-vars
    const { ratio, ...frameSettings } = current
    onSave({
      ...frameSettings,
      radius,
      positionX: round(current.positionX),
      positionY: round(current.positionY),
      crop: rect
        ? {
            x: round(rect.x),
            y: round(rect.y),
            width: round(rect.width),
            height: round(rect.height),
          }
        : null,
      altText: currentAltText.trim(),
      ...(showApplyToAll ? { applyToAll } : {}),
    })
  }

  if (!imageUrl) {
    return null
  }

  const titleId = `${idPrefix}-title`
  // The button names what a click does, like its tooltip.
  const ratioLockLabel = current.keepRatio
    ? t('Unlock aspect ratio')
    : t('Lock aspect ratio')

  return (
    <Modal
      show={showModal}
      onHide={onClose}
      onEntered={() => widthInputRef.current?.focus()}
      aria-labelledby={titleId}
      className="image-editor-modal"
      dialogClassName="image-editor-dialog"
    >
      <Modal.Header closeButton>
        <Modal.Title id={titleId}>{t('Edit image')}</Modal.Title>
      </Modal.Header>
      <Modal.Body>
        <div className="d-flex gap-4">
          <div
            className="image-editor-preview d-flex flex-column align-items-center flex-shrink-0"
            style={{ width: PREVIEW_MAX_WIDTH + 2 * PREVIEW_BORDER }}
          >
            {/* Top aligned: The image starts level with the labels of the
                width and height inputs. Where the image can move vertically,
                that is the dimmed strip above the frame. Otherwise the strip
                stays empty, so it is moved out of the box and cut off, and
                the frame starts there. Not decided by the current position:
                The frame would jump while dragging. */}
            <div
              className="d-flex align-items-start justify-content-center overflow-hidden"
              style={{
                width: PREVIEW_MAX_WIDTH + 2 * PREVIEW_BORDER,
                height: PREVIEW_MAX_HEIGHT + 2 * PREVIEW_BORDER,
              }}
            >
              <AvatarEditor
                ref={editorRef}
                image={imageUrl}
                width={Math.round(current.width * previewScale)}
                height={Math.round(current.height * previewScale)}
                border={PREVIEW_BORDER}
                style={{
                  marginTop: movableAxes.vertical ? 0 : -PREVIEW_BORDER,
                }}
                color={[255, 255, 255, 0.6]}
                scale={current.zoom}
                rotate={0}
                borderRadius={radius * previewScale}
                position={{ x: current.positionX, y: current.positionY }}
                onPositionChange={({ x, y }) =>
                  update({ positionX: x, positionY: y })
                }
              />
            </div>
            {naturalSize && (
              <p
                className="text-muted small mt-2 mb-0"
                data-testid="image-editor-source-info"
              >
                {`${naturalSize.width}×${naturalSize.height}px`}
                {fileSize !== null && ` · ${formatFileSize(fileSize)}`}
              </p>
            )}
            {/* The hint and the warning keep their space while hidden, so
                the modal doesn't change its height. */}
            <p
              className={classNames('text-muted small mb-0', {
                invisible: !isMovable,
              })}
              aria-hidden={!isMovable}
              data-testid="image-editor-drag-hint"
            >
              {t('Drag the image to move it inside the frame.')}
            </p>
            <div aria-live="polite" className="w-100 image-editor-warning-slot">
              {isBlurry && (
                <div
                  className="image-editor-warning d-flex align-items-center gap-2 small mt-3"
                  data-testid="image-editor-blurry-warning"
                >
                  <ExclamationTriangleFill
                    className="flex-shrink-0"
                    aria-hidden="true"
                  />
                  <span>
                    {format(
                      t(
                        'The visible part of the image (%s×%spx) is smaller than the frame (%s×%spx). The image will look blurry.'
                      ),
                      visibleSource.width,
                      visibleSource.height,
                      current.width,
                      current.height
                    )}
                  </span>
                </div>
              )}
            </div>
          </div>
          <div
            className="flex-grow-1 d-flex flex-column gap-3"
            style={{ minWidth: 0 }}
          >
            {/* With the aspect ratio locked, a line between the inputs shows
                that they are connected (see ImageEditor.scss). */}
            <div
              className={classNames(
                'image-editor-frame-size d-flex gap-3 align-items-end',
                { locked: current.keepRatio }
              )}
              data-testid="image-editor-frame-size"
            >
              <FrameSizeInput
                id={`${idPrefix}-width`}
                label={t('Width (px)')}
                value={current.width}
                onCommit={(value) => handleFrameChange('width', value)}
                inputRef={widthInputRef}
                isLocked={current.keepRatio}
              />
              <FrameSizeInput
                id={`${idPrefix}-height`}
                label={t('Height (px)')}
                value={current.height}
                onCommit={(value) => handleFrameChange('height', value)}
                isLocked={current.keepRatio}
                className="image-editor-frame-height"
              />
              <TooltipContainer tip={ratioLockLabel} placement="right">
                <Button
                  variant="link"
                  className={classNames('image-editor-ratio-lock', {
                    locked: current.keepRatio,
                  })}
                  aria-label={ratioLockLabel}
                  aria-pressed={current.keepRatio}
                  data-testid="image-editor-ratio-lock"
                  onClick={() => update({ keepRatio: !current.keepRatio })}
                >
                  <AspectRatioIcon />
                </Button>
              </TooltipContainer>
            </div>
            <InputRange
              key={`zoom-${resetCount}`}
              id={`${idPrefix}-zoom`}
              onChange={(value) => update({ zoom: value[0] })}
              labelText={t('Zoom')}
              min={ZOOM_MIN}
              step={0.1}
              max={ZOOM_MAX}
              direction={Direction.Right}
              value={current.zoom}
            />
            <InputRange
              key={`radius-${resetCount}-${maxRadius}`}
              id={`${idPrefix}-radius`}
              onChange={(value) => update({ radius: value[0] })}
              labelText={t('Corner radius')}
              min={0}
              step={1}
              // The slider needs max > min, also for a frame of 0 or 1px.
              max={Math.max(1, maxRadius)}
              direction={Direction.Right}
              value={radius}
            />
            {showAltText && (
              <div>
                <Form.Label htmlFor={`${idPrefix}-alt`}>
                  {t('Alt text')}
                </Form.Label>
                <Form.Control
                  id={`${idPrefix}-alt`}
                  as="textarea"
                  rows={3}
                  maxLength={ALT_TEXT_MAX_LENGTH}
                  placeholder={t('Image description')}
                  value={currentAltText}
                  onChange={(event) => setCurrentAltText(event.target.value)}
                  aria-describedby={`${idPrefix}-alt-count`}
                />
                <div
                  id={`${idPrefix}-alt-count`}
                  className="text-muted small text-end"
                >
                  {currentAltText.length}/{ALT_TEXT_MAX_LENGTH}
                </div>
              </div>
            )}
            {showApplyToAll && (
              <Form.Check
                id={`${idPrefix}-apply-to-all`}
                className="ps-0 d-flex align-items-center justify-content-start gap-2"
              >
                <Form.Check.Input
                  type="checkbox"
                  className="m-0"
                  checked={applyToAll}
                  onChange={(event) => setApplyToAll(event.target.checked)}
                />
                <Form.Check.Label>
                  {t('Apply to all images of this question')}
                </Form.Check.Label>
              </Form.Check>
            )}
          </div>
        </div>
      </Modal.Body>
      <Modal.Footer className="justify-content-between">
        <Button variant="outline-secondary" onClick={handleReset}>
          {t('Reset')}
        </Button>
        <div className="d-flex gap-2">
          <Button variant="secondary" onClick={onClose}>
            {t('Cancel')}
          </Button>
          <Button variant="primary" onClick={handleSave}>
            {t('Save')}
          </Button>
        </div>
      </Modal.Footer>
    </Modal>
  )
}
