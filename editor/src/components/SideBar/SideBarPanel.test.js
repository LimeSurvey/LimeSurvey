import 'tests/mocks'

import { screen } from '@testing-library/react'
import { renderWithProviders } from 'tests/testUtils'

import { SideBarPanel } from './SideBarPanel'

// Component a plugin registers for the side bar item slot; null means no plugin
let mockItemPlugin = null
jest.mock('plugins/PluginManager', () => {
  const { PLUGIN_SLOTS } = jest.requireActual('plugins/slots')
  return {
    __esModule: true,
    default: {
      getPlugin: (slotName) =>
        slotName === PLUGIN_SLOTS.SIDEBAR_PANEL_ITEM_AFTER
          ? mockItemPlugin
          : undefined,
    },
  }
})

const options = [
  { labelEditor: 'Sharing overview', menu: 'sharingOverview' },
  { labelEditor: 'Participant lists', menu: 'participants' },
]

describe('SideBarPanel', () => {
  beforeEach(() => {
    mockItemPlugin = null
  })

  test('renders a plugin below each menu item with that item and the page', async () => {
    const React = require('react')
    mockItemPlugin = ({ option, page }) =>
      React.createElement(
        'span',
        { 'data-testid': `item-extra-${option.menu}` },
        `${page}: ${option.labelEditor}`
      )

    await renderWithProviders(
      <SideBarPanel label="Share survey" options={options} page="sharing" />
    )

    expect(screen.getByTestId('item-extra-sharingOverview')).toHaveTextContent(
      'sharing: Sharing overview'
    )
    expect(screen.getByTestId('item-extra-participants')).toHaveTextContent(
      'sharing: Participant lists'
    )
    // Each plugin entry follows its own menu item
    const overview = screen.getByText('Sharing overview')
    expect(
      overview.compareDocumentPosition(
        screen.getByTestId('item-extra-sharingOverview')
      ) & Node.DOCUMENT_POSITION_FOLLOWING
    ).toBeTruthy()
    expect(
      screen
        .getByTestId('item-extra-sharingOverview')
        .compareDocumentPosition(screen.getByText('Participant lists')) &
        Node.DOCUMENT_POSITION_FOLLOWING
    ).toBeTruthy()
  })

  test('renders only the menu items without a plugin', async () => {
    await renderWithProviders(
      <SideBarPanel label="Share survey" options={options} page="sharing" />
    )

    expect(screen.getByText('Sharing overview')).toBeInTheDocument()
    expect(screen.getByText('Participant lists')).toBeInTheDocument()
    expect(screen.queryByTestId(/^item-extra-/)).not.toBeInTheDocument()
  })
})
