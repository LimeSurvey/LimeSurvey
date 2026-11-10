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

  test('Should show an edit button next to the delete button when onEdit is set', async () => {
    const onEdit = jest.fn()
    await renderWithProviders(
      <DropZone
        previewUrlInit="/upload/surveys/1/images/a.png"
        onEdit={onEdit}
        imageName="A1"
      />
    )

    const editButton = screen.getByRole('button', {
      name: 'Edit image A1',
      hidden: true,
    })
    expect(
      screen.getByRole('button', { name: 'Delete image A1', hidden: true })
    ).toBeInTheDocument()

    const input = screen.getByTestId('dropzone').querySelector('input')
    const inputClick = jest.spyOn(input, 'click')
    await userEvent.click(editButton)

    expect(onEdit).toHaveBeenCalledTimes(1)
    // The click must not open the file dialog of the dropzone.
    expect(inputClick).not.toHaveBeenCalled()
  })

  test('Should not show an edit button without onEdit', async () => {
    await renderWithProviders(
      <DropZone previewUrlInit="/upload/surveys/1/images/a.png" />
    )

    expect(
      screen.queryByRole('button', { name: 'Edit image', hidden: true })
    ).not.toBeInTheDocument()
    expect(
      screen.getByRole('button', { name: 'Delete image', hidden: true })
    ).toBeInTheDocument()
  })
})
