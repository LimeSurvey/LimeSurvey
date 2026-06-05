import classNames from 'classnames'

/**
 * Component for rendering a single survey setting item
 */
export const SettingItem = ({
  setting,
  formattedValue,
  previewUrl,
  component: Component,
  active,
  sid,
  updateSurveySetting,
  rerenderSettings,
  setRerenderSettings,
  globalStates,
  noAccessDisabled = false,
}) => {
  return (
    <div
      className={classNames('mb-2 survey-setting', {
        'survey-border-top': !setting.noBorderTop,
      })}
    >
      <Component
        options={
          setting.selectOptions ? setting.selectOptions(globalStates) : []
        }
        {...setting.props}
        value={formattedValue}
        defaultValue={formattedValue}
        previewUrl={previewUrl}
        update={(value, _setting = setting) =>
          updateSurveySetting(_setting, value)
        }
        activeDisabled={active && setting.props.activeDisabled}
        noPermissionDisabled={setting.props.noPermissionDisabled}
        noAccessDisabled={noAccessDisabled}
        disabled={setting.disabled}
        overlayMessage={setting.overlayMessage}
        setting={setting}
        surveyId={sid}
        rerenderSettings={rerenderSettings}
        setRerenderSettings={setRerenderSettings}
      />
    </div>
  )
}
