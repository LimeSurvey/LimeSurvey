import { useCallback, useState } from 'react'
import { Form, Button } from 'react-bootstrap'

import {
  AlignButtons,
  DropZone,
  ImageEditor,
  Input,
  InputRange,
} from 'components/UIComponents'
import { TrashIcon, BrushIcon } from 'components/icons'

export const ImageSettings = ({
  handleUpdate,
  image,
  imageAlign,
  imageBrightness,
}) => {
  const [show, setShow] = useState(false)
  const [selectedFile, setSelectedFile] = useState(null)

  const handleClose = () => setShow(false)

  const onReaderResult = useCallback(
    (readerResult) => {
      handleUpdate({
        image: readerResult,
        imageAlign: imageAlign ? imageAlign : 'left',
      })
    },

    // eslint-disable-next-line react-hooks/exhaustive-deps
    [handleUpdate]
  )

  const handleAlignChange = (value) => {
    handleUpdate({ imageAlign: value })
  }

  const handleBrightnessChange = (value) => {
    handleUpdate({ imageBrightness: value[0] })
  }
  const handleEditImage = () => {
    setShow(true)
    setSelectedFile({
      ...{
        ...image,
        zoom: [1],
        rotate: [0],
        radius: [0],
      },
    })
  }
  return (
    <div>
      <div>
        <DropZone
          onReaderResult={onReaderResult}
          labelText="Add image or video"
          image={image?.preview}
        />
      </div>
      {image && (
        <>
          <div className="mt-3">
            <AlignButtons
              onChange={handleAlignChange}
              labelText="Alignment"
              value={imageAlign}
            />
          </div>
          <div className="mt-3 d-flex align-items-center justify-content-between">
            <div>
              <Form.Label>Image or Video</Form.Label>
            </div>
            <div>
              <Button onClick={handleEditImage} variant="secondary">
                <BrushIcon />
              </Button>
              <Button
                onClick={() => handleUpdate({ image: '' })}
                variant="secondary"
                className="ms-2"
              >
                <TrashIcon />
              </Button>
            </div>
          </div>
          {imageAlign === 'center' && (
            <div className="mt-3">
              <InputRange
                onChange={handleBrightnessChange}
                labelText={'Brightness'}
                min={-100}
                max={100}
                value={imageBrightness}
              />
            </div>
          )}
          <div className="mt-3">
            <Input labelText="Alt Text" placeholder="Leave a comment here" />
          </div>
        </>
      )}

      {selectedFile && (
        <ImageEditor
          showModal={show}
          onClose={handleClose}
          onChange={(file) => handleUpdate({ image: { ...file } })}
          file={selectedFile}
        />
      )}
    </div>
  )
}
