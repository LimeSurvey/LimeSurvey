// Import shared mocks
import 'tests/mocks'

import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'

import { renderWithProviders } from 'tests/testUtils'

import { MeatballMenu } from './MeatballMenu'
describe('MeatballMenu', () => {
  const handleDelete = jest.fn(() => {})
  const handleDuplicate = jest.fn(() => {})

  beforeEach(async () => {
    await renderWithProviders(
      <MeatballMenu
        duplicateText={'duplicateText'}
        deleteText={'deleteText'}
        handleDelete={handleDelete}
        handleDuplicate={handleDuplicate}
      />
    )
  })

  test('Should open and close the meatball menu', async () => {
    const meatballToggler = screen.getByTestId('meatball-menu-button')

    await userEvent.click(meatballToggler)
    expect(screen.queryByTestId('meatball-menu-overlay')).toBeTruthy()
  })

  test('Should click on duplicate button', async () => {
    const meatballToggler = screen.getByTestId('meatball-menu-button')
    await userEvent.click(meatballToggler)

    const button = await screen.findByTestId('duplicate-button')
    await userEvent.click(button)
    expect(handleDuplicate).toHaveBeenCalled()
  })

  test('Should click on delete button', async () => {
    const meatballToggler = screen.getByTestId('meatball-menu-button')
    await userEvent.click(meatballToggler, { delay: 250 })

    const button = await screen.findByTestId('delete-button')
    await userEvent.click(button, { delay: 250 })
    expect(handleDelete).toHaveBeenCalled()
  })
})
