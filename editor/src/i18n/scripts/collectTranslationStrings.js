import { fileURLToPath } from 'url'
import { dirname } from 'path'
import fs from 'fs'
import path from 'path'
import traverseModule from '@babel/traverse'
import { parse } from '@babel/parser'

// Get __dirname equivalent in ES modules
const __filename = fileURLToPath(import.meta.url)
const __dirname = dirname(__filename)

// Handle default export for @babel/traverse in ES modules
const traverse = traverseModule.default || traverseModule

/* eslint-disable no-console */
/**
 * Translation String Collector
 *
 * This script scans JavaScript files in a specified directory (and its subdirectories)
 * to collect unique translation strings used with 't' or 'st' functions.
 *
 * Key features:
 * - Recursively scans directories for .js files
 * - Extracts translation strings from 't' and 'st' function calls
 * - Collects unique strings in a Set to avoid duplicates
 * - Saves collected strings to a JSON file, sorted alphabetically
 * - Provides console output for progress and results
 *
 * Output:
 * - Collected strings are saved to 'translationStrings.json' in the public directory
 *
 * Usage:
 * - Can be run directly as a script
 * - is actually run as prestart script, which is automatically run when you do "npm run start"
 *
 * Main function: collectTranslationStrings()
 * - Orchestrates the scanning and saving process
 * - Defines source and output directories
 *
 * Helper functions:
 * - extractTranslationStrings(filePath): Parses a file and extracts translation strings
 * - scanDirectory(dir): Recursively scans a directory for JS files
 * - saveTranslationStrings(outputPath): Saves collected strings to a JSON file
 *
 * Note: Requires @babel/parser and @babel/traverse for AST parsing and traversal.
 */

function extractTranslationStrings(filePath) {
  const translationStrings = new Set()

  const content = fs.readFileSync(filePath, 'utf8')
  const ast = parse(content, {
    sourceType: 'module',
    plugins: ['jsx'],
  })

  traverse(ast, {
    CallExpression(path) {
      if (
        (path.node.callee.name === 't' || path.node.callee.name === 'st') &&
        path.node.arguments.length > 0
      ) {
        const arg = path.node.arguments[0]
        if (arg.type === 'StringLiteral') {
          translationStrings.add(arg.value)
        }
      }
    },
  })

  return translationStrings
}

// TODO: think of how to write tests for it.
function scanDirectory(dir, translationStrings = new Set()) {
  const files = fs.readdirSync(dir)

  files.forEach((file) => {
    const filePath = path.join(dir, file)
    const stat = fs.statSync(filePath)

    if (stat.isDirectory()) {
      // recursive calling the function and attach the result to the translationString set
      translationStrings = new Set([
        ...translationStrings,
        ...scanDirectory(filePath, translationStrings),
      ])
    } else if (stat.isFile() && file.endsWith('.js')) {
      const extractTranslationStringsFromFilePath =
        extractTranslationStrings(filePath)

      translationStrings = new Set([
        ...translationStrings,
        ...extractTranslationStringsFromFilePath,
      ])
    }
  })

  return translationStrings
}

function sortTranslationStringsAlphabetically(translationStrings) {
  return Array.from(translationStrings).sort((a, b) =>
    a.localeCompare(b, 'en', { sensitivity: 'base' })
  )
}

function stringifyTranslationStrings(allTranslationStrings) {
  return JSON.stringify(allTranslationStrings, null, 2)
}

/**
 * Main function to run the script
 * Collects translation strings from JavaScript files in the src directory.
 *
 * This function:
 * 1. Scans the src directory for .js files
 * 2. Scans the secondary directory for .js files ( if specified )
 * 3. Extracts translation strings from these files
 * 4. Saves the unique translation strings to a JSON file
 *
 * The scan starts from the parent directory of the current script.
 * Progress and results are logged to the console.
 */
function collectTranslationStrings(secondarySrcPath) {
  console.log('Starting to collect translation strings...')

  // Start scanning from the src directory (two levels up from i18n/scripts)
  const srcDir = path.resolve(__dirname, '..', '..')
  console.log(`Scanning directory: ${srcDir}`)
  let allTranslationStrings = scanDirectory(srcDir)
  console.log(secondarySrcPath)
  // Start scanning from the secondary directory
  if (secondarySrcPath) {
    const secondarySrcDir = path.resolve(process.cwd(), secondarySrcPath)
    console.log(`Scanning Secondary directory: ${secondarySrcDir}`)

    const secondaryStrings = scanDirectory(secondarySrcDir)

    allTranslationStrings = new Set([
      ...allTranslationStrings,
      ...secondaryStrings,
    ])
  }

  console.log(`Found ${allTranslationStrings.size} unique translation strings`)

  // Save the collected strings to a file
  const outputPath = path.join(
    __dirname,
    '..',
    '..',
    '..',
    'public',
    'translationStrings.json'
  )

  const sortedTranslationStrings = sortTranslationStringsAlphabetically(
    allTranslationStrings
  )
  const stringifiedTranslationStrings = stringifyTranslationStrings(
    sortedTranslationStrings
  )

  fs.writeFileSync(outputPath, stringifiedTranslationStrings)
  console.log(`Translation strings saved to ${outputPath}`)

  console.log('Finished collecting translation strings')
}

// If this script is run directly (not imported), execute the main function
if (import.meta.url === `file://${process.argv[1]}`) {
  const secondaryPath = process.argv[2]
  collectTranslationStrings(secondaryPath)
}

export { collectTranslationStrings }
