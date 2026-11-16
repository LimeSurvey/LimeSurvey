import { useState } from 'react'
import { screen, within } from '@testing-library/react'
import userEvent from '@testing-library/user-event'

import 'tests/mocks'
import { renderWithProviders } from 'tests/testUtils'

import { Setting } from './Setting'
import { getInputAttributes } from './attributes/getInputAttributes'

let storedAttributes = {}

const DateFormatSetting = ({ attributes }) => {
  const [question, setQuestion] = useState({
    qid: 1,
    type: 'D',
    attributes,
  })

  return (
    <Setting
      question={question}
      handleUpdate={(value) =>
        setQuestion((current) => {
          storedAttributes = { ...current.attributes, ...value }
          return { ...current, attributes: storedAttributes }
        })
      }
      title="Input"
      simpleSettings
      attributes={[getInputAttributes().DATE_FORMAT]}
    />
  )
}

const getDateFormatField = () => within(screen.getByTestId('date-time-format'))

describe('Date/time format setting', () => {
  beforeEach(() => {
    storedAttributes = {}
  })

  test('shows a stored format that is not one of the presets', async () => {
    await renderWithProviders(
      <DateFormatSetting attributes={{ date_format: { '': 'H:MM' } }} />
    )

    expect(getDateFormatField().getByText('H:MM')).toBeInTheDocument()
  })

  test('allows typing in a time-only format', async () => {
    await renderWithProviders(<DateFormatSetting attributes={{}} />)

    await userEvent.type(getDateFormatField().getByRole('combobox'), 'H.MM')
    await userEvent.keyboard('{Enter}')

    expect(storedAttributes.date_format).toEqual({ '': 'H.MM' })
    expect(getDateFormatField().getByText('H.MM')).toBeInTheDocument()
  })

  test('offers a time-only preset', async () => {
    await renderWithProviders(<DateFormatSetting attributes={{}} />)

    await userEvent.click(getDateFormatField().getByRole('combobox'))
    await userEvent.click(screen.getByText('HH:MM'))

    expect(storedAttributes.date_format).toEqual({ '': 'HH:MM' })
  })
})
