<?php

namespace LimeSurvey\Models\Services\SurveyStatistics\Charts;

/**
 * Encapsulates graph data (title, legend, series data, totals, meta)
 * into a structured array for use in APIs or frontend visualization.
 */
class StatisticsChartDTO
{
    /** @var string Title of the graph (usually the question text) */
    private string $title;

    /** @var array Legend labels used in the graph (e.g. "Yes", "No") */
    private array $legend;

    /** @var array Data points for the graph (usually array of key/value pairs) */
    private array $data;

    /** @var int|null Total count of responses (optional, falls back to count($data)) */
    private ?int $total;


    /** @var array Additional metadata that may be included (e.g. question, filters applied) */
    private array $meta;

    /**
     * @param string $title
     * @param array $legend
     * @param array $data
     * @param int $total
     * @param array $meta
     */
    public function __construct(string $title, array $legend, array $data, $total = null, array $meta = [])
    {
        $this->title = $title;
        $this->legend = $legend;
        $this->data = $data;
        $this->total = $total ?? count($data);
        $this->meta = $meta;
    }

    /**
     * Convert the DTO into an associative array for API responses.
     *
     * @return array Structured graph data
     */
    public function toArray(): array
    {
        $object = [
            'title' => $this->title,
            'legend' => $this->legend,
            'data' => $this->data,
        ];

        if ($this->total !== null) {
            $object['total'] = $this->total;
        }

        if (!empty($this->meta)) {
            $object['meta'] = $this->meta;
        }

        return $object;
    }
}
