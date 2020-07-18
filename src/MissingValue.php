<?php

namespace Jgile\ArrayBuilder;

use JsonSerializable;

class MergeValue
{
    /**
     * The data to be merged.
     *
     * @var array
     */
    public $data;

    /**
     * Create new merge value instance.
     *
     * @param $data
     */
    public function __construct($data)
    {
        if ($data instanceof JsonSerializable) {
            $this->data = $data->jsonSerialize();
        } else {
            $this->data = $data;
        }
    }
}
