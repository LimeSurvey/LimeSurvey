import { useState } from 'react'
import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'

import 'tests/mocks'
import { renderWithProviders } from 'tests/testUtils'
import { Input } from 'components/UIComponents'

import { Setting } from './Setting'
import { getOtherAttributes } from './attributes/getOtherAttributes'

const SettingWithState = ({ attributes }) => {
  const [question, setQuestion] = useState({
    qid: 1,
    type: '|',
    attributes,
  })
  const otherAttributes = getOtherAttributes()

  return (
    <Setting
      question={question}
      handleUpdate={(value) =>
        setQuestion((current) => ({
          ...current,
          attributes: { ...current.attributes, ...value },
        }))
      }
      title="Simple"
      simpleSettings
      attributes={[
        otherAttributes.MIN_NUMBER_FILES,
        otherAttributes.MAX_NUMBER_FILES,
        otherAttributes.ALLOWED_FILE_TYPES,
        otherAttributes.MAXIMUM_FILE_SIZE_ALLOWED,
      ]}
    />
  )
}

const DependentSettingWithState = ({ onDependsToggle }) => {
  const [question, setQuestion] = useState({
    qid: 1,
    type: '|',
    attributes: {
      controller: { '': 'x' },
      dependent: { '': 'kept' },
    },
  })

  return (
    <Setting
      question={question}
      handleUpdate={(value) =>
        setQuestion((current) => ({
          ...current,
          attributes: { ...current.attributes, ...value },
        }))
      }
      title="Simple"
      simpleSettings
      attributes={[
        {
          component: Input,
          attributePath: 'attributes.controller',
          props: { dataTestId: 'controller' },
        },
        {
          component: Input,
          attributePath: 'attributes.dependent',
          props: { dataTestId: 'dependent' },
          dependsOn: { attributePath: 'attributes.controller', value: 'x' },
          onDependsToggle,
        },
      ]}
    />
  )
}

describe('Setting dependent attributes', () => {
  const toggleController = async () => {
    const controller = screen.getByTestId('controller')
    await userEvent.clear(controller)
    expect(screen.queryByTestId('dependent')).toBeNull()
    await userEvent.type(controller, 'x')
  }

  test('resets the dependent value when its condition becomes false', async () => {
    await renderWithProviders(
      <DependentSettingWithState onDependsToggle={{ onFalse: '' }} />
    )

    await toggleController()

    expect(screen.getByTestId('dependent').value).toBe('')
  })

  test('keeps the dependent value when keepValue is enabled', async () => {
    await renderWithProviders(
      <DependentSettingWithState
        onDependsToggle={{ onFalse: '', keepValue: true }}
      />
    )

    await toggleController()

    expect(screen.getByTestId('dependent').value).toBe('kept')
  })
})

describe('Setting input default values', () => {
  test('shows the default values when the attributes are not set', async () => {
    await renderWithProviders(<SettingWithState attributes={{}} />)

    expect(screen.getByTestId('min-number-of-files').value).toBe('0')
    expect(screen.getByTestId('max-number-of-files').value).toBe('1')
    expect(screen.getByTestId('maximum-file-size-allowed').value).toBe('10240')
    expect(screen.getByTestId('allowed-file-types').value).toBe(
      'png, gif, doc, odt, jpg, jpeg, pdf, heic'
    )
  })

  test('shows the stored values instead of the default values', async () => {
    await renderWithProviders(
      <SettingWithState
        attributes={{
          min_num_of_files: { '': '2' },
          max_num_of_files: { '': '5' },
          max_filesize: { '': '2048' },
          allowed_filetypes: { '': 'pdf' },
        }}
      />
    )

    expect(screen.getByTestId('min-number-of-files').value).toBe('2')
    expect(screen.getByTestId('max-number-of-files').value).toBe('5')
    expect(screen.getByTestId('maximum-file-size-allowed').value).toBe('2048')
    expect(screen.getByTestId('allowed-file-types').value).toBe('pdf')
  })

  test('keeps an emptied field empty and allows changing the value', async () => {
    await renderWithProviders(<SettingWithState attributes={{}} />)
    const input = screen.getByTestId('max-number-of-files')

    await userEvent.clear(input)
    expect(input.value).toBe('')

    await userEvent.type(input, '7')
    expect(input.value).toBe('7')
  })
})

const DerivedToggleWithState = ({ attributes }) => {
  const [question, setQuestion] = useState({ qid: 1, type: 'L', attributes })

  return (
    <Setting
      question={question}
      handleUpdate={(value) =>
        setQuestion((current) => ({
          ...current,
          attributes: { ...current.attributes, ...value },
        }))
      }
      title="Timer"
      simpleSettings
      attributes={[
        {
          component: Input,
          attributePath: 'attributes.use_warning',
          props: { dataTestId: 'use-warning', defaultValue: '0' },
          valueWhenDependentsSet: {
            value: '1',
            paths: ['attributes.warning'],
          },
        },
        {
          component: Input,
          attributePath: 'attributes.warning',
          props: { dataTestId: 'warning' },
          dependsOn: { attributePath: 'attributes.use_warning', value: '1' },
          onDependsToggle: { onFalse: '' },
        },
      ]}
    />
  )
}

describe('Setting toggle derived from its dependents', () => {
  test('is on when a dependent has a value but the toggle is not stored', async () => {
    await renderWithProviders(
      <DerivedToggleWithState attributes={{ warning: { '': '10' } }} />
    )

    expect(screen.getByTestId('use-warning').value).toBe('1')
    expect(screen.getByTestId('warning').value).toBe('10')
  })

  test('shows its default when no dependent has a value', async () => {
    await renderWithProviders(<DerivedToggleWithState attributes={{}} />)

    expect(screen.getByTestId('use-warning').value).toBe('0')
    expect(screen.queryByTestId('warning')).toBeNull()
  })

  test('uses the stored toggle value over the derived one', async () => {
    await renderWithProviders(
      <DerivedToggleWithState
        attributes={{ use_warning: { '': '0' }, warning: { '': '10' } }}
      />
    )

    expect(screen.getByTestId('use-warning').value).toBe('0')
    expect(screen.queryByTestId('warning')).toBeNull()
  })

  test('stays on while its only value is cleared and retyped', async () => {
    await renderWithProviders(
      <DerivedToggleWithState attributes={{ warning: { '': '10' } }} />
    )
    const warning = screen.getByTestId('warning')

    await userEvent.clear(warning)
    expect(screen.getByTestId('use-warning').value).toBe('1')

    await userEvent.type(screen.getByTestId('warning'), '15')
    expect(screen.getByTestId('warning').value).toBe('15')
  })
})
