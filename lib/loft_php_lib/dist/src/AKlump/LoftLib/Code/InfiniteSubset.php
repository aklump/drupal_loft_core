<?php


namespace AKlump\LoftLib\Code;


use AKlump\Data\Data;

/**
 * Class InfiniteSubset
 *
 * Return randomly ordered slices of a dataset.  Designed to work with the session for persistence of state across page
 * loads. An example use case is to show three different tiles at the bottom of each page, which change each page load,
 * and are pulled from a larger set of tiles.  When all tiles in the superset are shown, show them again, but this time
 * in a different order, never running out of sets.
 *
 * If $_SESSION is not the desired way to hold state, then you may pass the third argument to the constructor, a
 * pass-by-reference array which will be used to hold state instead of $_SESSION.
 *
 * @code
 *  // Each time this page loads, 3 tile nids will be loaded from the list of nids.
 *  $nids = new InfiniteSubset([255, 365, 987, 123, 455, 99, 101, 345], 'round_robin.related.123');
 *  $tiles = node_]oad_multiple($nids->slice(3));
 * @endcode
 *
 * @package AKlump\LoftLib\Code
 */
class InfiniteSubset {

    protected $g;

    protected $dataset;

    protected $stateArray;

    protected $stateArrayPath;

    /**
     * InfiniteSubset constructor.
     *
     * @param  array            $dataset        The original array to step through.  Keys must not
     *                                          be important as only the values will be used.
     *                                          Elements should be single values (strings, int, etc)
     *                                          not arrays nor objects.
     * @param string            $stateArrayPath The dot separated path in $stateArray.
     * @param array             $stateArray     Defaults to $_SESSION.  An array to hold state.
     * @param \AKlump\Data\Data $data           Only needed to override default.
     */
    public function __construct($dataset, $stateArrayPath = '', array &$stateArray = null, Data $data = null)
    {
        $this->g = $data ? $data : new Data();
        $this->dataset = $dataset;
        if ($stateArray === null) {
            $this->container =& $_SESSION;
        }
        else {
            $this->container =& $stateArray;
        }
        $this->containerPath = $stateArrayPath;
        $this->reset();
    }

    /**
     * Return a randomly ordered slice of dataset $count items long.
     *
     * @param int $count
     *
     * @return array
     */
    public function slice($count)
    {
        $stack = $this->getStack();
        while (count($stack) < $count) {
            $stack = array_merge($stack, $this->getSortedDataset());
        }
        $slice = array_slice($stack, 0, $count);
        $stack = array_slice($stack, $count);
        $this->setStack($stack);

        return $slice;
    }

    /**
     * Return the original dataset, order untouched.
     *
     * @return array
     */
    public function getDataset()
    {
        return $this->getDataFromContainer()['original'];
    }

    /**
     * Return the current stack, randomized order, less any values already sliced.
     *
     * @return mixed
     */
    private function getStack()
    {
        return $this->getDataFromContainer()['stack'];
    }

    private function reset()
    {
        return $this->setStack($this->getSortedDataset());
    }

    /**
     * Return the dataset in a new random order.
     *
     * You may want to extend this class and override this method to control sorting algorithm.
     *
     * @return array
     */
    private function getSortedDataset()
    {
        $stack = $this->dataset;
        shuffle($stack);

        return $stack;
    }

    private function getDataFromContainer()
    {
        return $this->g->get($this->container, $this->containerPath);
    }

    /**
     * Sets the data into our container.
     *
     * @param $stack
     *
     * @return $this
     */
    private function setStack($stack)
    {
        $value = [
            'original' => $this->dataset,
            'stack' => $stack,
        ];
        if (!$this->containerPath) {
            $this->container = $value;
        }
        else {
            $this->g->set($this->container, $this->containerPath, $value);
        }

        return $this;
    }
}
