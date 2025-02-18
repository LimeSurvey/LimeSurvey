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
 * @package    Zend_Ldap
 * @subpackage RootDSE
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Ldap_Node_Abstract
 */
require_once 'Zend/Ldap/Node/Abstract.php';

/**
 * Zend_Ldap_Node_RootDse provides a simple data-container for the RootDSE node.
 *
 * @category   Zend
 * @package    Zend_Ldap
 * @subpackage RootDSE
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Ldap_Node_RootDse extends Zend_Ldap_Node_Abstract
{
    public const SERVER_TYPE_GENERIC         = 1;
    public const SERVER_TYPE_OPENLDAP        = 2;
    public const SERVER_TYPE_ACTIVEDIRECTORY = 3;
    public const SERVER_TYPE_EDIRECTORY      = 4;

    /**
     * Factory method to create the RootDSE.
     *
     * @param  Zend_Ldap $ldap
     * @return Zend_Ldap_Node_RootDse
     * @throws Zend_Ldap_Exception
     */
    public static function create(Zend_Ldap $ldap)
    {
        $dn = Zend_Ldap_Dn::fromString('');
        $data = $ldap->getEntry($dn, ['*', '+'], true);
        if (isset($data['domainfunctionality'])) {
            /**
             * @see Zend_Ldap_Node_RootDse_ActiveDirectory
             */
            require_once 'Zend/Ldap/Node/RootDse/ActiveDirectory.php';
            return new Zend_Ldap_Node_RootDse_ActiveDirectory($dn, $data);
        } else if (isset($data['dsaname'])) {
            /**
             * @see Zend_Ldap_Node_RootDse_ActiveDirectory
             */
            require_once 'Zend/Ldap/Node/RootDse/eDirectory.php';
            return new Zend_Ldap_Node_RootDse_eDirectory($dn, $data);
        } else if (isset($data['structuralobjectclass']) &&
                $data['structuralobjectclass'][0] === 'OpenLDAProotDSE') {
            /**
             * @see Zend_Ldap_Node_RootDse_OpenLdap
             */
            require_once 'Zend/Ldap/Node/RootDse/OpenLdap.php';
            return new Zend_Ldap_Node_RootDse_OpenLdap($dn, $data);
        } else {
            return new self($dn, $data);
        }
    }

    /**
     * Constructor.
     *
     * Constructor is protected to enforce the use of factory methods.
     *
     * @param  Zend_Ldap_Dn $dn
     * @param  array        $data
     */
    protected function __construct(Zend_Ldap_Dn $dn, array $data)
    {
        parent::__construct($dn, $data, true);
    }

    /**
     * Gets the namingContexts.
     *
     * @return array
     */
    public function getNamingContexts()
    {
        return $this->getAttribute('namingContexts', null);
    }

    /**
     * Gets the subschemaSubentry.
     *
     * @return string|null
     */
    public function getSubschemaSubentry()
    {
        return $this->getAttribute('subschemaSubentry', 0);
    }

    /**
     * Determines if the version is supported
     *
     * @param  string|int|array $versions version(s) to check
     * @return boolean
     */
    public function supportsVersion($versions)
    {
        return $this->attributeHasValue('supportedLDAPVersion', $versions);
    }

    /**
     * Determines if the sasl mechanism is supported
     *
     * @param  string|array $mechlist SASL mechanisms to check
     * @return boolean
     */
    public function supportsSaslMechanism($mechlist)
    {
        return $this->attributeHasValue('supportedSASLMechanisms', $mechlist);
    }

    /**
     * Gets the server type
     *
     * @return int
     */
    public function getServerType()
    {
        return self::SERVER_TYPE_GENERIC;
    }

    /**
     * Returns the schema DN
     *
     * @return Zend_Ldap_Dn
     */
    public function getSchemaDn()
    {
        $schemaDn = $this->getSubschemaSubentry();
        /**
         * @see Zend_Ldap_Dn
         */
        require_once 'Zend/Ldap/Dn.php';
        return Zend_Ldap_Dn::fromString($schemaDn);
    }
}
