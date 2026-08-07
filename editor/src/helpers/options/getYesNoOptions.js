export const YESNO_BOOLEAN = 'boolean'
export const YESNO_STRING_BOOLEAN = 'stringBoolean'
export const YESNO_NUMERIC = 'numeric'
export const YESNO_SHORTSTRING = 'shortString'
export const YESNO_LONGSTRING = 'longString'
export const YESNO_AS_ONOFFSTRING = 'asOnOffString'

export const getYesNoOptions = (valueType = YESNO_NUMERIC) => {
  let options = []
  switch (valueType) {
    case YESNO_BOOLEAN:
      options = [
        { name: t('Yes'), value: true },
        { name: t('No'), value: false },
      ]
      break
    case YESNO_STRING_BOOLEAN:
      options = [
        { name: t('Yes'), value: 'true' },
        { name: t('No'), value: 'false' },
      ]
      break
    case YESNO_NUMERIC:
      options = [
        { name: t('Yes'), value: '1' },
        { name: t('No'), value: '0' },
      ]
      break
    case YESNO_SHORTSTRING:
      options = [
        { name: t('Yes'), value: 'Y' },
        { name: t('No'), value: 'N' },
      ]
      break
    case YESNO_LONGSTRING:
      options = [
        { name: t('Yes'), value: 'yes' },
        { name: t('No'), value: 'no' },
      ]
      break
    case YESNO_AS_ONOFFSTRING:
      options = [
        { name: t('Yes'), value: 'on' },
        { name: t('No'), value: 'off' },
      ]
      break
  }
  return options
}
