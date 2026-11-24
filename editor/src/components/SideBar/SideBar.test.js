import 'tests/mocks'

import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { renderWithProviders } from 'tests/testUtils'

import { SideBar } from './SideBar'

const Icon = () => <svg />

// The test router opens the "structure" panel
const items = [
  { panel: 'structure', label: 'Structure', icon: Icon },
  { panel: 'settings', label: 'Settings', tip: 'Survey settings', icon: Icon },
  {
    panel: 'translations',
    label: 'Translations',
    icon: Icon,
    isDisabled: true,
    disabledMessage: 'Not available',
  },
]

describe('SideBar', () => {
  test('the icons are buttons named after their panel, the open panel is pressed', async () => {
    await renderWithProviders(<SideBar items={items} />)

    expect(screen.getByRole('button', { name: 'Structure' })).toHaveAttribute(
      'aria-pressed',
      'true'
    )
    expect(
      screen.getByRole('button', { name: 'Survey settings' })
    ).toHaveAttribute('aria-pressed', 'false')
  })

  test('an icon opens its panel from the keyboard', async () => {
    const onIconClick = jest.fn()
    await renderWithProviders(
      <SideBar items={items} onIconClick={onIconClick} />
    )

    screen.getByRole('button', { name: 'Survey settings' }).focus()
    await userEvent.keyboard('{Enter}')
    expect(onIconClick).toHaveBeenCalledWith('settings')
  })

  test('a disabled icon stays focusable but does not open its panel', async () => {
    const onIconClick = jest.fn()
    await renderWithProviders(
      <SideBar items={items} onIconClick={onIconClick} />
    )

    const button = screen.getByRole('button', { name: 'Translations' })
    expect(button).toHaveAttribute('aria-disabled', 'true')
    button.focus()
    expect(button).toHaveFocus()
    await userEvent.keyboard('{Enter}')
    expect(onIconClick).not.toHaveBeenCalled()
  })
})
