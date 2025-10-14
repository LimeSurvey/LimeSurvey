<?php

namespace LimeSurvey\Libraries\Api\Command\V1\LLMs;

use LimeSurvey\Api\Command\ApiCommandException;
use LimeSurvey\Api\Command\ResponseData\ResponseDataError;
use LimeSurvey\Libraries\Api\Command\V1\LLMs\Handlers\Command;
use LimeSurvey\Libraries\Api\Command\V1\LLMs\Handlers\CommandPatcher;
use LimeSurvey\Libraries\Api\Command\V1\LLMs\Handlers\AIClientInterface;

use LimeSurvey\Api\ApiException;

class Gemini implements AIClientInterface
{
    /**
     * Gemini API URL
     *
     * @var string
     */
    protected string $url = 'https://generativelanguage.googleapis.com/v1beta';

    /**
     * GeminiAPI model
     * @var string
     */
    protected string $modelType = 'models/gemini-pro';

    /**
     * GeminiAPI model
     * @var string
     */
    protected string $apikey;

    protected Command $command;

    public function __construct(Command $command)
    {
        $this->apikey = trim((string) App()->getConfig("googleGeminiAPIKey"));
        $this->command = $command;
    }

    private function buildPostFields(): string
    {
        // Temperature controls the degree of randomness in token selectiom
        $temperature = 0.1;
        //top-K & topP change how the model selects tokens for output.
        // Specify a lower value for less random responses and a higher value for more random responses.
        $topK = 1;
        $topP = 1;
        //  Maximum number of tokens that can be generated in the response
        $maxOutputTokens = 2000;

        $prompt = "{$this->command->getOperation()}: {$this->command->getPrompt()}";

        return json_encode([
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt]
                    ]
                ]
            ],
            "generationConfig" => [
                "temperature" => $temperature,
                "topK" => $topK,
                "topP" => $topP,
                "maxOutputTokens" => $maxOutputTokens,
                "stopSequences" => []
            ],
            "safetySettings" => [
                [
                    "category" => "HARM_CATEGORY_HARASSMENT",
                    "threshold" => "BLOCK_ONLY_HIGH"
                ],
                [
                    "category" => "HARM_CATEGORY_HATE_SPEECH",
                    "threshold" => "BLOCK_ONLY_HIGH"
                ],
                [
                    "category" => "HARM_CATEGORY_SEXUALLY_EXPLICIT",
                    "threshold" => "BLOCK_ONLY_HIGH"
                ],
                [
                    "category" => "HARM_CATEGORY_DANGEROUS_CONTENT",
                    "threshold" => "BLOCK_ONLY_HIGH"
                ]
            ]
        ]);
    }

    private function buildHeader(): array
    {
        return [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($this->buildPostFields())
        ];
    }

    private function buildURL(): string
    {
        return "{$this->url}/{$this->modelType}:generateContent?key={$this->apikey}";
    }

    /**
     * @throws ApiCommandException
     */
    private function handleResponse(string $response): string
    {
        $responseData = json_decode($response, true);
        if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
            return $responseData['candidates'][0]['content']['parts'][0]['text'];
        }

        throw new ApiCommandException('No_CONTENT_FOUND_IN_RESPONSE');
    }

    /**
     * @throws ApiCommandException
     */
    public function generateContent(): string
    {
        if (!empty($this->apikey)) {
            $ch = curl_init($this->buildURL());
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->buildPostFields());
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->buildHeader());
            $response = curl_exec($ch);
            curl_close($ch);

            return $this->handleResponse($response);
        }

        throw new ApiCommandException('NO_API_KEY');
    }

    public function run(): string
    {
        $patcher = new CommandPatcher($this->command, $this);
        return $patcher->apply();
    }

    public function getModelInfo(): array
    {
        return [
            'name' => 'Gemini',
            'type' => $this->modelType
        ];
    }
}
