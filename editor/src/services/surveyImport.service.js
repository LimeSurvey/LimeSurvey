import { RestClient } from './restClient.service'

export class SurveyImportService {
  constructor(auth) {
    this.csrfToken = auth.csrfToken
    this.csrfTokenName = auth.csrfTokenName
    this.importUrl = auth.surveyImportUrl
    this.restClient = new RestClient('', {})
  }

  importSurvey = async ({ convertResourceLinks, file, groupStrategy }) => {
    if (!this.importUrl || !this.csrfTokenName || !this.csrfToken) {
      throw new Error(t('The survey could not be imported.'))
    }

    const formData = new FormData()
    formData.append('the_file', file, file.name)
    formData.append('surveysgroup', groupStrategy)
    formData.append('translinksfields', convertResourceLinks ? '1' : '0')
    formData.append(this.csrfTokenName, this.csrfToken)

    const result = await this.restClient.post(
      this.importUrl,
      formData,
      { 'X-Requested-With': 'XMLHttpRequest' },
      true
    )

    if (!result.success) {
      throw new Error(result.error || t('The survey could not be imported.'))
    }

    return result.summary
  }
}
