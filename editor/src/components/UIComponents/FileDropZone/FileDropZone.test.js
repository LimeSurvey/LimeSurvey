import 'tests/mocks'

import { useState } from 'react'
import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'

import { renderWithProviders } from 'tests/testUtils'
import { FileDropZone } from './FileDropZone'

describe('FileDropZone', () => {
  test('selects and removes a file', async () => {
    const user = userEvent.setup()
    const onChange = jest.fn()
    const file = new File(['survey'], 'survey.lss', { type: 'text/xml' })
    const ControlledFileDropZone = () => {
      const [selectedFile, setSelectedFile] = useState(null)
      const handleChange = (nextFile) => {
        setSelectedFile(nextFile)
        onChange(nextFile)
      }

      return (
        <FileDropZone
          label="Survey file"
          file={selectedFile}
          onChange={handleChange}
        />
      )
    }
    await renderWithProviders(<ControlledFileDropZone />)

    await user.upload(screen.getByLabelText('Survey file'), file)
    expect(onChange).toHaveBeenCalledWith(file)

    await user.click(
      screen.getByRole('button', { name: 'Remove selected file' })
    )
    expect(onChange).toHaveBeenLastCalledWith(null)
  })
})
