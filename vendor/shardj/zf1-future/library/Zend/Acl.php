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
 * @package    Zend_Acl
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * @see Zend_Acl_Resource_Interface
 */
require_once 'Zend/Acl/Resource/Interface.php';


/**
 * @see Zend_Acl_Role_Registry
 */
require_once 'Zend/Acl/Role/Registry.php';


/**
 * @see Zend_Acl_Assert_Interface
 */
require_once 'Zend/Acl/Assert/Interface.php';


/**
 * @see Zend_Acl_Role
 */
require_once 'Zend/Acl/Role.php';


/**
 * @see Zend_Acl_Resource
 */
require_once 'Zend/Acl/Resource.php';


/**
 * @category   Zend
 * @package    Zend_Acl
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Acl
{
    /**
     * Rule type: allow
     */
    public const TYPE_ALLOW = 'TYPE_ALLOW';

    /**
     * Rule type: deny
     */
    public const TYPE_DENY  = 'TYPE_DENY';

    /**
     * Rule operation: add
     */
    public const OP_ADD = 'OP_ADD';

    /**
     * Rule operation: remove
     */
    public const OP_REMOVE = 'OP_REMOVE';

    /**
     * Role registry
     *
     * @var Zend_Acl_Role_Registry
     */
    protected $_roleRegistry = null;

    /**
     * Resource tree
     *
     * @var array
     */
    protected $_resources = [];

    /**
     * @var Zend_Acl_Role_Interface
     */
    protected $_isAllowedRole     = null;

    /**
     * @var Zend_Acl_Resource_Interface
     */
    protected $_isAllowedResource = null;

    /**
     * @var String
     */
    protected $_isAllowedPrivilege = null;

    /**
     * ACL rules; whitelist (deny everything to all) by default
     *
     * @var array
     */
    protected $_rules = [
        'allResources' => [
            'allRoles' => [
                'allPrivileges' => [
                    'type'   => self::TYPE_DENY,
                    'assert' => null
                    ],
                'byPrivilegeId' => []
                ],
            'byRoleId' => []
            ],
        'byResourceId' => []
        ];

    /**
     * Adds a Role having an identifier unique to the registry
     *
     * The $parents parameter may be a reference to, or the string identifier for,
     * a Role existing in the registry, or $parents may be passed as an array of
     * these - mixing string identifiers and objects is ok - to indicate the Roles
     * from which the newly added Role will directly inherit.
     *
     * In order to resolve potential ambiguities with conflicting rules inherited
     * from different parents, the most recently added parent takes precedence over
     * parents that were previously added. In other words, the first parent added
     * will have the least priority, and the last parent added will have the
     * highest priority.
     *
     * @param  Zend_Acl_Role_Interface|string       $role
     * @param  Zend_Acl_Role_Interface|string|array $parents
     * @uses   Zend_Acl_Role_Registry::add()
     * @return $this
     */
    public function addRole($role, $parents = null)
    {
        if (is_string($role)) {
            $role = new Zend_Acl_Role($role);
        }

        if (!$role instanceof Zend_Acl_Role_Interface) {
            require_once 'Zend/Acl/Exception.php';
            throw new Zend_Acl_Exception('addRole() expects $role to be of type Zend_Acl_Role_Interface');
        }


        $this->_getRoleRegistry()->add($role, $parents);

        return $this;
    }

    /**
     * Returns the identified Role
     *
     * The $role parameter can either be a Role or Role identifier.
     *
     * @param  Zend_Acl_Role_Interface|string $role
     * @uses   Zend_Acl_Role_Registry::get()
     * @return Zend_Acl_Role_Interface
     */
    public function getRole($role)
    {
        return $this->_getRoleRegistry()->get($role);
    }

    /**
     * Returns true if and only if the Role exists in the registry
     *
     * The $role parameter can either be a Role or a Role identifier.
     *
     * @param  Zend_Acl_Role_Interface|string $role
     * @uses   Zend_Acl_Role_Registry::has()
     * @return boolean
     */
    public function hasRole($role)
    {
        return $this->_getRoleRegistry()->has($role);
    }

    /**
     * Returns true if and only if $role inherits from $inherit
     *
     * Both parameters may be either a Role or a Role identifier. If
     * $onlyParents is true, then $role must inherit directly from
     * $inherit in order to return true. By default, this method looks
     * through the entire inheritance DAG to determine whether $role
     * inherits from $inherit through its ancestor Roles.
     *
     * @param  Zend_Acl_Role_Interface|string $role
     * @param  Zend_Acl_Role_Interface|string $inherit
     * @param  boolean                        $onlyParents
     * @uses   Zend_Acl_Role_Registry::inherits()
     * @return boolean
     */
    public function inheritsRole($role, $inherit, $onlyParents = false)
    {
        return $this->_getRoleRegistry()->inherits($role, $inherit, $onlyParents);
    }

    /**
     * Removes the Role from the registry
     *
     * The $role parameter can either be a Role or a Role identifier.
     *
     * @param  Zend_Acl_Role_Interface|string $role
     * @uses   Zend_Acl_Role_Registry::remove()
     * @return $this
     */
    public function removeRole($role)
    {
        $this->_getRoleRegistry()->remove($role);

        if ($role instanceof Zend_Acl_Role_Interface) {
            $roleId = $role->getRoleId();
        } else {
            $roleId = $role;
        }

        foreach ($this->_rules['allResources']['byRoleId'] as $roleIdCurrent => $rules) {
            if ($roleId === $roleIdCurrent) {
                unset($this->_rules['allResources']['byRoleId'][$roleIdCurrent]);
            }
        }
        foreach ($this->_rules['byResourceId'] as $resourceIdCurrent => $visitor) {
            if (array_key_exists('byRoleId', $visitor)) {
                foreach ($visitor['byRoleId'] as $roleIdCurrent => $rules) {
                    if ($roleId === $roleIdCurrent) {
                        unset($this->_rules['byResourceId'][$resourceIdCurrent]['byRoleId'][$roleIdCurrent]);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Removes all Roles from the registry
     *
     * @uses   Zend_Acl_Role_Registry::removeAll()
     * @return $this
     */
    public function removeRoleAll()
    {
        $this->_getRoleRegistry()->removeAll();

        foreach ($this->_rules['allResources']['byRoleId'] as $roleIdCurrent => $rules) {
            unset($this->_rules['allResources']['byRoleId'][$roleIdCurrent]);
        }
        foreach ($this->_rules['byResourceId'] as $resourceIdCurrent => $visitor) {
            foreach ($visitor['byRoleId'] as $roleIdCurrent => $rules) {
                unset($this->_rules['byResourceId'][$resourceIdCurrent]['byRoleId'][$roleIdCurrent]);
            }
        }

        return $this;
    }

    /**
     * Adds a Resource having an identifier unique to the ACL
     *
     * The $parent parameter may be a reference to, or the string identifier for,
     * the existing Resource from which the newly added Resource will inherit.
     *
     * @param  Zend_Acl_Resource_Interface|string $resource
     * @param  Zend_Acl_Resource_Interface|string $parent
     * @throws Zend_Acl_Exception
     * @return $this
     */
    public function addResource($resource, $parent = null)
    {
        if (is_string($resource)) {
            $resource = new Zend_Acl_Resource($resource);
        }

        if (!$resource instanceof Zend_Acl_Resource_Interface) {
            require_once 'Zend/Acl/Exception.php';
            throw new Zend_Acl_Exception('addResource() expects $resource to be of type Zend_Acl_Resource_Interface');
        }

        $resourceId = $resource->getResourceId();

        if ($this->has($resourceId)) {
            require_once 'Zend/Acl/Exception.php';
            throw new Zend_Acl_Exception("Resource id '$resourceId' already exists in the ACL");
        }

        $resourceParent = null;

        if (null !== $parent) {
            try {
                if ($parent instanceof Zend_Acl_Resource_Interface) {
                    $resourceParentId = $parent->getResourceId();
                } else {
                    $resourceParentId = $parent;
                }
                $resourceParent = $this->get($resourceParentId);
            } catch (Zend_Acl_Exception $e) {
                require_once 'Zend/Acl/Exception.php';
                throw new Zend_Acl_Exception("Parent Resource id '$resourceParentId' does not exist", 0, $e);
            }
            $this->_resources[$resourceParentId]['children'][$resourceId] = $resource;
        }

        $this->_resources[$resourceId] = [
            'instance' => $resource,
            'parent'   => $resourceParent,
            'children' => []
            ];

        return $this;
    }

    /**
     * Adds a Resource having an identifier unique to the ACL
     *
     * The $parent parameter may be a reference to, or the string identifier for,
     * the existing Resource from which the newly added Resource will inherit.
     *
     * @deprecated in version 1.9.1 and will be available till 2.0.  New code
     *             should use addResource() instead.
     *
     * @param  Zend_Acl_Resource_Interface        $resource
     * @param  Zend_Acl_Resource_Interface|string $parent
     * @throws Zend_Acl_Exception
     * @return $this
     */
    public function add(Zend_Acl_Resource_Interface $resource, $parent = null)
    {
        return $this->addResource($resource, $parent);
    }

    /**
     * Returns the identified Resource
     *
     * The $resource parameter can either be a Resource or a Resource identifier.
     *
     * @param  Zend_Acl_Resource_Interface|string $resource
     * @throws Zend_Acl_Exception
     * @return Zend_Acl_Resource_Interface
     */
    public function get($resource)
    {
        if ($resource instanceof Zend_Acl_Resource_Interface) {
            $resourceId = $resource->getResourceId();
        } else {
            $resourceId = (string) $resource;
        }

        if (!$this->has($resource)) {
            require_once 'Zend/Acl/Exception.php';
            throw new Zend_Acl_Exception("Resource '$resourceId' not found");
        }

        return $this->_resources[$resourceId]['instance'];
    }

    /**
     * Returns true if and only if the Resource exists in the ACL
     *
     * The $resource parameter can either be a Resource or a Resource identifier.
     *
     * @param  Zend_Acl_Resource_Interface|string $resource
     * @return boolean
     */
    public function has($resource)
    {
        if ($resource instanceof Zend_Acl_Resource_Interface) {
            $resourceId = $resource->getResourceId();
        } else {
            $resourceId = (string) $resource;
        }

        return isset($this->_resources[$resourceId]);
    }

    /**
     * Returns true if and only if $resource inherits from $inherit
     *
     * Both parameters may be either a Resource or a Resource identifier. If
     * $onlyParent is true, then $resource must inherit directly from
     * $inherit in order to return true. By default, this method looks
     * through the entire inheritance tree to determine whether $resource
     * inherits from $inherit through its ancestor Resources.
     *
     * @param  Zend_Acl_Resource_Interface|string $resource
     * @param  Zend_Acl_Resource_Interface|string $inherit
     * @param  boolean                            $onlyParent
     * @throws Zend_Acl_Resource_Registry_Exception
     * @return boolean
     */
    public function inherits($resource, $inherit, $onlyParent = false)
    {
        try {
            $resourceId     = $this->get($resource)->getResourceId();
            $inheritId = $this->get($inherit)->getResourceId();
        } catch (Zend_Acl_Exception $e) {
            require_once 'Zend/Acl/Exception.php';
            throw new Zend_Acl_Exception($e->getMessage(), $e->getCode(), $e);
        }

        if (null !== $this->_resources[$resourceId]['parent']) {
            $parentId = $this->_resources[$resourceId]['parent']->getResourceId();
            if ($inheritId === $parentId) {
                return true;
            } else if ($onlyParent) {
                return false;
            }
        } else {
            return false;
        }

        while (null !== $this->_resources[$parentId]['parent']) {
            $parentId = $this->_resources[$parentId]['parent']->getResourceId();
            if ($inheritId === $parentId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Removes a Resource and all of its children
     *
     * The $resource parameter can either be a Resource or a Resource identifier.
     *
     * @param  Zend_Acl_Resource_Interface|string $resource
     * @throws Zend_Acl_Exception
     * @return $this
     */
    public function remove($resource)
    {
        try {
            $resourceId = $this->get($resource)->getResourceId();
        } catch (Zend_Acl_Exception $e) {
            require_once 'Zend/Acl/Exception.php';
            throw new Zend_Acl_Exception($e->getMessage(), $e->getCode(), $e);
        }

        $resourcesRemoved = [$resourceId];
        if (null !== ($resourceParent = $this->_resources[$resourceId]['parent'])) {
            unset($this->_resources[$resourceParent->getResourceId()]['children'][$resourceId]);
        }
        foreach ($this->_resources[$resourceId]['children'] as $childId => $child) {
            $this->remove($childId);
            $resourcesRemoved[] = $childId;
        }

        foreach ($resourcesRemoved as $resourceIdRemoved) {
            foreach ($this->_rules['byResourceId'] as $resourceIdCurrent => $rules) {
                if ($resourceIdRemoved === $resourceIdCurrent) {
                    unset($this->_rules['byResourceId'][$resourceIdCurrent]);
                }
            }
        }

        unset($this->_resources[$resourceId]);

        return $this;
    }

    /**
     * Removes all Resources
     *
     * @return $this
     */
    public function removeAll()
    {
        foreach ($this->_resources as $resourceId => $resource) {
            unset($this->_rules['byResourceId'][$resourceId]);
        }

        $this->_resources = [];

        return $this;
    }

    /**
     * Adds an "allow" rule to the ACL
     *
     * @param  Zend_Acl_Role_Interface|string|array     $roles
     * @param  Zend_Acl_Resource_Interface|string|array $resources
     * @param  string|array                             $privileges
     * @param  Zend_Acl_Assert_Interface                $assert
     * @uses   Zend_Acl::setRule()
     * @return $this
     */
    public function allow($roles = null, $resources = null, $privileges = null, ?Zend_Acl_Assert_Interface $assert = null)
    {
        return $this->setRule(self::OP_ADD, self::TYPE_ALLOW, $roles, $resources, $privileges, $assert);
    }

    /**
     * Adds a "deny" rule to the ACL
     *
     * @param  Zend_Acl_Role_Interface|string|array     $roles
     * @param  Zend_Acl_Resource_Interface|string|array $resources
     * @param  string|array                             $privileges
     * @param  Zend_Acl_Assert_Interface                $assert
     * @uses   Zend_Acl::setRule()
     * @return $this
     */
    public function deny($roles = null, $resources = null, $privileges = null, ?Zend_Acl_Assert_Interface $assert = null)
    {
        return $this->setRule(self::OP_ADD, self::TYPE_DENY, $roles, $resources, $privileges, $assert);
    }

    /**
     * Removes "allow" permissions from the ACL
     *
     * @param  Zend_Acl_Role_Interface|string|array     $roles
     * @param  Zend_Acl_Resource_Interface|string|array $resources
     * @param  string|array                             $privileges
     * @uses   Zend_Acl::setRule()
     * @return $this
     */
    public function removeAllow($roles = null, $resources = null, $privileges = null)
    {
        return $this->setRule(self::OP_REMOVE, self::TYPE_ALLOW, $roles, $resources, $privileges);
    }

    /**
     * Removes "deny" restrictions from the ACL
     *
     * @param  Zend_Acl_Role_Interface|string|array     $roles
     * @param  Zend_Acl_Resource_Interface|string|array $resources
     * @param  string|array                             $privileges
     * @uses   Zend_Acl::setRule()
     * @return $this
     */
    public function removeDeny($roles = null, $resources = null, $privileges = null)
    {
        return $this->setRule(self::OP_REMOVE, self::TYPE_DENY, $roles, $resources, $privileges);
    }

    /**
     * Performs operations on ACL rules
     *
     * The $operation parameter may be either OP_ADD or OP_REMOVE, depending on whether the
     * user wants to add or remove a rule, respectively:
     *
     * OP_ADD specifics:
     *
     *      A rule is added that would allow one or more Roles access to [certain $privileges
     *      upon] the specified Resource(s).
     *
     * OP_REMOVE specifics:
     *
     *      The rule is removed only in the context of the given Roles, Resources, and privileges.
     *      Existing rules to which the remove operation does not apply would remain in the
     *      ACL.
     *
     * The $type parameter may be either TYPE_ALLOW or TYPE_DENY, depending on whether the
     * rule is intended to allow or deny permission, respectively.
     *
     * The $roles and $resources parameters may be references to, or the string identifiers for,
     * existing Resources/Roles, or they may be passed as arrays of these - mixing string identifiers
     * and objects is ok - to indicate the Resources and Roles to which the rule applies. If either
     * $roles or $resources is null, then the rule applies to all Roles or all Resources, respectively.
     * Both may be null in order to work with the default rule of the ACL.
     *
     * The $privileges parameter may be used to further specify that the rule applies only
     * to certain privileges upon the Resource(s) in question. This may be specified to be a single
     * privilege with a string, and multiple privileges may be specified as an array of strings.
     *
     * If $assert is provided, then its assert() method must return true in order for
     * the rule to apply. If $assert is provided with $roles, $resources, and $privileges all
     * equal to null, then a rule having a type of:
     *
     *      TYPE_ALLOW will imply a type of TYPE_DENY, and
     *
     *      TYPE_DENY will imply a type of TYPE_ALLOW
     *
     * when the rule's assertion fails. This is because the ACL needs to provide expected
     * behavior when an assertion upon the default ACL rule fails.
     *
     * @param  string                                   $operation
     * @param  string                                   $type
     * @param  Zend_Acl_Role_Interface|string|array     $roles
     * @param  Zend_Acl_Resource_Interface|string|array $resources
     * @param  string|array                             $privileges
     * @param  Zend_Acl_Assert_Interface                $assert
     * @throws Zend_Acl_Exception
     * @uses   Zend_Acl_Role_Registry::get()
     * @uses   Zend_Acl::get()
     * @return $this
     */
    public function setRule($operation, $type, $roles = null, $resources = null, $privileges = null,
                            ?Zend_Acl_Assert_Interface $assert = null)
    {
        // ensure that the rule type is valid; normalize input to uppercase
        $type = strtoupper($type);
        if (self::TYPE_ALLOW !== $type && self::TYPE_DENY !== $type) {
            require_once 'Zend/Acl/Exception.php';
            throw new Zend_Acl_Exception("Unsupported rule type; must be either '" . self::TYPE_ALLOW . "' or '"
                                       . self::TYPE_DENY . "'");
        }

        // ensure that all specified Roles exist; normalize input to array of Role objects or null
        if (!is_array($roles)) {
            $roles = [$roles];
        } else if (0 === count($roles)) {
            $roles = [null];
        }
        $rolesTemp = $roles;
        $roles = [];
        foreach ($rolesTemp as $role) {
            if (null !== $role) {
                $roles[] = $this->_getRoleRegistry()->get($role);
            } else {
                $roles[] = null;
            }
        }
        unset($rolesTemp);

        // ensure that all specified Resources exist; normalize input to array of Resource objects or null
        if ($resources !== null) {
            if (!is_array($resources)) {
                $resources = [$resources];
            } else if (0 === count($resources)) {
                $resources = [null];
            }
            $resourcesTemp = $resources;
            $resources = [];
            foreach ($resourcesTemp as $resource) {
                if (null !== $resource) {
                    $resources[] = $this->get($resource);
                } else {
                    $resources[] = null;
                }
            }
            unset($resourcesTemp, $resource);
        } else {
            $allResources = []; // this might be used later if resource iteration is required
            foreach ($this->_resources as $rTarget) {
                $allResources[] = $rTarget['instance'];
            }
            unset($rTarget);
        }

        // normalize privileges to array
        if (null === $privileges) {
            $privileges = [];
        } else if (!is_array($privileges)) {
            $privileges = [$privileges];
        }

        switch ($operation) {

            // add to the rules
            case self::OP_ADD:
                if ($resources !== null) {
                    // this block will iterate the provided resources
                    foreach ($resources as $resource) {
                        foreach ($roles as $role) {
                            $rules =& $this->_getRules($resource, $role, true);
                            if (0 === count($privileges)) {
                                $rules['allPrivileges']['type']   = $type;
                                $rules['allPrivileges']['assert'] = $assert;
                                if (!isset($rules['byPrivilegeId'])) {
                                    $rules['byPrivilegeId'] = [];
                                }
                            } else {
                                foreach ($privileges as $privilege) {
                                    $rules['byPrivilegeId'][$privilege]['type']   = $type;
                                    $rules['byPrivilegeId'][$privilege]['assert'] = $assert;
                                }
                            }
                        }
                    }
                } else {
                    // this block will apply to all resources in a global rule
                    foreach ($roles as $role) {
                        $rules =& $this->_getRules(null, $role, true);
                        if (0 === count($privileges)) {
                            $rules['allPrivileges']['type']   = $type;
                            $rules['allPrivileges']['assert'] = $assert;
                        } else {
                            foreach ($privileges as $privilege) {
                                $rules['byPrivilegeId'][$privilege]['type']   = $type;
                                $rules['byPrivilegeId'][$privilege]['assert'] = $assert;
                            }
                        }
                    }
                }
                break;

            // remove from the rules
            case self::OP_REMOVE:
                if ($resources !== null) {
                    // this block will iterate the provided resources
                    foreach ($resources as $resource) {
                        foreach ($roles as $role) {
                            $rules =& $this->_getRules($resource, $role);
                            if (null === $rules) {
                                continue;
                            }
                            if (0 === count($privileges)) {
                                if (null === $resource && null === $role) {
                                    if ($type === $rules['allPrivileges']['type']) {
                                        $rules = [
                                            'allPrivileges' => [
                                                'type'   => self::TYPE_DENY,
                                                'assert' => null
                                                ],
                                            'byPrivilegeId' => []
                                            ];
                                    }
                                    continue;
                                }

                                if (isset($rules['allPrivileges']['type']) &&
                                    $type === $rules['allPrivileges']['type'])
                                {
                                    unset($rules['allPrivileges']);
                                }
                            } else {
                                foreach ($privileges as $privilege) {
                                    if (isset($rules['byPrivilegeId'][$privilege]) &&
                                        $type === $rules['byPrivilegeId'][$privilege]['type'])
                                    {
                                        unset($rules['byPrivilegeId'][$privilege]);
                                    }
                                }
                            }
                        }
                    }
                } else {
                    // this block will apply to all resources in a global rule
                    foreach ($roles as $role) {
                        /**
                         * since null (all resources) was passed to this setRule() call, we need
                         * clean up all the rules for the global allResources, as well as the indivually
                         * set resources (per privilege as well)
                         */
                        foreach (array_merge([null], $allResources) as $resource) {
                            $rules =& $this->_getRules($resource, $role, true);
                            if (null === $rules) {
                                continue;
                            }
                            if (0 === count($privileges)) {
                                if (null === $role) {
                                    if ($type === $rules['allPrivileges']['type']) {
                                        $rules = [
                                            'allPrivileges' => [
                                                'type'   => self::TYPE_DENY,
                                                'assert' => null
                                                ],
                                            'byPrivilegeId' => []
                                            ];
                                    }
                                    continue;
                                }

                                if (isset($rules['allPrivileges']['type']) && $type === $rules['allPrivileges']['type']) {
                                    unset($rules['allPrivileges']);
                                }
                            } else {
                                foreach ($privileges as $privilege) {
                                    if (isset($rules['byPrivilegeId'][$privilege]) &&
                                        $type === $rules['byPrivilegeId'][$privilege]['type'])
                                    {
                                        unset($rules['byPrivilegeId'][$privilege]);
                                    }
                                }
                            }
                        }
                    }
                }
                break;

            default:
                require_once 'Zend/Acl/Exception.php';
                throw new Zend_Acl_Exception("Unsupported operation; must be either '" . self::OP_ADD . "' or '"
                                           . self::OP_REMOVE . "'");
        }

        return $this;
    }

    /**
     * Returns true if and only if the Role has access to the Resource
     *
     * The $role and $resource parameters may be references to, or the string identifiers for,
     * an existing Resource and Role combination.
     *
     * If either $role or $resource is null, then the query applies to all Roles or all Resources,
     * respectively. Both may be null to query whether the ACL has a "blacklist" rule
     * (allow everything to all). By default, Zend_Acl creates a "whitelist" rule (deny
     * everything to all), and this method would return false unless this default has
     * been overridden (i.e., by executing $acl->allow()).
     *
     * If a $privilege is not provided, then this method returns false if and only if the
     * Role is denied access to at least one privilege upon the Resource. In other words, this
     * method returns true if and only if the Role is allowed all privileges on the Resource.
     *
     * This method checks Role inheritance using a depth-first traversal of the Role registry.
     * The highest priority parent (i.e., the parent most recently added) is checked first,
     * and its respective parents are checked similarly before the lower-priority parents of
     * the Role are checked.
     *
     * @param  Zend_Acl_Role_Interface|string     $role
     * @param  Zend_Acl_Resource_Interface|string $resource
     * @param  string                             $privilege
     * @uses   Zend_Acl::get()
     * @uses   Zend_Acl_Role_Registry::get()
     * @return boolean
     */
    public function isAllowed($role = null, $resource = null, $privilege = null)
    {
        // reset role & resource to null
        $this->_isAllowedRole = null;
        $this->_isAllowedResource = null;
        $this->_isAllowedPrivilege = null;

        if (null !== $role) {
            // keep track of originally called role
            $this->_isAllowedRole = $role;
            $role = $this->_getRoleRegistry()->get($role);
            if (!$this->_isAllowedRole instanceof Zend_Acl_Role_Interface) {
                $this->_isAllowedRole = $role;
            }
        }

        if (null !== $resource) {
            // keep track of originally called resource
            $this->_isAllowedResource = $resource;
            $resource = $this->get($resource);
            if (!$this->_isAllowedResource instanceof Zend_Acl_Resource_Interface) {
                $this->_isAllowedResource = $resource;
            }
        }

        if (null === $privilege) {
            // query on all privileges
            do {
                // depth-first search on $role if it is not 'allRoles' pseudo-parent
                if (null !== $role && null !== ($result = $this->_roleDFSAllPrivileges($role, $resource, $privilege))) {
                    return $result;
                }

                // look for rule on 'allRoles' psuedo-parent
                if (null !== ($rules = $this->_getRules($resource, null))) {
                    foreach ($rules['byPrivilegeId'] as $privilege => $rule) {
                        if (self::TYPE_DENY === ($ruleTypeOnePrivilege = $this->_getRuleType($resource, null, $privilege))) {
                            return false;
                        }
                    }
                    if (null !== ($ruleTypeAllPrivileges = $this->_getRuleType($resource, null, null))) {
                        return self::TYPE_ALLOW === $ruleTypeAllPrivileges;
                    }
                }

                // try next Resource
                $resource = $this->_resources[$resource->getResourceId()]['parent'];

            } while (true); // loop terminates at 'allResources' pseudo-parent
        } else {
            $this->_isAllowedPrivilege = $privilege;
            // query on one privilege
            do {
                // depth-first search on $role if it is not 'allRoles' pseudo-parent
                if (null !== $role && null !== ($result = $this->_roleDFSOnePrivilege($role, $resource, $privilege))) {
                    return $result;
                }

                // look for rule on 'allRoles' pseudo-parent
                if (null !== ($ruleType = $this->_getRuleType($resource, null, $privilege))) {
                    return self::TYPE_ALLOW === $ruleType;
                } else if (null !== ($ruleTypeAllPrivileges = $this->_getRuleType($resource, null, null))) {
                    return self::TYPE_ALLOW === $ruleTypeAllPrivileges;
                }

                // try next Resource
                $resource = $this->_resources[$resource->getResourceId()]['parent'];

            } while (true); // loop terminates at 'allResources' pseudo-parent
        }
    }

    /**
     * Returns the Role registry for this ACL
     *
     * If no Role registry has been created yet, a new default Role registry
     * is created and returned.
     *
     * @return Zend_Acl_Role_Registry
     */
    protected function _getRoleRegistry()
    {
        if (null === $this->_roleRegistry) {
            $this->_roleRegistry = new Zend_Acl_Role_Registry();
        }
        return $this->_roleRegistry;
    }

    /**
     * Performs a depth-first search of the Role DAG, starting at $role, in order to find a rule
     * allowing/denying $role access to all privileges upon $resource
     *
     * This method returns true if a rule is found and allows access. If a rule exists and denies access,
     * then this method returns false. If no applicable rule is found, then this method returns null.
     *
     * @param  Zend_Acl_Role_Interface     $role
     * @param  Zend_Acl_Resource_Interface $resource
     * @return boolean|null
     */
    protected function _roleDFSAllPrivileges(Zend_Acl_Role_Interface $role, ?Zend_Acl_Resource_Interface $resource = null)
    {
        $dfs = [
            'visited' => [],
            'stack'   => []
            ];

        if (null !== ($result = $this->_roleDFSVisitAllPrivileges($role, $resource, $dfs))) {
            return $result;
        }

        while (null !== ($role = array_pop($dfs['stack']))) {
            if (!isset($dfs['visited'][$role->getRoleId()])) {
                if (null !== ($result = $this->_roleDFSVisitAllPrivileges($role, $resource, $dfs))) {
                    return $result;
                }
            }
        }

        return null;
    }

    /**
     * Visits an $role in order to look for a rule allowing/denying $role access to all privileges upon $resource
     *
     * This method returns true if a rule is found and allows access. If a rule exists and denies access,
     * then this method returns false. If no applicable rule is found, then this method returns null.
     *
     * This method is used by the internal depth-first search algorithm and may modify the DFS data structure.
     *
     * @param  Zend_Acl_Role_Interface     $role
     * @param  Zend_Acl_Resource_Interface $resource
     * @param  array                  $dfs
     * @return boolean|null
     * @throws Zend_Acl_Exception
     */
    protected function _roleDFSVisitAllPrivileges(Zend_Acl_Role_Interface $role, ?Zend_Acl_Resource_Interface $resource = null,
                                                 &$dfs = null)
    {
        if (null === $dfs) {
            /**
             * @see Zend_Acl_Exception
             */
            require_once 'Zend/Acl/Exception.php';
            throw new Zend_Acl_Exception('$dfs parameter may not be null');
        }

        if (null !== ($rules = $this->_getRules($resource, $role))) {
            foreach ($rules['byPrivilegeId'] as $privilege => $rule) {
                if (self::TYPE_DENY === ($ruleTypeOnePrivilege = $this->_getRuleType($resource, $role, $privilege))) {
                    return false;
                }
            }
            if (null !== ($ruleTypeAllPrivileges = $this->_getRuleType($resource, $role, null))) {
                return self::TYPE_ALLOW === $ruleTypeAllPrivileges;
            }
        }

        $dfs['visited'][$role->getRoleId()] = true;
        foreach ($this->_getRoleRegistry()->getParents($role) as $roleParentId => $roleParent) {
            $dfs['stack'][] = $roleParent;
        }

        return null;
    }

    /**
     * Performs a depth-first search of the Role DAG, starting at $role, in order to find a rule
     * allowing/denying $role access to a $privilege upon $resource
     *
     * This method returns true if a rule is found and allows access. If a rule exists and denies access,
     * then this method returns false. If no applicable rule is found, then this method returns null.
     *
     * @param  Zend_Acl_Role_Interface     $role
     * @param  Zend_Acl_Resource_Interface $resource
     * @param  string                      $privilege
     * @return boolean|null
     * @throws Zend_Acl_Exception
     */
    protected function _roleDFSOnePrivilege(Zend_Acl_Role_Interface $role, ?Zend_Acl_Resource_Interface $resource = null,
                                            $privilege = null)
    {
        if (null === $privilege) {
            /**
             * @see Zend_Acl_Exception
             */
            require_once 'Zend/Acl/Exception.php';
            throw new Zend_Acl_Exception('$privilege parameter may not be null');
        }

        $dfs = [
            'visited' => [],
            'stack'   => []
            ];

        if (null !== ($result = $this->_roleDFSVisitOnePrivilege($role, $resource, $privilege, $dfs))) {
            return $result;
        }

        while (null !== ($role = array_pop($dfs['stack']))) {
            if (!isset($dfs['visited'][$role->getRoleId()])) {
                if (null !== ($result = $this->_roleDFSVisitOnePrivilege($role, $resource, $privilege, $dfs))) {
                    return $result;
                }
            }
        }

        return null;
    }

    /**
     * Visits an $role in order to look for a rule allowing/denying $role access to a $privilege upon $resource
     *
     * This method returns true if a rule is found and allows access. If a rule exists and denies access,
     * then this method returns false. If no applicable rule is found, then this method returns null.
     *
     * This method is used by the internal depth-first search algorithm and may modify the DFS data structure.
     *
     * @param  Zend_Acl_Role_Interface     $role
     * @param  Zend_Acl_Resource_Interface $resource
     * @param  string                      $privilege
     * @param  array                       $dfs
     * @return boolean|null
     * @throws Zend_Acl_Exception
     */
    protected function _roleDFSVisitOnePrivilege(Zend_Acl_Role_Interface $role, ?Zend_Acl_Resource_Interface $resource = null,
                                                $privilege = null, &$dfs = null)
    {
        if (null === $privilege) {
            /**
             * @see Zend_Acl_Exception
             */
            require_once 'Zend/Acl/Exception.php';
            throw new Zend_Acl_Exception('$privilege parameter may not be null');
        }

        if (null === $dfs) {
            /**
             * @see Zend_Acl_Exception
             */
            require_once 'Zend/Acl/Exception.php';
            throw new Zend_Acl_Exception('$dfs parameter may not be null');
        }

        if (null !== ($ruleTypeOnePrivilege = $this->_getRuleType($resource, $role, $privilege))) {
            return self::TYPE_ALLOW === $ruleTypeOnePrivilege;
        } else if (null !== ($ruleTypeAllPrivileges = $this->_getRuleType($resource, $role, null))) {
            return self::TYPE_ALLOW === $ruleTypeAllPrivileges;
        }

        $dfs['visited'][$role->getRoleId()] = true;
        foreach ($this->_getRoleRegistry()->getParents($role) as $roleParentId => $roleParent) {
            $dfs['stack'][] = $roleParent;
        }

        return null;
    }

    /**
     * Returns the rule type associated with the specified Resource, Role, and privilege
     * combination.
     *
     * If a rule does not exist or its attached assertion fails, which means that
     * the rule is not applicable, then this method returns null. Otherwise, the
     * rule type applies and is returned as either TYPE_ALLOW or TYPE_DENY.
     *
     * If $resource or $role is null, then this means that the rule must apply to
     * all Resources or Roles, respectively.
     *
     * If $privilege is null, then the rule must apply to all privileges.
     *
     * If all three parameters are null, then the default ACL rule type is returned,
     * based on whether its assertion method passes.
     *
     * @param  Zend_Acl_Resource_Interface $resource
     * @param  Zend_Acl_Role_Interface     $role
     * @param  string                      $privilege
     * @return string|null
     */
    protected function _getRuleType(?Zend_Acl_Resource_Interface $resource = null, ?Zend_Acl_Role_Interface $role = null,
                                    $privilege = null)
    {
        // get the rules for the $resource and $role
        if (null === ($rules = $this->_getRules($resource, $role))) {
            return null;
        }

        // follow $privilege
        if (null === $privilege) {
            if (isset($rules['allPrivileges'])) {
                $rule = $rules['allPrivileges'];
            } else {
                return null;
            }
        } else if (!isset($rules['byPrivilegeId'][$privilege])) {
            return null;
        } else {
            $rule = $rules['byPrivilegeId'][$privilege];
        }

        // check assertion first
        if ($rule['assert']) {
            $assertion = $rule['assert'];
            $assertionValue = $assertion->assert(
                $this,
                ($this->_isAllowedRole instanceof Zend_Acl_Role_Interface) ? $this->_isAllowedRole : $role,
                ($this->_isAllowedResource instanceof Zend_Acl_Resource_Interface) ? $this->_isAllowedResource : $resource,
                $this->_isAllowedPrivilege
                );
        }

        if (null === $rule['assert'] || $assertionValue) {
            return $rule['type'];
        } else if (null !== $resource || null !== $role || null !== $privilege) {
            return null;
        } else if (self::TYPE_ALLOW === $rule['type']) {
            return self::TYPE_DENY;
        } else {
            return self::TYPE_ALLOW;
        }
    }

    /**
     * Returns the rules associated with a Resource and a Role, or null if no such rules exist
     *
     * If either $resource or $role is null, this means that the rules returned are for all Resources or all Roles,
     * respectively. Both can be null to return the default rule set for all Resources and all Roles.
     *
     * If the $create parameter is true, then a rule set is first created and then returned to the caller.
     *
     * @param  Zend_Acl_Resource_Interface $resource
     * @param  Zend_Acl_Role_Interface     $role
     * @param  boolean                     $create
     * @return array|null
     */
    protected function &_getRules(?Zend_Acl_Resource_Interface $resource = null, ?Zend_Acl_Role_Interface $role = null,
                                  $create = false)
    {
        // create a reference to null
        $null = null;
        $nullRef =& $null;

        // follow $resource
        do {
            if (null === $resource) {
                $visitor =& $this->_rules['allResources'];
                break;
            }
            $resourceId = $resource->getResourceId();
            if (!isset($this->_rules['byResourceId'][$resourceId])) {
                if (!$create) {
                    return $nullRef;
                }
                $this->_rules['byResourceId'][$resourceId] = [];
            }
            $visitor =& $this->_rules['byResourceId'][$resourceId];
        } while (false);


        // follow $role
        if (null === $role) {
            if (!isset($visitor['allRoles'])) {
                if (!$create) {
                    return $nullRef;
                }
                $visitor['allRoles']['byPrivilegeId'] = [];
            }
            return $visitor['allRoles'];
        }
        $roleId = $role->getRoleId();
        if (!isset($visitor['byRoleId'][$roleId])) {
            if (!$create) {
                return $nullRef;
            }
            $visitor['byRoleId'][$roleId]['byPrivilegeId'] = [];
            $visitor['byRoleId'][$roleId]['allPrivileges'] = ['type' => null, 'assert' => null];
        }
        return $visitor['byRoleId'][$roleId];
    }


    /**
     * @return array of registered roles (Deprecated)
     * @deprecated Deprecated since version 1.10 (December 2009)
     */
    public function getRegisteredRoles()
    {
        trigger_error('The method getRegisteredRoles() was deprecated as of '
                    . 'version 1.0, and may be removed. You\'re encouraged '
                    . 'to use getRoles() instead.');

        return $this->_getRoleRegistry()->getRoles();
    }

    /**
     * Returns an array of registered roles.
     *
     * Note that this method does not return instances of registered roles,
     * but only the role identifiers.
     *
     * @return array of registered roles
     */
    public function getRoles()
    {
        return array_keys($this->_getRoleRegistry()->getRoles());
    }

    /**
     * @return array of registered resources
     */
    public function getResources()
    {
        return array_keys($this->_resources);
    }

}

