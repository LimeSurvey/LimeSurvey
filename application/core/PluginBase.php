<?php

/**
 * NB: Deleting this class will break plugin API, since
 * plugins then would have to extend PluginBase in the
 * namespace instead of this class. This is especially
 * a problem for plugins that should work on both
 * 2.73 and 3.x, so please don't delete this class.
 */
class PluginBase extends \LimeSurvey\PluginManager\PluginBase
{

}
