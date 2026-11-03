import { RestClient } from './restClient.service'

export class SurveyImportService {
  constructor(auth, baseUrl) {
    this.restClient = new RestClient(baseUrl, auth.restHeaders)
  }

  importSurvey = async ({ convertResourceLinks, file, groupStrategy }) => {
    const formData = new FormData()
    formData.append('file', file, file.name)
    formData.append('surveysgroup', groupStrategy)
    formData.append('translinksfields', convertResourceLinks ? '1' : '0')

    return await this.restClient.post('/survey-import', formData, {}, true)
  }
}
