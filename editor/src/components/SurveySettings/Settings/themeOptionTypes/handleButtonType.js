import { ToggleButtons } from 'components/UIComponents'
import { Entities } from 'helpers'
import {
  CornerRoundedIcon,
  CornerMediumIcon,
  CornerNormalIcon,
} from '../../../icons/index.js'

// Map out the icon filenames to the imported icon components
const iconMappings = {
  'cornerradius_0.svg': CornerNormalIcon,
  'cornerradius_4.svg': CornerMediumIcon,
  'cornerradius_20.svg': CornerRoundedIcon,
}
const getToggleOptions = (optionLabels, options, optionimages) => {
  optionLabels = optionLabels.split('|')
  optionimages = optionimages.split('|')
  options = options.split('|')

  return options.map((option, index) => ({
    name: t(optionLabels[index]),
    icon: iconMappings[optionimages[index]] ?? optionimages[index],
    value: option,
  }))
}

/**
 * @param attribute {object}
 * @param keyPath {string}
 */
export const handleButtonType = (attribute = {}, keyPath) => {
  return {
    keyPath: `themesettings.${keyPath}`,
    entity: Entities.themeSettings,
    props: {
      id: keyPath,
      mainText: t(attribute.title),
      noPermissionDisabled: true,
      // subtext: does not exist in the core app.
      childComponent: ToggleButtons,
      toggleOptions: getToggleOptions(
        attribute.optionlabels,
        attribute.options,
        attribute.optionimages
      ),
    },
  }
}
