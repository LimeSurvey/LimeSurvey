<?php

namespace LimeSurvey\Libraries\Api\Command\V1\LLMs;

use LimeSurvey\Libraries\Api\Command\V1\LLMs\Handlers\Command;
use LimeSurvey\Libraries\Api\Command\V1\LLMs\Handlers\CommandPatcher;
use LimeSurvey\Libraries\Api\Command\V1\LLMs\Handlers\AIClientInterface;

class GoogleGeminiPro implements AIClientInterface
{
    /**
     * Gemini API URL
     *
     * @var string
     */
    protected string $googleai_url = 'https://generativelanguage.googleapis.com/v1beta';

    /**
     * GeminiAPI model
     * @var string
     */
    protected string $googleai_model = 'models/gemini-1.5-pro-latest';

    /**
     * GeminiAPI model
     * @var string
     */
    protected string $googleai_apikey;

    protected Command $command;

    public function __construct(Command $command)
    {
        $this->googleai_apikey = trim((string) \Yii::app()->getConfig("googleGeminiAPIKey"));
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
        return "{$this->googleai_url}/{$this->googleai_model}:generateContent?key={$this->googleai_apikey}";
    }

    private function handleResponse(string $response)
    {
        $responseData = json_decode($response, true);
        if (isset($responseData['candidates'])) {
            $array = array_map(function ($candidate) {
                return $candidate['content']['parts'][0]['text'];
            }, $responseData['candidates']);
            return json_encode([
                'content' => $array,
                'error' => false,
                'errorMsg' => null,
            ]);
        }
    }

    public function generateContent(): string
    {
        if (!empty($this->googleai_apikey)) {
            $ch = curl_init($this->buildURL());
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->buildPostFields());
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->buildHeader());
            $response = curl_exec($ch);
            curl_close($ch);

            if (is_string($response)) {
                return $this->handleResponse($response);
            }
            return json_encode([
                'content' => null,
                'error' => true,
                'errorMsg' => 'No text generated',
            ]);
        }
        return json_encode([
            'content' => null,
            'error' => true,
            'errorMsg' => 'No API key',
        ]);
    }

    public function run(): string
    {
        $patcher = new CommandPatcher($this->command, $this);
        return $patcher->apply();
    }
}
