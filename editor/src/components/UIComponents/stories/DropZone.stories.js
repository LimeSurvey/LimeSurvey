import { DropZone as DropZoneComponent } from '../DropZone/DropZone'
import { getNoAnswerLabel } from 'helpers'

export default {
  title: 'UIComponents/DropZone',
  component: DropZoneComponent,
}

export const DropZone = () => {
  return (
    <DropZoneComponent
      labelText="Drop zone label"
      image={getNoAnswerLabel(true)}
      onReaderResult={() => {}}
    />
  )
}
