import 'tests/mocks'

import { screen } from '@testing-library/react'
import { renderWithProviders } from 'tests/testUtils'
import userEvent from '@testing-library/user-event'

import { FileUploadItem } from './FileUploadItem'

describe('FileUploadItem', () => {
  test('Has a delete button named by the position, but no edit button', async () => {
    const onDelete = jest.fn()
    await renderWithProviders(
      <FileUploadItem
        file={{ preview: '/upload/a.png', name: 'a.png' }}
        index={2}
        onDelete={onDelete}
        onMetadataChange={jest.fn()}
        question={{ attributes: {} }}
      />
    )

    expect(screen.getAllByRole('button')).toHaveLength(1)
    expect(screen.getByRole('img')).toHaveAttribute('alt', 'a.png')
    await userEvent.click(
      screen.getByRole('button', { name: 'Delete image 3' })
    )
    expect(onDelete).toHaveBeenCalledWith(2)
  })
})
