import React from 'react'
import { STATES, isTrue } from 'helpers'
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
  simpleSettings = false,
}) => {
  const [isSurveyActive] = useAppState(STATES.IS_SURVEY_ACTIVE)
  const getAttributeValueFromPath = (attributePath, languageBased) => {
    const path = attributePath.split('.')
    const attribute = path.reduce((acc, key) => acc[key], question)

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

  const getUpdateValueFromPath = (value, attribute) => {
    const attributePath = attribute?.attributePath ?? ''
    const attributeName = attributePath.toString().includes('attributes.')
      ? attributePath.replace('attributes.', '')
      : ''

    const isAdvancedAttribute = attributePath.includes('attributes.')

    const updateValue = {}

    if (isAdvancedAttribute) {
      updateValue[attributeName] = {
        [attribute.languageBased ? language : '']: value,
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

    // update other attributes that depends on this attribute
    attributes.map((dependsOnAttribute) => {
      if (
        dependsOnAttribute.dependsOn &&
        dependsOnAttribute.dependsOn.attributePath === attribute.attributePath
      ) {
        if (typeof value !== 'object' && !isTrue(value)) {
          const isAdvancedAttribute =
            dependsOnAttribute.attributePath.includes('attributes.')

          const updateValue = getUpdateValueFromPath(
            dependsOnAttribute.onDependsToggle.onFalse,
            dependsOnAttribute
          )
          handleUpdate(updateValue, isAdvancedAttribute)
        }
      }
    })
  }

  return (
    <SettingsWrapper
      simpleSettings={simpleSettings}
      isAdvanced={isAdvanced}
      title={title}
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

        // if the attribute depends on another attribute and it's not true, skip this attribute
        if (attribute.dependsOn) {
          const dependsOn = attribute.dependsOn
          const dependsOnValue = getAttributeValueFromPath(
            dependsOn.attributePath,
            dependsOn.languageBased
          )

          if (!isTrue(dependsOnValue)) {
            return (
              <React.Fragment
                key={`${title}-settings-${attribute.attributePath}`}
              ></React.Fragment>
            )
          }
        }

        const value = getAttributeValueFromPath(
          attribute.attributePath,
          attribute.languageBased
        )

        return (
          <div
            className="right-side-bar-settings"
            key={`${title}-settings-${attribute.attributePath}${attribute.props.labelText}`}
          >
            {/*Globally disabled when survey is active for beta */}
            <TooltipContainer
              tip={getTooltipMessages().ACTIVE_DISABLED}
              showTip={isSurveyActive}
            >
              <attribute.component
                {...attribute.props}
                activeDisabled={isSurveyActive}
                noPermissionDisabled={true}
                value={
                  value
                    ? value
                    : attribute.props.value
                      ? attribute.props.value
                      : ''
                }
                name={attribute.attributePath}
                update={(value) => handleUpdateAttribute(value, attribute)}
                isSimpleSettings={simpleSettings}
              />
            </TooltipContainer>
          </div>
        )
      })}
    </SettingsWrapper>
  )
}
