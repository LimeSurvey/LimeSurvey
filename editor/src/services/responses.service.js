import { RestClient } from './restClient.service'

export class ResponseService {
  constructor(auth, surveyId, baseUrl) {
    this.auth = auth
    this.surveyId = surveyId
    this.baseurl = baseUrl

    this.restClient = new RestClient(baseUrl, auth.restHeaders)
  }

  patchResponses = async (patch) => {
    return await this.restClient.patch(`/survey-responses/${this.surveyId}`, {
      patch,
    })
  }

  getSurveyResponses = async (
    sid,
    options = {
      pagination: { pageIndex: 0, pageSize: 10 },
      filters: [],
      sorting: [],
    }
  ) => {
    const {
      pagination = { pageIndex: 0, pageSize: 10 },
      filters = [],
      sorting = [],
    } = options

    const body = {
      page: {
        currentPage: pagination.pageIndex,
        pageSize: pagination.pageSize,
      },
      sort: {},
      filters: [],
    }

    if (sorting.length > 0 && sorting[0].id) {
      body.sort[sorting[0].id] = sorting[0].desc ? 'desc' : 'asc'
    }

    Object.entries(filters || {}).forEach(
      ([, { value, filterMethod: type, keys }]) => {
        if (!value?.length && !value) return

        body.filters.push({
          key: keys[0],
          filterMethod: type,
          value,
        })
      }
    )

    return await this.restClient.post(`survey-responses/${sid}`, body)
  }

  getResponsesOverview = async (sid) => {
    return await this.restClient.get(`statistics-overview/${sid}`)
  }
}
