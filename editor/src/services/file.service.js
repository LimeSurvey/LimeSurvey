import { RestClient } from './restClient.service'

export class FileService {
  constructor(auth, baseUrl, surveyId) {
    this.auth = auth
    this.surveyId = surveyId
    this.baseurl = baseUrl

    this.restClient = new RestClient(baseUrl, auth.restHeaders)
  }

  uploadSurveyImage = async (data) => {
    return await this.restClient.post(
      `${this.baseurl}/file-upload-survey-image/${this.surveyId}`,
      data,
      {},
      true
    )
  }

  /**
   * Uploads an image inserted into a text, e.g. with the HTML editor. The
   * server only accepts PNG, JPEG and WebP up to 10 MB and removes metadata.
   *
   * @param {FormData} data the image in the field "file"
   * @returns {Promise<Object>} the upload result: success,
   *   uploadResultMessage, and the image's filePath and previewUrl in uploaded
   */
  uploadContentImage = async (data) => {
    return await this.restClient.post(
      `${this.baseurl}/file-upload-content-image/${this.surveyId}`,
      data,
      {},
      true
    )
  }
}
