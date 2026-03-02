/**
 * scenarioConditionsParser.js
 *
 * This module handles parsing and processing of scenario conditions from the survey system.
 * It transforms raw condition data into a standardized format for use in the application.
 */

import {
  determineConditionSourceType,
  determineConditionTargetType,
  getBaseConditionObject,
} from './questionConditionHelpers'

export const buildScenarioConditionsForUpdate = (
  scenario,
  previousQuestions
) => {
  if (!scenario) return false

  const scenarios = scenario.conditions.map((condition) => {
    // Create base condition structure
    const processedCondition = getBaseConditionObject(condition, scenario.scid)

    // Determine and set source/target types
    determineConditionSourceType(condition, processedCondition)
    determineConditionTargetType(
      condition,
      processedCondition,
      previousQuestions
    )

    // Ensure answers array exists (even if empty)
    if (!processedCondition.answers) {
      processedCondition.answers = []
    }

    return processedCondition
  })

  // Return conditions sorted by field name for consistent ordering
  return scenarios.sort((a, b) => {
    return a.cfieldname.localeCompare(b.cfieldname)
  })
}
