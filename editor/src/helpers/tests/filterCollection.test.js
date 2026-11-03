import { filterCollection } from '../'

describe('filterCollection', () => {
  test('Should filter even numbers from an array', () => {
    const numbers = [1, 2, 3, 4, 5, 6]
    const result = filterCollection(numbers, (num) => num % 2 === 0)
    expect(result).toEqual([2, 4, 6])
  })

  test('Should filter objects by property value', () => {
    const students = {
      John: { age: 18, grade: 'A' },
      Jane: { age: 20, grade: 'B' },
      Mike: { age: 17, grade: 'A' },
      Emma: { age: 19, grade: 'C' },
    }
    const result = filterCollection(
      students,
      (details) => details.grade === 'A'
    )
    expect(result).toEqual({
      John: { age: 18, grade: 'A' },
      Mike: { age: 17, grade: 'A' },
    })
  })

  test('Should return an empty array if no elements match in an array', () => {
    const numbers = [1, 3, 5]
    const result = filterCollection(numbers, (num) => num % 2 === 0)
    expect(result).toEqual([])
  })

  test('Should return an empty object if no properties match in an object', () => {
    const students = {
      John: { age: 18, grade: 'A' },
      Jane: { age: 20, grade: 'B' },
    }
    const result = filterCollection(
      students,
      (details) => details.grade === 'C'
    )
    expect(result).toEqual({})
  })

  test('Should throw a TypeError for non-array and non-object inputs', () => {
    expect(() => filterCollection(null, () => true)).toThrow(TypeError)
    expect(() => filterCollection(42, () => true)).toThrow(TypeError)
    expect(() => filterCollection('string', () => true)).toThrow(TypeError)
  })
})
