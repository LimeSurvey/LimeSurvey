import axios from 'axios'
import { handleAxiosError } from 'helpers/network/axiosErrorHandler'

export class RestClient {
  constructor(baseUrl, restHeaders) {
    this.restHeaders = restHeaders
    this.client = axios.create({
      baseURL: baseUrl,
      headers: restHeaders,
    })
  }

  async get(
    endpoint,
    additionalHeaders = {},
    signal,
    canThrowError = false,
    params
  ) {
    try {
      const response = await this.client.get(endpoint, {
        headers: { ...this.restHeaders, ...additionalHeaders },
        params,
        signal,
      })
      return response.data
    } catch (error) {
      return handleAxiosError(error, { throwError: canThrowError })
    }
  }

  async post(endpoint, data, additionalHeaders = {}, canThrowError = false) {
    try {
      const response = await this.client.post(endpoint, data, {
        headers: { ...this.restHeaders, ...additionalHeaders },
      })
      return response.data
    } catch (error) {
      return handleAxiosError(error, { throwError: canThrowError })
    }
  }

  async patch(endpoint, data, additionalHeaders = {}, canThrowError = false) {
    try {
      const response = await this.client.patch(endpoint, data, {
        headers: { ...this.restHeaders, ...additionalHeaders },
      })
      return response.data
    } catch (error) {
      return handleAxiosError(error, { throwError: canThrowError })
    }
  }

  async put(endpoint, data, additionalHeaders = {}, canThrowError = false) {
    try {
      const response = await this.client.put(endpoint, data, {
        headers: { ...this.restHeaders, ...additionalHeaders },
      })
      return response.data
    } catch (error) {
      return handleAxiosError(error, { throwError: canThrowError })
    }
  }

  async delete(endpoint, additionalHeaders = {}, canThrowError = false) {
    try {
      const response = await this.client.delete(endpoint, {
        headers: { ...this.restHeaders, ...additionalHeaders },
      })
      return response.data
    } catch (error) {
      return handleAxiosError(error, { throwError: canThrowError })
    }
  }
}
