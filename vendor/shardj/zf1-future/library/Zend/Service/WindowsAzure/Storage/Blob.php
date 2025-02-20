<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage Storage
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://todo     name_todo
 * @version    $Id$
 */

/**
 * @see Zend_Service_WindowsAzure_Storage
 */
require_once 'Zend/Service/WindowsAzure/Storage.php';

/**
 * @see Zend_Service_WindowsAzure_Storage_BlobInstance
 */
require_once 'Zend/Service/WindowsAzure/Storage/BlobInstance.php';

/**
 * @see Zend_Service_WindowsAzure_Storage_BlobContainer
 */
require_once 'Zend/Service/WindowsAzure/Storage/BlobContainer.php';

/**
 * @see Zend_Service_WindowsAzure_Storage_PageRegionInstance
 */
require_once 'Zend/Service/WindowsAzure/Storage/PageRegionInstance.php';

/**
 * @see Zend_Service_WindowsAzure_Storage_LeaseInstance
 */
require_once 'Zend/Service/WindowsAzure/Storage/LeaseInstance.php';

/**
 * @see Zend_Service_WindowsAzure_Storage_Blob_Stream
 */
require_once 'Zend/Service/WindowsAzure/Storage/Blob/Stream.php';

/**
 * @see Zend_Service_WindowsAzure_Credentials_SharedAccessSignature
 */
require_once 'Zend/Service/WindowsAzure/Credentials/SharedAccessSignature.php';
/**
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage Storage
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_WindowsAzure_Storage_Blob extends Zend_Service_WindowsAzure_Storage
{
	/**
	 * ACL - Private access
	 */
	public const ACL_PRIVATE = null;

	/**
	 * ACL - Public access (read all blobs)
	 *
	 * @deprecated Use ACL_PUBLIC_CONTAINER or ACL_PUBLIC_BLOB instead.
	 */
	public const ACL_PUBLIC = 'container';

	/**
	 * ACL - Blob Public access (read all blobs)
	 */
	public const ACL_PUBLIC_BLOB = 'blob';

	/**
	 * ACL - Container Public access (enumerate and read all blobs)
	 */
	public const ACL_PUBLIC_CONTAINER = 'container';

	/**
	 * Blob lease constants
	 */
	public const LEASE_ACQUIRE = 'acquire';
	public const LEASE_RENEW   = 'renew';
	public const LEASE_RELEASE = 'release';
	public const LEASE_BREAK   = 'break';

	/**
	 * Maximal blob size (in bytes)
	 */
	public const MAX_BLOB_SIZE = 67108864;

	/**
	 * Maximal blob transfer size (in bytes)
	 */
	public const MAX_BLOB_TRANSFER_SIZE = 4194304;

	/**
	 * Blob types
	 */
	public const BLOBTYPE_BLOCK = 'BlockBlob';
	public const BLOBTYPE_PAGE  = 'PageBlob';

	/**
	 * Put page write options
	 */
	public const PAGE_WRITE_UPDATE = 'update';
	public const PAGE_WRITE_CLEAR  = 'clear';

	/**
	 * Stream wrapper clients
	 *
	 * @var array
	 */
	protected static $_wrapperClients = [];

	/**
	 * SharedAccessSignature credentials
	 *
	 * @var Zend_Service_WindowsAzure_Credentials_SharedAccessSignature
	 */
	protected $_sharedAccessSignatureCredentials = null;

	/**
	 * Creates a new Zend_Service_WindowsAzure_Storage_Blob instance
	 *
	 * @param string $host Storage host name
	 * @param string $accountName Account name for Windows Azure
	 * @param string $accountKey Account key for Windows Azure
	 * @param boolean $usePathStyleUri Use path-style URI's
	 * @param Zend_Service_WindowsAzure_RetryPolicy_RetryPolicyAbstract $retryPolicy Retry policy to use when making requests
	 */
	public function __construct($host = Zend_Service_WindowsAzure_Storage::URL_DEV_BLOB, $accountName = Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::DEVSTORE_ACCOUNT, $accountKey = Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::DEVSTORE_KEY, $usePathStyleUri = false, ?Zend_Service_WindowsAzure_RetryPolicy_RetryPolicyAbstract $retryPolicy = null)
	{
		parent::__construct($host, $accountName, $accountKey, $usePathStyleUri, $retryPolicy);

		// API version
		$this->_apiVersion = '2009-09-19';

		// SharedAccessSignature credentials
		$this->_sharedAccessSignatureCredentials = new Zend_Service_WindowsAzure_Credentials_SharedAccessSignature($accountName, $accountKey, $usePathStyleUri);
	}

	/**
	 * Check if a blob exists
	 *
	 * @param string $containerName Container name
	 * @param string $blobName      Blob name
	 * @param string $snapshotId    Snapshot identifier
	 * @return boolean
	 */
	public function blobExists($containerName = '', $blobName = '', $snapshotId = null)
	{
		if ($containerName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name is not specified.');
		}
		if (!self::isValidContainerName($containerName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name does not adhere to container naming conventions. See http://msdn.microsoft.com/en-us/library/dd135715.aspx for more information.');
		}
		if ($blobName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Blob name is not specified.');
		}

		// Get blob instance
		try {
			$this->getBlobInstance($containerName, $blobName, $snapshotId);
		} catch (Zend_Service_WindowsAzure_Exception $e) {
			return false;
		}

		return true;
	}

	/**
	 * Check if a container exists
	 *
	 * @param string $containerName Container name
	 * @return boolean
	 */
	public function containerExists($containerName = '')
	{
		if ($containerName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name is not specified.');
		}
		if (!self::isValidContainerName($containerName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name does not adhere to container naming conventions. See http://msdn.microsoft.com/en-us/library/dd135715.aspx for more information.');
		}

		// List containers
		$containers = $this->listContainers($containerName, 1);
		foreach ($containers as $container) {
			if ($container->Name == $containerName) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Create container
	 *
	 * @param string $containerName Container name
	 * @param array  $metadata      Key/value pairs of meta data
	 * @return object Container properties
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function createContainer($containerName = '', $metadata = [])
	{
		if ($containerName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name is not specified.');
		}
		if (!self::isValidContainerName($containerName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name does not adhere to container naming conventions. See http://msdn.microsoft.com/en-us/library/dd135715.aspx for more information.');
		}
		if (!is_array($metadata)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Meta data should be an array of key and value pairs.');
		}

		// Create metadata headers
		$headers = [];
		$headers = array_merge($headers, $this->_generateMetadataHeaders($metadata));

		// Perform request
		$response = $this->_performRequest($containerName, '?restype=container', Zend_Http_Client::PUT, $headers, false, null, Zend_Service_WindowsAzure_Storage::RESOURCE_CONTAINER, Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::PERMISSION_WRITE);
		if ($response->isSuccessful()) {

			return new Zend_Service_WindowsAzure_Storage_BlobContainer(
			$containerName,
			$response->getHeader('Etag'),
			$response->getHeader('Last-modified'),
			$metadata
			);
		} else {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}

	/**
	 * Create container if it does not exist
	 *
	 * @param string $containerName Container name
	 * @param array  $metadata      Key/value pairs of meta data
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function createContainerIfNotExists($containerName = '', $metadata = [])
	{
		if (!$this->containerExists($containerName)) {
			$this->createContainer($containerName, $metadata);
		}
	}

	/**
	 * Get container ACL
	 *
	 * @param string $containerName Container name
	 * @param bool   $signedIdentifiers Display only private/blob/container or display signed identifiers?
	 * @return string Acl, to be compared with Zend_Service_WindowsAzure_Storage_Blob::ACL_*
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function getContainerAcl($containerName = '', $signedIdentifiers = false)
	{
		if ($containerName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name is not specified.');
		}
		if (!self::isValidContainerName($containerName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name does not adhere to container naming conventions. See http://msdn.microsoft.com/en-us/library/dd135715.aspx for more information.');
		}

		// Perform request
		$response = $this->_performRequest($containerName, '?restype=container&comp=acl', Zend_Http_Client::GET, [], false, null, Zend_Service_WindowsAzure_Storage::RESOURCE_CONTAINER, Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::PERMISSION_READ);
		if ($response->isSuccessful()) {
			if ($signedIdentifiers == false)  {
				// Only private/blob/container
				$accessType = $response->getHeader(Zend_Service_WindowsAzure_Storage::PREFIX_STORAGE_HEADER . 'blob-public-access');
				if (strtolower($accessType) == 'true') {
					$accessType = self::ACL_PUBLIC_CONTAINER;
				}
				return $accessType;
			} else {
				// Parse result
				$result = $this->_parseResponse($response);
				if (!$result) {
					return [];
				}

				$entries = null;
				if ($result->SignedIdentifier) {
					if (count($result->SignedIdentifier) > 1) {
						$entries = $result->SignedIdentifier;
					} else {
						$entries = [$result->SignedIdentifier];
					}
				}

				require_once 'Zend/Service/WindowsAzure/Storage/SignedIdentifier.php';
				// Return value
				$returnValue = [];
				foreach ($entries as $entry) {
					$returnValue[] = new Zend_Service_WindowsAzure_Storage_SignedIdentifier(
					$entry->Id,
					$entry->AccessPolicy ? $entry->AccessPolicy->Start ? $entry->AccessPolicy->Start : '' : '',
					$entry->AccessPolicy ? $entry->AccessPolicy->Expiry ? $entry->AccessPolicy->Expiry : '' : '',
					$entry->AccessPolicy ? $entry->AccessPolicy->Permission ? $entry->AccessPolicy->Permission : '' : ''
					);
				}

				// Return
				return $returnValue;
			}
		} else {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}

	/**
	 * Set container ACL
	 *
	 * @param string $containerName Container name
	 * @param bool $acl Zend_Service_WindowsAzure_Storage_Blob::ACL_*
	 * @param array $signedIdentifiers Signed identifiers
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function setContainerAcl($containerName = '', $acl = self::ACL_PRIVATE, $signedIdentifiers = [])
	{
		if ($containerName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name is not specified.');
		}
		if (!self::isValidContainerName($containerName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name does not adhere to container naming conventions. See http://msdn.microsoft.com/en-us/library/dd135715.aspx for more information.');
		}

		// Headers
		$headers = [];

		// Acl specified?
		if ($acl != self::ACL_PRIVATE && !is_null($acl) && $acl != '') {
			$headers[Zend_Service_WindowsAzure_Storage::PREFIX_STORAGE_HEADER . 'blob-public-access'] = $acl;
		}

		// Policies
		$policies = null;
		if (is_array($signedIdentifiers) && count($signedIdentifiers) > 0) {
			$policies  = '';
			$policies .= '<?xml version="1.0" encoding="utf-8"?>' . "\r\n";
			$policies .= '<SignedIdentifiers>' . "\r\n";
			foreach ($signedIdentifiers as $signedIdentifier) {
				$policies .= '  <SignedIdentifier>' . "\r\n";
				$policies .= '    <Id>' . $signedIdentifier->Id . '</Id>' . "\r\n";
				$policies .= '    <AccessPolicy>' . "\r\n";
				if ($signedIdentifier->Start != '')
				$policies .= '      <Start>' . $signedIdentifier->Start . '</Start>' . "\r\n";
				if ($signedIdentifier->Expiry != '')
				$policies .= '      <Expiry>' . $signedIdentifier->Expiry . '</Expiry>' . "\r\n";
				if ($signedIdentifier->Permissions != '')
				$policies .= '      <Permission>' . $signedIdentifier->Permissions . '</Permission>' . "\r\n";
				$policies .= '    </AccessPolicy>' . "\r\n";
				$policies .= '  </SignedIdentifier>' . "\r\n";
			}
			$policies .= '</SignedIdentifiers>' . "\r\n";
		}

		// Perform request
		$response = $this->_performRequest($containerName, '?restype=container&comp=acl', Zend_Http_Client::PUT, $headers, false, $policies, Zend_Service_WindowsAzure_Storage::RESOURCE_CONTAINER, Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::PERMISSION_WRITE);
		if (!$response->isSuccessful()) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}

	/**
	 * Get container
	 *
	 * @param string $containerName  Container name
	 * @return Zend_Service_WindowsAzure_Storage_BlobContainer
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function getContainer($containerName = '')
	{
		if ($containerName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name is not specified.');
		}
		if (!self::isValidContainerName($containerName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name does not adhere to container naming conventions. See http://msdn.microsoft.com/en-us/library/dd135715.aspx for more information.');
		}

		// Perform request
		$response = $this->_performRequest($containerName, '?restype=container', Zend_Http_Client::GET, [], false, null, Zend_Service_WindowsAzure_Storage::RESOURCE_CONTAINER, Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::PERMISSION_READ);
		if ($response->isSuccessful()) {
			// Parse metadata
			$metadata = $this->_parseMetadataHeaders($response->getHeaders());

			// Return container
			return new Zend_Service_WindowsAzure_Storage_BlobContainer(
			$containerName,
			$response->getHeader('Etag'),
			$response->getHeader('Last-modified'),
			$metadata
			);
		} else {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}

	/**
	 * Get container metadata
	 *
	 * @param string $containerName  Container name
	 * @return array Key/value pairs of meta data
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function getContainerMetadata($containerName = '')
	{
		if ($containerName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name is not specified.');
		}
		if (!self::isValidContainerName($containerName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name does not adhere to container naming conventions. See http://msdn.microsoft.com/en-us/library/dd135715.aspx for more information.');
		}

		return $this->getContainer($containerName)->Metadata;
	}

	/**
	 * Set container metadata
	 *
	 * Calling the Set Container Metadata operation overwrites all existing metadata that is associated with the container. It's not possible to modify an individual name/value pair.
	 *
	 * @param string $containerName      Container name
	 * @param array  $metadata           Key/value pairs of meta data
	 * @param array  $additionalHeaders  Additional headers. See http://msdn.microsoft.com/en-us/library/dd179371.aspx for more information.
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function setContainerMetadata($containerName = '', $metadata = [], $additionalHeaders = [])
	{
		if ($containerName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name is not specified.');
		}
		if (!self::isValidContainerName($containerName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name does not adhere to container naming conventions. See http://msdn.microsoft.com/en-us/library/dd135715.aspx for more information.');
		}
		if (!is_array($metadata)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Meta data should be an array of key and value pairs.');
		}
		if (count($metadata) === 0) {
			return;
		}

		// Create metadata headers
		$headers = [];
		$headers = array_merge($headers, $this->_generateMetadataHeaders($metadata));

		// Additional headers?
		foreach ($additionalHeaders as $key => $value) {
			$headers[$key] = $value;
		}

		// Perform request
		$response = $this->_performRequest($containerName, '?restype=container&comp=metadata', Zend_Http_Client::PUT, $headers, false, null, Zend_Service_WindowsAzure_Storage::RESOURCE_CONTAINER, Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::PERMISSION_WRITE);
		if (!$response->isSuccessful()) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}

	/**
	 * Delete container
	 *
	 * @param string $containerName      Container name
	 * @param array  $additionalHeaders  Additional headers. See http://msdn.microsoft.com/en-us/library/dd179371.aspx for more information.
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function deleteContainer($containerName = '', $additionalHeaders = [])
	{
		if ($containerName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name is not specified.');
		}
		if (!self::isValidContainerName($containerName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name does not adhere to container naming conventions. See http://msdn.microsoft.com/en-us/library/dd135715.aspx for more information.');
		}

		// Additional headers?
		$headers = [];
		foreach ($additionalHeaders as $key => $value) {
			$headers[$key] = $value;
		}

		// Perform request
		$response = $this->_performRequest($containerName, '?restype=container', Zend_Http_Client::DELETE, $headers, false, null, Zend_Service_WindowsAzure_Storage::RESOURCE_CONTAINER, Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::PERMISSION_WRITE);
		if (!$response->isSuccessful()) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}

	/**
	 * List containers
	 *
	 * @param string $prefix     Optional. Filters the results to return only containers whose name begins with the specified prefix.
	 * @param int    $maxResults Optional. Specifies the maximum number of containers to return per call to Azure storage. This does NOT affect list size returned by this function. (maximum: 5000)
	 * @param string $marker     Optional string value that identifies the portion of the list to be returned with the next list operation.
	 * @param string $include    Optional. Include this parameter to specify that the container's metadata be returned as part of the response body. (allowed values: '', 'metadata')
	 * @param int    $currentResultCount Current result count (internal use)
	 * @return array
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function listContainers($prefix = null, $maxResults = null, $marker = null, $include = null, $currentResultCount = 0)
	{
		// Build query string
		$queryString = ['comp=list'];
		if (!is_null($prefix)) {
			$queryString[] = 'prefix=' . $prefix;
		}
		if (!is_null($maxResults)) {
			$queryString[] = 'maxresults=' . $maxResults;
		}
		if (!is_null($marker)) {
			$queryString[] = 'marker=' . $marker;
		}
		if (!is_null($include)) {
			$queryString[] = 'include=' . $include;
		}
		$queryString = self::createQueryStringFromArray($queryString);

		// Perform request
		$response = $this->_performRequest('', $queryString, Zend_Http_Client::GET, [], false, null, Zend_Service_WindowsAzure_Storage::RESOURCE_CONTAINER, Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::PERMISSION_LIST);
		if ($response->isSuccessful()) {
			$xmlContainers = $this->_parseResponse($response)->Containers->Container;
			$xmlMarker = (string)$this->_parseResponse($response)->NextMarker;

			$containers = [];
			if (!is_null($xmlContainers)) {
				for ($i = 0; $i < count($xmlContainers); $i++) {

					$containers[] = new Zend_Service_WindowsAzure_Storage_BlobContainer(
					(string)$xmlContainers[$i]->Name,
					(string)$xmlContainers[$i]->Etag,
					(string)$xmlContainers[$i]->LastModified,
					$this->_parseMetadataElement($xmlContainers[$i])
					);
				}
			}
			$currentResultCount = $currentResultCount + count($containers);
			if (!is_null($maxResults) && $currentResultCount < $maxResults) {
				if (!is_null($xmlMarker) && $xmlMarker != '') {
					$containers = array_merge($containers, $this->listContainers($prefix, $maxResults, $xmlMarker, $include, $currentResultCount));
				}
			}
			if (!is_null($maxResults) && count($containers) > $maxResults) {
				$containers = array_slice($containers, 0, $maxResults);
			}

			return $containers;
		} else {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}

	/**
	 * Put blob
	 *
	 * @param string $containerName      Container name
	 * @param string $blobName           Blob name
	 * @param string $localFileName      Local file name to be uploaded
	 * @param array  $metadata           Key/value pairs of meta data
	 * @param string $leaseId            Lease identifier
	 * @param array  $additionalHeaders  Additional headers. See http://msdn.microsoft.com/en-us/library/dd179371.aspx for more information.
	 * @return object Partial blob properties
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function putBlob($containerName = '', $blobName = '', $localFileName = '', $metadata = [], $leaseId = null, $additionalHeaders = [])
	{
		if ($containerName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name is not specified.');
		}
		if (!self::isValidContainerName($containerName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name does not adhere to container naming conventions. See http://msdn.microsoft.com/en-us/library/dd135715.aspx for more information.');
		}
		if ($blobName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Blob name is not specified.');
		}
		if ($localFileName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Local file name is not specified.');
		}
		if (!file_exists($localFileName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Local file not found.');
		}
		if ($containerName === '$root' && strpos($blobName, '/') !== false) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Blobs stored in the root container can not have a name containing a forward slash (/).');
		}

		// Check file size
		if (filesize($localFileName) >= self::MAX_BLOB_SIZE) {
			return $this->putLargeBlob($containerName, $blobName, $localFileName, $metadata, $leaseId, $additionalHeaders);
		}

		// Put the data to Windows Azure Storage
		return $this->putBlobData($containerName, $blobName, file_get_contents($localFileName), $metadata, $leaseId, $additionalHeaders);
	}

	/**
	 * Put blob data
	 *
	 * @param string $containerName      Container name
	 * @param string $blobName           Blob name
	 * @param mixed  $data      		 Data to store
	 * @param array  $metadata           Key/value pairs of meta data
	 * @param string $leaseId            Lease identifier
	 * @param array  $additionalHeaders  Additional headers. See http://msdn.microsoft.com/en-us/library/dd179371.aspx for more information.
	 * @return object Partial blob properties
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function putBlobData($containerName = '', $blobName = '', $data = '', $metadata = [], $leaseId = null, $additionalHeaders = [])
	{
		if ($containerName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name is not specified.');
		}
		if (!self::isValidContainerName($containerName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name does not adhere to container naming conventions. See http://msdn.microsoft.com/en-us/library/dd135715.aspx for more information.');
		}
		if ($blobName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Blob name is not specified.');
		}
		if ($containerName === '$root' && strpos($blobName, '/') !== false) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Blobs stored in the root container can not have a name containing a forward slash (/).');
		}

		// Create metadata headers
		$headers = [];
		if (!is_null($leaseId)) {
			$headers['x-ms-lease-id'] = $leaseId;
		}
		$headers = array_merge($headers, $this->_generateMetadataHeaders($metadata));

		// Additional headers?
		foreach ($additionalHeaders as $key => $value) {
			$headers[$key] = $value;
		}

		// Specify blob type
		$headers[Zend_Service_WindowsAzure_Storage::PREFIX_STORAGE_HEADER . 'blob-type'] = self::BLOBTYPE_BLOCK;

		// Resource name
		$resourceName = self::createResourceName($containerName , $blobName);

		// Perform request
		$response = $this->_performRequest($resourceName, '', Zend_Http_Client::PUT, $headers, false, $data, Zend_Service_WindowsAzure_Storage::RESOURCE_BLOB, Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::PERMISSION_WRITE);
		if ($response->isSuccessful()) {

			return new Zend_Service_WindowsAzure_Storage_BlobInstance(
			$containerName,
			$blobName,
			null,
			$response->getHeader('Etag'),
			$response->getHeader('Last-modified'),
			$this->getBaseUrl() . '/' . $containerName . '/' . $blobName,
			strlen($data),
				'',
				'',
				'',
			false,
			$metadata
			);
		} else {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}

	/**
	 * Put large blob (> 64 MB)
	 *
	 * @param string $containerName Container name
	 * @param string $blobName Blob name
	 * @param string $localFileName Local file name to be uploaded
	 * @param array  $metadata      Key/value pairs of meta data
	 * @param string $leaseId       Lease identifier
	 * @param array  $additionalHeaders  Additional headers. See http://msdn.microsoft.com/en-us/library/dd179371.aspx for more information.
	 * @return object Partial blob properties
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function putLargeBlob($containerName = '', $blobName = '', $localFileName = '', $metadata = [], $leaseId = null, $additionalHeaders = [])
	{
		if ($containerName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name is not specified.');
		}
		if (!self::isValidContainerName($containerName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name does not adhere to container naming conventions. See http://msdn.microsoft.com/en-us/library/dd135715.aspx for more information.');
		}
		if ($blobName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Blob name is not specified.');
		}
		if ($localFileName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Local file name is not specified.');
		}
		if (!file_exists($localFileName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Local file not found.');
		}
		if ($containerName === '$root' && strpos($blobName, '/') !== false) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Blobs stored in the root container can not have a name containing a forward slash (/).');
		}

		// Check file size
		if (filesize($localFileName) < self::MAX_BLOB_SIZE) {
			return $this->putBlob($containerName, $blobName, $localFileName, $metadata, $leaseId, $additionalHeaders);
		}

		// Determine number of parts
		$numberOfParts = ceil( filesize($localFileName) / self::MAX_BLOB_TRANSFER_SIZE );

		// Generate block id's
		$blockIdentifiers = [];
		for ($i = 0; $i < $numberOfParts; $i++) {
			$blockIdentifiers[] = $this->_generateBlockId($i);
		}

		// Open file
		$fp = fopen($localFileName, 'r');
		if ($fp === false) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Could not open local file.');
		}

		// Upload parts
		for ($i = 0; $i < $numberOfParts; $i++) {
			// Seek position in file
			fseek($fp, $i * self::MAX_BLOB_TRANSFER_SIZE);

			// Read contents
			$fileContents = fread($fp, self::MAX_BLOB_TRANSFER_SIZE);

			// Put block
			$this->putBlock($containerName, $blobName, $blockIdentifiers[$i], $fileContents, $leaseId);

			// Dispose file contents
			$fileContents = null;
			unset($fileContents);
		}

		// Close file
		fclose($fp);

		// Put block list
		$this->putBlockList($containerName, $blobName, $blockIdentifiers, $metadata, $leaseId, $additionalHeaders);

		// Return information of the blob
		return $this->getBlobInstance($containerName, $blobName, null, $leaseId);
	}

	/**
	 * Put large blob block
	 *
	 * @param string $containerName Container name
	 * @param string $blobName      Blob name
	 * @param string $identifier    Block ID
	 * @param array  $contents      Contents of the block
	 * @param string $leaseId       Lease identifier
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function putBlock($containerName = '', $blobName = '', $identifier = '', $contents = '', $leaseId = null)
	{
		if ($containerName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name is not specified.');
		}
		if (!self::isValidContainerName($containerName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name does not adhere to container naming conventions. See http://msdn.microsoft.com/en-us/library/dd135715.aspx for more information.');
		}
		if ($identifier === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Block identifier is not specified.');
		}
		if (strlen($contents) > self::MAX_BLOB_TRANSFER_SIZE) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Block size is too big.');
		}
		if ($containerName === '$root' && strpos($blobName, '/') !== false) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Blobs stored in the root container can not have a name containing a forward slash (/).');
		}

		// Headers
		$headers = [];
		if (!is_null($leaseId)) {
			$headers['x-ms-lease-id'] = $leaseId;
		}

		// Resource name
		$resourceName = self::createResourceName($containerName , $blobName);

		// Upload
		$response = $this->_performRequest($resourceName, '?comp=block&blockid=' . base64_encode($identifier), Zend_Http_Client::PUT, $headers, false, $contents, Zend_Service_WindowsAzure_Storage::RESOURCE_BLOB, Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::PERMISSION_WRITE);
		if (!$response->isSuccessful()) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}

	/**
	 * Put block list
	 *
	 * @param string $containerName      Container name
	 * @param string $blobName           Blob name
	 * @param array $blockList           Array of block identifiers
	 * @param array  $metadata           Key/value pairs of meta data
	 * @param string $leaseId            Lease identifier
	 * @param array  $additionalHeaders  Additional headers. See http://msdn.microsoft.com/en-us/library/dd179371.aspx for more information.
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function putBlockList($containerName = '', $blobName = '', $blockList = [], $metadata = [], $leaseId = null, $additionalHeaders = [])
	{
		if ($containerName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name is not specified.');
		}
		if (!self::isValidContainerName($containerName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name does not adhere to container naming conventions. See http://msdn.microsoft.com/en-us/library/dd135715.aspx for more information.');
		}
		if ($blobName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Blob name is not specified.');
		}
		if (count($blockList) === 0) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Block list does not contain any elements.');
		}
		if ($containerName === '$root' && strpos($blobName, '/') !== false) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Blobs stored in the root container can not have a name containing a forward slash (/).');
		}

		// Generate block list
		$blocks = '';
		foreach ($blockList as $block) {
			$blocks .= '  <Latest>' . base64_encode($block) . '</Latest>' . "\n";
		}

		// Generate block list request
		$fileContents = mb_convert_encoding(implode("\n", [
				'<?xml version="1.0" encoding="utf-8"?>',
				'<BlockList>',
				$blocks,
				'</BlockList>'
			]), 'UTF-8', 'ISO-8859-1');

			// Create metadata headers
			$headers = [];
			if (!is_null($leaseId)) {
				$headers['x-ms-lease-id'] = $leaseId;
			}
			$headers = array_merge($headers, $this->_generateMetadataHeaders($metadata));

			// Additional headers?
			foreach ($additionalHeaders as $key => $value) {
				$headers[$key] = $value;
			}

			// Resource name
			$resourceName = self::createResourceName($containerName , $blobName);

			// Perform request
			$response = $this->_performRequest($resourceName, '?comp=blocklist', Zend_Http_Client::PUT, $headers, false, $fileContents, Zend_Service_WindowsAzure_Storage::RESOURCE_BLOB, Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::PERMISSION_WRITE);
			if (!$response->isSuccessful()) {
				require_once 'Zend/Service/WindowsAzure/Exception.php';
				throw new Zend_Service_WindowsAzure_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
			}
	}

	/**
	 * Get block list
	 *
	 * @param string $containerName Container name
	 * @param string $blobName      Blob name
	 * @param string $snapshotId    Snapshot identifier
	 * @param string $leaseId       Lease identifier
	 * @param integer $type         Type of block list to retrieve. 0 = all, 1 = committed, 2 = uncommitted
	 * @return array
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function getBlockList($containerName = '', $blobName = '', $snapshotId = null, $leaseId = null, $type = 0)
	{
		if ($containerName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name is not specified.');
		}
		if (!self::isValidContainerName($containerName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name does not adhere to container naming conventions. See http://msdn.microsoft.com/en-us/library/dd135715.aspx for more information.');
		}
		if ($blobName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Blob name is not specified.');
		}
		if ($type < 0 || $type > 2) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Invalid type of block list to retrieve.');
		}

		// Set $blockListType
		$blockListType = 'all';
		if ($type == 1) {
			$blockListType = 'committed';
		}
		if ($type == 2) {
			$blockListType = 'uncommitted';
		}

		// Headers
		$headers = [];
		if (!is_null($leaseId)) {
			$headers['x-ms-lease-id'] = $leaseId;
		}

		// Build query string
		$queryString = ['comp=blocklist', 'blocklisttype=' . $blockListType];
		if (!is_null($snapshotId)) {
			$queryString[] = 'snapshot=' . $snapshotId;
		}
		$queryString = self::createQueryStringFromArray($queryString);

		// Resource name
		$resourceName = self::createResourceName($containerName , $blobName);

		// Perform request
		$response = $this->_performRequest($resourceName, $queryString, Zend_Http_Client::GET, $headers, false, null, Zend_Service_WindowsAzure_Storage::RESOURCE_BLOB, Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::PERMISSION_READ);
		if ($response->isSuccessful()) {
			// Parse response
			$blockList = $this->_parseResponse($response);

			// Create return value
			$returnValue = [];
			if ($blockList->CommittedBlocks) {
				foreach ($blockList->CommittedBlocks->Block as $block) {
					$returnValue['CommittedBlocks'][] = (object)[
			            'Name' => (string)$block->Name,
			            'Size' => (string)$block->Size
					];
				}
			}
			if ($blockList->UncommittedBlocks)  {
				foreach ($blockList->UncommittedBlocks->Block as $block) {
					$returnValue['UncommittedBlocks'][] = (object)[
			            'Name' => (string)$block->Name,
			            'Size' => (string)$block->Size
					];
				}
			}

			return $returnValue;
		} else {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}

	/**
	 * Create page blob
	 *
	 * @param string $containerName      Container name
	 * @param string $blobName           Blob name
	 * @param int    $size      		 Size of the page blob in bytes
	 * @param array  $metadata           Key/value pairs of meta data
	 * @param string $leaseId            Lease identifier
	 * @param array  $additionalHeaders  Additional headers. See http://msdn.microsoft.com/en-us/library/dd179371.aspx for more information.
	 * @return object Partial blob properties
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function createPageBlob($containerName = '', $blobName = '', $size = 0, $metadata = [], $leaseId = null, $additionalHeaders = [])
	{
		if ($containerName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name is not specified.');
		}
		if (!self::isValidContainerName($containerName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name does not adhere to container naming conventions. See http://msdn.microsoft.com/en-us/library/dd135715.aspx for more information.');
		}
		if ($blobName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Blob name is not specified.');
		}
		if ($containerName === '$root' && strpos($blobName, '/') !== false) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Blobs stored in the root container can not have a name containing a forward slash (/).');
		}
		if ($size <= 0) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Page blob size must be specified.');
		}

		// Create metadata headers
		$headers = [];
		if (!is_null($leaseId)) {
			$headers['x-ms-lease-id'] = $leaseId;
		}
		$headers = array_merge($headers, $this->_generateMetadataHeaders($metadata));

		// Additional headers?
		foreach ($additionalHeaders as $key => $value) {
			$headers[$key] = $value;
		}

		// Specify blob type & blob length
		$headers[Zend_Service_WindowsAzure_Storage::PREFIX_STORAGE_HEADER . 'blob-type'] = self::BLOBTYPE_PAGE;
		$headers[Zend_Service_WindowsAzure_Storage::PREFIX_STORAGE_HEADER . 'blob-content-length'] = $size;
		$headers['Content-Length'] = 0;

		// Resource name
		$resourceName = self::createResourceName($containerName , $blobName);

		// Perform request
		$response = $this->_performRequest($resourceName, '', Zend_Http_Client::PUT, $headers, false, '', Zend_Service_WindowsAzure_Storage::RESOURCE_BLOB, Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::PERMISSION_WRITE);
		if ($response->isSuccessful()) {

			return new Zend_Service_WindowsAzure_Storage_BlobInstance(
			$containerName,
			$blobName,
			null,
			$response->getHeader('Etag'),
			$response->getHeader('Last-modified'),
			$this->getBaseUrl() . '/' . $containerName . '/' . $blobName,
			$size,
				'',
				'',
				'',
			false,
			$metadata
			);
		} else {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}

	/**
	 * Put page in page blob
	 *
	 * @param string $containerName      Container name
	 * @param string $blobName           Blob name
	 * @param int    $startByteOffset    Start byte offset
	 * @param int    $endByteOffset      End byte offset
	 * @param mixed  $contents			 Page contents
	 * @param string $writeMethod        Write method (Zend_Service_WindowsAzure_Storage_Blob::PAGE_WRITE_*)
	 * @param string $leaseId            Lease identifier
	 * @param array  $additionalHeaders  Additional headers. See http://msdn.microsoft.com/en-us/library/dd179371.aspx for more information.
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function putPage($containerName = '', $blobName = '', $startByteOffset = 0, $endByteOffset = 0, $contents = '', $writeMethod = self::PAGE_WRITE_UPDATE, $leaseId = null, $additionalHeaders = [])
	{
		if ($containerName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name is not specified.');
		}
		if (!self::isValidContainerName($containerName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name does not adhere to container naming conventions. See http://msdn.microsoft.com/en-us/library/dd135715.aspx for more information.');
		}
		if ($blobName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Blob name is not specified.');
		}
		if ($containerName === '$root' && strpos($blobName, '/') !== false) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Blobs stored in the root container can not have a name containing a forward slash (/).');
		}
		if ($startByteOffset % 512 != 0) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Start byte offset must be a modulus of 512.');
		}
		if (($endByteOffset + 1) % 512 != 0) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('End byte offset must be a modulus of 512 minus 1.');
		}

		// Determine size
		$size = strlen($contents);
		if ($size >= self::MAX_BLOB_TRANSFER_SIZE) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Page blob size must not be larger than ' . self::MAX_BLOB_TRANSFER_SIZE . ' bytes.');
		}

		// Create metadata headers
		$headers = [];
		if (!is_null($leaseId)) {
			$headers['x-ms-lease-id'] = $leaseId;
		}

		// Additional headers?
		foreach ($additionalHeaders as $key => $value) {
			$headers[$key] = $value;
		}

		// Specify range
		$headers['Range'] = 'bytes=' . $startByteOffset . '-' . $endByteOffset;

		// Write method
		$headers[Zend_Service_WindowsAzure_Storage::PREFIX_STORAGE_HEADER . 'page-write'] = $writeMethod;

		// Resource name
		$resourceName = self::createResourceName($containerName , $blobName);

		// Perform request
		$response = $this->_performRequest($resourceName, '?comp=page', Zend_Http_Client::PUT, $headers, false, $contents, Zend_Service_WindowsAzure_Storage::RESOURCE_BLOB, Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::PERMISSION_WRITE);
		if (!$response->isSuccessful()) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}

	/**
	 * Put page in page blob
	 *
	 * @param string $containerName      Container name
	 * @param string $blobName           Blob name
	 * @param int    $startByteOffset    Start byte offset
	 * @param int    $endByteOffset      End byte offset
	 * @param string $leaseId            Lease identifier
	 * @return array Array of page ranges
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function getPageRegions($containerName = '', $blobName = '', $startByteOffset = 0, $endByteOffset = 0, $leaseId = null)
	{
		if ($containerName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name is not specified.');
		}
		if (!self::isValidContainerName($containerName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name does not adhere to container naming conventions. See http://msdn.microsoft.com/en-us/library/dd135715.aspx for more information.');
		}
		if ($blobName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Blob name is not specified.');
		}
		if ($containerName === '$root' && strpos($blobName, '/') !== false) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Blobs stored in the root container can not have a name containing a forward slash (/).');
		}
		if ($startByteOffset % 512 != 0) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Start byte offset must be a modulus of 512.');
		}
		if ($endByteOffset > 0 && ($endByteOffset + 1) % 512 != 0) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('End byte offset must be a modulus of 512 minus 1.');
		}

		// Create metadata headers
		$headers = [];
		if (!is_null($leaseId)) {
			$headers['x-ms-lease-id'] = $leaseId;
		}

		// Specify range?
		if ($endByteOffset > 0) {
			$headers['Range'] = 'bytes=' . $startByteOffset . '-' . $endByteOffset;
		}

		// Resource name
		$resourceName = self::createResourceName($containerName , $blobName);

		// Perform request
		$response = $this->_performRequest($resourceName, '?comp=pagelist', Zend_Http_Client::GET, $headers, false, null, Zend_Service_WindowsAzure_Storage::RESOURCE_BLOB, Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::PERMISSION_WRITE);
		if ($response->isSuccessful()) {
			$result = $this->_parseResponse($response);
			$xmlRanges = null;
			if (count($result->PageRange) > 1) {
				$xmlRanges = $result->PageRange;
			} else {
				$xmlRanges = [$result->PageRange];
			}


			$ranges = [];

			for ($i = 0; $i < count($xmlRanges); $i++) {
				$ranges[] = new Zend_Service_WindowsAzure_Storage_PageRegionInstance(
				(int)$xmlRanges[$i]->Start,
				(int)$xmlRanges[$i]->End
				);
			}

			return $ranges;
		} else {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}

	/**
	 * Copy blob
	 *
	 * @param string $sourceContainerName       Source container name
	 * @param string $sourceBlobName            Source blob name
	 * @param string $destinationContainerName  Destination container name
	 * @param string $destinationBlobName       Destination blob name
	 * @param array  $metadata                  Key/value pairs of meta data
	 * @param string $sourceSnapshotId          Source snapshot identifier
	 * @param string $destinationLeaseId        Destination lease identifier
	 * @param array  $additionalHeaders         Additional headers. See http://msdn.microsoft.com/en-us/library/dd894037.aspx for more information.
	 * @return object Partial blob properties
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function copyBlob($sourceContainerName = '', $sourceBlobName = '', $destinationContainerName = '', $destinationBlobName = '', $metadata = [], $sourceSnapshotId = null, $destinationLeaseId = null, $additionalHeaders = [])
	{
		if ($sourceContainerName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Source container name is not specified.');
		}
		if (!self::isValidContainerName($sourceContainerName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Source container name does not adhere to container naming conventions. See http://msdn.microsoft.com/en-us/library/dd135715.aspx for more information.');
		}
		if ($sourceBlobName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Source blob name is not specified.');
		}
		if ($destinationContainerName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Destination container name is not specified.');
		}
		if (!self::isValidContainerName($destinationContainerName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Destination container name does not adhere to container naming conventions. See http://msdn.microsoft.com/en-us/library/dd135715.aspx for more information.');
		}
		if ($destinationBlobName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Destination blob name is not specified.');
		}
		if ($sourceContainerName === '$root' && strpos($sourceBlobName, '/') !== false) {
		require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Blobs stored in the root container can not have a name containing a forward slash (/).');
		}
		if ($destinationContainerName === '$root' && strpos($destinationBlobName, '/') !== false) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Blobs stored in the root container can not have a name containing a forward slash (/).');
		}

		// Create metadata headers
		$headers = [];
		if (!is_null($destinationLeaseId)) {
			$headers['x-ms-lease-id'] = $destinationLeaseId;
		}
		$headers = array_merge($headers, $this->_generateMetadataHeaders($metadata));

		// Additional headers?
		foreach ($additionalHeaders as $key => $value) {
			$headers[$key] = $value;
		}

		// Resource names
		$sourceResourceName = self::createResourceName($sourceContainerName, $sourceBlobName);
		if (!is_null($sourceSnapshotId)) {
			$sourceResourceName .= '?snapshot=' . $sourceSnapshotId;
		}
		$destinationResourceName = self::createResourceName($destinationContainerName, $destinationBlobName);

		// Set source blob
		$headers["x-ms-copy-source"] = '/' . $this->_accountName . '/' . $sourceResourceName;

		// Perform request
		$response = $this->_performRequest($destinationResourceName, '', Zend_Http_Client::PUT, $headers, false, null, Zend_Service_WindowsAzure_Storage::RESOURCE_BLOB, Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::PERMISSION_WRITE);
		if ($response->isSuccessful()) {

			return new Zend_Service_WindowsAzure_Storage_BlobInstance(
			$destinationContainerName,
			$destinationBlobName,
			null,
			$response->getHeader('Etag'),
			$response->getHeader('Last-modified'),
			$this->getBaseUrl() . '/' . $destinationContainerName . '/' . $destinationBlobName,
			0,
				'',
				'',
				'',
			false,
			$metadata
			);
		} else {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}

	/**
	 * Get blob
	 *
	 * @param string $containerName      Container name
	 * @param string $blobName           Blob name
	 * @param string $localFileName      Local file name to store downloaded blob
	 * @param string $snapshotId         Snapshot identifier
	 * @param string $leaseId            Lease identifier
	 * @param array  $additionalHeaders  Additional headers. See http://msdn.microsoft.com/en-us/library/dd179371.aspx for more information.
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function getBlob($containerName = '', $blobName = '', $localFileName = '', $snapshotId = null, $leaseId = null, $additionalHeaders = [])
	{
		if ($containerName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name is not specified.');
		}
		if (!self::isValidContainerName($containerName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name does not adhere to container naming conventions. See http://msdn.microsoft.com/en-us/library/dd135715.aspx for more information.');
		}
		if ($blobName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Blob name is not specified.');
		}
		if ($localFileName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Local file name is not specified.');
		}

		// Fetch data
		file_put_contents($localFileName, $this->getBlobData($containerName, $blobName, $snapshotId, $leaseId, $additionalHeaders));
	}

	/**
	 * Get blob data
	 *
	 * @param string $containerName      Container name
	 * @param string $blobName           Blob name
	 * @param string $snapshotId         Snapshot identifier
	 * @param string $leaseId            Lease identifier
	 * @param array  $additionalHeaders  Additional headers. See http://msdn.microsoft.com/en-us/library/dd179371.aspx for more information.
	 * @return string|null Blob contents
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function getBlobData($containerName = '', $blobName = '', $snapshotId = null, $leaseId = null, $additionalHeaders = [])
	{
		if ($containerName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name is not specified.');
		}
		if (!self::isValidContainerName($containerName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name does not adhere to container naming conventions. See http://msdn.microsoft.com/en-us/library/dd135715.aspx for more information.');
		}
		if ($blobName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Blob name is not specified.');
		}

		// Build query string
		$queryString = [];
		if (!is_null($snapshotId)) {
			$queryString[] = 'snapshot=' . $snapshotId;
		}
		$queryString = self::createQueryStringFromArray($queryString);

		// Additional headers?
		$headers = [];
		if (!is_null($leaseId)) {
			$headers['x-ms-lease-id'] = $leaseId;
		}
		foreach ($additionalHeaders as $key => $value) {
			$headers[$key] = $value;
		}

		// Resource name
		$resourceName = self::createResourceName($containerName , $blobName);

		// Perform request
		$response = $this->_performRequest($resourceName, $queryString, Zend_Http_Client::GET, $headers, false, null, Zend_Service_WindowsAzure_Storage::RESOURCE_BLOB, Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::PERMISSION_READ);
		if ($response->isSuccessful()) {
			return $response->getBody();
		} else {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}

	/**
	 * Get blob instance
	 *
	 * @param string $containerName      Container name
	 * @param string $blobName           Blob name
	 * @param string $snapshotId         Snapshot identifier
	 * @param string $leaseId            Lease identifier
	 * @param array  $additionalHeaders  Additional headers. See http://msdn.microsoft.com/en-us/library/dd179371.aspx for more information.
	 * @return Zend_Service_WindowsAzure_Storage_BlobInstance
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function getBlobInstance($containerName = '', $blobName = '', $snapshotId = null, $leaseId = null, $additionalHeaders = [])
	{
		if ($containerName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name is not specified.');
		}
		if (!self::isValidContainerName($containerName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name does not adhere to container naming conventions. See http://msdn.microsoft.com/en-us/library/dd135715.aspx for more information.');
		}
		if ($blobName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Blob name is not specified.');
		}
		if ($containerName === '$root' && strpos($blobName, '/') !== false) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Blobs stored in the root container can not have a name containing a forward slash (/).');
		}

		// Build query string
		$queryString = [];
		if (!is_null($snapshotId)) {
			$queryString[] = 'snapshot=' . $snapshotId;
		}
		$queryString = self::createQueryStringFromArray($queryString);

		// Additional headers?
		$headers = [];
		if (!is_null($leaseId)) {
			$headers['x-ms-lease-id'] = $leaseId;
		}
		foreach ($additionalHeaders as $key => $value) {
			$headers[$key] = $value;
		}

		// Resource name
		$resourceName = self::createResourceName($containerName , $blobName);

		// Perform request
		$response = $this->_performRequest($resourceName, $queryString, Zend_Http_Client::HEAD, $headers, false, null, Zend_Service_WindowsAzure_Storage::RESOURCE_BLOB, Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::PERMISSION_READ);
		if ($response->isSuccessful()) {
			// Parse metadata
			$metadata = $this->_parseMetadataHeaders($response->getHeaders());

			// Return blob
			return new Zend_Service_WindowsAzure_Storage_BlobInstance(
				$containerName,
				$blobName,
				$snapshotId,
				$response->getHeader('Etag'),
				$response->getHeader('Last-modified'),
				$this->getBaseUrl() . '/' . $containerName . '/' . $blobName,
				$response->getHeader('Content-Length'),
				$response->getHeader('Content-Type'),
				$response->getHeader('Content-Encoding'),
				$response->getHeader('Content-Language'),
				$response->getHeader('Cache-Control'),
				$response->getHeader('x-ms-blob-type'),
				$response->getHeader('x-ms-lease-status'),
				false,
				$metadata
			);
		} else {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}

	/**
	 * Get blob metadata
	 *
	 * @param string $containerName  Container name
	 * @param string $blobName       Blob name
	 * @param string $snapshotId     Snapshot identifier
	 * @param string $leaseId        Lease identifier
	 * @return array Key/value pairs of meta data
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function getBlobMetadata($containerName = '', $blobName = '', $snapshotId = null, $leaseId = null)
	{
		if ($containerName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name is not specified.');
		}
		if (!self::isValidContainerName($containerName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name does not adhere to container naming conventions. See http://msdn.microsoft.com/en-us/library/dd135715.aspx for more information.');
		}
		if ($blobName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Blob name is not specified.');
		}
		if ($containerName === '$root' && strpos($blobName, '/') !== false) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Blobs stored in the root container can not have a name containing a forward slash (/).');
		}

		return $this->getBlobInstance($containerName, $blobName, $snapshotId, $leaseId)->Metadata;
	}

	/**
	 * Set blob metadata
	 *
	 * Calling the Set Blob Metadata operation overwrites all existing metadata that is associated with the blob. It's not possible to modify an individual name/value pair.
	 *
	 * @param string $containerName      Container name
	 * @param string $blobName           Blob name
	 * @param array  $metadata           Key/value pairs of meta data
	 * @param string $leaseId            Lease identifier
	 * @param array  $additionalHeaders  Additional headers. See http://msdn.microsoft.com/en-us/library/dd179371.aspx for more information.
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function setBlobMetadata($containerName = '', $blobName = '', $metadata = [], $leaseId = null, $additionalHeaders = [])
	{
		if ($containerName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name is not specified.');
		}

		if (!self::isValidContainerName($containerName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name does not adhere to container naming conventions. See http://msdn.microsoft.com/en-us/library/dd135715.aspx for more information.');
		}

		if ($blobName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Blob name is not specified.');
		}

		if ($containerName === '$root' && strpos($blobName, '/') !== false) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Blobs stored in the root container can not have a name containing a forward slash (/).');
		}

		if (count($metadata) === 0) {
			return;
		}

		// Create metadata headers
		$headers = [];
		if (!is_null($leaseId)) {
			$headers['x-ms-lease-id'] = $leaseId;
		}
		$headers = array_merge($headers, $this->_generateMetadataHeaders($metadata));

		// Additional headers?
		foreach ($additionalHeaders as $key => $value) {
			$headers[$key] = $value;
		}

		// Perform request
		$response = $this->_performRequest($containerName . '/' . $blobName, '?comp=metadata', Zend_Http_Client::PUT, $headers, false, null, Zend_Service_WindowsAzure_Storage::RESOURCE_BLOB, Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::PERMISSION_WRITE);
		if (!$response->isSuccessful()) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}

	/**
	 * Set blob properties
	 *
	 * All available properties are listed at http://msdn.microsoft.com/en-us/library/ee691966.aspx and should be provided in the $additionalHeaders parameter.
	 *
	 * @param string $containerName      Container name
	 * @param string $blobName           Blob name
	 * @param string $leaseId            Lease identifier
	 * @param array  $additionalHeaders  Additional headers. See http://msdn.microsoft.com/en-us/library/dd179371.aspx for more information.
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function setBlobProperties($containerName = '', $blobName = '', $leaseId = null, $additionalHeaders = [])
	{
		if ($containerName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name is not specified.');
		}

		if (!self::isValidContainerName($containerName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name does not adhere to container naming conventions. See http://msdn.microsoft.com/en-us/library/dd135715.aspx for more information.');
		}

		if ($blobName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Blob name is not specified.');
		}

		if ($containerName === '$root' && strpos($blobName, '/') !== false) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Blobs stored in the root container can not have a name containing a forward slash (/).');
		}

		if (count($additionalHeaders) === 0) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('No additional headers are specified.');
		}

		// Create headers
		$headers = [];

		// Lease set?
		if (!is_null($leaseId)) {
			$headers['x-ms-lease-id'] = $leaseId;
		}

		// Additional headers?
		foreach ($additionalHeaders as $key => $value) {
			$headers[$key] = $value;
		}

		// Perform request
		$response = $this->_performRequest($containerName . '/' . $blobName, '?comp=properties', Zend_Http_Client::PUT, $headers, false, null, Zend_Service_WindowsAzure_Storage::RESOURCE_BLOB, Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::PERMISSION_WRITE);
		if (!$response->isSuccessful()) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}

	/**
	 * Get blob properties
	 *
	 * @param string $containerName      Container name
	 * @param string $blobName           Blob name
	 * @param string $snapshotId         Snapshot identifier
	 * @param string $leaseId            Lease identifier
	 * @return Zend_Service_WindowsAzure_Storage_BlobInstance
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function getBlobProperties($containerName = '', $blobName = '', $snapshotId = null, $leaseId = null)
	{
		if ($containerName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name is not specified.');
		}
		if (!self::isValidContainerName($containerName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name does not adhere to container naming conventions. See http://msdn.microsoft.com/en-us/library/dd135715.aspx for more information.');
		}
		if ($blobName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Blob name is not specified.');
		}
		if ($containerName === '$root' && strpos($blobName, '/') !== false) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Blobs stored in the root container can not have a name containing a forward slash (/).');
		}

		return $this->getBlobInstance($containerName, $blobName, $snapshotId, $leaseId);
	}

	/**
	 * Delete blob
	 *
	 * @param string $containerName      Container name
	 * @param string $blobName           Blob name
	 * @param string $snapshotId         Snapshot identifier
	 * @param string $leaseId            Lease identifier
	 * @param array  $additionalHeaders  Additional headers. See http://msdn.microsoft.com/en-us/library/dd179371.aspx for more information.
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function deleteBlob($containerName = '', $blobName = '', $snapshotId = null, $leaseId = null, $additionalHeaders = [])
	{
		if ($containerName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name is not specified.');
		}
		if (!self::isValidContainerName($containerName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name does not adhere to container naming conventions. See http://msdn.microsoft.com/en-us/library/dd135715.aspx for more information.');
		}
		if ($blobName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Blob name is not specified.');
		}
		if ($containerName === '$root' && strpos($blobName, '/') !== false) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Blobs stored in the root container can not have a name containing a forward slash (/).');
		}

		// Build query string
		$queryString = [];
		if (!is_null($snapshotId)) {
			$queryString[] = 'snapshot=' . $snapshotId;
		}
		$queryString = self::createQueryStringFromArray($queryString);

		// Additional headers?
		$headers = [];
		if (!is_null($leaseId)) {
			$headers['x-ms-lease-id'] = $leaseId;
		}
		foreach ($additionalHeaders as $key => $value) {
			$headers[$key] = $value;
		}

		// Resource name
		$resourceName = self::createResourceName($containerName , $blobName);

		// Perform request
		$response = $this->_performRequest($resourceName, $queryString, Zend_Http_Client::DELETE, $headers, false, null, Zend_Service_WindowsAzure_Storage::RESOURCE_BLOB, Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::PERMISSION_WRITE);
		if (!$response->isSuccessful()) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}

	/**
	 * Snapshot blob
	 *
	 * @param string $containerName      Container name
	 * @param string $blobName           Blob name
	 * @param array  $metadata           Key/value pairs of meta data
	 * @param array  $additionalHeaders  Additional headers. See http://msdn.microsoft.com/en-us/library/dd179371.aspx for more information.
	 * @return string Date/Time value representing the snapshot identifier.
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function snapshotBlob($containerName = '', $blobName = '', $metadata = [], $additionalHeaders = [])
	{
		if ($containerName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name is not specified.');
		}
		if (!self::isValidContainerName($containerName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name does not adhere to container naming conventions. See http://msdn.microsoft.com/en-us/library/dd135715.aspx for more information.');
		}
		if ($blobName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Blob name is not specified.');
		}
		if ($containerName === '$root' && strpos($blobName, '/') !== false) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Blobs stored in the root container can not have a name containing a forward slash (/).');
		}

		// Additional headers?
		$headers = [];
		foreach ($additionalHeaders as $key => $value) {
			$headers[$key] = $value;
		}

		// Resource name
		$resourceName = self::createResourceName($containerName , $blobName);

		// Perform request
		$response = $this->_performRequest($resourceName, '?comp=snapshot', Zend_Http_Client::PUT, $headers, false, null, Zend_Service_WindowsAzure_Storage::RESOURCE_BLOB, Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::PERMISSION_WRITE);
		if ($response->isSuccessful()) {
			return $response->getHeader('x-ms-snapshot');
		} else {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}

	/**
	 * Lease blob - See (http://msdn.microsoft.com/en-us/library/ee691972.aspx)
	 *
	 * @param string $containerName      Container name
	 * @param string $blobName           Blob name
	 * @param string $leaseAction        Lease action (Zend_Service_WindowsAzure_Storage_Blob::LEASE_*)
	 * @param string $leaseId            Lease identifier, required to renew the lease or to release the lease.
	 * @return Zend_Service_WindowsAzure_Storage_LeaseInstance Lease instance
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function leaseBlob($containerName = '', $blobName = '', $leaseAction = self::LEASE_ACQUIRE, $leaseId = null)
	{
		if ($containerName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name is not specified.');
		}
		if (!self::isValidContainerName($containerName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name does not adhere to container naming conventions. See http://msdn.microsoft.com/en-us/library/dd135715.aspx for more information.');
		}
		if ($blobName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Blob name is not specified.');
		}
		if ($containerName === '$root' && strpos($blobName, '/') !== false) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Blobs stored in the root container can not have a name containing a forward slash (/).');
		}

		// Additional headers?
		$headers = [];
		$headers['x-ms-lease-action'] = strtolower($leaseAction);
		if (!is_null($leaseId)) {
			$headers['x-ms-lease-id'] = $leaseId;
		}

		// Resource name
		$resourceName = self::createResourceName($containerName , $blobName);

		// Perform request
		$response = $this->_performRequest($resourceName, '?comp=lease', Zend_Http_Client::PUT, $headers, false, null, Zend_Service_WindowsAzure_Storage::RESOURCE_BLOB, Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::PERMISSION_WRITE);



		if ($response->isSuccessful()) {
			return new Zend_Service_WindowsAzure_Storage_LeaseInstance(
			$containerName,
			$blobName,
			$response->getHeader('x-ms-lease-id'),
			$response->getHeader('x-ms-lease-time'));
		} else {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}

	/**
	 * List blobs
	 *
	 * @param string $containerName Container name
	 * @param string $prefix     Optional. Filters the results to return only blobs whose name begins with the specified prefix.
	 * @param string $delimiter  Optional. Delimiter, i.e. '/', for specifying folder hierarchy
	 * @param int    $maxResults Optional. Specifies the maximum number of blobs to return per call to Azure storage. This does NOT affect list size returned by this function. (maximum: 5000)
	 * @param string $marker     Optional string value that identifies the portion of the list to be returned with the next list operation.
	 * @param string $include    Optional. Specifies that the response should include one or more of the following subsets: '', 'metadata', 'snapshots', 'uncommittedblobs'). Multiple values can be added separated with a comma (,)
	 * @param int    $currentResultCount Current result count (internal use)
	 * @return array
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function listBlobs($containerName = '', $prefix = '', $delimiter = '', $maxResults = null, $marker = null, $include = null, $currentResultCount = 0)
	{
		if ($containerName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name is not specified.');
		}
		if (!self::isValidContainerName($containerName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name does not adhere to container naming conventions. See http://msdn.microsoft.com/en-us/library/dd135715.aspx for more information.');
		}

		// Build query string
		$queryString = ['restype=container', 'comp=list'];
		if (!is_null($prefix)) {
			$queryString[] = 'prefix=' . $prefix;
		}
		if ($delimiter !== '') {
			$queryString[] = 'delimiter=' . $delimiter;
		}
		if (!is_null($maxResults)) {
			$queryString[] = 'maxresults=' . $maxResults;
		}
		if (!is_null($marker)) {
			$queryString[] = 'marker=' . $marker;
		}
		if (!is_null($include)) {
			$queryString[] = 'include=' . $include;
		}
		$queryString = self::createQueryStringFromArray($queryString);

		// Perform request
		$response = $this->_performRequest($containerName, $queryString, Zend_Http_Client::GET, [], false, null, Zend_Service_WindowsAzure_Storage::RESOURCE_BLOB, Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::PERMISSION_LIST);
		if ($response->isSuccessful()) {
			// Return value
			$blobs = [];

			// Blobs
			$xmlBlobs = $this->_parseResponse($response)->Blobs->Blob;
			if (!is_null($xmlBlobs)) {

				for ($i = 0; $i < count($xmlBlobs); $i++) {
					$properties = (array)$xmlBlobs[$i]->Properties;

					$blobs[] = new Zend_Service_WindowsAzure_Storage_BlobInstance(
					$containerName,
					(string)$xmlBlobs[$i]->Name,
					(string)$xmlBlobs[$i]->Snapshot,
					(string)$properties['Etag'],
					(string)$properties['Last-Modified'],
					(string)$xmlBlobs[$i]->Url,
					(string)$properties['Content-Length'],
					(string)$properties['Content-Type'],
					(string)$properties['Content-Encoding'],
					(string)$properties['Content-Language'],
					(string)$properties['Cache-Control'],
					(string)$properties['BlobType'],
					(string)$properties['LeaseStatus'],
					false,
					$this->_parseMetadataElement($xmlBlobs[$i])
					);
				}
			}

			// Blob prefixes (folders)
			$xmlBlobs = $this->_parseResponse($response)->Blobs->BlobPrefix;

			if (!is_null($xmlBlobs)) {

				for ($i = 0; $i < count($xmlBlobs); $i++) {
					$blobs[] = new Zend_Service_WindowsAzure_Storage_BlobInstance(
					$containerName,
					(string)$xmlBlobs[$i]->Name,
					null,
						'',
						'',
						'',
					0,
						'',
						'',
						'',
						'',
						'',
						'',
					true,
					$this->_parseMetadataElement($xmlBlobs[$i])
					);
				}
			}

			// More blobs?
			$xmlMarker = (string)$this->_parseResponse($response)->NextMarker;
			$currentResultCount = $currentResultCount + count($blobs);
			if (!is_null($maxResults) && $currentResultCount < $maxResults) {
				if (!is_null($xmlMarker) && $xmlMarker != '') {
					$blobs = array_merge($blobs, $this->listBlobs($containerName, $prefix, $delimiter, $maxResults, $marker, $include, $currentResultCount));
				}
			}
			if (!is_null($maxResults) && count($blobs) > $maxResults) {
				$blobs = array_slice($blobs, 0, $maxResults);
			}

			return $blobs;
		} else {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}

	/**
	 * Generate shared access URL
	 *
	 * @param string $containerName  Container name
	 * @param string $blobName       Blob name
	 * @param string $resource       Signed resource - container (c) - blob (b)
	 * @param string $permissions    Signed permissions - read (r), write (w), delete (d) and list (l)
	 * @param string $start          The time at which the Shared Access Signature becomes valid.
	 * @param string $expiry         The time at which the Shared Access Signature becomes invalid.
	 * @param string $identifier     Signed identifier
	 * @return string
	 */
	public function generateSharedAccessUrl($containerName = '', $blobName = '', $resource = 'b', $permissions = 'r', $start = '', $expiry = '', $identifier = '')
	{
		if ($containerName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name is not specified.');
		}
		if (!self::isValidContainerName($containerName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Container name does not adhere to container naming conventions. See http://msdn.microsoft.com/en-us/library/dd135715.aspx for more information.');
		}

		// Resource name
		$resourceName = self::createResourceName($containerName , $blobName);

		// Generate URL
		return $this->getBaseUrl() . '/' . $resourceName . '?' .
		$this->_sharedAccessSignatureCredentials->createSignedQueryString(
		$resourceName,
		        '',
		$resource,
		$permissions,
		$start,
		$expiry,
		$identifier);
	}

	/**
	 * Register this object as stream wrapper client
	 *
	 * @param  string $name Protocol name
	 * @return Zend_Service_WindowsAzure_Storage_Blob
	 */
	public function registerAsClient($name)
	{
		self::$_wrapperClients[$name] = $this;
		return $this;
	}

	/**
	 * Unregister this object as stream wrapper client
	 *
	 * @param  string $name Protocol name
	 * @return Zend_Service_WindowsAzure_Storage_Blob
	 */
	public function unregisterAsClient($name)
	{
		unset(self::$_wrapperClients[$name]);
		return $this;
	}

	/**
	 * Get wrapper client for stream type
	 *
	 * @param  string $name Protocol name
	 * @return Zend_Service_WindowsAzure_Storage_Blob
	 */
	public static function getWrapperClient($name)
	{
		return self::$_wrapperClients[$name];
	}

	/**
	 * Register this object as stream wrapper
	 *
	 * @param  string $name Protocol name
	 */
	public function registerStreamWrapper($name = 'azure')
	{
		stream_register_wrapper($name, 'Zend_Service_WindowsAzure_Storage_Blob_Stream');
		$this->registerAsClient($name);
	}

	/**
	 * Unregister this object as stream wrapper
	 *
	 * @param  string $name Protocol name
	 * @return void
     */
	public function unregisterStreamWrapper($name = 'azure')
	{
		stream_wrapper_unregister($name);
		$this->unregisterAsClient($name);
	}

	/**
	 * Create resource name
	 *
	 * @param string $containerName  Container name
	 * @param string $blobName Blob name
	 * @return string
	 */
	public static function createResourceName($containerName = '', $blobName = '')
	{
		// Resource name
		$resourceName = $containerName . '/' . $blobName;
		if ($containerName === '' || $containerName === '$root') {
			$resourceName = $blobName;
		}
		if ($blobName === '') {
			$resourceName = $containerName;
		}

		return $resourceName;
	}

	/**
	 * Is valid container name?
	 *
	 * @param string $containerName Container name
	 * @return boolean
	 */
	public static function isValidContainerName($containerName = '')
	{
		if ($containerName == '$root') {
			return true;
		}

		if (preg_match("/^[a-z0-9][a-z0-9-]*$/", $containerName) === 0) {
			return false;
		}

		if (strpos($containerName, '--') !== false) {
			return false;
		}

		if (strtolower($containerName) != $containerName) {
			return false;
		}

		if (strlen($containerName) < 3 || strlen($containerName) > 63) {
			return false;
		}

		if (substr($containerName, -1) == '-') {
			return false;
		}

		return true;
	}

	/**
	 * Get error message from Zend_Http_Response
	 *
	 * @param Zend_Http_Response $response Repsonse
	 * @param string $alternativeError Alternative error message
	 * @return string
	 */
	protected function _getErrorMessage(Zend_Http_Response $response, $alternativeError = 'Unknown error.')
	{
		$response = $this->_parseResponse($response);
		if ($response && $response->Message) {
			return (string)$response->Message;
		} else {
			return $alternativeError;
		}
	}

	/**
	 * Generate block id
	 *
	 * @param int $part Block number
	 * @return string Windows Azure Blob Storage block number
	 */
	protected function _generateBlockId($part = 0)
	{
		$returnValue = $part;
		while (strlen($returnValue) < 64) {
			$returnValue = '0' . $returnValue;
		}

		return $returnValue;
	}
}
