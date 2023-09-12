import { useEffect, useState } from 'react'
import { Button } from 'react-bootstrap'

import { L10ns, RemoveHTMLTagsInString } from 'helpers'
import { AddIcon } from 'components/icons'

import { Option } from './Option'

export const StringBuilder = ({
  codeToQuestion,
  value,
  selection,
  onConfirm,
}) => {
  const [variables, setVariables] = useState([])
  const [allQuestionOptions, setAllQuestionOptions] = useState([])
  const [options, setOptions] = useState([])
  const [startingTagIndex, setStartingTagIndex] = useState(-1) // index of {
  const [closingTagIndex, setClosingTagIndex] = useState(-1) // index of }

  const selectionOffset = selection.anchor.offset
  const valueWithoutHTML = RemoveHTMLTagsInString(value)

  const allOperatorsOptions = [
    { value: '*', label: '*', index: 0 },
    { value: '/', label: '/', index: 1 },
    { value: '%', label: '%', index: 2 },
    { value: '+', label: '+', index: 3 },
    { value: '-', label: '-', index: 4 },
    { value: '^', label: '^', index: 5 },
  ]

  const handleConfirm = () => {
    const _options = [...options]
    let hasErrors = false

    for (let i = 0; i < _options.length; i++) {
      const option = _options[i]
      if (!option.value) {
        hasErrors = true
        option.hasError = true
      } else {
        delete option.hasError
      }
    }

    setOptions(_options)

    if (hasErrors) {
      return
    }

    let stringResult = '{'
    for (let i = 0; i < _options.length; i++) {
      const option = _options[i]
      stringResult += option.value
    }

    stringResult += '}'

    onConfirm(stringResult, startingTagIndex, closingTagIndex)
  }

  const addOption = () => {
    const _options = [...options]
    if (_options.length === 0) {
      _options.push({})
    } else {
      _options.push({})
      _options.push({})
    }

    setOptions(_options)
  }

  const handleSelection = (e, index) => {
    const _options = [...options]
    _options[index] = e

    setOptions(_options)
  }

  const handleRemoveOption = (index) => {
    const _options = [...options]
    let removeIndex = index
    let numberOfOptionsToRemove

    if (index === 0 && _options.length === 1) {
      numberOfOptionsToRemove = 1
    } else if (index === 0 && _options.length > 1) {
      numberOfOptionsToRemove = 2
    } else {
      numberOfOptionsToRemove = 2
      removeIndex = index - 1
    }

    _options.splice(removeIndex, numberOfOptionsToRemove)

    setOptions(_options)
  }

  const getVariables = () => {
    let startingTagIndex = -1
    let closingTagIndex = -1

    for (let i = selectionOffset; i > -1; i--) {
      const letter = valueWithoutHTML[i]
      if (letter === '{') {
        startingTagIndex = i
        // In case of the user selected the end of the expression.
      } else if (letter === '}' && i !== selectionOffset) {
        break
      }
    }

    for (let i = selectionOffset; i < valueWithoutHTML.length; i++) {
      const letter = valueWithoutHTML[i]
      if (letter === '}') {
        closingTagIndex = i
      } else if (letter === '{') {
        break
      }
    }

    if (startingTagIndex === -1 || closingTagIndex === -1) {
      return
    }

    const variableString = valueWithoutHTML.slice(
      startingTagIndex + 1,
      closingTagIndex
    )
    const variables = variableString
      .split(/([+\-*/])/)
      .map((variable) => variable.trim())

    setStartingTagIndex(startingTagIndex)
    setClosingTagIndex(closingTagIndex)

    return variables
  }

  useEffect(() => {
    const allQuestionOptions = []
    let index = 0 // used to keep track of the index of the current question code/variable.

    for (const key in codeToQuestion) {
      const title = RemoveHTMLTagsInString(
        L10ns({
          prop: 'question',
          language: 'en',
          l10ns: codeToQuestion[key].question.l10ns,
        })
      )

      allQuestionOptions.push({
        label:
          title === ''
            ? `${index + 1}. What's your question?`
            : `${index + 1}. ${title}`,
        value: key,
        index: index,
      })
      ++index
    }

    setAllQuestionOptions(allQuestionOptions)
    setVariables(getVariables())
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  useEffect(() => {
    const _options = []

    if (!variables?.length) {
      setOptions([{}])
      return
    }

    for (let i = 0; i < variables.length; i++) {
      const variable = variables[i]
      if (i % 2 === 0) {
        _options.push({
          ...allQuestionOptions.find((option) => option.value === variable),
        })
      } else {
        _options.push({
          ...allOperatorsOptions.find((option) => option.value === variable),
        })
      }
    }

    setOptions(_options)
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [variables])

  return (
    <div
      style={{
        height: 400,
        minWidth: 600,
        maxWidth: 600,
        overflowY: 'auto',
      }}
    >
      <div className="d-flex flex-column align-items-center gap-2 justify-content-center">
        {options.map((option, index) => {
          return (
            <Option
              allOperatorsOptions={allOperatorsOptions}
              allQuestionOptions={allQuestionOptions}
              handleSelection={handleSelection}
              key={`option-key-${index}`}
              option={option}
              index={index}
              removeOption={() => handleRemoveOption(index)}
            />
          )
        })}
      </div>

      <div className="mt-3">
        <Button
          variant={'primary'}
          style={{ color: 'white' }}
          className="m-1 add-question-button"
          onClick={addOption}
        >
          <AddIcon className="text-white fill-current" />
        </Button>
        <Button
          variant={'success'}
          style={{ color: 'white' }}
          className="m-1 add-question-button"
          onClick={handleConfirm}
        >
          Confirm
        </Button>
      </div>
    </div>
  )
}
