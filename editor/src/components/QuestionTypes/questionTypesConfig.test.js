import fs from 'fs'
import path from 'path'

import questionTypesData from 'helpers/data/question-types.json'
import { getQuestionTypeInfo } from './getQuestionTypeInfo'

// The question type selector is configured in the backend, in react/questiontypes.xml.
// These tests keep it in sync with the question types the editor can render, and with the
// fixture used in demo mode, storybook and tests.
const xmlPath = path.resolve(
  __dirname,
  '../../../../application/views/survey/questions/react/questiontypes.xml'
)

/**
 * Reads the categories and themes of questiontypes.xml.
 * @returns {{categories: {name: string, title: string, icon: string}[], themes: Object<string, Object>}}
 */
const readQuestionTypesXml = () => {
  const xml = new DOMParser().parseFromString(
    fs.readFileSync(xmlPath, 'utf8'),
    'application/xml'
  )
  const text = (element, tagName) =>
    element.querySelector(`:scope > ${tagName}`)?.textContent.trim() ?? ''

  const categories = []
  const themes = {}
  xml.querySelectorAll('categories > category').forEach((category) => {
    const categoryName = text(category, 'name')
    categories.push({
      name: categoryName,
      title: text(category, 'title'),
      icon: text(category, 'icon'),
    })
    category.querySelectorAll('themes > theme').forEach((theme, index) => {
      themes[text(theme, 'name')] = {
        category: categoryName,
        sortOrder: index,
        title: text(theme, 'title'),
        type: text(theme, 'type'),
        devOnly: !['', '0'].includes(text(theme, 'devOnly')),
      }
    })
  })

  return { categories, themes }
}

describe('react/questiontypes.xml', () => {
  const { categories, themes } = readQuestionTypesXml()
  const questionTypeInfos = Object.values(getQuestionTypeInfo())

  test('Should list categories and themes', () => {
    expect(categories.length).toBeGreaterThan(0)
    expect(Object.keys(themes).length).toBeGreaterThan(0)
  })

  test('Should only list themes the editor can render, with the same type', () => {
    Object.keys(themes).forEach((themeName) => {
      const types = questionTypeInfos
        .filter((info) => info.theme === themeName)
        .map((info) => info.type)
      const type = questionTypesData.questionTypes[themeName]?.type

      expect({ themeName, renderable: types.length > 0 }).toEqual({
        themeName,
        renderable: true,
      })
      expect({ themeName, type: types.includes(type) }).toEqual({
        themeName,
        type: true,
      })
    })
  })

  test('Should match the question types fixture', () => {
    expect(questionTypesData.categories).toEqual(categories)

    Object.entries(themes).forEach(([themeName, theme]) => {
      const questionType = questionTypesData.questionTypes[themeName]
      expect({
        themeName,
        category: questionType?.category,
        sortOrder: questionType?.sortOrder,
        devOnly: questionType?.devOnly,
      }).toEqual({
        themeName,
        category: theme.category,
        sortOrder: theme.sortOrder,
        devOnly: theme.devOnly,
      })
      if (theme.title) {
        expect({ themeName, title: questionType.title }).toEqual({
          themeName,
          title: theme.title,
        })
      }
    })
    Object.values(questionTypesData.questionTypes)
      .filter((questionType) => questionType.category)
      .forEach((questionType) => {
        expect(themes).toHaveProperty([questionType.name])
      })
  })
})
