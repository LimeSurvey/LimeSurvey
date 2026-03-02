/* eslint-disable no-console */
import axios from 'axios'

import { RestClient } from '../restClient.service'

describe('RestClient', () => {
  const baseUrl = 'https://api.example.com'
  const restHeaders = {
    'Authorization': 'Bearer token',
    'Content-Type': 'application/json',
  }

  let restClient
  let axiosInstance

  beforeAll(() => {
    axiosInstance = axios.create({
      baseURL: baseUrl,
      headers: restHeaders,
    })

    jest.spyOn(axios, 'create').mockReturnValue(axiosInstance)

    restClient = new RestClient(baseUrl, restHeaders)
  })

  afterAll(() => {
    jest.clearAllMocks()
  })

  test('should make a GET request and return data', async () => {
    const mockData = { data: 'test data' }
    jest.spyOn(axiosInstance, 'get').mockResolvedValueOnce({ data: mockData })

    const data = await restClient.get('/test-endpoint')

    expect(axiosInstance.get).toHaveBeenCalledWith('/test-endpoint', {
      headers: restHeaders,
    })

    expect(data).toEqual(mockData)
  })

  test('should make a POST request with data and return response', async () => {
    const mockData = { data: 'test data' }
    const postData = { key: 'value' }
    jest.spyOn(axiosInstance, 'post').mockResolvedValueOnce({ data: mockData })

    const data = await restClient.post('/test-endpoint', postData)

    expect(axiosInstance.post).toHaveBeenCalledWith(
      '/test-endpoint',
      postData,
      {
        headers: restHeaders,
      }
    )

    expect(data).toEqual(mockData)
  })

  test('should merge additional headers with default headers in GET request', async () => {
    const mockData = { data: 'test data' }
    const additionalHeaders = { 'X-Custom-Header': 'custom-value' }
    jest.spyOn(axiosInstance, 'get').mockResolvedValueOnce({ data: mockData })

    const data = await restClient.get('/test-endpoint', additionalHeaders)

    expect(axiosInstance.get).toHaveBeenCalledWith('/test-endpoint', {
      headers: { ...restHeaders, ...additionalHeaders },
    })
    expect(data).toEqual(mockData)
  })

  test('should handle error in GET request', async () => {
    const errorMessage = 'Network Error'
    const originalConsoleError = console.error
    console.error = jest.fn()

    jest
      .spyOn(axiosInstance, 'get')
      .mockRejectedValueOnce(new Error(errorMessage))

    await expect(
      restClient.get('/test-endpoint', {}, undefined, true)
    ).rejects.toThrow(errorMessage)

    console.error = originalConsoleError
  })

  test('should make a DELETE request and return response', async () => {
    const mockData = { success: true }
    jest
      .spyOn(axiosInstance, 'delete')
      .mockResolvedValueOnce({ data: mockData })

    const data = await restClient.delete('/test-endpoint')

    expect(axiosInstance.delete).toHaveBeenCalledWith('/test-endpoint', {
      headers: restHeaders,
    })
    expect(data).toEqual(mockData)
  })
})
