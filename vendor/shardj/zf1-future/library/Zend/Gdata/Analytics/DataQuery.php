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
 * @package    Zend_Gdata
 * @subpackage Analytics
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Gdata_Query
 */
require_once 'Zend/Gdata/Query.php';

/**
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Analytics
 */
class Zend_Gdata_Analytics_DataQuery extends Zend_Gdata_Query
{
    public const ANALYTICS_FEED_URI = 'https://www.googleapis.com/analytics/v2.4/data';

    /**
     * The default URI used for feeds.
     */
    protected $_defaultFeedUri = self::ANALYTICS_FEED_URI;

    // D1. Visitor
    public const DIMENSION_BROWSER = 'ga:browser';
    public const DIMENSION_BROWSER_VERSION = 'ga:browserVersion';
    public const DIMENSION_CITY = 'ga:city';
    public const DIMENSION_CONNECTIONSPEED = 'ga:connectionSpeed';
    public const DIMENSION_CONTINENT = 'ga:continent';
    public const DIMENSION_COUNTRY = 'ga:country';
    public const DIMENSION_DATE = 'ga:date';
    public const DIMENSION_DAY = 'ga:day';
    public const DIMENSION_DAYS_SINCE_LAST_VISIT= 'ga:daysSinceLastVisit';
    public const DIMENSION_FLASH_VERSION = 'ga:flashVersion';
    public const DIMENSION_HOSTNAME = 'ga:hostname';
    public const DIMENSION_HOUR = 'ga:hour';
    public const DIMENSION_JAVA_ENABLED= 'ga:javaEnabled';
    public const DIMENSION_LANGUAGE= 'ga:language';
    public const DIMENSION_LATITUDE = 'ga:latitude';
    public const DIMENSION_LONGITUDE = 'ga:longitude';
    public const DIMENSION_MONTH = 'ga:month';
    public const DIMENSION_NETWORK_DOMAIN = 'ga:networkDomain';
    public const DIMENSION_NETWORK_LOCATION = 'ga:networkLocation';
    public const DIMENSION_OPERATING_SYSTEM = 'ga:operatingSystem';
    public const DIMENSION_OPERATING_SYSTEM_VERSION = 'ga:operatingSystemVersion';
    public const DIMENSION_PAGE_DEPTH = 'ga:pageDepth';
    public const DIMENSION_REGION = 'ga:region';
    public const DIMENSION_SCREEN_COLORS= 'ga:screenColors';
    public const DIMENSION_SCREEN_RESOLUTION = 'ga:screenResolution';
    public const DIMENSION_SUB_CONTINENT = 'ga:subContinent';
    public const DIMENSION_USER_DEFINED_VALUE = 'ga:userDefinedValue';
    public const DIMENSION_VISIT_COUNT = 'ga:visitCount';
    public const DIMENSION_VISIT_LENGTH = 'ga:visitLength';
    public const DIMENSION_VISITOR_TYPE = 'ga:visitorType';
    public const DIMENSION_WEEK = 'ga:week';
    public const DIMENSION_YEAR = 'ga:year';

    // D2. Campaign
    public const DIMENSION_AD_CONTENT = 'ga:adContent';
    public const DIMENSION_AD_GROUP = 'ga:adGroup';
    public const DIMENSION_AD_SLOT = 'ga:adSlot';
    public const DIMENSION_AD_SLOT_POSITION = 'ga:adSlotPosition';
    public const DIMENSION_CAMPAIGN = 'ga:campaign';
    public const DIMENSION_KEYWORD = 'ga:keyword';
    public const DIMENSION_MEDIUM = 'ga:medium';
    public const DIMENSION_REFERRAL_PATH = 'ga:referralPath';
    public const DIMENSION_SOURCE = 'ga:source';

    // D3. Content
    public const DIMENSION_EXIT_PAGE_PATH = 'ga:exitPagePath';
    public const DIMENSION_LANDING_PAGE_PATH = 'ga:landingPagePath';
    public const DIMENSION_PAGE_PATH = 'ga:pagePath';
    public const DIMENSION_PAGE_TITLE = 'ga:pageTitle';
    public const DIMENSION_SECOND_PAGE_PATH = 'ga:secondPagePath';

    // D4. Ecommerce
    public const DIMENSION_AFFILIATION = 'ga:affiliation';
    public const DIMENSION_DAYS_TO_TRANSACTION = 'ga:daysToTransaction';
    public const DIMENSION_PRODUCT_CATEGORY = 'ga:productCategory';
    public const DIMENSION_PRODUCT_NAME = 'ga:productName';
    public const DIMENSION_PRODUCT_SKU = 'ga:productSku';
    public const DIMENSION_TRANSACTION_ID = 'ga:transactionId';
    public const DIMENSION_VISITS_TO_TRANSACTION = 'ga:visitsToTransaction';

    // D5. Internal Search
    public const DIMENSION_SEARCH_CATEGORY = 'ga:searchCategory';
    public const DIMENSION_SEARCH_DESTINATION_PAGE = 'ga:searchDestinationPage';
    public const DIMENSION_SEARCH_KEYWORD = 'ga:searchKeyword';
    public const DIMENSION_SEARCH_KEYWORD_REFINEMENT = 'ga:searchKeywordRefinement';
    public const DIMENSION_SEARCH_START_PAGE = 'ga:searchStartPage';
    public const DIMENSION_SEARCH_USED = 'ga:searchUsed';

    // D6. Navigation
    public const DIMENSION_NEXT_PAGE_PATH = 'ga:nextPagePath';
    public const DIMENSION_PREV_PAGE_PATH= 'ga:previousPagePath';

    // D7. Events
    public const DIMENSION_EVENT_CATEGORY = 'ga:eventCategory';
    public const DIMENSION_EVENT_ACTION = 'ga:eventAction';
    public const DIMENSION_EVENT_LABEL = 'ga:eventLabel';

    // D8. Custon Variables
    public const DIMENSION_CUSTOM_VAR_NAME_1 = 'ga:customVarName1';
    public const DIMENSION_CUSTOM_VAR_NAME_2 = 'ga:customVarName2';
    public const DIMENSION_CUSTOM_VAR_NAME_3 = 'ga:customVarName3';
    public const DIMENSION_CUSTOM_VAR_NAME_4 = 'ga:customVarName4';
    public const DIMENSION_CUSTOM_VAR_NAME_5 = 'ga:customVarName5';
    public const DIMENSION_CUSTOM_VAR_VALUE_1 = 'ga:customVarValue1';
    public const DIMENSION_CUSTOM_VAR_VALUE_2 = 'ga:customVarValue2';
    public const DIMENSION_CUSTOM_VAR_VALUE_3 = 'ga:customVarValue3';
    public const DIMENSION_CUSTOM_VAR_VALUE_4 = 'ga:customVarValue4';
    public const DIMENSION_CUSTOM_VAR_VALUE_5 = 'ga:customVarValue5';

    // M1. Visitor
    public const METRIC_BOUNCES = 'ga:bounces';
    public const METRIC_ENTRANCES = 'ga:entrances';
    public const METRIC_EXITS = 'ga:exits';
    public const METRIC_NEW_VISITS = 'ga:newVisits';
    public const METRIC_PAGEVIEWS = 'ga:pageviews';
    public const METRIC_TIME_ON_PAGE = 'ga:timeOnPage';
    public const METRIC_TIME_ON_SITE = 'ga:timeOnSite';
    public const METRIC_VISITORS = 'ga:visitors';
    public const METRIC_VISITS = 'ga:visits';

    // M2. Campaign
    public const METRIC_AD_CLICKS = 'ga:adClicks';
    public const METRIC_AD_COST = 'ga:adCost';
    public const METRIC_CPC = 'ga:CPC';
    public const METRIC_CPM = 'ga:CPM';
    public const METRIC_CTR = 'ga:CTR';
    public const METRIC_IMPRESSIONS = 'ga:impressions';

    // M3. Content
    public const METRIC_UNIQUE_PAGEVIEWS = 'ga:uniquePageviews';

    // M4. Ecommerce
    public const METRIC_ITEM_REVENUE = 'ga:itemRevenue';
    public const METRIC_ITEM_QUANTITY = 'ga:itemQuantity';
    public const METRIC_TRANSACTIONS = 'ga:transactions';
    public const METRIC_TRANSACTION_REVENUE = 'ga:transactionRevenue';
    public const METRIC_TRANSACTION_SHIPPING = 'ga:transactionShipping';
    public const METRIC_TRANSACTION_TAX = 'ga:transactionTax';
    public const METRIC_UNIQUE_PURCHASES = 'ga:uniquePurchases';

    // M5. Internal Search
    public const METRIC_SEARCH_DEPTH = 'ga:searchDepth';
    public const METRIC_SEARCH_DURATION = 'ga:searchDuration';
    public const METRIC_SEARCH_EXITS = 'ga:searchExits';
    public const METRIC_SEARCH_REFINEMENTS = 'ga:searchRefinements';
    public const METRIC_SEARCH_UNIQUES = 'ga:searchUniques';
    public const METRIC_SEARCH_VISIT = 'ga:searchVisits';

    // M6. Goals
    public const METRIC_GOAL_COMPLETIONS_ALL = 'ga:goalCompletionsAll';
    public const METRIC_GOAL_STARTS_ALL = 'ga:goalStartsAll';
    public const METRIC_GOAL_VALUE_ALL = 'ga:goalValueAll';
    // TODO goals 1-20
    public const METRIC_GOAL_1_COMPLETION = 'ga:goal1Completions';
    public const METRIC_GOAL_1_STARTS = 'ga:goal1Starts';
    public const METRIC_GOAL_1_VALUE = 'ga:goal1Value';

    // M7. Events
    public const METRIC_TOTAL_EVENTS = 'ga:totalEvents';
    public const METRIC_UNIQUE_EVENTS = 'ga:uniqueEvents';
    public const METRIC_EVENT_VALUE = 'ga:eventValue';

    // suported filter operators
    public const EQUALS = "==";
    public const EQUALS_NOT = "!=";
    public const GREATER = ">";
    public const LESS = ">";
    public const GREATER_EQUAL = ">=";
    public const LESS_EQUAL = "<=";
    public const CONTAINS = "=@";
    public const CONTAINS_NOT ="!@";
    public const REGULAR ="=~";
    public const REGULAR_NOT ="!~";

    /**
     * @var string
     */
    protected $_profileId;
    /**
     * @var array
     */
    protected $_dimensions = [];
    /**
     * @var array
     */
    protected $_metrics = [];
    /**
     * @var array
     */
    protected $_sort = [];
    /**
     * @var array
     */
    protected $_filters = [];

    /**
     * @param string $id
     * @return Zend_Gdata_Analytics_DataQuery
     */
    public function setProfileId($id)
    {
        $this->_profileId = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getProfileId()
    {
        return $this->_profileId;
    }

    /**
     * @param string $dimension
     * @return Zend_Gdata_Analytics_DataQuery
     */
    public function addDimension($dimension)
    {
        $this->_dimensions[$dimension] = true;
        return $this;
    }

    /**
     * @param string $metric
     * @return Zend_Gdata_Analytics_DataQuery
     */
    public function addMetric($metric)
    {
        $this->_metrics[$metric] = true;
        return $this;
    }

    /**
     * @return array
     */
    public function getDimensions()
    {
        return $this->_dimensions;
    }

    /**
     * @return array
     */
    public function getMetrics()
    {
        return $this->_metrics;
    }

    /**
     * @param string $dimension
     * @return Zend_Gdata_Analytics_DataQuery
     */
    public function removeDimension($dimension)
    {
        unset($this->_dimensions[$dimension]);
        return $this;
    }
    /**
     * @param string $metric
     * @return Zend_Gdata_Analytics_DataQuery
     */
    public function removeMetric($metric)
    {
        unset($this->_metrics[$metric]);
        return $this;
    }
    /**
     * @param string $value
     * @return Zend_Gdata_Analytics_DataQuery
     */
    public function setStartDate($date)
    {
        $this->setParam("start-date", $date);
        return $this;
    }
    /**
     * @param string $value
     * @return Zend_Gdata_Analytics_DataQuery
     */
    public function setEndDate($date)
    {
        $this->setParam("end-date", $date);
        return $this;
    }

    /**
     * @param string $filter
     * @return Zend_Gdata_Analytics_DataQuery
     */
    public function addFilter($filter)
    {
        $this->_filters[] = [$filter, true];
        return $this;
    }

    /**
     * @param string $filter
     * @return Zend_Gdata_Analytics_DataQuery
     */
    public function addOrFilter($filter)
    {
        $this->_filters[] = [$filter, false];
        return $this;
    }

    /**
     * @param string $sort
     * @param bool $descending [optional]
     * @return Zend_Gdata_Analytics_DataQuery
     */
    public function addSort($sort, $descending=false)
    {
        // add to sort storage
        $this->_sort[] = ($descending?'-':'').$sort;
        return $this;
    }

    /**
     * @return Zend_Gdata_Analytics_DataQuery
     */
    public function clearSort()
    {
        $this->_sort = [];
        return $this;
    }

    /**
     * @param string $segment
     * @return Zend_Gdata_Analytics_DataQuery
     */
    public function setSegment($segment)
    {
        $this->setParam('segment', $segment);
        return $this;
    }

    /**
     * @return string url
     */
    public function getQueryUrl()
    {
        $uri = $this->_defaultFeedUri;
        if (isset($this->_url)) {
            $uri = $this->_url;
        }

        $dimensions = $this->getDimensions();
        if (!empty($dimensions)) {
            $this->setParam('dimensions', implode(",", array_keys($dimensions)));
        }

        $metrics = $this->getMetrics();
        if (!empty($metrics)) {
            $this->setParam('metrics', implode(",", array_keys($metrics)));
        }

        // profile id (ga:tableId)
        if ($this->getProfileId() != null) {
            $this->setParam('ids', 'ga:'.ltrim($this->getProfileId(), "ga:"));
        }

        // sorting
        if ($this->_sort) {
            $this->setParam('sort', implode(",", $this->_sort));
        }

        // filtering
        $filters = "";
        foreach ($this->_filters as $filter) {
            $filters.=($filter[1]===true?';':',').$filter[0];
        }

        if ($filters!="") {
            $this->setParam('filters', ltrim($filters, ",;"));
        }

        $uri .= $this->getQueryString();
        return $uri;
    }
}
