<?php

namespace cmf\lib;

class JsonExample implements \JsonSerializable
{
    private $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    public function jsonSerialize(): mixed
    {
        return json_decode($this->content);
    }


}