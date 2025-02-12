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
 * see Zend_Service_WindowsAzure_Storage
 */
require_once 'Zend/Service/WindowsAzure/Storage.php';

/**
 * @see Zend_Service_WindowsAzure_Storage_QueueInstance
 */
require_once 'Zend/Service/WindowsAzure/Storage/QueueInstance.php';

/**
 * @see Zend_Service_WindowsAzure_Storage_QueueMessage
 */
require_once 'Zend/Service/WindowsAzure/Storage/QueueMessage.php';

/**
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage Storage
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_WindowsAzure_Storage_Queue extends Zend_Service_WindowsAzure_Storage
{
	/**
	 * Maximal message size (in bytes)
	 */
	public const MAX_MESSAGE_SIZE = 8388608;

	/**
	 * Maximal message ttl (in seconds)
	 */
	public const MAX_MESSAGE_TTL = 604800;

	/**
	 * Creates a new Zend_Service_WindowsAzure_Storage_Queue instance
	 *
	 * @param string $host Storage host name
	 * @param string $accountName Account name for Windows Azure
	 * @param string $accountKey Account key for Windows Azure
	 * @param boolean $usePathStyleUri Use path-style URI's
	 * @param Zend_Service_WindowsAzure_RetryPolicy_RetryPolicyAbstract $retryPolicy Retry policy to use when making requests
	 */
	public function __construct($host = Zend_Service_WindowsAzure_Storage::URL_DEV_QUEUE, $accountName = Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::DEVSTORE_ACCOUNT, $accountKey = Zend_Service_WindowsAzure_Credentials_CredentialsAbstract::DEVSTORE_KEY, $usePathStyleUri = false, ?Zend_Service_WindowsAzure_RetryPolicy_RetryPolicyAbstract $retryPolicy = null)
	{
		parent::__construct($host, $accountName, $accountKey, $usePathStyleUri, $retryPolicy);

		// API version
		$this->_apiVersion = '2009-09-19';
	}

	/**
	 * Check if a queue exists
	 *
	 * @param string $queueName Queue name
	 * @return boolean
	 */
	public function queueExists($queueName = '')
	{
		if ($queueName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Queue name is not specified.');
		}
		if (!self::isValidQueueName($queueName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
		    throw new Zend_Service_WindowsAzure_Exception('Queue name does not adhere to queue naming conventions. See http://msdn.microsoft.com/en-us/library/dd179349.aspx for more information.');
		}

		// List queues
        $queues = $this->listQueues($queueName, 1);
        foreach ($queues as $queue) {
            if ($queue->Name == $queueName) {
                return true;
            }
        }

        return false;
	}

	/**
	 * Create queue
	 *
	 * @param string $queueName Queue name
	 * @param array  $metadata  Key/value pairs of meta data
	 * @return object Queue properties
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function createQueue($queueName = '', $metadata = [])
	{
		if ($queueName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Queue name is not specified.');
		}
		if (!self::isValidQueueName($queueName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
		    throw new Zend_Service_WindowsAzure_Exception('Queue name does not adhere to queue naming conventions. See http://msdn.microsoft.com/en-us/library/dd179349.aspx for more information.');
		}

		// Create metadata headers
		$headers = [];
		$headers = array_merge($headers, $this->_generateMetadataHeaders($metadata));

		// Perform request
		$response = $this->_performRequest($queueName, '', Zend_Http_Client::PUT, $headers);
		if ($response->isSuccessful()) {

		    return new Zend_Service_WindowsAzure_Storage_QueueInstance(
		        $queueName,
		        $metadata
		    );
		} else {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}

	/**
	 * Create queue if it does not exist
	 *
	 * @param string $queueName Queue name
	 * @param array  $metadata  Key/value pairs of meta data
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function createQueueIfNotExists($queueName = '', $metadata = [])
	{
		if (!$this->queueExists($queueName)) {
			$this->createQueue($queueName, $metadata);
		}
	}

	/**
	 * Get queue
	 *
	 * @param string $queueName  Queue name
	 * @return Zend_Service_WindowsAzure_Storage_QueueInstance
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function getQueue($queueName = '')
	{
		if ($queueName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Queue name is not specified.');
		}
		if (!self::isValidQueueName($queueName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
		    throw new Zend_Service_WindowsAzure_Exception('Queue name does not adhere to queue naming conventions. See http://msdn.microsoft.com/en-us/library/dd179349.aspx for more information.');
		}

		// Perform request
		$response = $this->_performRequest($queueName, '?comp=metadata', Zend_Http_Client::GET);
		if ($response->isSuccessful()) {
		    // Parse metadata
		    $metadata = $this->_parseMetadataHeaders($response->getHeaders());

		    // Return queue
		    $queue = new Zend_Service_WindowsAzure_Storage_QueueInstance(
		        $queueName,
		        $metadata
		    );
		    $queue->ApproximateMessageCount = (int)$response->getHeader('x-ms-approximate-message-count');
		    return $queue;
		} else {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
		    throw new Zend_Service_WindowsAzure_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}

	/**
	 * Get queue metadata
	 *
	 * @param string $queueName  Queue name
	 * @return array Key/value pairs of meta data
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function getQueueMetadata($queueName = '')
	{
		if ($queueName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Queue name is not specified.');
		}
		if (!self::isValidQueueName($queueName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
		    throw new Zend_Service_WindowsAzure_Exception('Queue name does not adhere to queue naming conventions. See http://msdn.microsoft.com/en-us/library/dd179349.aspx for more information.');
		}

	    return $this->getQueue($queueName)->Metadata;
	}

	/**
	 * Set queue metadata
	 *
	 * Calling the Set Queue Metadata operation overwrites all existing metadata that is associated with the queue. It's not possible to modify an individual name/value pair.
	 *
	 * @param string $queueName  Queue name
	 * @param array  $metadata       Key/value pairs of meta data
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function setQueueMetadata($queueName = '', $metadata = [])
	{
		if ($queueName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Queue name is not specified.');
		}

		if (!self::isValidQueueName($queueName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
		    throw new Zend_Service_WindowsAzure_Exception('Queue name does not adhere to queue naming conventions. See http://msdn.microsoft.com/en-us/library/dd179349.aspx for more information.');
		}

		if (count($metadata) === 0) {
		    return;
		}

		// Create metadata headers
		$headers = [];
		$headers = array_merge($headers, $this->_generateMetadataHeaders($metadata));

		// Perform request
		$response = $this->_performRequest($queueName, '?comp=metadata', Zend_Http_Client::PUT, $headers);

		if (!$response->isSuccessful()) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}

	/**
	 * Delete queue
	 *
	 * @param string $queueName Queue name
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function deleteQueue($queueName = '')
	{
		if ($queueName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Queue name is not specified.');
		}
		if (!self::isValidQueueName($queueName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
		    throw new Zend_Service_WindowsAzure_Exception('Queue name does not adhere to queue naming conventions. See http://msdn.microsoft.com/en-us/library/dd179349.aspx for more information.');
		}

		// Perform request
		$response = $this->_performRequest($queueName, '', Zend_Http_Client::DELETE);
		if (!$response->isSuccessful()) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}

	/**
	 * List queues
	 *
	 * @param string $prefix     Optional. Filters the results to return only queues whose name begins with the specified prefix.
	 * @param int    $maxResults Optional. Specifies the maximum number of queues to return per call to Azure storage. This does NOT affect list size returned by this function. (maximum: 5000)
	 * @param string $marker     Optional string value that identifies the portion of the list to be returned with the next list operation.
	 * @param string $include    Optional. Include this parameter to specify that the queue's metadata be returned as part of the response body. (allowed values: '', 'metadata')
	 * @param int    $currentResultCount Current result count (internal use)
	 * @return array
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function listQueues($prefix = null, $maxResults = null, $marker = null, $include = null, $currentResultCount = 0)
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
		$response = $this->_performRequest('', $queryString, Zend_Http_Client::GET);
		if ($response->isSuccessful()) {
			$xmlQueues = $this->_parseResponse($response)->Queues->Queue;
			$xmlMarker = (string)$this->_parseResponse($response)->NextMarker;

			$queues = [];
			if (!is_null($xmlQueues)) {

				for ($i = 0; $i < count($xmlQueues); $i++) {
					$queues[] = new Zend_Service_WindowsAzure_Storage_QueueInstance(
						(string)$xmlQueues[$i]->Name,
						$this->_parseMetadataElement($xmlQueues[$i])
					);
				}
			}
			$currentResultCount = $currentResultCount + count($queues);
			if (!is_null($maxResults) && $currentResultCount < $maxResults) {
    			if (!is_null($xmlMarker) && $xmlMarker != '') {
    			    $queues = array_merge($queues, $this->listQueues($prefix, $maxResults, $xmlMarker, $include, $currentResultCount));
    			}
			}
			if (!is_null($maxResults) && count($queues) > $maxResults) {
			    $queues = array_slice($queues, 0, $maxResults);
			}

			return $queues;
		} else {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}

	/**
	 * Put message into queue
	 *
	 * @param string $queueName  Queue name
	 * @param string $message    Message
	 * @param int    $ttl        Message Time-To-Live (in seconds). Defaults to 7 days if the parameter is omitted.
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function putMessage($queueName = '', $message = '', $ttl = null)
	{
		if ($queueName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Queue name is not specified.');
		}
		if (!self::isValidQueueName($queueName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
		    throw new Zend_Service_WindowsAzure_Exception('Queue name does not adhere to queue naming conventions. See http://msdn.microsoft.com/en-us/library/dd179349.aspx for more information.');
		}
		if (strlen($message) > self::MAX_MESSAGE_SIZE) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
		    throw new Zend_Service_WindowsAzure_Exception('Message is too big. Message content should be < 8KB.');
		}
		if ($message == '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
		    throw new Zend_Service_WindowsAzure_Exception('Message is not specified.');
		}
		if (!is_null($ttl) && ($ttl <= 0 || $ttl > self::MAX_MESSAGE_SIZE)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
		    throw new Zend_Service_WindowsAzure_Exception('Message TTL is invalid. Maximal TTL is 7 days (' . self::MAX_MESSAGE_SIZE . ' seconds) and should be greater than zero.');
		}

	    // Build query string
		$queryString = [];
        if (!is_null($ttl)) {
	        $queryString[] = 'messagettl=' . $ttl;
        }
	    $queryString = self::createQueryStringFromArray($queryString);

	    // Build body
	    $rawData = '';
	    $rawData .= '<QueueMessage>';
	    $rawData .= '    <MessageText>' . base64_encode($message) . '</MessageText>';
	    $rawData .= '</QueueMessage>';

		// Perform request
		$response = $this->_performRequest($queueName . '/messages', $queryString, Zend_Http_Client::POST, [], false, $rawData);

		if (!$response->isSuccessful()) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Error putting message into queue.');
		}
	}

	/**
	 * Get queue messages
	 *
	 * @param string $queueName         Queue name
	 * @param string $numOfMessages     Optional. A nonzero integer value that specifies the number of messages to retrieve from the queue, up to a maximum of 32. By default, a single message is retrieved from the queue with this operation.
	 * @param int    $visibilityTimeout Optional. An integer value that specifies the message's visibility timeout in seconds. The maximum value is 2 hours. The default message visibility timeout is 30 seconds.
	 * @param string $peek              Peek only?
	 * @return array
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function getMessages($queueName = '', $numOfMessages = 1, $visibilityTimeout = null, $peek = false)
	{
		if ($queueName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Queue name is not specified.');
		}
		if (!self::isValidQueueName($queueName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
		    throw new Zend_Service_WindowsAzure_Exception('Queue name does not adhere to queue naming conventions. See http://msdn.microsoft.com/en-us/library/dd179349.aspx for more information.');
		}
		if ($numOfMessages < 1 || $numOfMessages > 32 || (int)$numOfMessages != $numOfMessages) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
		    throw new Zend_Service_WindowsAzure_Exception('Invalid number of messages to retrieve.');
		}
		if (!is_null($visibilityTimeout) && ($visibilityTimeout <= 0 || $visibilityTimeout > 7200)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
		    throw new Zend_Service_WindowsAzure_Exception('Visibility timeout is invalid. Maximum value is 2 hours (7200 seconds) and should be greater than zero.');
		}

	    // Build query string
		$queryString = [];
    	if ($peek) {
    	    $queryString[] = 'peekonly=true';
    	}
    	if ($numOfMessages > 1) {
	        $queryString[] = 'numofmessages=' . $numOfMessages;
    	}
    	if (!$peek && !is_null($visibilityTimeout)) {
	        $queryString[] = 'visibilitytimeout=' . $visibilityTimeout;
    	}
	    $queryString = self::createQueryStringFromArray($queryString);

		// Perform request
		$response = $this->_performRequest($queueName . '/messages', $queryString, Zend_Http_Client::GET);
		if ($response->isSuccessful()) {
		    // Parse results
			$result = $this->_parseResponse($response);
		    if (!$result) {
		        return [];
		    }

		    $xmlMessages = null;
		    if (count($result->QueueMessage) > 1) {
    		    $xmlMessages = $result->QueueMessage;
    		} else {
    		    $xmlMessages = [$result->QueueMessage];
    		}

			$messages = [];
			for ($i = 0; $i < count($xmlMessages); $i++) {
				$messages[] = new Zend_Service_WindowsAzure_Storage_QueueMessage(
					(string)$xmlMessages[$i]->MessageId,
					(string)$xmlMessages[$i]->InsertionTime,
					(string)$xmlMessages[$i]->ExpirationTime,
					($peek ? '' : (string)$xmlMessages[$i]->PopReceipt),
					($peek ? '' : (string)$xmlMessages[$i]->TimeNextVisible),
					(string)$xmlMessages[$i]->DequeueCount,
					base64_decode((string)$xmlMessages[$i]->MessageText)
			    );
			}

			return $messages;
		} else {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}

	/**
	 * Peek queue messages
	 *
	 * @param string $queueName         Queue name
	 * @param string $numOfMessages     Optional. A nonzero integer value that specifies the number of messages to retrieve from the queue, up to a maximum of 32. By default, a single message is retrieved from the queue with this operation.
	 * @return array
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function peekMessages($queueName = '', $numOfMessages = 1)
	{
	    return $this->getMessages($queueName, $numOfMessages, null, true);
	}

	/**
	 * Checks to see if a given queue has messages
	 *
	 * @param string $queueName         Queue name
	 * @return boolean
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function hasMessages($queueName = '')
	{
		return count($this->peekMessages($queueName)) > 0;
	}

	/**
	 * Clear queue messages
	 *
	 * @param string $queueName         Queue name
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function clearMessages($queueName = '')
	{
		if ($queueName === '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Queue name is not specified.');
		}
		if (!self::isValidQueueName($queueName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
		    throw new Zend_Service_WindowsAzure_Exception('Queue name does not adhere to queue naming conventions. See http://msdn.microsoft.com/en-us/library/dd179349.aspx for more information.');
		}

		// Perform request
		$response = $this->_performRequest($queueName . '/messages', '', Zend_Http_Client::DELETE);
		if (!$response->isSuccessful()) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Error clearing messages from queue.');
		}
	}

	/**
	 * Delete queue message
	 *
	 * @param string $queueName Queue name
	 * @param Zend_Service_WindowsAzure_Storage_QueueMessage $message Message to delete from queue. A message retrieved using "peekMessages" can NOT be deleted!
	 * @throws Zend_Service_WindowsAzure_Exception
	 */
	public function deleteMessage($queueName, Zend_Service_WindowsAzure_Storage_QueueMessage $message)
	{

		if (empty($queueName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Queue name is not specified.');
		}
		if (!self::isValidQueueName($queueName)) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
		    throw new Zend_Service_WindowsAzure_Exception('Queue name does not adhere to queue naming conventions. See http://msdn.microsoft.com/en-us/library/dd179349.aspx for more information.');
		}
		if ($message->PopReceipt == '') {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
		    throw new Zend_Service_WindowsAzure_Exception('A message retrieved using "peekMessages" can NOT be deleted! Use "getMessages" instead.');
		}

		// Perform request
		$response = $this->_performRequest($queueName . '/messages/' . $message->MessageId, '?popreceipt=' . urlencode($message->PopReceipt), Zend_Http_Client::DELETE);
		if (!$response->isSuccessful()) {
			require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception($this->_getErrorMessage($response, 'Resource could not be accessed.'));
		}
	}

	/**
	 * Is valid queue name?
	 *
	 * @param string $queueName Queue name
	 * @return boolean
	 */
    public static function isValidQueueName($queueName = '')
    {
        if (preg_match("/^[a-z0-9][a-z0-9-]*$/", $queueName) === 0) {
            return false;
        }

        if (strpos($queueName, '--') !== false) {
            return false;
        }

        if (strtolower($queueName) != $queueName) {
            return false;
        }

        if (strlen($queueName) < 3 || strlen($queueName) > 63) {
            return false;
        }

        if (substr($queueName, -1) == '-') {
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
}
