import { screen } from '@testing-library/react'

import 'tests/mocks'
import { renderWithProviders } from 'tests/testUtils'

import { SettingItem } from './SettingItem'
import { SurveySetting } from './SurveySetting'
import { getNotificationsDataSettingsBlocks } from './Settings/getNotificationsDataSettingsBlocks'

const renderNotificationSetting = (settingKey, active) => {
  const setting =
    getNotificationsDataSettingsBlocks().NOTIFICATIONS_DATA.settings[settingKey]

  return renderWithProviders(
    <SettingItem
      setting={setting}
      formattedValue="admin@example.com"
      component={SurveySetting}
      active={active}
      sid={123}
      updateSurveySetting={() => {}}
      rerenderSettings={{}}
      setRerenderSettings={() => {}}
      globalStates={{}}
    />
  )
}

// SettingItem is where survey settings combine activeDisabled with the
// survey's active state, so the input components can trust the prop.
describe('Date stamp', () => {
  const getDateStampOptions = () => {
    const options = document.querySelectorAll('#datestamp input')
    expect(options.length).toBeGreaterThan(0)

    return options
  }

  test('is locked on an active survey', async () => {
    await renderNotificationSetting('DATESTAMP', true)

    getDateStampOptions().forEach((option) => expect(option).toBeDisabled())
  })

  test('can be changed on an inactive survey', async () => {
    await renderNotificationSetting('DATESTAMP', false)

    getDateStampOptions().forEach((option) => expect(option).toBeEnabled())
  })
})

describe('Detailed admin notifications email address', () => {
  test('stays editable on an active survey', async () => {
    await renderNotificationSetting('EMAIL_RESPONSE_TO', true)

    expect(screen.getByRole('input')).toBeEnabled()
  })
})
