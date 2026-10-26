/* eslint-disable no-console */
import { fileURLToPath } from 'url'
import path from 'path'
import fs from 'fs'

const _filename = fileURLToPath(import.meta.url)
const _dirname = path.dirname(_filename)
const source = path.join(_dirname, '..', 'build', 'index.html')
const destination = path.join(_dirname, '..', 'index.html')

fs.rename(source, destination, (err) => {
  if (err) {
    console.error('Failed to move index.html:', err)
    process.exit(1)
  }
  console.log('Successfully moved index.html.')
})
