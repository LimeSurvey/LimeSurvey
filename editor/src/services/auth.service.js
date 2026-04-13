import axios from 'axios'
import { handleAxiosError } from 'helpers/network/axiosErrorHandler'

export class AuthService {
  constructor(baseUrl) {
    this.baseurl = baseUrl
  }
  login = async (username, password) => {
    try {
      const res = await axios.post(`${this.baseurl}/auth`, {
        username,
        password,
      })
      return res.data
    } catch (error) {
      handleAxiosError(error)
    }
  }
  refresh = async (headers) => {
    try {
      const res = await axios.put(
        `${this.baseurl}/auth`,
        {},
        {
          headers,
        }
      )
      return res.data
    } catch (error) {
      handleAxiosError(error)
    }
  }
  logout = async (headers) => {
    try {
      const res = await axios.delete(`${this.baseurl}/auth`, {
        headers,
      })
      return res.data
    } catch (error) {
      handleAxiosError(error)
    }
  }
}
