import { MeatballMenu } from './MeatballMenu'
import { fn } from '@storybook/test'

export default {
  title: 'MeatballMenu',
  component: MeatballMenu,
}

const handleDelete = fn(() => {})
const handleDuplicate = fn(() => {})

export const Basic = () => {
  return (
    <MeatballMenu
      duplicateText={'duplicateText'}
      deleteText={'deleteText'}
      handleDelete={handleDelete}
      handleDuplicate={handleDuplicate}
    />
  )
}
