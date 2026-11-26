// Import shared mocks
import 'tests/mocks'

import { useState } from 'react'
import { renderWithProviders } from 'tests/testUtils'
import { DropZone } from './DropZone'
import { act, fireEvent, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { getNoAnswerLabel } from 'helpers'

// jsdom does not implement URL.createObjectURL / revokeObjectURL
beforeAll(() => {
  global.URL.createObjectURL = jest.fn(() => 'blob:mock-url')
  global.URL.revokeObjectURL = jest.fn()
})

let setTestIsEditing
/**
 * A dropzone whose edit button opens an editor, as in ImageChoice: The test
 * closes it with setTestIsEditing(false).
 *
 * @returns {JSX.Element} the dropzone
 */
const EditableDropZone = () => {
  const [isEditing, setIsEditing] = useState(false)
  setTestIsEditing = setIsEditing
  return (
    <DropZone
      previewUrlInit="/upload/surveys/1/images/a.png"
      onEdit={() => setIsEditing(true)}
      isEditing={isEditing}
      imageName="A1"
      buttonsBeside={true}
    />
  )
}

/**
 * Renders EditableDropZone.
 *
 * @returns {Promise<HTMLElement>} its edit button
 */
const renderEditableDropZone = async () => {
  await renderWithProviders(<EditableDropZone />)
  return screen.getByRole('button', { name: 'Edit image A1', hidden: true })
}

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

  test('The focus goes back to the edit button after editing with the keyboard', async () => {
    const editButton = await renderEditableDropZone()

    editButton.focus()
    await userEvent.keyboard('{Enter}')
    // The editor takes the focus while it is open.
    act(() => editButton.blur())
    expect(editButton.parentElement).not.toHaveClass('invisible')

    act(() => setTestIsEditing(false))
    expect(editButton).toHaveFocus()
    expect(editButton.parentElement).not.toHaveClass('invisible')
  })

  test('After editing with the mouse, the buttons are hidden', async () => {
    const editButton = await renderEditableDropZone()

    await userEvent.click(editButton)
    act(() => editButton.blur())
    act(() => setTestIsEditing(false))

    expect(editButton).not.toHaveFocus()
    expect(editButton.parentElement).toHaveClass('invisible')
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

  test('Shows the placeholder and only the delete button when the image is not found', async () => {
    await renderWithProviders(
      <DropZone
        previewUrlInit="/upload/surveys/1/images/missing.png"
        onEdit={jest.fn()}
        imageName="A1"
      />
    )

    fireEvent.error(screen.getByAltText('Image Select List'))

    // The no-image-found placeholder, stubbed in the tests, with an alt text
    // telling screen reader users that the image is missing.
    expect(screen.getByAltText('No image found for A1')).toHaveAttribute(
      'src',
      'test-file-stub'
    )
    expect(
      screen.queryByRole('button', { name: 'Edit image A1', hidden: true })
    ).not.toBeInTheDocument()
    expect(
      screen.getByRole('button', { name: 'Delete image A1', hidden: true })
    ).toBeInTheDocument()
  })

  test('A focused edit button hidden because the image is not found passes the focus to the delete button', async () => {
    const editButton = await renderEditableDropZone()

    editButton.focus()
    fireEvent.error(screen.getByAltText('Image Select List'))

    expect(editButton).not.toBeInTheDocument()
    expect(
      screen.getByRole('button', { name: 'Delete image A1', hidden: true })
    ).toHaveFocus()
  })

  test('After editing with the keyboard, the focus goes to the delete button when the image is not found meanwhile', async () => {
    const editButton = await renderEditableDropZone()

    editButton.focus()
    await userEvent.keyboard('{Enter}')
    // The editor takes the focus while it is open.
    act(() => editButton.blur())
    fireEvent.error(screen.getByAltText('Image Select List'))

    act(() => setTestIsEditing(false))
    const deleteButton = screen.getByRole('button', {
      name: 'Delete image A1',
      hidden: true,
    })
    expect(deleteButton).toHaveFocus()
    expect(deleteButton.parentElement).not.toHaveClass('invisible')
  })

  test('Shows the edit button again when a missing image is replaced', async () => {
    let setTestUrl
    const ReplaceableDropZone = () => {
      const [url, setUrl] = useState('/upload/surveys/1/images/missing.png')
      setTestUrl = setUrl
      return <DropZone previewUrlInit={url} onEdit={jest.fn()} imageName="A1" />
    }
    await renderWithProviders(<ReplaceableDropZone />)
    fireEvent.error(screen.getByAltText('Image Select List'))

    act(() => setTestUrl('/upload/surveys/1/images/b.png'))

    expect(screen.getByAltText('Image Select List')).toHaveAttribute(
      'src',
      '/upload/surveys/1/images/b.png'
    )
    expect(
      screen.getByRole('button', { name: 'Edit image A1', hidden: true })
    ).toBeInTheDocument()
  })

  test('Shows a missing image again when a file with the same name is uploaded', async () => {
    const url = '/upload/surveys/1/images/missing.png'
    const fileService = {
      uploadSurveyImage: jest.fn(async () => ({
        success: true,
        uploaded: { filePath: url, previewUrl: url },
      })),
    }
    await renderWithProviders(
      <DropZone
        previewUrlInit={url}
        fileService={fileService}
        onEdit={jest.fn()}
        imageName="A1"
      />
    )
    fireEvent.error(screen.getByAltText('Image Select List'))

    const input = screen.getByTestId('dropzone').querySelector('input')
    await userEvent.upload(
      input,
      new File(['image'], 'missing.png', { type: 'image/png' })
    )

    expect(fileService.uploadSurveyImage).toHaveBeenCalledTimes(1)
    expect(screen.getByAltText('Image Select List')).toHaveAttribute('src', url)
    expect(
      screen.getByRole('button', { name: 'Edit image A1', hidden: true })
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
