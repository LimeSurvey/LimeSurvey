import axios from 'axios'

export class VersionInfoService {
  constructor(baseUrl) {
    this.baseurl = baseUrl
  }

  getVersionInfo = async () => {
    return await axios.get(`${this.baseurl}/version-info`)
  }
}
