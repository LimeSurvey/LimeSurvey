// Import the function
import { hasTempId, NEW_OBJECT_ID_PREFIX } from 'helpers'

describe('isTempId', () => {
  test('should return true for a string containing the temp ID prefix', () => {
    expect(hasTempId(`${NEW_OBJECT_ID_PREFIX}-12345`)).toBe(true)
    expect(hasTempId(`${NEW_OBJECT_ID_PREFIX}-abc`)).toBe(true)
  })

  test('should return false for a string without the temp ID prefix', () => {
    expect(hasTempId('regular_id')).toBe(false)
    expect(hasTempId('12345')).toBe(false)
  })

  test('should return false for non-string input', () => {
    expect(hasTempId(12345)).toBe(false)
    expect(hasTempId(null)).toBe(false)
    expect(hasTempId(undefined)).toBe(false)
    expect(hasTempId({})).toBe(false)
  })

  test('should return true for an object containing the key with temp ID', () => {
    const obj = { id: `${NEW_OBJECT_ID_PREFIX}-12345` }
    expect(hasTempId(obj, 'id')).toBe(true)
  })

  test('should return false for an object with a key but no temp ID', () => {
    const obj = { id: 'regular_id' }
    expect(hasTempId(obj, 'id')).toBe(false)
  })

  test('should return true for a deeply nested object with a temp ID', () => {
    const nestedObj = {
      level1: {
        level2: {
          id: `${NEW_OBJECT_ID_PREFIX}-12345`,
        },
      },
    }
    expect(hasTempId(nestedObj, 'id')).toBe(true)
  })

  test('should return false if the key does not contain a temp ID at any level', () => {
    const nestedObj = {
      level1: {
        level2: {
          id: 'regular_id',
          other: `${NEW_OBJECT_ID_PREFIX}-12345`,
        },
      },
    }
    expect(hasTempId(nestedObj, 'id')).toBe(false)
  })

  test('should return true for arrays containing objects with the key and temp ID', () => {
    const arr = [{ id: 'regular_id' }, { id: `${NEW_OBJECT_ID_PREFIX}-12345` }]
    expect(hasTempId(arr, 'id')).toBe(true)
  })

  test('should return false for arrays without the key containing a temp ID', () => {
    const arr = [
      { id: 'regular_id' },
      { name: `${NEW_OBJECT_ID_PREFIX}-12345` },
    ]
    expect(hasTempId(arr, 'id')).toBe(false)
  })

  test('should handle mixed data types safely', () => {
    const mixed = {
      id: 'regular_id',
      data: null,
      e: undefined,
      nested: [
        { id: null },
        { k: undefined, id: `${NEW_OBJECT_ID_PREFIX}-56789` },
      ],
    }
    expect(hasTempId(mixed, 'id')).toBe(true)
  })

  test('should retun false if the data is null', () => {
    expect(hasTempId(null, 'id')).toBe(false)
  })
})
