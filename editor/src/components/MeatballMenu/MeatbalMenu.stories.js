import { MeatballMenu } from './MeatballMenu'
import { userEvent, within, screen, waitFor, expect, fn } from '@storybook/test'

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

Basic.play = async ({ canvasElement, step }) => {
  const { getByTestId } = within(canvasElement)
  await waitFor(() => getByTestId('meatball-menu-button'))

  const meatballToggler = getByTestId('meatball-menu-button')

  await step('Should open and close the meatball menu', async () => {
    await userEvent.click(meatballToggler)
    await expect(screen.queryByTestId('meatball-menu-overlay')).toBeTruthy()
  })

  await step('Should click on duplicate button', async () => {
    const button = await screen.findByTestId('duplicate-button')
    await userEvent.click(button)
    await expect(handleDuplicate).toHaveBeenCalled()
  })

  await step('Should click on delete button', async () => {
    await userEvent.click(meatballToggler, { delay: 250 })
    const button = await screen.findByTestId('delete-button')
    await userEvent.click(button, { delay: 250 })
    await expect(handleDelete).toHaveBeenCalled()
  })
}
