import { getQuestionAttributesTitles } from 'helpers'

import {
  componentRegistry,
  inputtypeFallback,
  optionsPresets,
} from './registries'
import { optionsSources } from './optionsSources'

const TAB_KEYS = {
  'general': 'GENERAL',
  'display': 'DISPLAY',
  'logic': 'LOGIC',
  'other': 'OTHER',
  'input': 'INPUT',
  'statistics': 'STATISTICS',
  'timer': 'TIMER',
  'display theme options': 'THEME_OPTIONS',
  'file metadata': 'FILE_META_DATA',
  'location': 'LOCATION',
  'slider': 'SLIDER',
}

const TAB_ORDER = [
  'GENERAL',
  'DISPLAY',
  'LOGIC',
  'OTHER',
  'INPUT',
  'STATISTICS',
  'TIMER',
  'THEME_OPTIONS',
  'FILE_META_DATA',
  'LOCATION',
  'SLIDER',
]

const getTabTitle = (tab = '') => {
  const key = TAB_KEYS[tab.toLowerCase()]
  return key ? getQuestionAttributesTitles()[key] : tab
}

const getOptions = (attribute) => {
  if (attribute.optionsPreset) {
    return optionsPresets[attribute.optionsPreset]?.(attribute.optionsVariant)
  }

  if (Array.isArray(attribute.options) && attribute.options.length) {
    return attribute.options.map(({ value, text }) => ({
      label: text,
      value,
    }))
  }

  return undefined
}

const toConditions = (dependsOn, attributesByName) => {
  if (!Array.isArray(dependsOn)) {
    return []
  }

  return dependsOn
    .map(({ attribute, path, value, values, notEmpty }) => {
      const target = attribute ? attributesByName[attribute] : null

      // The attribute this depends on doesn't exist for this question theme.
      if (attribute && !target) {
        return null
      }

      return {
        attributePath: target ? target.attributePath : path,
        languageBased: target ? !!target.languageBased : false,
        ...(value !== undefined ? { value } : {}),
        ...(Array.isArray(values) ? { values } : {}),
        ...(notEmpty ? { notEmpty: true } : {}),
      }
    })
    .filter(Boolean)
}

const toDefinition = (attribute, attributesByName) => {
  if (attribute.hidden) {
    return null
  }

  const fallback = inputtypeFallback[attribute.inputtype] || {}
  const component = attribute.component
    ? componentRegistry[attribute.component]
    : fallback.component

  if (!component) {
    return null
  }

  const props = {
    ...(attribute.component ? {} : fallback.props),
    ...(attribute.props || {}),
  }

  if (attribute.caption && props.labelText === undefined) {
    props.labelText = attribute.caption
  }

  const options = getOptions(attribute)
  if (options && component === componentRegistry.ToggleButtons) {
    props.toggleOptions = options.map(({ name, label, value }) => ({
      name: name ?? label,
      value,
    }))
  } else if (options) {
    props.options = options.map(({ name, label, value }) => ({
      label: label ?? name,
      value,
    }))
  }

  if (
    attribute.default !== undefined &&
    attribute.default !== '' &&
    props.defaultValue === undefined
  ) {
    const matchingOption = (props.toggleOptions || props.options || []).find(
      (option) => String(option.value) === String(attribute.default)
    )
    props.defaultValue = matchingOption
      ? matchingOption.value
      : attribute.default
  }

  const isQuestionLevel = !attribute.attributePath.startsWith('attributes.')
  const conditions = toConditions(attribute.dependsOn, attributesByName)

  const definition = {
    name: attribute.name,
    component,
    attributePath: attribute.attributePath,
    languageBased: !!attribute.languageBased,
    props,
  }

  if (conditions.length) {
    definition.dependsOn = conditions.length === 1 ? conditions[0] : conditions
    definition.onDependsToggle = {
      onFalse: attribute.onDependsToggle?.onFalse ?? '',
    }
  }

  if (attribute.devOnly) {
    definition.hidden = !process.env.REACT_APP_DEV_MODE
  }

  if (attribute.disableWhenActive) {
    definition.disableWhenActive = true
  }

  if (attribute.action) {
    definition.action = true
  } else if (isQuestionLevel) {
    definition.returnValues = attribute.returnValues?.length
      ? attribute.returnValues
      : [attribute.attributePath]
  }

  if (attribute.optionsSource && optionsSources[attribute.optionsSource]) {
    definition.getOptions = optionsSources[attribute.optionsSource]
  }

  return definition
}

const bySortOrder = (a, b) =>
  (a.sortorder ?? Number.MAX_SAFE_INTEGER) -
  (b.sortorder ?? Number.MAX_SAFE_INTEGER)

/**
 * Builds the QuestionSettings sections from the backend attribute metadata
 * (config.xml merged with the react/*.xml overlays).
 * @param {Object<string, Object>} attributes Attributes keyed by name.
 * @returns {Array<{title: string, attributes: Array}>}
 */
export const buildSettingsFromAttributes = (attributes = {}) => {
  const attributeList = Object.values(attributes).filter(
    (attribute) => attribute?.attributePath
  )

  if (!attributeList.length) {
    return []
  }

  const definitions = new Map()
  attributeList.forEach((attribute) => {
    const definition = toDefinition(attribute, attributes)
    if (definition) {
      definitions.set(attribute.name, definition)
    }
  })

  const simpleAttributes = attributeList
    .filter(
      (attribute) =>
        typeof attribute.simpleOrder === 'number' &&
        definitions.has(attribute.name)
    )
    .sort((a, b) => a.simpleOrder - b.simpleOrder)
    .map((attribute) => definitions.get(attribute.name))

  const tabs = {}
  ;[...attributeList].sort(bySortOrder).forEach((attribute) => {
    if (!definitions.has(attribute.name)) {
      return
    }
    const title = getTabTitle(attribute.tab)
    tabs[title] = tabs[title] || []
    tabs[title].push(definitions.get(attribute.name))
  })

  const orderedTitles = TAB_ORDER.map(
    (key) => getQuestionAttributesTitles()[key]
  )
  const tabTitles = [
    ...orderedTitles.filter((title) => tabs[title]),
    ...Object.keys(tabs).filter((title) => !orderedTitles.includes(title)),
  ]

  return [
    {
      title: getQuestionAttributesTitles().SIMPLE,
      attributes: simpleAttributes,
    },
    ...tabTitles.map((title) => ({ title, attributes: tabs[title] })),
  ]
}
