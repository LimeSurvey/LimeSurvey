import { useState } from 'react'
import { ImageEditor } from '../ImageEditor/ImageEditor'

export default {
  title: 'UIComponents/ImageEditor',
  component: ImageEditor,
}

const FILE = {
  origin: 'image3.jpg',
  zoom: 1,
  rotate: 0,
  radius: 0,
}

export function Basic() {
  const [selectedFile, setSelectedFile] = useState(FILE)

  return (
    <ImageEditor
      showModal={true}
      onChange={(file) => {
        setSelectedFile({ ...file })
      }}
      file={selectedFile}
    />
  )
}
