import { RestClient } from './restClient.service'

export class ExpressionScriptValidationService {
  constructor(auth, surveyId, baseUrl) {
    this.surveyId = surveyId
    this.restClient = new RestClient(baseUrl, auth.restHeaders)
  }

  validate = async (expression, questionId, signal) => {
    return await this.restClient.post(
      `expression-script-validation/${this.surveyId}`,
      { expression, questionId },
      {},
      true,
      { signal }
    )
  }
}
