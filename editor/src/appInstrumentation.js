/* eslint-disable no-unused-vars */
import React from 'react'
import { createHashRouter } from 'react-router-dom'

export const createRouter = (routes) => {
  return createHashRouter(routes)
}

export const AppErrorBoundary = ({ children }) => {
  return <>{children}</>
}

export const withAppProfiler = (Component) => {
  return Component
}

export const initInstrumentation = () => {}

export const reportExtras = ({ extraData, message }) => {}

export const addBreadcrumb = ({ category, message, data, level }) => {}
