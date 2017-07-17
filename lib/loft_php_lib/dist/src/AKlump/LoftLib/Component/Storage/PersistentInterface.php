<?php
namespace AKlump\LoftLib\Component\Storage;

interface PersistentInterface
{
    /**
     * Get temporary data, previously loaded.
     *
     * @return mixed
     *
     * @see load().
     */
    public function get();

    /**
     * Get temporary json data, previously loaded, converted from Json.
     *
     * @return {object}
     *
     * @see    load().
     */
    public function getJson();

    /**
     * Load data from persistent storage to temporary.
     *
     * @return {object}
     *
     * @see    get().
     */
    public function load();

    /**
     * Put data into temporary storage
     *
     * @param mixed $contents
     *
     * @return  {object}
     *
     * @see     save();
     */
    public function put($contents);

    /**
     * Put data into temporary storage converting to a json string.
     *
     * @param array $data
     *
     * @return  {object}
     *
     * @see     save();
     */
    public function putJson(array $data);

    /**
     * Save temporary data to persistent storage
     *
     * @return {object}
     *
     * @see    save();
     */
    public function save();

    /**
     * Indicate a name for the contents when saving.
     *
     * @param $id
     *
     * @return {object}
     */
    public function to($id);

    /**
     * Indicate a persistent name
     *
     * @param $id
     *
     * @return {object}
     *
     * @see to()
     */
    public function from($id);

    /**
     * Return the id set in to().
     * @return string
     */
    public function getId();
}
