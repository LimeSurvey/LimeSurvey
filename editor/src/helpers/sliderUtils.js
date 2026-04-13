import { THUMB_TYPES } from './constants/slider'

export const getStringPartsUsingSeperator = (inputString, separator = '|') => {
  if (!inputString || typeof inputString !== 'string') {
    return { value: '', leftText: null, rightText: null }
  }

  const parts = inputString.split(separator)

  if (parts.length >= 3) {
    return {
      value: parts[0],
      leftText: parts[1],
      rightText: parts[2],
    }
  } else if (parts.length === 2) {
    return {
      value: parts[0],
      leftText: parts[1],
      rightText: null,
    }
  } else {
    return {
      value: parts[0],
      leftText: null,
      rightText: null,
    }
  }
}

export const getThumbStyle = (baseStyle, isDragged, thumbType) => {
  const commonStyle = {
    ...baseStyle,
    display: 'flex',
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: 'rgb(129, 70, 246)',
    boxShadow: isDragged
      ? '0 0 0 1.5px #fff, 0 0 0 .3rem rgba(129, 70, 246, 0.25)'
      : 'none',
  }

  switch (thumbType) {
    case THUMB_TYPES.CIRCLE:
      return {
        ...commonStyle,
        height: '12px',
        width: '12px',
        borderRadius: '50%',
      }
    case THUMB_TYPES.SQUARE:
      return {
        ...commonStyle,
        height: '12px',
        width: '12px',
        borderRadius: '0',
      }
    case THUMB_TYPES.TRIANGLE:
      return {
        ...commonStyle,
        height: '0',
        width: '0',
        backgroundColor: 'transparent',
        borderLeft: '6px solid transparent',
        borderRight: '6px solid transparent',
        borderBottom: '12px solid rgb(129, 70, 246)',
      }
    case THUMB_TYPES.CUSTOM:
      return {
        ...commonStyle,
        height: '16px',
        width: '16px',
        borderRadius: '0',
        backgroundColor: 'transparent',
        color: 'rgb(129, 70, 246)',
        fontSize: '16px',
      }
    default:
      return {
        ...commonStyle,
        height: '12px',
        width: '12px',
        borderRadius: '50%',
      }
  }
}
