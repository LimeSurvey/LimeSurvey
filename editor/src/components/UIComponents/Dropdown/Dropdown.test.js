import 'tests/mocks'

import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { renderWithProviders } from 'tests/testUtils'

import { Dropdown } from './Dropdown'

const openWith = async (menuItems) => {
  await renderWithProviders(
    <Dropdown
      menuItems={menuItems}
      toggleSettings={{ ariaLabel: 'Actions', testId: 'toggle' }}
    />
  )
  await userEvent.click(await screen.findByRole('button', { name: 'Actions' }))
}

describe('Dropdown hideIconSpace', () => {
  test('drops the placeholder icon when there is no icon', async () => {
    await openWith([{ label: 'Plain', hideIconSpace: true, testId: 'plain' }])
    expect(screen.getByTestId('plain').querySelector('i')).toBeNull()
  })

  test('keeps a real icon', async () => {
    await openWith([
      {
        label: 'Copy',
        icon: 'ri-file-copy-line',
        hideIconSpace: true,
        testId: 'copy',
      },
    ])
    expect(screen.getByTestId('copy').querySelector('i')).toHaveClass(
      'ri-file-copy-line'
    )
  })

  test('keeps the checkmark', async () => {
    await openWith([
      { label: 'Codes', checked: true, hideIconSpace: true, testId: 'codes' },
    ])
    expect(
      screen.getByTestId('codes').querySelector('.ri-check-line')
    ).not.toBeNull()
  })

  test('keeps the placeholder by default for alignment', async () => {
    await openWith([{ label: 'Aligned', testId: 'aligned' }])
    expect(screen.getByTestId('aligned').querySelector('i')).not.toBeNull()
  })
})
