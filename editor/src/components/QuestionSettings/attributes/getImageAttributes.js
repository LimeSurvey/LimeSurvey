import { useCallback, useState } from 'react'
import { Button, Form } from 'react-bootstrap'
import { Direction } from 'react-range'

import {
  AlignButtons,
  DropZone,
  ImageEditor,
  InputRange,
} from 'components/UIComponents'
import { TrashIcon, BrushIcon } from 'components/icons'

export const GetImageAttributes = ({ update, value = {} }) => {
  const charLimit = 125
  const [show, setShow] = useState(false)
  const [selectedFile, setSelectedFile] = useState(null)
  const [remainingChars, setRemainingChars] = useState(charLimit) // Init with all remaining.

  const handleClose = () => setShow(false)

  const onReaderResult = useCallback(
    (readerResult) => {
      update({
        image: readerResult,
        // imageAlign: imageAlign ? imageAlign : 'left',
      })
    },

    [update]
  )

  const handleAlignChange = (value) => {
    update({ imageAlign: value })
  }

  const handleBrightnessChange = (value) => {
    update({ imageBrightness: value[0] })
  }
  const handleEditImage = () => {
    setShow(true)
    setSelectedFile({
      ...{
        ...value.image,
        zoom: 1,
        rotate: 0,
        radius: 0,
      },
    })
  }

  if (!process.env.REACT_APP_DEV_MODE) {
    return <></>
  }

  return (
    <div>
      <div className="ms-3">
        <DropZone
          onReaderResult={onReaderResult}
          labelText="Add image or video"
          image={value?.image?.preview}
          dataTestId="add-image-or-video"
        />
      </div>

      <div
        className="mt-3 d-flex align-items-center justify-content-between"
        data-testid="image-or-video-edit-delete"
      >
        <span></span>
        <div>
          <Button onClick={handleEditImage} variant="secondary">
            <BrushIcon />
          </Button>
          <Button
            onClick={() => update({ image: '' })}
            variant="secondary"
            className="ms-2"
          >
            <TrashIcon />
          </Button>
        </div>
      </div>
      <div className="ms-3 mt-3">
        <AlignButtons
          update={handleAlignChange}
          labelText="Alignment"
          value={value?.imageAlign}
        />
      </div>
      <div className="ms-3 mt-3">
        <InputRange
          onChange={handleBrightnessChange}
          labelText={'Brightness'}
          min={-100}
          max={100}
          value={value?.imageBrightness}
          step={1}
          direction={Direction.Right}
        />
      </div>
      <div className="ms-3 mt-3">
        <Form.Label>{t('Alt text')}</Form.Label>
        <div className=" position-relative">
          <Form.Control
            placeholder={t('Image description')}
            as="textarea"
            rows={6}
            data-testid="alt-text"
            onChange={(e) => {
              if (charLimit) {
                let remains = charLimit - e.target.value.length
                setRemainingChars(remains) // Update characters remaining every change.
              }
            }}
          />
          <p
            className="bottom-0 position-absolute"
            style={{
              right: '10px',
              color: getCharactersColor(remainingChars, charLimit),
            }}
          >
            {remainingChars}/125
          </p>
        </div>
      </div>

      {selectedFile && (
        <ImageEditor
          showModal={show}
          onClose={handleClose}
          onChange={(file) => update({ image: { ...file } })}
          file={selectedFile}
        />
      )}
    </div>
  )
}

export const getCharactersColor = (remainingChars, maxChars) => {
  const ratio = remainingChars / maxChars
  if (ratio >= 0.2) return '#1A7A47'
  if (ratio >= 0.06) return '#8F5A00'
  if (ratio >= 0 || ratio < 0) return '#D12323'
}
