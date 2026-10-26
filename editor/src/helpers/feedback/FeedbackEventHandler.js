class FeedbackEventTarget extends EventTarget {
  constructor() {
    super()
  }

  requestFeedback(feedbackType) {
    this.dispatchEvent(
      new CustomEvent('feedbackRequested', {
        detail: { feedbackType },
      })
    )
  }

  onFeedbackRequested(callback) {
    this.addEventListener('feedbackRequested', callback)
    return () => this.removeEventListener('feedbackRequested', callback)
  }
}

export const FeedbackEventHandler = new FeedbackEventTarget()
