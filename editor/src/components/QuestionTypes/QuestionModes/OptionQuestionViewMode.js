import { useEffect, useMemo, useState } from 'react'
import {
  Entities,
  getStringPartsUsingSeperator,
  getAttributeValue,
  isTrue,
  L10ns,
  OTHER_CODE,
} from 'helpers'
import {
  Button,
  ContentEditor,
  contentEditorName,
  FormCheck,
  Input,
  Select,
  selectName,
} from 'components'
import { ImageChoice } from 'components/QuestionTypes/ImageChoice'

import { getQuestionTypeInfo } from '../getQuestionTypeInfo'
import { getNotSupportedQuestionTypeInfo } from '../getNotSupportedQuestionTypeInfo'
import { getCommentedCheckboxOptions } from 'helpers/options'
import { MultipleChoiceNumericalSubquestion } from '../MultipleChoice/MultipleChoiceNumericalSubquestion'
import { cloneDeep } from 'lodash'
import { singleChoiceThemes } from '../singleChoiceThemes'

const dropdownThemeComponents = [
  getQuestionTypeInfo().SINGLE_CHOICE_DROPDOWN.theme,
  getNotSupportedQuestionTypeInfo().LANGUAGE_SWITCH.theme,
  getNotSupportedQuestionTypeInfo().LIST_DROPDOWN_DEFAULT.theme,
]

const commentedCheckboxOptions = getCommentedCheckboxOptions()

const OTHER_POSITIONS = {
  BEGINNING: 'beginning',
  END: 'end',
  SPECIFIC: 'specific',
}

const insertOtherByPosition = (
  items = [],
  otherItem,
  { position = OTHER_POSITIONS.END, specificCode, getCode }
) => {
  if (!otherItem) {
    return
  }

  if (position === OTHER_POSITIONS.BEGINNING) {
    items.unshift(otherItem)
    return
  }

  if (
    position === OTHER_POSITIONS.SPECIFIC &&
    specificCode !== undefined &&
    specificCode !== null &&
    specificCode !== ''
  ) {
    const index = items.findIndex((item) => {
      return String(getCode(item)) === String(specificCode)
    })

    if (index !== -1) {
      items.splice(index + 1, 0, otherItem)
      return
    }
  }

  items.push(otherItem)
}

// todo: add input fields for mutliple numerical/texts
export const OptionQuestionViewMode = ({
  question: { questionThemeName, qid, gid, attributes, mandatory, other } = {},
  language,
  _children = [],
  onValueChange = () => {},
  values = [],
  participantMode = false,
}) => {
  const valueInfo = values?.[0] || {}
  const [selectedIndex, setSelectedIndex] = useState(-1)
  const {
    commented_checkbox,
    slider_layout,
    slider_separator,
    other_replace_text,
    other_position,
    other_position_code,
  } = attributes || {}
  const isDropdownTheme = useMemo(
    () => dropdownThemeComponents.includes(questionThemeName),
    [questionThemeName]
  )
  const isSingleChoiceTheme = useMemo(
    () => singleChoiceThemes.includes(questionThemeName),
    [questionThemeName]
  )

  const isSingleChoiceWithComments = useMemo(() => {
    return (
      questionThemeName ===
      getQuestionTypeInfo().SINGLE_CHOICE_LIST_RADIO_WITH_COMMENT.theme
    )
  }, [questionThemeName])

  const isMultipleShortTexts = useMemo(() => {
    return (
      questionThemeName === getQuestionTypeInfo().MULTIPLE_SHORT_TEXTS.theme
    )
  }, [questionThemeName, commented_checkbox])

  const isMultipleChoiceWithComments = useMemo(() => {
    return (
      questionThemeName ===
      getQuestionTypeInfo().MULTIPLE_CHOICE_WITH_COMMENTS.theme
    )
  }, [questionThemeName, commented_checkbox])

  const isMultipleChoiceNumerical = useMemo(() => {
    return (
      questionThemeName ===
      getQuestionTypeInfo().MULTIPLE_NUMERICAL_INPUTS.theme
    )
  }, [questionThemeName])

  const hasSliderLayout = useMemo(
    () => getAttributeValue(slider_layout, language) === '1',
    [slider_layout, language]
  )

  const childrenInfo = {
    idKey: isSingleChoiceTheme ? 'aid' : 'qid',
    titleKey: isSingleChoiceTheme ? 'answer' : 'question',
    codeKey: isSingleChoiceTheme ? 'code' : 'title',
    entity: isSingleChoiceTheme ? Entities.answer : Entities.subquestion,
  }

  const UiComponentToRender =
    {
      [getQuestionTypeInfo().SINGLE_CHOICE_LIST_RADIO.theme]: FormCheck,
      [getQuestionTypeInfo().SINGLE_CHOICE_LIST_RADIO_WITH_COMMENT.theme]:
        FormCheck,
      [getQuestionTypeInfo().SINGLE_CHOICE_DROPDOWN.theme]: Select,
      [getNotSupportedQuestionTypeInfo().LANGUAGE_SWITCH.theme]: Select,
      [getNotSupportedQuestionTypeInfo().LIST_DROPDOWN_DEFAULT.theme]: Select,
      [getQuestionTypeInfo().SINGLE_CHOICE_BUTTONS.theme]: Button,
      [getQuestionTypeInfo().SINGLE_CHOICE_IMAGE_SELECT.theme]: ImageChoice,
      [getQuestionTypeInfo().MULTIPLE_CHOICE.theme]: FormCheck,
      [getQuestionTypeInfo().MULTIPLE_CHOICE_WITH_COMMENTS.theme]: FormCheck,
      [getQuestionTypeInfo().MULTIPLE_CHOICE_BUTTONS.theme]: Button,
      [getQuestionTypeInfo().MULTIPLE_CHOICE_IMAGE_SELECT.theme]: ImageChoice,
      [getQuestionTypeInfo().MULTIPLE_SHORT_TEXTS.theme]: ContentEditor,
      [getQuestionTypeInfo().MULTIPLE_NUMERICAL_INPUTS.theme]: ContentEditor,
    }[questionThemeName] || FormCheck

  // "Other" is only supported on choice themes (radio/checkbox/buttons/dropdown/ImageChoice)
  const hasOther = isTrue(other)
  const supportsOther = hasOther && UiComponentToRender !== ContentEditor

  const otherLabel = useMemo(() => {
    const replaceText = getAttributeValue(other_replace_text, language)
    return replaceText || t('Other')
  }, [other_replace_text, language])

  const otherPosition = useMemo(() => {
    const position = (getAttributeValue(other_position) || '').toLowerCase()

    if (Object.values(OTHER_POSITIONS).includes(position)) {
      return position
    }

    return OTHER_POSITIONS.END
  }, [other_position])

  const otherPositionCode = useMemo(
    () => getAttributeValue(other_position_code),
    [other_position_code]
  )

  const children = useMemo(() => {
    const childrenArray = cloneDeep(_children)

    if (isDropdownTheme) {
      const selectOptions = childrenArray?.map((child = { l10ns: {} }) => {
        const label = L10ns({
          prop: childrenInfo.titleKey,
          language,
          l10ns: child.l10ns,
        })

        return {
          label,
          value: child[childrenInfo.codeKey],
        }
      })

      if (supportsOther) {
        insertOtherByPosition(
          selectOptions,
          {
            label: otherLabel,
            value: OTHER_CODE,
          },
          {
            position: otherPosition,
            specificCode: otherPositionCode,
            getCode: (item) => item.value,
          }
        )
      }

      // incase of a dropdown question, we only need one select
      return [{ options: selectOptions }]
    } else {
      if (!mandatory && isSingleChoiceTheme) {
        childrenArray.push({
          l10ns: { [language]: { answer: t('No answer') } },
          aid: -999,
          code: '',
          isNoAnswer: true,
        })
      }

      if (supportsOther) {
        insertOtherByPosition(
          childrenArray,
          {
            l10ns: { [language]: { [childrenInfo.titleKey]: otherLabel } },
            [childrenInfo.idKey]: OTHER_CODE,
            [childrenInfo.codeKey]: OTHER_CODE,
            isOther: true,
          },
          {
            position: otherPosition,
            specificCode: otherPositionCode,
            getCode: (item) => item?.[childrenInfo.codeKey],
          }
        )
      }

      return childrenArray
    }
  }, [
    _children,
    childrenInfo.codeKey,
    childrenInfo.idKey,
    childrenInfo.titleKey,
    isDropdownTheme,
    isSingleChoiceTheme,
    language,
    mandatory,
    otherLabel,
    otherPosition,
    otherPositionCode,
    supportsOther,
  ])

  const getChildTitle = (l10ns) => {
    const text = L10ns({
      prop: childrenInfo.titleKey,
      language,
      l10ns: l10ns,
    })

    if (isMultipleChoiceNumerical && hasSliderLayout) {
      const seperator = getAttributeValue(slider_separator) || '|'
      const { value } = getStringPartsUsingSeperator(text, seperator)
      return value
    }

    return text
  }

  useEffect(() => {
    if (UiComponentToRender.name === selectName) {
      children[0]?.options.map((option, index) => {
        if (option.value === valueInfo?.aid) {
          setSelectedIndex(index)
        }
      })
    } else {
      children.map((child, index) => {
        if (child[childrenInfo.idKey] === valueInfo?.aid) {
          setSelectedIndex(index)
        }
      })
    }
  }, [children])

  const shouldShowInput =
    (isMultipleChoiceWithComments &&
      getAttributeValue(commented_checkbox) ===
        commentedCheckboxOptions.ALWAYS.value) ||
    getAttributeValue(commented_checkbox) ===
      commentedCheckboxOptions.CHECKED.value

  const childrenValuesInOrder = useMemo(() => {
    if (!participantMode) {
      return []
    }

    const valuesInOrder = []

    if (isSingleChoiceTheme) {
      valuesInOrder.push(valueInfo)
      return valuesInOrder
    }

    for (let i = 0; i < children.length; i++) {
      const child = children[i]

      valuesInOrder.push(
        values?.find((value) => {
          return value[childrenInfo.idKey] == child[childrenInfo.idKey]
        })
      )
    }

    return valuesInOrder
  }, [children.length])

  // disable the dropdown if the question is in participant mode and the dropdown has no options
  if (
    participantMode &&
    UiComponentToRender.name === selectName &&
    !children[0]?.options?.length
  ) {
    return null
  }
  return (
    <div className="children-parent">
      {children?.map((child, index) => {
        const value = isSingleChoiceTheme
          ? childrenValuesInOrder[0]
          : childrenValuesInOrder[index]

        return (
          <div
            className={'child'}
            data-testid="child-option"
            key={`view-mode-child-${index}-${child[childrenInfo.idKey]}`}
          >
            <UiComponentToRender
              value={
                UiComponentToRender.name === selectName
                  ? child.options[selectedIndex]
                  : getChildTitle(child.l10ns)
              }
              defaultValue={
                UiComponentToRender.name === selectName
                  ? child.options[selectedIndex]
                  : getChildTitle(child.l10ns)
              }
              text={getChildTitle(child.l10ns)}
              variant="outline-success"
              update={(newValue) => {
                onValueChange(
                  isSingleChoiceTheme && UiComponentToRender.name !== selectName
                    ? child[childrenInfo.codeKey]
                    : newValue,
                  value?.key
                )
              }}
              onClick={() => {
                setSelectedIndex(index)
                onValueChange(
                  isSingleChoiceTheme
                    ? child[childrenInfo.codeKey]
                    : getChildTitle(child.l10ns),
                  value?.key
                )
              }}
              className="child-ui-component"
              label={
                <ContentEditor
                  placeholder={
                    isSingleChoiceTheme ? 'Answer option' : 'Subquestion'
                  }
                  className="choice"
                  value={getChildTitle(child.l10ns)}
                  disabled={true}
                />
              }
              key={`uicomponent-${qid}-${index}-questionmode`}
              index={index}
              type={isSingleChoiceTheme ? 'radio' : 'checkbox'}
              inputType={isSingleChoiceTheme ? 'radio' : 'checkbox'}
              isFocused={false}
              idPrefix={isSingleChoiceTheme ? 'a' : 'q'}
              id={child[childrenInfo.idKey]}
              hasReset={false}
              options={child.options}
              defaultChecked={
                isSingleChoiceTheme
                  ? child[childrenInfo.idKey] === value?.aid
                  : value?.checked
              }
              groupName={`${gid}X${qid}`}
              active={selectedIndex === index}
              disabled={UiComponentToRender.name === contentEditorName}
              isNoAnswer={child.isNoAnswer}
            />
            {shouldShowInput && (
              <Input
                onClick={(e) => {
                  e.stopPropagation()
                }}
                value={value?.comment?.value}
                placeholder={st('Enter your answer here.')}
                rows={1}
                maxLength={Infinity}
                className={`w-100 d-block ${!participantMode ? 'comment-input' : ''}`}
                dataTestId="multiple-choice-comment-input"
                type="textarea"
                update={(newValue) =>
                  onValueChange(newValue, value?.comment?.key)
                }
              />
            )}
            {isMultipleShortTexts && (
              <Input
                onClick={(e) => {
                  e.stopPropagation()
                }}
                value={value?.value}
                placeholder={st('Enter your answer here.')}
                maxLength={Infinity}
                className={`w-100 d-block ${!participantMode ? 'comment-input' : ''}`}
                dataTestId="multiple-choice-comment-input"
                update={(newValue) => onValueChange(newValue, value?.key)}
              />
            )}
            {isMultipleChoiceNumerical && (
              <div className="ms-auto">
                <MultipleChoiceNumericalSubquestion
                  onChange={(newValue) => onValueChange(newValue, value?.key)}
                  hasSliderLayout={hasSliderLayout}
                  attributes={attributes}
                  child={child}
                  childrenInfo={childrenInfo}
                  language={language}
                  valueInfo={value}
                  participantMode={participantMode}
                />
              </div>
            )}
            {child.isOther && (
              <Input
                onClick={(e) => {
                  e.stopPropagation()
                }}
                placeholder={st('Enter your answer here.')}
                rows={1}
                maxLength={Infinity}
                className={`w-100 d-block ${!participantMode ? 'comment-input' : ''}`}
                dataTestId="other-option-input"
                type="textarea"
              />
            )}
          </div>
        )
      })}
      {isSingleChoiceWithComments && (
        <div className="flex-1 w-50">
          <Input
            onClick={(e) => {
              e.stopPropagation()
            }}
            value={valueInfo?.comment?.value}
            update={(value) => onValueChange(value, valueInfo?.comment?.key)}
            placeholder={st('Enter your comment here.')}
            rows={1}
            maxLength={Infinity}
            dataTestId="multiple-choice-comment-input"
          />
        </div>
      )}
    </div>
  )
}
