import React from 'react'
import { render, screen } from '@testing-library/react'

import { BrowserDetectionTextAnswer } from './BrowserDetectionTextAnswer'

jest.mock('bowser', () => ({
  parse: () => ({
    browser: { name: 'Chrome', version: '120' },
    os: { name: 'Windows', versionName: '10' },
  }),
}))

beforeEach(() => {
  global.t = (key) => key
  global.st = (key) => key
})

describe('BrowserDetectionTextAnswer', () => {
  it('renders browser info in a disabled text input', () => {
    render(<BrowserDetectionTextAnswer />)

    expect(screen.getByTestId('text-question-answer-input')).toHaveValue(
      'Chrome (120)'
    )
    expect(screen.getByTestId('text-question-answer-input')).toBeDisabled()
  })

  it('includes platform information when add_platform_info is yes', () => {
    render(
      <BrowserDetectionTextAnswer
        attributes={{ add_platform_info: { '': { value: 'yes' } } }}
      />
    )

    expect(screen.getByTestId('text-question-answer-input')).toHaveValue(
      'Chrome (120) | Windows (10)'
    )
  })

  it('does not render a map regardless of location_mapservice', () => {
    const { rerender } = render(
      <BrowserDetectionTextAnswer
        attributes={{ location_mapservice: { '': '100' } }}
      />
    )

    expect(screen.queryByTestId('map')).not.toBeInTheDocument()
    expect(screen.queryByTitle(/google/i)).not.toBeInTheDocument()
    expect(document.querySelector('iframe')).not.toBeInTheDocument()

    rerender(
      <BrowserDetectionTextAnswer
        attributes={{ location_mapservice: { '': '1' } }}
      />
    )

    expect(screen.queryByTestId('map')).not.toBeInTheDocument()
    expect(document.querySelector('iframe')).not.toBeInTheDocument()
    expect(screen.getByTestId('text-question-answer-input')).toBeInTheDocument()
  })
})
