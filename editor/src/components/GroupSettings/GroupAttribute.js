import { SettingsWrapper } from 'components/UIComponents'

export const GroupAttribute = ({
  component: Component,
  propName,
  handleUpdate,
  props,
  value,
}) => {
  return (
    <SettingsWrapper
      simpleSettings={true}
      isDefaultOpen={true}
      title={t('Basic')}
    >
      <div className="right-side-bar-settings">
        <Component
          {...props}
          onChange={({ target: { value } }) => handleUpdate(propName, value)}
          value={value}
        />
      </div>
    </SettingsWrapper>
  )
}
