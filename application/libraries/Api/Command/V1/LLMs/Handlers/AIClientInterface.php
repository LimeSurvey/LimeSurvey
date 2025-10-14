<?php

namespace LimeSurvey\Libraries\Api\Command\V1\LLMs\Handlers;

interface AIClientInterface
{
    /**
     * Generate a text or structured response from the LLM
     *
     * @return string
     */
    public function generateContent(): string;

    /**
     * Run the full LLM workflow (with optional patching)
     *
     * @return string
     */
    public function run(): string;

    /**
     * Return model metadata (e.g. model name, provider, version)
     *
     * @return array
     */
    public function getModelInfo(): array;

}
