const fs = require('fs')
const path = require('path')
const babelParser = require('@babel/parser')
const traverse = require('@babel/traverse').default
const generate = require('@babel/generator').default

function getExcludedFiles() {
  return [
    'node_modules',
    'dist',
    'build',
    'test',
    'tests',
    'i18next.test.js',
    'App.js',
    'Home.js',
    '.stories.js',
    'I18nextProvider.js',
    'StoryOutput.js',
    'StoryWrapper.js',
    'getLabelWrapperWidthOptions.js',
    'getColumnWidthOptions.js',
    'constants.js',
    'languages.js',
    'survey-detail.json',
    'helpers',
    'panelOptions.js',
    'getThemeOptionsSettingsBlocks.js',
    'components/SurveySettings/Settings/themeOptionTypes',
  ]
}
function getAllFiles(dirPath, arrayOfFiles = [], excludeList = []) {
  const files = fs.readdirSync(dirPath)

  files.forEach((file) => {
    const filePath = path.join(dirPath, file)
    const relativePath = path.relative(process.cwd(), filePath)
    if (excludeList.some((excludeItem) => relativePath.includes(excludeItem))) {
      return
    }
    if (fs.statSync(filePath).isDirectory()) {
      arrayOfFiles = getAllFiles(filePath, arrayOfFiles, excludeList)
    } else if (file.endsWith('.js') || file.endsWith('.jsx')) {
      arrayOfFiles.push(filePath)
    }
  })

  return arrayOfFiles
}

function checkForHardcodedText(filePath) {
  const code = fs.readFileSync(filePath, 'utf-8')
  const ast = babelParser.parse(code, {
    sourceType: 'module',
    plugins: ['jsx'],
  })

  const hardcodedTexts = []
  const allowedAttributes = [
    'label',
    'placeholder',
    'title',
    'description',
    'titleValue',
    'languageValue',
    'tip',
    'text',
    'header',
  ]
  const unallowedAttributesInFile = {
    'RowQuestion.js': 'title',
    'RowQuestionGroup.js': 'title',
  }

  // Extract the filename from the filePath
  const filename = path.basename(filePath)

  traverse(ast, {
    JSXElement(path) {
      // Check JSXText within JSXElement
      path.traverse({
        JSXText(innerPath) {
          const text = innerPath.node.value.trim()
          if (
            text &&
            !isExcludedText(text) &&
            !isWrappedInTranslationFunction(innerPath)
          ) {
            hardcodedTexts.push({
              text,
              line: innerPath.node.loc.start.line,
            })
          }
        },
      })

      // Check JSXAttributes
      path.node.openingElement.attributes.forEach((attribute) => {
        if (
          attribute.type === 'JSXAttribute' &&
          allowedAttributes.includes(attribute.name.name) &&
          !(unallowedAttributesInFile[filename] === attribute.name.name)
        ) {
          if (attribute.value) {
            if (attribute.value.type === 'StringLiteral') {
              const text = attribute.value.value.trim()
              if (
                text &&
                !isExcludedText(text) &&
                !isWrappedInTranslationFunction(path)
              ) {
                hardcodedTexts.push({
                  text,
                  line: attribute.loc.start.line,
                  attribute: attribute.name.name,
                })
              }
            } else if (attribute.value.type === 'JSXExpressionContainer') {
              const expression = attribute.value.expression
              if (expression.type === 'StringLiteral') {
                const text = expression.value.trim()
                if (
                  text &&
                  !isExcludedText(text) &&
                  !isWrappedInTranslationFunction(path)
                ) {
                  hardcodedTexts.push({
                    text,
                    line: expression.loc.start.line,
                    attribute: attribute.name.name,
                  })
                }
              } else if (expression.type === 'Identifier') {
                const binding = path.scope.getBinding(expression.name)
                if (binding) {
                  if (binding.path.type === 'VariableDeclarator') {
                    const init = binding.path.node.init
                    if (init && init.type === 'StringLiteral') {
                      const text = init.value.trim()
                      if (
                        text &&
                        !isExcludedText(text) &&
                        !isWrappedInTranslationFunction(binding.path)
                      ) {
                        hardcodedTexts.push({
                          text,
                          line: binding.path.node.loc.start.line,
                          constant: expression.name,
                          attribute: attribute.name.name,
                        })
                      }
                    }
                  }
                } else {
                  // If there's no binding, it might be a prop or state variable
                  hardcodedTexts.push({
                    text: `Potential untranslated text in prop or state: ${expression.name}`,
                    line: expression.loc.start.line,
                    variable: expression.name,
                    attribute: attribute.name.name,
                  })
                }
              }
            }
          } else {
            // Handle cases where the attribute value is not present (e.g., placeholder={placeholder})
            hardcodedTexts.push({
              text: `Potential untranslated text in attribute: ${attribute.name.name}`,
              line: attribute.loc.start.line,
              attribute: attribute.name.name,
            })
          }
        }
      })
    },

    ObjectProperty(path) {
      if (
        allowedAttributes.includes(path.node.key.name) &&
        path.node.value.type === 'StringLiteral'
      ) {
        const text = path.node.value.value.trim()
        if (
          text &&
          !isExcludedText(text) &&
          !isWrappedInTranslationFunction(path)
        ) {
          hardcodedTexts.push({
            text,
            line: path.node.loc.start.line,
            context: 'Object Property',
          })
        }
      }
    },
  })

  const uniqueTextsAndLines = [
    ...new Set(hardcodedTexts.map((item) => JSON.stringify(item))),
  ].map((item) => JSON.parse(item))

  return uniqueTextsAndLines
}

function isExcludedText(text) {
  const excludedStrings = [
    'LimeSurvey',
    '[X]',
    'yyyy-mm-dd',
    'yyyy/mm/dd',
    'mm-dd-yyyy',
    'mm/dd/yyyy',
    'dd-mm-yyyy',
    'dd/mm/yyyy',
    'yyyy-mm-dd HH:MM',
    'yyyy/mm/dd HH:MM',
    'mm-dd-yyyy HH:MM',
    'mm/dd/yyyy HH:MM',
    'dd-mm-yyyy HH:MM',
    'dd/mm/yyyy HH:MM',
    'DD/MM/YYYY',
  ]
  // Exclude single characters
  if (text.length === 1) return true

  // Exclude numbers, fractions, and simple mathematical expressions
  if (/^(\d+|\/\d+|\d+\/\d+)$/.test(text)) return true

  // Exclude strings that are just special characters
  if (/^[!@#$%^&*()_+\-=[\]{};':"\\|,.<>/?]+$/.test(text)) return true

  // Exclude common programming syntax elements
  if (/^(===|!==|==|!=|&&|\|\||=>|<=|>=|<|>)$/.test(text)) return true

  // Exclude certain strings
  return excludedStrings.includes(text)
}

function isWrappedInTranslationFunction(path) {
  let parent = path.parent
  while (parent) {
    if (
      parent.type === 'CallExpression' &&
      (parent.callee.name === 't' || parent.callee.property?.name === 't')
    ) {
      return true
    }
    if (parent.type === 'JSXAttribute' && parent.name.name === 't') {
      return true
    }
    parent = parent.parent
  }
  return false
}

function checkForVariablesInTranslationFunctions(filePath) {
  const code = fs.readFileSync(filePath, 'utf-8')
  const ast = babelParser.parse(code, {
    sourceType: 'module',
    plugins: ['jsx'],
  })

  const variablesInTranslations = []

  traverse(ast, {
    CallExpression(path) {
      if (
        (path.node.callee.name === 't' || path.node.callee.name === 'st') &&
        path.node.arguments.length > 0
      ) {
        const firstArg = path.node.arguments[0]

        // Allow template literals that only contain static text
        if (
          firstArg.type === 'TemplateLiteral' &&
          firstArg.expressions.length === 0
        ) {
          // This is a template literal with no expressions, so it's fine
          return
        }

        if (firstArg.type !== 'StringLiteral') {
          variablesInTranslations.push({
            function: path.node.callee.name,
            line: path.node.loc.start.line,
            column: path.node.loc.start.column,
            argument: generate(firstArg).code,
          })
        }
      }
    },
  })

  return variablesInTranslations
}

function checkForWhitespaceInTranslations(filePath) {
  const code = fs.readFileSync(filePath, 'utf-8')
  const ast = babelParser.parse(code, {
    sourceType: 'module',
    plugins: ['jsx'],
  })

  const whitespaceIssues = []

  traverse(ast, {
    CallExpression(path) {
      if (
        (path.node.callee.name === 't' || path.node.callee.name === 'st') &&
        path.node.arguments.length > 0
      ) {
        const firstArg = path.node.arguments[0]

        if (firstArg.type === 'StringLiteral') {
          const text = firstArg.value
          if (text.startsWith(' ') || text.endsWith(' ')) {
            whitespaceIssues.push({
              function: path.node.callee.name,
              line: path.node.loc.start.line,
              column: path.node.loc.start.column,
              text: text,
              issue:
                text.startsWith(' ') && text.endsWith(' ')
                  ? 'starts and ends with space'
                  : text.startsWith(' ')
                    ? 'starts with space'
                    : 'ends with space',
            })
          }
        } else if (
          firstArg.type === 'TemplateLiteral' &&
          firstArg.expressions.length === 0 &&
          firstArg.quasis.length === 1
        ) {
          const text = firstArg.quasis[0].value.raw
          if (text.startsWith(' ') || text.endsWith(' ')) {
            whitespaceIssues.push({
              function: path.node.callee.name,
              line: path.node.loc.start.line,
              column: path.node.loc.start.column,
              text: text,
              issue:
                text.startsWith(' ') && text.endsWith(' ')
                  ? 'starts and ends with space'
                  : text.startsWith(' ')
                    ? 'starts with space'
                    : 'ends with space',
            })
          }
        }
      }
    },
  })

  return whitespaceIssues
}

describe('Translation Function Tests', () => {
  const componentsDir = path.join(__dirname)
  const files = getAllFiles(componentsDir, [], getExcludedFiles())

  beforeAll(() => {
    jest
      .spyOn(console, 'log')
      .mockImplementation((msg) => process.stdout.write(msg + '\n'))
  })

  describe('Untranslated Text Detection', () => {
    files.forEach((filePath) => {
      const relativePath = path.relative(componentsDir, filePath)
      it(`should not have untranslated text in ${relativePath}`, () => {
        const hardcodedTexts = checkForHardcodedText(filePath)
        expect(hardcodedTexts).toEqual([])
      })
    })
  })

  describe('Variables in Translation Functions', () => {
    files.forEach((filePath) => {
      const relativePath = path.relative(componentsDir, filePath)
      it(`should not have variables in t or st functions in ${relativePath}`, () => {
        const variablesInTranslations =
          checkForVariablesInTranslationFunctions(filePath)
        expect(variablesInTranslations).toEqual([])
      })
    })
  })

  describe('Whitespace in Translation Strings', () => {
    files.forEach((filePath) => {
      const relativePath = path.relative(componentsDir, filePath)
      it(`should not have leading or trailing whitespace in t or st functions in ${relativePath}`, () => {
        const whitespaceIssues = checkForWhitespaceInTranslations(filePath)
        expect(whitespaceIssues).toEqual([])
      })
    })
  })
})
