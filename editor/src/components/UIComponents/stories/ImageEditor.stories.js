import { useState } from 'react'
import { ImageEditor } from '../ImageEditor/ImageEditor'

export default {
  title: 'imageWrapper/ImageEditor',
  component: ImageEditor,
}

export function Basic() {
  const [settings, setSettings] = useState(null)
  const [altText, setAltText] = useState('')

  return (
    <ImageEditor
      showModal={true}
      imageUrl="image3.jpg"
      settings={settings}
      altText={altText}
      onSave={({ altText, ...newSettings }) => {
        setSettings(newSettings)
        setAltText(altText)
      }}
    />
  )
}
