import axios from 'axios'
import { handleAxiosError } from 'helpers/network/axiosErrorHandler'

export class SiteSettingsService {
  constructor(baseUrl) {
    this.baseurl = baseUrl
  }

  getSiteData = async () => {
    try {
      const response = await axios.get(`${this.baseurl}/site-settings`)
      return response.data
    } catch (error) {
      handleAxiosError(error)
    }
  }
}
