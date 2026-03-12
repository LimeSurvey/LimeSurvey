import { EditableImage } from './EditableImage'

export default {
  title: 'EditableImage',
}

export const Basic = () => {
  return <EditableImage />
}

export const Customized = () => {
  return (
    <EditableImage
      width={'180px'}
      height={'120px'}
      handleRemoveImage={() => {}}
      showController={false}
      imageSrc={
        'https://media.istockphoto.com/id/1460853312/photo/abstract-connected-dots-and-lines-concept-of-ai-technology-motion-of-digital-data-flow.jpg?s=2048x2048&w=is&k=20&c=7yqKsEDy7_n6bG1jOFFFmYGYDa0MiSjJjYH_JvbxuWs='
      }
    />
  )
}
