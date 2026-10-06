import React from 'react'
import { STATES, isTempId, isTrue } from 'helpers'
import { getTooltipMessages } from 'helpers/options'
import { useAppState } from 'hooks'
import { SettingsWrapper } from 'components/UIComponents'

import { TooltipContainer } from '../TooltipContainer/TooltipContainer'

export const Setting = ({
  question,
  handleUpdate,
  isAdvanced = false,
  language = 'en',
  title = '',
  attributes = [],
  allAttributes = [],
  simpleSettings = false,
  hasDefaultAttributeValues = false,
  sectionExpanded,
  onSectionToggle,
}) => {
  const [isSurveyActive] = useAppState(STATES.IS_SURVEY_ACTIVE)
  const [hasSurveyUpdatePermission] = useAppState(
    STATES.HAS_SURVEY_UPDATE_PERMISSION
  )
  const isDependsOnSatisfied = (dependsOn, dependsOnValue) => {
    if (!dependsOn) {
      return true
    }

    if (Object.prototype.hasOwnProperty.call(dependsOn, 'value')) {
      return String(dependsOnValue) === String(dependsOn.value)
    }

    if (Array.isArray(dependsOn.values)) {
      return dependsOn.values
        .map((value) => String(value))
        .includes(String(dependsOnValue))
    }

    return isTrue(dependsOnValue)
  }

  const getConditions = (dependsOn) =>
    Array.isArray(dependsOn) ? dependsOn : dependsOn ? [dependsOn] : []

  const isConditionSatisfied = (condition, value) => {
    if (condition.notEmpty) {
      return Array.isArray(value)
        ? value.length > 0
        : value !== '' && value !== null && value !== undefined
    }

    return isDependsOnSatisfied(condition, value)
  }

  // `overrides` holds values that were just changed but are not in `question` yet.
  const areConditionsSatisfied = (dependsOn, overrides = {}) =>
    getConditions(dependsOn).every((condition) => {
      const value = Object.prototype.hasOwnProperty.call(
        overrides,
        condition.attributePath
      )
        ? overrides[condition.attributePath]
        : condition.notEmpty
          ? getRawValueFromPath(condition.attributePath)
          : getAttributeValueFromPath(
              condition.attributePath,
              condition.languageBased
            )

      return isConditionSatisfied(condition, value)
    })

  const getRawValueFromPath = (attributePath) =>
    attributePath.split('.').reduce((acc, key) => acc?.[key], question)

  const getAttributeValueFromPath = (attributePath, languageBased) => {
    const attribute = getRawValueFromPath(attributePath)

    if (!attribute) {
      return ''
    }

    if (['string', 'number', 'boolean'].includes(typeof attribute)) {
      return attribute
    } else if (typeof attribute === 'object') {
      if (attribute[''] && !languageBased) {
        return attribute['']
      }

      return attribute[language]
    }

    return undefined
  }

  const getFullAttributeValueFromPath = (attributePath) => {
    return getRawValueFromPath(attributePath) || ''
  }

  const getUpdateValueFromPath = (value, attribute) => {
    const attributePath = attribute?.attributePath ?? ''
    const attributeName = attributePath.toString().includes('attributes.')
      ? attributePath.replace('attributes.', '')
      : ''

    // Todo: why is it called advanced attribute?
    const isAdvancedAttribute = attributePath.includes('attributes.')

    const updateValue = {}

    if (isAdvancedAttribute) {
      if (attribute.languageBased) {
        updateValue[attributeName] = {
          ...getFullAttributeValueFromPath(attributePath),
          [language]: value,
        }
      } else {
        updateValue[attributeName] = {
          ['']: value,
        }
      }
    } else {
      attribute.returnValues.map((returnValue) => {
        updateValue[returnValue] = value[returnValue]
          ? value[returnValue]
          : value
      })
    }

    return { ...updateValue }
  }

  if (!attributes.length) {
    return <></>
  }

  const handleUpdateAttribute = (value, attribute) => {
    // Advanced means the attribute is  inside the attributes object.
    // Like attributes.numbers_only => is an advanced attribute
    // But mandatory for instance is not an advanced attribute but a base attribute.
    const isAdvancedAttribute = attribute.attributePath.includes('attributes.')

    const updateValue = getUpdateValueFromPath(value, attribute)
    handleUpdate(updateValue, isAdvancedAttribute)

    // update other attributes that depends on this attribute (in any section)
    const candidates = allAttributes.length ? allAttributes : attributes
    const handledPaths = new Set()
    candidates.forEach((dependsOnAttribute) => {
      const conditions = getConditions(dependsOnAttribute.dependsOn)
      if (
        handledPaths.has(dependsOnAttribute.attributePath) ||
        !conditions.some(
          (condition) => condition.attributePath === attribute.attributePath
        )
      ) {
        return
      }
      handledPaths.add(dependsOnAttribute.attributePath)

      if (
        !areConditionsSatisfied(dependsOnAttribute.dependsOn, {
          [attribute.attributePath]: value,
        })
      ) {
        const isAdvancedAttribute =
          dependsOnAttribute.attributePath.includes('attributes.')

        const updateValue = getUpdateValueFromPath(
          dependsOnAttribute.onDependsToggle?.onFalse ?? '',
          dependsOnAttribute
        )
        handleUpdate(updateValue, isAdvancedAttribute)
      }
    })
  }

  return (
    <SettingsWrapper
      simpleSettings={simpleSettings}
      isAdvanced={isAdvanced}
      title={title}
      isExpanded={sectionExpanded}
      onToggle={(isExpanded) => onSectionToggle?.(title, isExpanded)}
    >
      {attributes.map((attribute) => {
        if (
          (attribute.attributePath === 'relevance' &&
            process.env.REACT_APP_DEV_MODE) ||
          attribute.hidden
        ) {
          return (
            <React.Fragment
              key={`${title}-settings-${attribute.attributePath}`}
            ></React.Fragment>
          )
        }

        // if the attribute depends on other attributes/question data that aren't satisfied, skip this attribute
        if (
          attribute.dependsOn &&
          !areConditionsSatisfied(attribute.dependsOn)
        ) {
          return (
            <React.Fragment
              key={`${title}-settings-${attribute.attributePath}`}
            ></React.Fragment>
          )
        }

        const value = getAttributeValueFromPath(
          attribute.attributePath,
          attribute.languageBased
        )
        const isDisabled =
          ([
            'questionThemeName',
            'encrypted',
            'attributes.save_as_default',
            'defaultAttributeValuesActions',
            'other',
          ].includes(attribute.attributePath) ||
            attribute.disableWhenActive) &&
          isSurveyActive
            ? true
            : attribute.action &&
              (isTempId(question.qid) || !hasSurveyUpdatePermission)

        const options =
          typeof attribute.getOptions === 'function'
            ? attribute.getOptions({ question, language })
            : undefined

        const attributeProps = {
          ...attribute.props,
          ...(options ? { options } : {}),
          ...(attribute.action
            ? {
                hasDefaultAttributeValues,
              }
            : {}),
        }

        return (
          <div
            className="right-side-bar-settings"
            key={`${question?.qid}-${title}-settings-${attribute.attributePath}${attribute.props.labelText}`}
          >
            <TooltipContainer
              tip={getTooltipMessages().ACTIVE_DISABLED}
              showTip={isDisabled}
            >
              <attribute.component
                {...attributeProps}
                activeDisabled={isDisabled}
                noPermissionDisabled={true}
                value={
                  value
                    ? value
                    : attributeProps.value
                      ? attributeProps.value
                      : ''
                }
                name={attribute.attributePath}
                update={(value) =>
                  attribute.action
                    ? handleUpdate(value, false)
                    : handleUpdateAttribute(value, attribute)
                }
                isSimpleSettings={simpleSettings}
                theme="light"
              />
            </TooltipContainer>
          </div>
        )
      })}
    </SettingsWrapper>
  )
}
