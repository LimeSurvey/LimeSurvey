import { getSettingValueFromSurvey, errorToast, Entities } from 'helpers'

/**
 * Validates a setting update based on its condition
 * @param {Object} setting - The setting object
 * @param {*} value - The value to validate
 * @param {Object} survey - The survey object
 * @returns {Object|null} - Returns null if valid, or { error: true } if invalid
 */
export const validateSettingUpdate = (setting, value, survey) => {
  const updateConditionSettings = setting.condition?.update?.settings

  if (!updateConditionSettings) {
    return null
  }

  const settingsValue = updateConditionSettings.map((setting) => {
    return {
      setting,
      value: getSettingValueFromSurvey(survey, setting),
    }
  })

  const conditionResult = setting.condition.update.check(value, settingsValue)

  if (!conditionResult.valid) {
    errorToast(conditionResult.errorMessage)
    return { error: true }
  }

  return null
}

/**
 * Formats the update value for a setting
 * @param {Object} setting - The setting object
 * @param {*} value - The raw value
 * @param {Object} globalStates - The global states object
 * @returns {Object} - Object with updateValue and operationValue
 */
export const formatUpdateValue = (setting, value, globalStates) => {
  return setting.formatUpdateValue
    ? setting.formatUpdateValue(value, globalStates)
    : { updateValue: value, operationValue: value }
}

/**
 * Gets the update key for a setting
 * @param {Object} setting - The setting object
 * @param {Object} updateInfo - The update info object
 * @returns {string} - The update key
 */
export const getUpdateKey = (setting, updateInfo) => {
  // Getting the last string after '.' if it exists
  // for example: languageSettings.legalNotice ==Output=> legalNotice
  // or use the key that comes from the formatUpdateValue if it exists
  return updateInfo.updateValueKey
    ? updateInfo.updateValueKey
    : setting?.keyPath.split('.').pop()
}

/**
 * Creates the survey update object based on entity type
 * @param {Object} setting - The setting object
 * @param {Object} updateInfo - The update info object
 * @param {string} updateKey - The update key
 * @param {string} entity - The entity type
 * @param {Object} survey - The survey object
 * @returns {Object} - The update object to pass to update()
 */
export const createSurveyUpdate = (
  setting,
  updateInfo,
  updateKey,
  entity,
  survey
) => {
  if (entity == Entities.themeSettings) {
    let updatedSettings = {
      themesettings: {
        ...survey.themesettings,
        [updateKey]: {
          ...survey.themesettings[updateKey],
          currentValue: updateInfo.updateValue,
        },
      },
    }

    if (updateInfo.secondaryKey && updateInfo.secondaryValue) {
      updatedSettings.themesettings[updateInfo.secondaryKey] = {
        ...survey.themesettings[updateInfo.secondaryKey],
        currentValue: updateInfo.secondaryValue,
      }
    }

    if (
      setting.type === 'dropdown' &&
      !updatedSettings.themesettings[updateKey].dropdownoptions[
        updateInfo.updateValue
      ]
    ) {
      updatedSettings.themesettings[updateKey].dropdownoptions.push({
        value: updateInfo.updateValue,
        label: updateInfo.operationValue,
        group: 'Survey',
        imagePreviewUrl: updateInfo.filePath || null,
      })
    }

    return updatedSettings
  } else {
    return { [updateKey]: updateInfo.updateValue }
  }
}

/**
 * Creates a buffer operation for a setting update
 * @param {Object} setting - The setting object
 * @param {Object} updateInfo - The update info object
 * @param {string} updateKey - The update key
 * @param {string} entity - The entity type
 * @param {string|number} id - The survey/entity ID
 * @param {Object} survey - The survey object
 * @param {Function} createBufferOperation - Function to create buffer operations
 * @returns {Object} - Object with operation and operationEntity
 */
export const createBufferOperationForSetting = (
  setting,
  updateInfo,
  updateKey,
  entity,
  id,
  survey,
  createBufferOperation
) => {
  const operationKey = updateInfo.updateOperationKey
    ? updateInfo.updateOperationKey
    : setting?.keyPath.split('.').pop()

  let operationProps = {
    [operationKey]: updateInfo.operationValue,
  }

  if (updateInfo.secondaryKey && updateInfo.secondaryValue) {
    operationProps[updateInfo.secondaryKey] = updateInfo.secondaryValue
  }

  let operationEntity
  let operationId = id

  switch (entity) {
    case Entities.languageSetting:
      operationEntity = Entities.languageSetting
      operationId = null
      break
    case Entities.themeSettings:
      operationEntity = Entities.themeSettings
      operationProps = {
        templateName: survey.template,
        ...operationProps,
      }
      break
    default:
      operationEntity = Entities.survey
      break
  }

  const operation = createBufferOperation(operationId)
    // eslint-disable-next-line no-unexpected-multiline
    [operationEntity]()
    .update(operationProps)

  return { operation, operationEntity }
}
