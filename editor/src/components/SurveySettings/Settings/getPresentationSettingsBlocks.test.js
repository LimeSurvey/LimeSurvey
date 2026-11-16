import { STATES } from 'helpers'

import { getPresentationSettingsBlocks } from './getPresentationSettingsBlocks'

describe('No answer preselection setting', () => {
  const getDisableCondition = () =>
    getPresentationSettingsBlocks().DISPLAY.settings.PRESELECT_NO_ANSWER
      .disableCondition

  const globalStatesWithShowNoAnswer = (showNoAnswer) => ({
    [STATES.SURVEY]: { survey: { showNoAnswer } },
  })

  it('is disabled when No answer is off', () => {
    expect(
      getDisableCondition().check(globalStatesWithShowNoAnswer(false))
    ).toBe(true)
  })

  it('is enabled when No answer is on', () => {
    expect(
      getDisableCondition().check(globalStatesWithShowNoAnswer(true))
    ).toBe(false)
  })

  it('remains enabled when the survey inherits the No answer setting', () => {
    expect(
      getDisableCondition().check(globalStatesWithShowNoAnswer(null))
    ).toBe(false)
  })
})
