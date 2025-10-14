<?php

namespace LimeSurvey\Libraries\Api\Command\V1\LLMs;

use LimeSurvey\Api\Command\ApiCommandException;
use LimeSurvey\Api\Command\ResponseData\ResponseDataError;
use LimeSurvey\Libraries\Api\Command\V1\LLMs\Handlers\Command;
use LimeSurvey\Libraries\Api\Command\V1\LLMs\Handlers\CommandPatcher;
use LimeSurvey\Libraries\Api\Command\V1\LLMs\Handlers\AIClientInterface;

use LimeSurvey\Api\ApiException;

class LimeSurveyLLM implements AIClientInterface
{

    protected string $url = 'https://limesurvey-30.limesurvey.org:9443';
    protected string $modelType;
    protected array $availableModels = [
        'qwen'  => 'qwen3:8b',
        'llama' => 'llama3.1:8b'
    ];

    protected Command $command;

    public function __construct(Command $command, $modelType = 'qwen3:8b')
    {
        $this->modelType = $modelType;
        $this->command = $command;
    }

    private function buildPostFields(): string
    {
        $prompt = "{$this->command->getOperation()}: {$this->command->getPrompt()}";

        return json_encode([
            "model" => $this->modelType,
            "prompt" => $prompt,
            "options" => [
                "temperature" => 0.1,
                "enable_thinking" => false
            ],
            "stream" => false
        ]);
    }

    private function buildHeader(): array
    {
        return [
            'Content-Type: application/json',
        ];
    }

    private function buildURL(): string
    {
        return "{$this->url}/api/generate";
    }

    /**
     * @throws ApiCommandException
     */
    private function handleResponse(string $response): string
    {
        $responseData = json_decode($response, true);

        if (isset($responseData['response']) && $this->modelType == $this->availableModels['qwen']) {
            return $this->removeTinkingTag($responseData['response']);
        }

        if (isset($responseData['response']) && $this->modelType == $this->availableModels['llama']) {
            return $responseData['response'];
        }

        throw new ApiCommandException('NO_CONTENT_FOUND_IN_RESPONSE');
    }

    public function removeTinkingTag(string $response): string
    {
        $cleanOutput = preg_replace(
            '/(?:\\\\u003c|<)think(?:\\\\u003e|>).+?(?:\\\\u003c|<)\/think(?:\\\\u003e|>)/si',
            '',
            $response
        );

        return trim($cleanOutput);
    }

    /**
     * @throws ApiCommandException
     */
    public function generateContent(): string
    {
        $ch = curl_init($this->buildURL());
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->buildPostFields());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->buildHeader());
        $response = curl_exec($ch);
        curl_close($ch);

        return $this->handleResponse($response);

    }

    public function run(): string
    {
        $patcher = new CommandPatcher($this->command, $this);
        return $patcher->apply();
    }

    public function getModelInfo(): array
    {
        return [
            'name' => 'LimeSurvey',
            'type' => $this->modelType
        ];
    }
}
