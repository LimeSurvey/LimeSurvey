import { getTimerAttributes } from '../attributes'
import { buildSettingsFromAttributes } from './buildSettingsFromAttributes'

const backendAttributes = {
  title: {
    name: 'title',
    virtual: true,
    always: true,
    sortorder: 1,
    component: 'QuestionCodeAttribute',
    attributePath: 'title',
    returnValues: ['title'],
    tab: 'General',
    simpleOrder: 0,
  },
  other: {
    name: 'other',
    general: true,
    component: 'ToggleButtons',
    optionsPreset: 'onOff',
    optionsVariant: 'boolean',
    default: 'false',
    caption: 'Other',
    attributePath: 'other',
    tab: 'General',
  },
  gid: { name: 'gid', general: true, hidden: true, attributePath: 'gid' },
  other_position: {
    name: 'other_position',
    category: 'Display',
    sortorder: 150,
    component: 'Select',
    caption: "Position for 'Other:' option",
    options: [
      { value: 'beginning', text: 'At beginning' },
      { value: 'specific', text: 'After specific option' },
    ],
    attributePath: 'attributes.other_position',
    tab: 'Display',
  },
  other_position_code: {
    name: 'other_position_code',
    category: 'Display',
    sortorder: 151,
    component: 'Select',
    optionsSource: 'answersOrSubquestions',
    dependsOn: [
      { attribute: 'other' },
      { attribute: 'other_position', value: 'specific' },
      { attribute: 'not_in_this_theme', value: '1' },
    ],
    onDependsToggle: { onFalse: '' },
    attributePath: 'attributes.other_position_code',
    tab: 'Display',
  },
  use_first_limit_warning: {
    name: 'use_first_limit_warning',
    virtual: true,
    sortorder: 107,
    component: 'ToggleButtons',
    caption: '1st time limit warning',
    optionsPreset: 'yesNo',
    default: '0',
    attributePath: 'attributes.use_first_limit_warning',
    tab: 'Timer',
  },
  time_limit_warning_message: {
    name: 'time_limit_warning_message',
    category: 'Timer',
    sortorder: 112,
    i18n: '1',
    languageBased: true,
    component: 'Input',
    caption: '1st time limit message',
    dependsOn: [{ attribute: 'use_first_limit_warning' }],
    onDependsToggle: { onFalse: '' },
    attributePath: 'attributes.time_limit_warning_message',
    tab: 'Timer',
  },
  hide_tip: {
    name: 'hide_tip',
    category: 'Display',
    sortorder: 100,
    inputtype: 'switch',
    caption: 'Hide tip',
    options: [
      { value: '0', text: 'No' },
      { value: '1', text: 'Yes' },
    ],
    default: '0',
    attributePath: 'attributes.hide_tip',
    tab: 'Display',
  },
}

const findSection = (settings, title) =>
  settings.find((setting) => setting.title === title)

describe('buildSettingsFromAttributes', () => {
  let settings

  beforeAll(() => {
    globalThis.t = (text) => text
    settings = buildSettingsFromAttributes(backendAttributes)
  })

  test('returns no settings without attributes', () => {
    expect(buildSettingsFromAttributes(undefined)).toEqual([])
  })

  test('builds the simple section from simpleOrder', () => {
    const simple = findSection(settings, 'Simple')
    expect(simple.attributes.map((a) => a.attributePath)).toEqual(['title'])
  })

  test('skips hidden attributes and groups the rest by tab and sortorder', () => {
    expect(settings.map((setting) => setting.title)).toEqual([
      'Simple',
      'General',
      'Display',
      'Timer',
    ])
    expect(
      findSection(settings, 'Display').attributes.map((a) => a.name)
    ).toEqual(['hide_tip', 'other_position', 'other_position_code'])
  })

  test('resolves multiple conditions and drops conditions on missing attributes', () => {
    const otherPositionCode = findSection(settings, 'Display').attributes.find(
      (a) => a.name === 'other_position_code'
    )
    expect(otherPositionCode.dependsOn).toEqual([
      { attributePath: 'other', languageBased: false },
      {
        attributePath: 'attributes.other_position',
        languageBased: false,
        value: 'specific',
      },
    ])
    expect(otherPositionCode.onDependsToggle).toEqual({ onFalse: '' })
    expect(typeof otherPositionCode.getOptions).toBe('function')
    expect(
      otherPositionCode.getOptions({
        question: { answers: [{ code: 'A1', l10ns: {} }] },
        language: 'en',
      })
    ).toEqual([{ label: 'A1', value: 'A1' }])
  })

  test('maps presets, defaults and question level return values', () => {
    const other = findSection(settings, 'General').attributes.find(
      (a) => a.name === 'other'
    )
    expect(other.props.toggleOptions.map((o) => o.value)).toEqual([true, false])
    expect(other.props.defaultValue).toBe(false)
    expect(other.returnValues).toEqual(['other'])

    const hideTip = findSection(settings, 'Display').attributes[0]
    expect(hideTip.props.toggleOptions).toEqual([
      { name: 'No', value: '0' },
      { name: 'Yes', value: '1' },
    ])
    expect(hideTip.returnValues).toBeUndefined()
  })

  test('matches the hardcoded timer definitions', () => {
    const timer = findSection(settings, 'Timer').attributes
    const legacy = getTimerAttributes()

    const toggle = timer.find((a) => a.name === 'use_first_limit_warning')
    expect(toggle.attributePath).toBe(legacy.FIRST_TIME_LIMIT.attributePath)
    expect(toggle.props.toggleOptions).toEqual(
      legacy.FIRST_TIME_LIMIT.props.toggleOptions
    )
    expect(toggle.props.defaultValue).toBe(
      legacy.FIRST_TIME_LIMIT.props.defaultValue
    )

    const message = timer.find((a) => a.name === 'time_limit_warning_message')
    expect(message.languageBased).toBe(true)
    expect(message.dependsOn.attributePath).toBe(
      legacy.FIRST_TIME_LIMIT_WARNING_MESSAGE.dependsOn.attributePath
    )
    expect(message.onDependsToggle).toEqual(
      legacy.FIRST_TIME_LIMIT_WARNING_MESSAGE.onDependsToggle
    )
  })
})
