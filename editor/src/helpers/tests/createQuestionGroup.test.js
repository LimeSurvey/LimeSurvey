import { createQuestionGroup } from '../createQuestionGroup'
import { APP_KEY_PREFIX, NEW_OBJECT_ID_PREFIX } from '../constants/constants'

describe('createQuestionGroup', () => {
  test('should create a question group with generated gid and appKey', () => {
    const group = createQuestionGroup({
      sid: 42,
      language: 'en',
    })

    expect(group.gid).toContain(NEW_OBJECT_ID_PREFIX)
    expect(group.appKey).toContain(`questionGroup-${APP_KEY_PREFIX}`)
    expect(group.sid).toBe(42)
    expect(group.type).toBe('QG')
    expect(group.theme).toBe('QuestionGroup')
    expect(group.questions).toEqual([])
    expect(group.l10ns.en).toEqual({ groupName: '', description: '' })
  })

  test('should keep provided gid, appKey, type and theme', () => {
    const group = createQuestionGroup({
      sid: 7,
      language: 'de',
      gid: 'temp__100',
      appKey: 'questionGroup-custom',
      type: 'CustomType',
      theme: 'CustomTheme',
    })

    expect(group.gid).toBe('temp__100')
    expect(group.appKey).toBe('questionGroup-custom')
    expect(group.type).toBe('CustomType')
    expect(group.theme).toBe('CustomTheme')
    expect(group.l10ns.de).toEqual({ groupName: '', description: '' })
  })
})
