import { fireEvent, render, screen } from '@testing-library/react'

import { STATES } from 'helpers'

import { DateQuestionInput } from './DateQuestionInput'

jest.mock('hooks', () => ({
  ...jest.requireActual('hooks'),
  useAppState: jest.fn((key) => {
    if (key === 'site_settings') {
      return [
        {
          dateFormats: {
            1: { dateformat: 'dd.mm.yyyy', jsdate: 'DD.MM.YYYY' },
          },
        },
      ]
    }

    return [undefined]
  }),
}))

global.t = (key) => key

const renderDropdowns = (attributes, props = {}) =>
  render(
    <DateQuestionInput
      question={{ qid: 1, attributes: { dropdown_dates: '1', ...attributes } }}
      language="en"
      surveySettings={{ dateFormats: { en: 1 } }}
      participantMode={true}
      values={[{ key: '123X1X1', value: '' }]}
      {...props}
    />
  )

// The values reported as savable (calls without the "can't be saved" flag)
const savedValues = (mock) =>
  mock.mock.calls.filter((call) => !call[2]).map((call) => call[0])

const select = (label, value) =>
  fireEvent.change(screen.getByLabelText(label), { target: { value } })

describe('DateQuestionInput in dropdown mode', () => {
  it('checks the site settings state key used by the mock', () => {
    expect(STATES.SITE_SETTINGS).toBe('site_settings')
  })

  it('shows the dropdowns in the order of the date format', () => {
    renderDropdowns({})

    const labels = screen
      .getAllByRole('combobox')
      .map((element) => element.className.split(' ').pop())
    expect(labels).toEqual(['day', 'month', 'year'])
  })

  it('offers only the years between the limits, latest first with reverse', () => {
    renderDropdowns({ date_min: '1980', date_max: '1983', reverse: '1' })

    const years = Array.from(screen.getByLabelText('Year').options)
      .map((option) => option.value)
      .filter(Boolean)
    expect(years).toEqual(['1983', '1982', '1981', '1980'])
  })

  it('saves a complete date in the storage format', () => {
    const onValueChange = jest.fn()
    renderDropdowns({ date_min: '1900' }, { onValueChange })

    select('Day', '17')
    select('Month', '4')
    expect(savedValues(onValueChange)).toEqual([])
    expect(onValueChange).toHaveBeenLastCalledWith(null, '123X1X1', true)

    select('Year', '1985')
    expect(onValueChange).toHaveBeenLastCalledWith(
      '1985-04-17 00:00:00',
      '123X1X1'
    )
  })

  it('does not save a date outside the limits and says why', () => {
    const onValueChange = jest.fn()
    renderDropdowns(
      { date_min: '1985-04-18', date_max: '1990' },
      { onValueChange }
    )

    select('Day', '17')
    select('Month', '4')
    select('Year', '1985')

    expect(savedValues(onValueChange)).toEqual([])
    expect(onValueChange).toHaveBeenLastCalledWith(null, '123X1X1', true)
    expect(screen.getByRole('alert').textContent).toBe(
      'Answer must be between 18.04.1985 and 31.12.1990'
    )
  })

  it('shows the limits as full dates when the format has no day', () => {
    const onValueChange = jest.fn()
    renderDropdowns(
      { date_format: 'mm/yyyy', date_min: '2024-05-15' },
      { onValueChange }
    )

    select('Month', '5')
    select('Year', '2024')

    expect(savedValues(onValueChange)).toEqual([])
    expect(screen.getByRole('alert').textContent).toBe(
      'Answer must be between 15.05.2024 and 31.12.2187'
    )

    select('Month', '6')
    expect(savedValues(onValueChange)).toEqual(['2024-06-01 00:00:00'])
  })

  it('does not save an impossible date', () => {
    const onValueChange = jest.fn()
    renderDropdowns({}, { onValueChange })

    select('Day', '31')
    select('Month', '2')
    select('Year', '2001')

    expect(savedValues(onValueChange)).toEqual([])
    const alert = screen.getByRole('alert')
    expect(alert.textContent).toBe(
      'Date 31.02.2001 is invalid, please review your answer.'
    )
    screen.getAllByRole('combobox').forEach((element) => {
      expect(element.getAttribute('aria-invalid')).toBe('true')
      expect(element.getAttribute('aria-describedby')).toBe(alert.id)
    })

    // Correcting the day makes the date valid again and clears the invalid state
    select('Day', '28')
    expect(savedValues(onValueChange)).toEqual(['2001-02-28 00:00:00'])
    expect(screen.queryByRole('alert')).toBeNull()
    screen.getAllByRole('combobox').forEach((element) => {
      expect(element.hasAttribute('aria-invalid')).toBe(false)
      expect(element.hasAttribute('aria-describedby')).toBe(false)
    })
  })

  it('shows the stored answer and the minute step', () => {
    renderDropdowns(
      { date_format: 'dd.mm.yyyy HH:MM', dropdown_dates_minute_step: '15' },
      { values: [{ key: '123X1X1', value: '1985-04-17 10:30:00' }] }
    )

    expect(screen.getByLabelText('Day').value).toBe('17')
    expect(screen.getByLabelText('Hour').value).toBe('10')
    expect(screen.getByLabelText('Minute').value).toBe('30')
    expect(
      Array.from(screen.getByLabelText('Minute').options)
        .map((option) => option.value)
        .filter(Boolean)
    ).toEqual(['0', '15', '30', '45'])
  })

  it('withdraws a saved change when the date becomes incomplete', () => {
    const onValueChange = jest.fn()
    renderDropdowns({}, { onValueChange })

    select('Day', '17')
    select('Month', '4')
    select('Year', '1985')
    expect(savedValues(onValueChange)).toEqual(['1985-04-17 00:00:00'])

    select('Day', '')
    expect(onValueChange).toHaveBeenLastCalledWith(null, '123X1X1', true)
  })

  it('is a disabled preview outside the response editor', () => {
    renderDropdowns({}, { participantMode: false })

    screen
      .getAllByRole('combobox')
      .forEach((element) => expect(element.disabled).toBe(true))
  })
})

describe('DateQuestionInput in picker mode', () => {
  // jsdom has no matchMedia, which MUI uses to choose the desktop picker
  beforeAll(() => {
    window.matchMedia = (query) => ({
      matches: query.includes('pointer: fine'),
      media: query,
      addListener: () => {},
      removeListener: () => {},
      addEventListener: () => {},
      removeEventListener: () => {},
    })
  })

  const renderPicker = (attributes, surveyDateFormat) =>
    render(
      <DateQuestionInput
        question={{ qid: 1, attributes }}
        language="en"
        surveySettings={{ dateFormats: { en: surveyDateFormat } }}
        participantMode={true}
        values={[{ key: '123X1X1', value: '' }]}
      />
    )

  it('links the input to the format hint', () => {
    renderPicker({}, 1)

    const input = screen.getByLabelText('Date in the format: dd.mm.yyyy')
    const hint = document.getElementById(input.getAttribute('aria-describedby'))
    expect(hint.textContent).toBe('Format: dd.mm.yyyy')
  })

  it('does not point to a hint that is not shown', () => {
    // An unknown survey date format: No format, so no hint
    renderPicker({}, 99)

    const input = screen.getByRole('textbox')
    expect(input.hasAttribute('aria-describedby')).toBe(false)
    expect(screen.queryByText(/^Format:/)).toBeNull()
  })
})
