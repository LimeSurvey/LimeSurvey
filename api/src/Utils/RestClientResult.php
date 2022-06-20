<?php

namespace App\Utils;

use Exception;

class RestClientResult
{
    private int $code = -1;
    private string|null $result = null;

    public function getCode(): int
    {
        return $this->code;
    }

    public function setCode(int $code): void
    {
        $this->code = $code;
    }

    public function setResult(string|null $result): void
    {
        $this->result = $result;
    }

    public function getRawResult(): string|null
    {
        return $this->result;
    }

    public function getJSONResult(): mixed
    {
        if (is_null($this->result)) {
            throw new Exception("result is null");
        }
        return json_decode($this->result, true);
    }
}
