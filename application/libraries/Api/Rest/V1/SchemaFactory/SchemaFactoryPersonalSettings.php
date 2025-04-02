<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use LimeSurvey\Api\Rest\V1\Schema;

class SchemaFactoryPersonalSettings implements SchemaFactoryInterface
{
    /**
     * Create and return the schema for personal settings
     *
     * @return Schema
     */
    public function make()
    {
        $schema = new Schema();
        
        // Define your schema properties here
        // Example:
        $schema->setProperties([
            'language' => [
                'type' => 'string',
                'description' => 'User interface language'
            ],
            'theme' => [
                'type' => 'string',
                'description' => 'User interface theme'
            ],
            // Add more personal settings as needed
        ]);
        
        return $schema;
    }
}