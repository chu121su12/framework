<?php

namespace PHPUnit\Framework;

final class ExpectationFailedException extends AssertionFailedError
{
    /**
     * @var SebastianBergmann\Comparator\ComparisonFailure
     */
    protected $comparisonFailure;

    public function __construct($message, \SebastianBergmann\Comparator\ComparisonFailure $comparisonFailure = null, Exception $previous = null)
    {
        $this->comparisonFailure = $comparisonFailure;

        parent::__construct($message, 0, $previous);
    }

    /**
     * @return SebastianBergmann\Comparator\ComparisonFailure
     */
    public function getComparisonFailure()
    {
        return $this->comparisonFailure;
    }
}
