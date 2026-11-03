// Import shared mocks
import 'tests/mocks'

import { renderWithProviders } from 'tests/testUtils'
import { DropZone } from './DropZone'
import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { getNoAnswerLabel } from 'helpers'

// jsdom does not implement URL.createObjectURL / revokeObjectURL
beforeAll(() => {
  global.URL.createObjectURL = jest.fn(() => 'blob:mock-url')
  global.URL.revokeObjectURL = jest.fn()
})

describe('DropZone', () => {
  test('Should render DropZone with a label', async () => {
    await renderWithProviders(
      <DropZone
        labelText="Drop zone label"
        image={getNoAnswerLabel(true)}
        onReaderResult={() => {}}
      />
    )

    const dropzone = screen.getByTestId('dropzone')
    const input = dropzone.querySelector('input')
    const label = dropzone.querySelector('label')

    expect(dropzone).toBeInTheDocument()
    expect(input).toBeInTheDocument()
    expect(label).toHaveTextContent('Drop zone label')
  })

  test('Should handle upload', async () => {
    await renderWithProviders(
      <DropZone
        labelText="Drop zone label"
        image={getNoAnswerLabel(true)}
        onReaderResult={() => {}}
      />
    )

    const dropzone = screen.getByTestId('dropzone')
    const input = dropzone.querySelector('input')

    const file = new File(['image'], 'image.png', { type: 'image/png' })
    await userEvent.upload(input, file)
    expect(input.files[0]).toStrictEqual(file)
    expect(input.files.item(0)).toStrictEqual(file)
    expect(input.files).toHaveLength(1)
  })
})
