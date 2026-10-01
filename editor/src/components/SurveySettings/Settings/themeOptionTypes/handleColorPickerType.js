import ColorPicker from 'components/UIComponents/ColorPicker/ColorPicker'

import { Entities } from 'helpers'

/**
 * @param attribute {object}
 * @param keyPath {string}
 */
export const handleColorPickerType = (attribute = {}, keyPath) => {
  return {
    keyPath: `themesettings.${keyPath}`,
    entity: Entities.themeSettings,
    props: {
      id: keyPath,
      mainText: t(attribute.title),
      childComponent: ColorPicker,
      noPermissionDisabled: true,
    },
  }
}
