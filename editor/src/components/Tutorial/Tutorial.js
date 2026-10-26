import { useEffect } from 'react'
import Joyride, { ACTIONS, EVENTS, STATUS } from 'react-joyride'

import { TutorialTooltip } from './TutorialTooltip'

export const Tutorial = ({
  onHighlightAreaClick = () => {},
  onNextStep = () => {},
  onPreviousStep = () => {},
  onStepChange = () => {},
  onTutorialFinish = () => {},
  onTutorialSkip = () => {},
  tutorialState,
  setTutorialState,
}) => {
  const { run, steps, stepIndex } = tutorialState

  const handleJoyrideCallback = async (data) => {
    const { action, index, type, status } = data
    const finishedStatuses = [STATUS.FINISHED, STATUS.SKIPPED]
    const nextStepIndex = index + (action === ACTIONS.PREV ? -1 : 1)

    if (finishedStatuses.includes(status)) {
      setTutorialState({ steps, stepIndex, run: false })
      onTutorialFinish()
    }

    if (type === EVENTS.TARGET_NOT_FOUND) {
      // rerender in race condition where the target is not mounted yet
      setTimeout(() => {
        setTutorialState({
          ...tutorialState,
          stepIndex: index,
          run: true,
        })
      }, 100)
      return
    }

    if (type === EVENTS.STEP_AFTER && action === ACTIONS.NEXT) {
      if (typeof data.step?.beforeNextStep === 'function') {
        await data.step.beforeNextStep(data)
      }

      setTutorialState({ run, steps, stepIndex: nextStepIndex })
      onNextStep(data)
      onStepChange(data)

      if (typeof data.step?.onNextStep === 'function') {
        data.step.onNextStep(data)
      }
    } else if (type === EVENTS.STEP_AFTER && action === ACTIONS.PREV) {
      setTutorialState({ run, steps, stepIndex: nextStepIndex })
      onPreviousStep(data)
      onStepChange(data)

      if (typeof data.step?.onPreviousStep === 'function') {
        data.step.onPreviousStep(data)
      }
    }
  }

  useEffect(() => {
    const currentStep = steps[stepIndex]
    document.body.classList.remove('no-scroll')
    if (!currentStep) return

    let childToHighLight = null
    let tagetElement

    const handleClick = async (event) => {
      onHighlightAreaClick(currentStep, tagetElement, setTutorialState, event)
      if (typeof currentStep.onHighlightAreaClick === 'function') {
        await currentStep.onHighlightAreaClick(
          currentStep,
          tagetElement,
          setTutorialState,
          event
        )
      }
    }

    // the reason for using MutationObserver is to wait until the element is rendered before adding the click event listener
    const observer = new MutationObserver(async () => {
      tagetElement = document.querySelector(currentStep.target)

      if (tagetElement) {
        tagetElement.addEventListener('click', handleClick)
        observer.disconnect() // stop observing once found
        childToHighLight = document.querySelector(currentStep.highlight)

        if (childToHighLight) {
          childToHighLight.classList.add('highlight')
        }

        return () => {
          tagetElement.removeEventListener('click', handleClick)
        }
      }
    })

    observer.observe(document.body, { childList: true, subtree: true })

    return () => {
      if (childToHighLight) {
        childToHighLight.classList.remove('highlight')
      }

      if (typeof tagetElement?.removeEventListener === 'function') {
        tagetElement?.removeEventListener('click', handleClick)
      }

      observer.disconnect()
      document.body.classList.remove('no-scroll')
    }
  }, [stepIndex, run])

  if (!run) {
    return null
  }

  return (
    <>
      <Joyride
        callback={handleJoyrideCallback}
        continuous
        run={run === true}
        showProgress
        showSkipButton
        steps={steps}
        spotlightClicks
        disableOverlayClose
        disableCloseOnEsc={true}
        stepIndex={stepIndex}
        tooltipComponent={TutorialTooltip}
        styles={{
          options: {
            zIndex: 10000,
          },
        }}
      />

      <div
        onClick={() => {
          setTutorialState({ ...tutorialState, run: false })
          onTutorialSkip()
        }}
        className="cursor-pointer tutorial-skip-button med12"
      >
        <div>{t('Close interactive help')}</div>
        <div className="x-button-container">
          <i className="ri-close-fill"></i>
        </div>
      </div>
    </>
  )
}
