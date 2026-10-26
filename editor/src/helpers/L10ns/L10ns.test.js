import React from 'react'
import { render, screen } from '@testing-library/react'

import { L10ns } from './L10ns'
import { LANGUAGE_CODES } from 'helpers/constants'

const l10ns = {
  en: { welcome: 'Hello' },
  es: { welcome: 'Hola' },
  fr: { welcome: 'Bonjour' },
}

test('renders selected language', () => {
  render(<L10ns prop="welcome" language={LANGUAGE_CODES.EN} l10ns={l10ns} />)
  const textEn = screen.getByText(/Hello/i)
  expect(textEn).toBeInTheDocument()

  render(<L10ns prop="welcome" language={LANGUAGE_CODES.ES} l10ns={l10ns} />)
  const textEs = screen.getByText(/Hola/i)
  expect(textEs).toBeInTheDocument()

  render(<L10ns prop="welcome" language={LANGUAGE_CODES.FR} l10ns={l10ns} />)
  const textFr = screen.getByText(/Bonjour/i)
  expect(textFr).toBeInTheDocument()
})
