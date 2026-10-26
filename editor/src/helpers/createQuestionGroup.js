import { APP_KEY_PREFIX, NEW_OBJECT_ID_PREFIX } from './constants/constants'
import { RandomNumber } from './RandomNumber'

export const createQuestionGroup = ({
  sid,
  language,
  gid,
  appKey = `questionGroup-${APP_KEY_PREFIX}${RandomNumber()}`,
  type = 'QG',
  theme = 'QuestionGroup',
}) => {
  const resolvedGid = gid ?? `${NEW_OBJECT_ID_PREFIX}${RandomNumber()}`

  return {
    gid: resolvedGid,
    appKey: appKey,
    sid,
    type,
    theme,
    l10ns: {
      [language]: {
        groupName: '',
        description: '',
      },
    },
    questions: [],
  }
}
