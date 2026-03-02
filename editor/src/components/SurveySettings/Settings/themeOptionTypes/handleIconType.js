import ThemeOptionsIconSelect from 'components/ThemeOptions/ThemeOptionsIconSelect'
import { Entities } from 'helpers'

/**
 *
 * @param attribute {object}
 * @param keyPath {string}
 */
export const handleIconType = (attribute = {}, keyPath) => {
  return {
    keyPath: `themesettings.${keyPath}`,
    entity: Entities.themeSettings,
    selectOptions: () => {
      return attribute.dropdownoptions
    },
    props: {
      id: keyPath,
      mainText: t(attribute.title),
      // subtext: does not exist in the core app.
      childComponent: ThemeOptionsIconSelect,
      noPermissionDisabled: true,
    },
  }
}
