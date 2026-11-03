import { Input } from 'components/UIComponents'

import { Entities } from 'helpers'

/**
 *
 * @param attribute {object}
 * @param keyPath {string}
 */
export const handleDurationType = (attribute = {}, keyPath) => {
  return {
    keyPath: `themesettings.${keyPath}`,
    entity: Entities.themeSettings,
    props: {
      id: keyPath,
      mainText: t(attribute.title),
      childComponent: Input,
      type: 'number',
      noPermissionDisabled: true,
    },
  }
}
