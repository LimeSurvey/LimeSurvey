import { SurveyImportService } from '../surveyImport.service'

describe('SurveyImportService', () => {
  const auth = {
    restHeaders: { Authorization: 'Bearer token' },
  }
  const baseUrl = 'http://localhost/rest/v1'

  let service

  beforeEach(() => {
    service = new SurveyImportService(auth, baseUrl)
  })

  test('imports a survey through the REST endpoint', async () => {
    const summary = { surveys: 1, questions: 3 }
    const file = new File(['survey'], 'survey.lss')

    jest.spyOn(service.restClient, 'post').mockResolvedValue(summary)

    await expect(
      service.importSurvey({
        convertResourceLinks: true,
        file,
        groupStrategy: 'default',
      })
    ).resolves.toEqual(summary)

    const [url, formData, headers, skipGlobalErrorHandler] =
      service.restClient.post.mock.calls[0]

    expect(url).toBe('/survey-import')
    expect(formData.get('file')).toEqual(file)
    expect(formData.get('surveysgroup')).toBe('default')
    expect(formData.get('translinksfields')).toBe('1')
    expect(headers).toEqual({})
    expect(skipGlobalErrorHandler).toBe(true)
  })

  test('surfaces an error returned by the REST endpoint', async () => {
    jest
      .spyOn(service.restClient, 'post')
      .mockRejectedValue(new Error('Invalid survey file.'))

    await expect(
      service.importSurvey({
        convertResourceLinks: false,
        file: new File(['invalid'], 'survey.lss'),
        groupStrategy: 'default',
      })
    ).rejects.toThrow('Invalid survey file.')
  })
})
