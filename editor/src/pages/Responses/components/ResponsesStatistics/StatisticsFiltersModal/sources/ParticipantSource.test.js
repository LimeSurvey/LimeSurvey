import 'tests/mocks'

import { fireEvent, render, screen } from '@testing-library/react'

import { ParticipantSource } from './ParticipantSource'

// A native select stands in for react-select, so picking an attribute is one change event.
jest.mock('../FilterSelect', () => ({
  FilterSelect: ({ options, value, update }) => (
    <select
      aria-label="attribute"
      value={value ?? ''}
      onChange={(event) => update(event.target.value)}
    >
      <option value="" />
      {options.map((option) => (
        <option key={option.value} value={option.value}>
          {option.label}
        </option>
      ))}
    </select>
  ),
}))

const HINT = /can only be searched by exact match/

const survey = {
  attributeDescriptions: { attribute_1: { description: 'Department' } },
  participantEncryptedAttributes: ['firstname', 'attribute_1'],
}

const renderSource = (filter, onUpdate = jest.fn()) => {
  render(
    <ParticipantSource filter={filter} survey={survey} onUpdate={onUpdate} />
  )
  return onUpdate
}

const pick = (attribute) =>
  fireEvent.change(screen.getByLabelText('attribute'), {
    target: { value: attribute },
  })

describe('ParticipantSource', () => {
  beforeAll(() => {
    globalThis.t = (text) => text
  })

  test.each(['{TOKEN:FIRSTNAME}', '{TOKEN:ATTRIBUTE_1}'])(
    'shows the exact match hint for encrypted %s',
    (attribute) => {
      renderSource({ attribute, attributeValue: '' })

      expect(screen.getByText(HINT)).toBeInTheDocument()
    }
  )

  test('shows no hint for an attribute that is not encrypted', () => {
    renderSource({ attribute: '{TOKEN:LASTNAME}', attributeValue: '' })

    expect(screen.queryByText(HINT)).not.toBeInTheDocument()
  })

  test('clears a text value when switching to a number attribute', () => {
    const onUpdate = renderSource({
      attribute: '{TOKEN:LANGUAGE}',
      attributeValue: 'en',
    })

    pick('{TOKEN:USESLEFT}')

    expect(onUpdate).toHaveBeenCalledWith('attribute', '{TOKEN:USESLEFT}')
    expect(onUpdate).toHaveBeenCalledWith('attributeValue', '')
  })

  test('clears a number when switching to a text attribute', () => {
    const onUpdate = renderSource({
      attribute: '{TOKEN:USESLEFT}',
      attributeValue: '1',
    })

    pick('{TOKEN:LANGUAGE}')

    expect(onUpdate).toHaveBeenCalledWith('attribute', '{TOKEN:LANGUAGE}')
    expect(onUpdate).toHaveBeenCalledWith('attributeValue', '')
  })

  test('keeps the value between two attributes of the same kind', () => {
    const onUpdate = renderSource({
      attribute: '{TOKEN:LANGUAGE}',
      attributeValue: 'en',
    })

    pick('{TOKEN:EMAIL}')

    expect(onUpdate).toHaveBeenCalledWith('attribute', '{TOKEN:EMAIL}')
    expect(onUpdate).not.toHaveBeenCalledWith('attributeValue', '')
  })
})
