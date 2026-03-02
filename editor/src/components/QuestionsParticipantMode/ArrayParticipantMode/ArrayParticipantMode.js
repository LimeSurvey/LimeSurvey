import { cloneDeep } from 'lodash'
import { useMemo, useRef } from 'react'
import { Table } from 'react-bootstrap'

import {
  getNotSupportedQuestionTypeInfo,
  getQuestionTypeInfo,
} from 'components/QuestionTypes'
import {
  FormCheck,
  formCheckName,
  Input,
  Select,
  selectName,
} from 'components/UIComponents'
import { Entities, L10ns, mockAnswer, SCALE_1, SCALE_2 } from 'helpers'

const arrayOrDualScaleIsChecked = (
  rowId,
  columnId,
  columnCode,
  rowIdKey,
  columnIdKey,
  valueInfo = {},
  questionThemeName,
  columnNumber
) => {
  if (
    ![
      getQuestionTypeInfo().ARRAY.theme,
      getQuestionTypeInfo().ARRAY_DUAL_SCALE.theme,
      getNotSupportedQuestionTypeInfo().ARRAY_FIVE_POINT.theme,
      getNotSupportedQuestionTypeInfo().ARRAY_TEN_POINT.theme,
      getNotSupportedQuestionTypeInfo().ARRAY_YES_NO_UNCERTAIN.theme,
      getNotSupportedQuestionTypeInfo().ARRAY_INCREASE_SAME_DECREASE.theme,
    ].includes(questionThemeName)
  ) {
    return false
  }

  const sameRowId = valueInfo[rowIdKey] == rowId
  const sameColumnId = valueInfo[columnIdKey] == columnId

  if (
    [
      getQuestionTypeInfo().ARRAY.theme,
      getQuestionTypeInfo().ARRAY_DUAL_SCALE.theme,
    ].includes(questionThemeName)
  ) {
    return sameRowId && sameColumnId
  } else if (
    [
      getNotSupportedQuestionTypeInfo().ARRAY_FIVE_POINT.theme,
      getNotSupportedQuestionTypeInfo().ARRAY_TEN_POINT.theme,
      getNotSupportedQuestionTypeInfo().ARRAY_YES_NO_UNCERTAIN.theme,
      getNotSupportedQuestionTypeInfo().ARRAY_INCREASE_SAME_DECREASE.theme,
    ].includes(questionThemeName)
  ) {
    return columnNumber == valueInfo.value || valueInfo.value == columnCode
  }

  return false
}

const arrayByColumnIsChecked = (
  rowId,
  columnId,
  rowIdKey,
  columnIdKey,
  valueInfo,
  questionThemeName
) => {
  if (questionThemeName !== getQuestionTypeInfo().ARRAY_COLUMN.theme) {
    return false
  }

  const sameRowId = valueInfo[rowIdKey] == rowId
  const sameColumnId = valueInfo[columnIdKey] == columnId

  return sameRowId && sameColumnId
}

const noAnswerIsChecked = (
  rowValueInfo,
  columnValueInfo,
  isLastColumn,
  isLastRow,
  isMandatory,
  questionThemeName
) => {
  const valueInfo =
    questionThemeName === getQuestionTypeInfo().ARRAY_COLUMN.theme
      ? columnValueInfo
      : rowValueInfo

  const isLastIndex =
    questionThemeName === getQuestionTypeInfo().ARRAY_COLUMN.theme
      ? isLastRow
      : isLastColumn

  // no answer is only shown when the question is not mandatory and always the last index.
  if (isMandatory || !isLastIndex || valueInfo.value) {
    return false
  }

  return true
}

export const ArrayParticipantMode = ({
  language,
  question: {
    subquestions,
    questionThemeName,
    answers,
    mandatory,
    attributes: {
      multiflexible_max: maxValue,
      multiflexible_min: minValue,
      multiflexible_step: valueStep,
    } = {},
  },
  values = {},
  onValueChange = () => {},
}) => {
  const rowsRef = useRef([])
  const isArrayByColumn =
    questionThemeName === getQuestionTypeInfo().ARRAY_COLUMN.theme
  const isArrayByText =
    questionThemeName === getQuestionTypeInfo().ARRAY_TEXT.theme
  const isArrayByNumbers =
    questionThemeName === getQuestionTypeInfo().ARRAY_NUMBERS.theme
  const isArrayDualScale =
    questionThemeName === getQuestionTypeInfo().ARRAY_DUAL_SCALE.theme
  const isArray5Point =
    questionThemeName ===
    getNotSupportedQuestionTypeInfo().ARRAY_FIVE_POINT.theme
  const isArray10Point =
    questionThemeName ===
    getNotSupportedQuestionTypeInfo().ARRAY_TEN_POINT.theme
  const isYesNoUncertain =
    questionThemeName ===
    getNotSupportedQuestionTypeInfo().ARRAY_YES_NO_UNCERTAIN.theme
  const isSameIncreaseDecrease =
    questionThemeName ===
    getNotSupportedQuestionTypeInfo().ARRAY_INCREASE_SAME_DECREASE.theme

  const type = useMemo(() => {
    if (isArrayByText) {
      return 'text'
    } else if (isArrayByNumbers) {
      return 'select' // not important, could return null but just to make it more readable
    } else {
      return 'radio'
    }
  }, [questionThemeName])

  const UiComponentToRender =
    {
      [getQuestionTypeInfo().ARRAY.theme]: FormCheck,
      [getQuestionTypeInfo().ARRAY_COLUMN.theme]: FormCheck,
      [getQuestionTypeInfo().ARRAY_DUAL_SCALE.theme]: FormCheck,
      [getQuestionTypeInfo().ARRAY_NUMBERS.theme]: Select,
      [getQuestionTypeInfo().ARRAY_TEXT.theme]: Input,
      [getNotSupportedQuestionTypeInfo().ARRAY_FIVE_POINT.theme]: FormCheck,
      [getNotSupportedQuestionTypeInfo().ARRAY_TEN_POINT.theme]: FormCheck,
    }[questionThemeName] || FormCheck

  const columns = useMemo(() => {
    let result = {}
    let scale = SCALE_1

    if (isArrayByText || isArrayByNumbers || isArrayByColumn) {
      const squestions = cloneDeep(subquestions)

      const items = Array.isArray(squestions)
        ? squestions.filter(
            (subquestion) => subquestion.scaleId === SCALE_2 || isArrayByColumn
          )
        : []

      scale = isArrayByColumn ? undefined : SCALE_2

      result = {
        items,
        itemsKey: 'subquestions',
        idKey: 'qid',
        sortKey: 'sortOrder',
        titleKey: 'question',
        rowName: 'subquestion',
        codeKey: 'title',
        placeholder: t('Subquestion'),
        scaleId: isArrayByColumn ? undefined : SCALE_2,
        entity: Entities.subquestion,
      }
    } else if (isArray5Point || isArray10Point) {
      const maxItems = isArray5Point ? 5 : 10
      const items = Array.from({ length: maxItems }).map((_, index) => {
        return mockAnswer(`${index + 1}`, [language], { code: index + 1 })
      })

      result = {
        items,
        itemsKey: 'answers',
        idKey: 'aid',
        sortKey: 'sortOrder',
        titleKey: 'answer',
        rowName: 'answer option',
        placeholder: t('Answer option'),
        codeKey: 'code',
        scaleId: SCALE_1,
        entity: Entities.answer,
      }
    } else if (isYesNoUncertain || isSameIncreaseDecrease) {
      const firstItem = isYesNoUncertain
        ? mockAnswer(t('Yes'), [language], { code: 'Y' })
        : mockAnswer(t('Increase'), [language], { code: 'I' })
      const secondItem = isYesNoUncertain
        ? mockAnswer(t('No'), [language], { code: 'N' })
        : mockAnswer(t('Same'), [language], { code: 'S' })
      const thirdItem = isYesNoUncertain
        ? mockAnswer(t('Uncertain'), [language], { code: 'U' })
        : mockAnswer(t('Decrease'), [language], { code: 'D' })

      result = {
        items: [firstItem, secondItem, thirdItem],
        itemsKey: 'answers',
        idKey: 'aid',
        sortKey: 'sortOrder',
        titleKey: 'answer',
        rowName: 'answer option',
        placeholder: t('Answer option'),
        codeKey: 'code',
        scaleId: SCALE_1,
        entity: Entities.answer,
      }
    } else {
      result = {
        items: cloneDeep(answers),
        itemsKey: 'answers',
        idKey: 'aid',
        sortKey: 'sortOrder',
        titleKey: 'answer',
        rowName: 'answer option',
        placeholder: t('Answer option'),
        codeKey: 'code',
        scaleId: SCALE_1,
        entity: Entities.answer,
      }
    }

    if (
      !mandatory &&
      UiComponentToRender.name === formCheckName &&
      !isArrayByColumn
    ) {
      result.items.push({
        l10ns: {
          [language]: {
            [result.entity === Entities.subquestion ? 'question' : 'answer']:
              'No answer',
          },
        },
        scaleId: isArrayDualScale ? undefined : scale,
      })
    }

    return result
  }, [answers, subquestions])

  const rows = useMemo(() => {
    let items
    let idKey = 'qid'
    let itemsKey = 'subquestions'
    let sortKey = 'sortOrder'
    let titleKey = 'question'
    let rowName = 'subquestion'
    let codeKey = 'title'
    let placeholder = 'Subquestion'
    let entity = Entities.subquestion

    const squestions = cloneDeep(subquestions)

    if (isArrayByText || isArrayByNumbers) {
      items = Array.isArray(squestions)
        ? squestions.filter((subquestion) => subquestion.scaleId === SCALE_1)
        : []
    } else if (isArrayByColumn) {
      items = cloneDeep(answers)
      idKey = 'aid'
      itemsKey = 'answers'
      sortKey = 'sortOrder'
      titleKey = 'answer'
      rowName = 'answer option'
      placeholder = 'Answer option'
      codeKey = 'code'
      entity = Entities.answer
    } else {
      items = squestions ?? []
    }

    if (
      !mandatory &&
      UiComponentToRender.name === formCheckName &&
      isArrayByColumn
    ) {
      items.push({
        l10ns: {
          [language]: {
            ['answer']: 'No answer',
          },
        },
      })
    }

    const info = {
      items,
      idKey,
      itemsKey,
      sortKey,
      titleKey,
      rowName,
      placeholder,
      SCALE_1,
      codeKey,
      entity,
    }

    return info
  }, [answers, subquestions])

  const options = useMemo(() => {
    if (UiComponentToRender.name !== selectName) {
      return []
    }

    const min = +minValue?.[''] || 0
    const max = +maxValue?.[''] || 10
    const step = +valueStep?.[''] || 1

    const _options = []
    for (let i = min; i <= max; i += step) {
      _options.push({
        label: i.toString(),
        value: i.toString(),
      })
    }

    if (!mandatory) {
      _options.push({
        label: t('No answer'),
        value: '',
      })
    }

    return _options
  }, [values, rows, columns])

  const valuesInfo = useMemo(() => {
    const clonedValues = cloneDeep(values)

    const infos = clonedValues.map((value) => {
      if (UiComponentToRender.name === selectName) {
        value.value = {
          label: value.value,
          value: value.value,
        }
      }

      return {
        ...value,
      }
    })

    return infos
  }, [values, options, rows, columns])

  const onUpdate = (value, rowIndex, isNoAnswer, valueInfo) => {
    const rowRef = rowsRef.current[rowIndex]
    const elements = rowRef?.querySelectorAll('#component-input')

    onValueChange(value, valueInfo.key)

    if (isNoAnswer) {
      elements.forEach((element, index) => {
        // Set checked to false for all elements except the last one
        if (index !== elements.length - 1) {
          element.checked = false
        }
      })
    }
  }

  return (
    <>
      <Table className="array-temp">
        <thead>
          <tr>
            <th></th>
            {columns.items.map((column, index) => (
              <th
                className="text-center choice"
                key={`${index}-${columns.titleKey}-th`}
              >
                {L10ns({
                  prop: columns.titleKey,
                  language,
                  l10ns: column.l10ns,
                })}
              </th>
            ))}
          </tr>
        </thead>
        <tbody>
          {rows.items.map((row, rowIndex) => {
            return (
              <tr
                ref={(el) => rowsRef.current.push(el)}
                key={`${rowIndex}-${row.titleKey}-tr`}
              >
                <td className="choice">
                  {L10ns({
                    prop: rows.titleKey,
                    language,
                    l10ns: row.l10ns,
                  })}
                </td>
                {columns.items.map((column, columnIndex) => {
                  let value =
                    valuesInfo[rowIndex * columns?.items?.length + columnIndex]
                      ?.value

                  value =
                    UiComponentToRender.name === formCheckName
                      ? isArrayByColumn
                        ? row[rows.codeKey]
                        : column[columns.codeKey]
                      : value

                  return (
                    <td
                      className="text-center choice"
                      key={`${columnIndex}-td-${rowIndex}`}
                    >
                      <UiComponentToRender
                        groupName={`${isArrayByColumn ? column[columns.idKey] : row[rows.idKey]}${column.scaleId}`}
                        type={type}
                        options={options}
                        value={value ? value : ''}
                        defaultValue={value ? value : ''}
                        id="component-input"
                        sendValueOnUpdate={true}
                        defaultChecked={
                          arrayOrDualScaleIsChecked(
                            row[rows.idKey],
                            column[columns.idKey],
                            column[columns.codeKey],
                            rows.idKey,
                            columns.idKey,
                            valuesInfo[
                              isArrayDualScale
                                ? rowIndex * 2 + column.scaleId
                                : rowIndex
                            ],
                            questionThemeName,
                            columnIndex + 1
                          ) ||
                          arrayByColumnIsChecked(
                            row[rows.idKey],
                            column[columns.idKey],
                            rows.idKey,
                            columns.idKey,
                            valuesInfo[columnIndex],
                            questionThemeName
                          ) ||
                          noAnswerIsChecked(
                            valuesInfo[rowIndex],
                            valuesInfo[columnIndex],
                            columnIndex === columns.items.length - 1,
                            rowIndex === rows.items.length - 1,
                            mandatory,
                            questionThemeName
                          )
                        }
                        update={(value) => {
                          onUpdate(
                            value,
                            rowIndex,
                            isArrayByColumn
                              ? rowIndex === rows.items.length - 1 && !mandatory
                              : columnIndex === columns.items.length - 1 &&
                                  !mandatory,
                            UiComponentToRender.name === formCheckName
                              ? isArrayByColumn
                                ? valuesInfo[columnIndex]
                                : isArrayDualScale
                                  ? valuesInfo[rowIndex * 2 + column.scaleId]
                                  : valuesInfo[rowIndex]
                              : valuesInfo[
                                  rowIndex * columns?.items?.length +
                                    columnIndex
                                ]
                          )
                        }}
                      />
                    </td>
                  )
                })}
              </tr>
            )
          })}
        </tbody>
      </Table>
    </>
  )
}
