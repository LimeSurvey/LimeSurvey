export const ONOFF_BOOLEAN = 'boolean'
export const ONOFF_NUMERIC = 'numeric'
export const ONOFF_SHORTSTRING = 'shortString'

export const getOnOffOptions = (valueType = ONOFF_NUMERIC) => {
  let options = []
  switch (valueType) {
    case ONOFF_BOOLEAN:
      options = [
        { name: t('On'), value: true },
        { name: t('Off'), value: false },
      ]
      break
    case ONOFF_NUMERIC:
      options = [
        { name: t('On'), value: '1' },
        { name: t('Off'), value: '0' },
      ]
      break
    case ONOFF_SHORTSTRING:
      options = [
        { name: t('On'), value: 'Y' },
        { name: t('Off'), value: 'N' },
      ]
      break
  }
  return options
}
