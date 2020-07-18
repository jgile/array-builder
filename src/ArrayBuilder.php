<?php

namespace Jgile\ArrayBuilder;

final class ArrayBuilder
{
    public $preserveKeys;
    protected array $data;

    public function __construct($callable)
    {
        $this->data = $this->filter(is_array($callable) ? $callable : $callable($this));
    }

    /**
     * @param $callable
     *
     * @return ArrayBuilder
     */
    public static function make($callable)
    {
        return new static($callable);
    }

    /**
     * Merge an array.
     *
     * @param $array
     *
     * @return MergeValue
     */
    public function merge($array)
    {
        return $this->mergeWhen(true, $array);
    }

    /**
     * Merge if condition is true.
     *
     * @param $condition
     * @param $value
     * @param  null  $else
     *
     * @return MergeValue|MissingValue|mixed
     */
    public function mergeWhen($condition, $value, $else = null)
    {
        if ($condition) {
            return new MergeValue($this->resolveValue($value));
        }

        if ($else !== null) {
            return $this->resolveValue($else);
        }

        return new MissingValue();
    }

    /**
     * Returning resulting array.
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Return the array.
     *
     * @return array
     */
    public function value()
    {
        return $this->data;
    }

    /**
     * Filter the given data, removing any optional values.
     *
     * @param  array  $data
     *
     * @return array
     */
    protected function filter($data)
    {
        $index = -1;

        foreach ($data as $key => $value) {
            $index++;

            if (is_array($value)) {
                $data[$key] = $this->filter($value);

                continue;
            }

            if (is_numeric($key) && $value instanceof MergeValue) {
                return $this->mergeData($data, $index, $this->filter($value->data),
                    array_values($value->data) === $value->data);
            }

            if ($value instanceof self && is_null($value->resource)) {
                $data[$key] = null;
            }
        }

        return $this->removeMissingValues($data);
    }

    /**
     * Merge the given data in at the given index.
     *
     * @param  array  $data
     * @param  int  $index
     * @param  array  $merge
     * @param  bool  $numericKeys
     *
     * @return array
     */
    protected function mergeData($data, $index, $merge, $numericKeys)
    {
        if ($numericKeys) {
            return $this->removeMissingValues(array_merge(
                array_merge(array_slice($data, 0, $index, true), $merge),
                $this->filter(array_values(array_slice($data, $index + 1, null, true))),
            ));
        }

        return $this->removeMissingValues(array_slice($data, 0, $index,
                true) + $merge + $this->filter(array_slice($data, $index + 1, null, true)));
    }

    /**
     * Remove the missing values from the filtered data.
     *
     * @param  array  $data
     *
     * @return array
     */
    protected function removeMissingValues($data)
    {
        $numericKeys = true;

        foreach ($data as $key => $value) {
            if (
                ($value instanceof MissingValue && $value->isMissing()) ||
                ($value instanceof self && $value->resource instanceof MissingValue && $value->isMissing())
            ) {
                unset($data[$key]);
            } else {
                $numericKeys = $numericKeys && is_numeric($key);
            }
        }

        if (property_exists($this, 'preserveKeys') && $this->preserveKeys === true) {
            return $data;
        }

        return $numericKeys ? array_values($data) : $data;
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    protected function resolveValue($value)
    {
        return $value instanceof \Closure ? $value() : $value;
    }
}
