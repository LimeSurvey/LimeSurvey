import 'tests/mocks'

import { screen } from '@testing-library/react'
import { renderWithProviders } from 'tests/testUtils'
import userEvent from '@testing-library/user-event'

import { EditableImage } from './EditableImage'

describe('EditableImage', () => {
  test('Has a delete button, but no edit button', async () => {
    const handleRemoveImage = jest.fn()
    await renderWithProviders(
      <EditableImage
        imageSrc={{ preview: '/upload/a.png' }}
        handleRemoveImage={handleRemoveImage}
      />
    )

    expect(screen.getAllByRole('button')).toHaveLength(1)
    await userEvent.click(screen.getByRole('button', { name: 'Delete image' }))
    expect(handleRemoveImage).toHaveBeenCalledTimes(1)
  })

  test('Names the image in the delete button', async () => {
    await renderWithProviders(
      <EditableImage
        imageSrc={{ preview: '/upload/a.png' }}
        imageName="SQ001"
      />
    )

    expect(
      screen.getByRole('button', { name: 'Delete image SQ001' })
    ).toBeInTheDocument()
  })
})
