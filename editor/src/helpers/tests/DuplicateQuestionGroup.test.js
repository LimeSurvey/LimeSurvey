import { DuplicateQuestionGroup } from '../DuplicateQuestionGroup'
import { APP_KEY_PREFIX } from '../constants/constants'

describe('DuplicateQuestionGroup', () => {
  test('should assign a fresh appKey for duplicated groups', () => {
    const original = {
      gid: 10,
      appKey: 'questionGroup-existing-key',
      l10ns: {
        en: {
          groupName: 'Group 1',
          description: 'Description',
        },
      },
      questions: [],
    }

    const duplicated = DuplicateQuestionGroup(original)

    expect(duplicated.appKey).toBeDefined()
    expect(duplicated.appKey).toContain(`questionGroup-${APP_KEY_PREFIX}`)
    expect(duplicated.appKey).not.toEqual(original.appKey)
  })
})
