import { Select } from 'components/UIComponents'
import { Entities, FILE_UPLOAD_MAX_SIZE_STRING } from 'helpers'
import { format } from 'util'
import ThemeOptionsImageUpload from '../../../ThemeOptions/ThemeOptionsImageUpload.js'

/**
 *
 * @param attribute {object}
 * @param keyPath {string}
 * @param imageFileList {object}
 * @param hasFileUpload {boolean}
 */
export const handleDropdownType = (
  attribute = {},
  keyPath,
  imageFileList,
  hasFileUpload = false
) => {
  return {
    keyPath: `themesettings.${keyPath}`,
    entity: Entities.themeSettings,
    selectOptions: () => {
      return attribute.dropdownoptions
    },
    formatPreview: (value) => {
      if (!hasFileUpload) {
        return null
      }
      const val =
        value.currentValue === 'inherit'
          ? value.parentValue
          : value.currentValue
      const image = value.dropdownoptions.find((option) => option.value === val)
      if (image) {
        return image?.imagePreviewPath ?? null
      }
      return null
    },
    props: {
      id: keyPath,
      mainText: t(attribute.title),
      // subtext: does not exist in the core app.
      childComponent: Select,
      subText: hasFileUpload
        ? format(
            t('Change or upload your own background image (max. file size %s)'),
            FILE_UPLOAD_MAX_SIZE_STRING
          )
        : null,
      secondaryChildComponent: hasFileUpload ? ThemeOptionsImageUpload : null,
      childOnNewLine: true,
      noPermissionDisabled: true,
    },
  }
}
