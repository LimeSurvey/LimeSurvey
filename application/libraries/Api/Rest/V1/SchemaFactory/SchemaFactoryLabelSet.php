<?php

namespace LimeSurvey\Api\Rest\V1\SchemaFactory;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

class SchemaFactoryLabelSet
{
    /**
     * Schema of one label as returned and accepted by the label set endpoints.
     *
     * @return Schema The label schema
     */
    public function makeLabel(): Schema
    {
        return Schema::object()->properties(
            Schema::string('code'),
            Schema::integer('sortOrder'),
            Schema::integer('assessmentValue')->default(0),
            Schema::object('l10ns')
                ->description('Label text keyed by language code')
                ->additionalProperties(Schema::string())
        );
    }

    /**
     * Response of GET/POST/PUT label-sets with an id.
     *
     * @return Schema The label set schema
     */
    public function make(): Schema
    {
        return Schema::create()
            ->title('Label Set')
            ->description('Label set with its labels')
            ->type(Schema::TYPE_OBJECT)
            ->properties(
                Schema::object('labelSet')->properties(
                    Schema::integer('lid'),
                    Schema::string('name'),
                    Schema::array('languages')->items(Schema::string()),
                    Schema::array('labels')->items($this->makeLabel()),
                    Schema::string('revision')
                        ->description('Changes whenever the languages or labels change; send it with an overwrite')
                )
            );
    }

    /**
     * Response of GET label-sets.
     *
     * @return Schema The label set list schema
     */
    public function makeList(): Schema
    {
        return Schema::create()
            ->title('Label Set List')
            ->description('Label sets without their labels')
            ->type(Schema::TYPE_OBJECT)
            ->properties(
                Schema::array('labelSets')->items(
                    Schema::object()->properties(
                        Schema::integer('lid'),
                        Schema::string('name'),
                        Schema::array('languages')->items(Schema::string()),
                        Schema::integer('labelCount'),
                        Schema::boolean('hasAssessmentValues')
                    )
                )
            );
    }

    /**
     * Request body of POST/PUT label-sets.
     *
     * @return Schema The request body schema
     */
    public function makeSaveRequest(): Schema
    {
        return Schema::create()
            ->title('Label Set Save Request')
            ->description(
                'Name is only used when creating a label set. Revision is required when overwriting: '
                . 'The revision of the label set the user reviewed, the overwrite fails with 409 when it changed since'
            )
            ->type(Schema::TYPE_OBJECT)
            ->required('languages', 'labels')
            ->properties(
                Schema::string('name'),
                Schema::array('languages')->items(Schema::string()),
                Schema::array('labels')->items($this->makeLabel()),
                Schema::string('revision')
            );
    }
}
