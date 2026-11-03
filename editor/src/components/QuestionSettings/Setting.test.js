import { useState } from 'react'
import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'

import 'tests/mocks'
import { renderWithProviders } from 'tests/testUtils'

import { Setting } from './Setting'
import { getOtherAttributes } from './attributes/getOtherAttributes'

const SettingWithState = ({ attributes }) => {
  const [question, setQuestion] = useState({
    qid: 1,
    type: '|',
    attributes,
  })
  const otherAttributes = getOtherAttributes()

  return (
    <Setting
      question={question}
      handleUpdate={(value) =>
        setQuestion((current) => ({
          ...current,
          attributes: { ...current.attributes, ...value },
        }))
      }
      title="Simple"
      simpleSettings
      attributes={[
        otherAttributes.MIN_NUMBER_FILES,
        otherAttributes.MAX_NUMBER_FILES,
        otherAttributes.ALLOWED_FILE_TYPES,
        otherAttributes.MAXIMUM_FILE_SIZE_ALLOWED,
      ]}
    />
  )
}

describe('Setting input default values', () => {
  test('shows the default values when the attributes are not set', async () => {
    await renderWithProviders(<SettingWithState attributes={{}} />)

    expect(screen.getByTestId('min-number-of-files').value).toBe('0')
    expect(screen.getByTestId('max-number-of-files').value).toBe('1')
    expect(screen.getByTestId('maximum-file-size-allowed').value).toBe('10240')
    expect(screen.getByTestId('allowed-file-types').value).toBe(
      'png, gif, doc, odt, jpg, jpeg, pdf, heic'
    )
  })

  test('shows the stored values instead of the default values', async () => {
    await renderWithProviders(
      <SettingWithState
        attributes={{
          min_num_of_files: { '': '2' },
          max_num_of_files: { '': '5' },
          max_filesize: { '': '2048' },
          allowed_filetypes: { '': 'pdf' },
        }}
      />
    )

    expect(screen.getByTestId('min-number-of-files').value).toBe('2')
    expect(screen.getByTestId('max-number-of-files').value).toBe('5')
    expect(screen.getByTestId('maximum-file-size-allowed').value).toBe('2048')
    expect(screen.getByTestId('allowed-file-types').value).toBe('pdf')
  })

  test('keeps an emptied field empty and allows changing the value', async () => {
    await renderWithProviders(<SettingWithState attributes={{}} />)
    const input = screen.getByTestId('max-number-of-files')

    await userEvent.clear(input)
    expect(input.value).toBe('')

    await userEvent.type(input, '7')
    expect(input.value).toBe('7')
  })
})
