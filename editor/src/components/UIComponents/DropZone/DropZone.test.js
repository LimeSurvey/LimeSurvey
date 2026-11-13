// Import shared mocks
import 'tests/mocks'

import { renderWithProviders } from 'tests/testUtils'
import { DropZone } from './DropZone'
import { act, screen } from '@testing-library/react'
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

  test('Shows the buttons next to the image with buttonsBeside', async () => {
    await renderWithProviders(
      <DropZone
        previewUrlInit="/upload/surveys/1/images/a.png"
        onEdit={jest.fn()}
        imageName="A1"
        buttonsBeside={true}
        previewBoxClassName="border border-3"
      />
    )

    const editButton = screen.getByRole('button', {
      name: 'Edit image A1',
      hidden: true,
    })
    const buttons = editButton.parentElement
    const image = screen.getByTestId('dropzone').querySelector('.dropzone-img')
    // Outside the image and its border, in a space kept free while hidden.
    expect(image.contains(editButton)).toBe(false)
    expect(image.parentElement).toHaveClass('border', 'border-3')
    expect(image.parentElement.contains(editButton)).toBe(false)
    expect(buttons).toHaveClass('invisible')
    expect(buttons).not.toHaveClass('d-none')

    // Hovering the area of the buttons shows them, like hovering the image.
    await userEvent.hover(buttons)
    expect(buttons).not.toHaveClass('invisible')
    await userEvent.unhover(screen.getByTestId('dropzone-preview'))
    expect(buttons).toHaveClass('invisible')
    await userEvent.hover(image)
    expect(buttons).not.toHaveClass('invisible')
  })

  test('Keeps the buttons shown while the focus is inside, also when the pointer leaves', async () => {
    await renderWithProviders(
      <>
        <DropZone
          previewUrlInit="/upload/surveys/1/images/a.png"
          onEdit={jest.fn()}
          imageName="A1"
          buttonsBeside={true}
        />
        <button type="button">Outside</button>
      </>
    )

    const editButton = screen.getByRole('button', {
      name: 'Edit image A1',
      hidden: true,
    })
    const buttons = editButton.parentElement
    const preview = screen.getByTestId('dropzone-preview')

    await userEvent.hover(preview)
    act(() => editButton.focus())
    await userEvent.unhover(preview)
    expect(buttons).not.toHaveClass('invisible')

    act(() => screen.getByRole('button', { name: 'Outside' }).focus())
    expect(buttons).toHaveClass('invisible')
  })
})
