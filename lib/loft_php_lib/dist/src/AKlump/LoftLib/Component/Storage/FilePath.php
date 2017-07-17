<?php

namespace AKlump\LoftLib\Component\Storage;

use LoftXmlElement;

/**
 * Class FilePath
 *
 * A wrapper class for writing to directories or files.  Can also read, but do
 * not use this class unless you intend to write, since instantiation will
 * create the parent directories.
 *
 * In this first example we are using the object as a "directory" object.
 *
 * @code
 *          $files = new FilePath('/to/my/files');
 *
 *   //
 *   //
 *   // Create a new json file in the directory.
 *   //
 *   $files->put('{"json":true}')
 *         ->to('test.json')
 *         ->save();
 *
 *   // ... or you can put an array as a JSON string like this:
 *   $files->putJson(['json' => true])
 *         ->to('test.json')
 *         ->save();
 *
 *   // You can then get the contents...
 *   $json = $files->get();
 *   $json === '{"json":true}';
 *   true === file_exists('/to/my/files/test.json');
 *
 *   // .. or get then as an object, since we know they are json encoded.
 *   $data = $files->getJson();
 *   $data == (object) ['json' => true];
 *
 *   //
 *   //
 *   // Load another file called records.txt from the directory
 *   //
 *   $contents = $files->from('records.txt')
 *                     ->load()
 *                     ->get();
 *
 *   //
 *   //
 *   // Load a json file to a data array
 *   //
 *   $data = $files->from('records.txt')->loadJson()->get();
 * @endcode
 *
 * In this next example we are using the object as as "file" object so the
 * syntax is simpler since we have indicated the $id in the constructor, by
 * passing a path to the filename, not the directory.
 *
 * @code
 *   $file = new FilePath('/to/my/file.json');
 *
 *   //
 *   //
 *   // Save some json to the file.
 *   //
 *   $file->putJson(['title' => 'Book'])->save();
 *
 *   //
 *   //
 *   // ...later on load the json from the file;
 *   //
 *   $data = $file->loadJson()->get();
 * @endcode
 *
 * @package AKlump\LoftLib\Component\Storage
 */
class FilePath implements PersistentInterface {

    const TYPE_DIR = 1;
    const TYPE_FILE = 2;

    protected $dir, $basename, $contents, $type, $alias;
    protected $cache = array();

    /**
     * FilePath constructor.
     *
     * @param string $path      Full path to directory or file. All parent
     *                          directories will be created, unless permissions
     *                          prevent it.
     * @param null   $extension To leverage the tempName method, pass an
     *                          extension, and a filepath to a temp-named file
     *                          will be created inside of $path--note: $path
     *                          must be a directory.
     */
    public function __construct($path, $extension = null)
    {
        if ($extension) {

            // Try to make sure $path references a directory, not a file.
            if (pathinfo($path, PATHINFO_EXTENSION)) {
                throw new \InvalidArgumentException("When providing an extension, \$path must reference a directory.");
            }

            // Make sure the extension is not a filename or a path.
            $test = explode('.', trim($extension, '.'));
            if (count($test) > 1 || strpos($extension, '/') !== false) {
                throw new \InvalidArgumentException("\$extension appears to be a filename; it must only contain the extension, e.g. 'pdf', and no leading dot");
            }
            $path .= '/' . static::tempName($extension);
        }
        list($this->dir, $this->basename) = static::ensureDir($path);
        $this->type = empty($this->basename) ? static::TYPE_DIR : static::TYPE_FILE;
    }

    /**
     * Generate a tempName string.
     *
     * Does not generate a file.
     *
     * @param string $extension Optional.
     *
     * @return string
     */
    public static function tempName($extension = '')
    {
        return static::extensionHandler(uniqid('', true), $extension);
    }

    /**
     * Ensure all directories in $path exist, if possible.
     *
     * @param     $path string Expecting a directory, but file works if it has
     *                  an extension as dirname() will be used.  However, if
     *                  the file does not have an extension, it will be assumed
     *                  it is a dirname, and that may be unexpected.  It is
     *                  most consistent to always pass a path to a directory
     *                  and avoid including the file component of the path.
     * @param int $mode
     *
     * @return array
     */
    public static function ensureDir($path, $mode = 0777)
    {
        $info = pathinfo($path);
        $basename = '';
        if (!empty($info['extension'])) {
            $path = $info['dirname'];
            $basename = $info['basename'];
        }

        file_exists($path) || mkdir($path, $mode, true);

        return array($path, $basename);
    }

    /**
     * Generate a filename based on date
     *
     * @param string    $extension , e.g. pdf
     * @param string    $format    To be used for the DateTime::format.
     * @param \DateTime $datetime  You may pass your own object, otherwise now
     *                             in UTC is used.
     *
     * @return string
     */
    public static function dateName($extension = '', $format = null, \DateTime $datetime = null)
    {
        $replaceOffsetWithZulu = false;
        if (is_null($format)) {
            $replaceOffsetWithZulu = true;
            $format = 'Y-m-d\TH-i-sO';
        }
        $datetime = $datetime ? $datetime : date_create('now', new \DateTimeZone('UTC'));
        $name = $datetime->format($format);

        // We only do this if format is not provided.
        if ($replaceOffsetWithZulu) {
            $name = preg_replace('/\+0000$/', 'Z', $name);
        }

        return static::extensionHandler($name, $extension);
    }

    protected static function extensionHandler($name, $extension)
    {
        return $extension ? $name . '.' . ltrim($extension, '.') : $name;
    }

    /**
     * Return the alias or basename if no alias.
     *
     * This can be used for altering the download filename.
     *
     * @return mixed
     */
    public function getAlias()
    {
        return empty($this->alias) ? $this->getBasename() : $this->alias;
    }

    /**
     * Determine if an alias is set.
     *
     * This can be used since getAlias will always return a value.
     *
     * @return bool
     */
    public function hasAlias()
    {
        return isset($this->alias);
    }

    /**
     * Set the alias of the file, which means the name which downloads, for
     * example.
     *
     * This has nothing to do with symlinks or os aliases.
     *
     * @param string $alias
     *
     * @return FilePath
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

    public function destroy()
    {
        if ($this->getType() === static::TYPE_FILE) {
            unlink($this->getPath());

            return $this;
        }

        throw new \RuntimeException("Only files can be destroyed.");
    }

    /**
     * Return if this is a dir or a file.
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string Path to dir (or file if $this->basename).
     */
    public function getPath()
    {
        $path = rtrim($this->dir, '/');
        if ($this->basename) {
            $path .= '/' . rtrim($this->basename);
        }

        return $path;
    }

    /**
     * For files return parent dir, for directories return self.
     */
    public function getDirname()
    {
        return $this->dir;
    }

    /**
     * For files only, return the filename (basename without extension)
     *
     * @return string|null
     */
    public function getFilename()
    {
        return $this->basename ? pathinfo($this->basename, PATHINFO_FILENAME) : null;
    }

    /**
     * For files only, return the extension without leading dot.
     *
     * @return string|null
     */
    public function getExtension()
    {
        return $this->basename ? pathinfo($this->basename, PATHINFO_EXTENSION) : null;
    }

    /**
     * Save the $contents to the $this->basename, or provide a new $basename.
     *
     * @return $this
     */
    public function save()
    {
        $this->validateBasename();

        if (@file_put_contents($this->getPath(), $this->contents) === false) {
            throw new \RuntimeException("Could not save to " . $this->getPath());
        }

        return $this;
    }

    public function put($contents)
    {
        $this->contents = $contents;

        return $this;
    }

    public function putJson(array $data)
    {
        $this->contents = json_encode($data);

        return $this;
    }

    public function to($id)
    {
        return $this->setBasename($id);
    }

    public function from($id)
    {
        return $this->setBasename($id);
    }

    public function getId()
    {
        return $this->basename;
    }

    public function load()
    {
        $this->validateBasename();
        $this->contents = file_get_contents($this->getPath());

        return $this;
    }

    public function get()
    {
        return $this->contents;
    }

    public function getJson()
    {
        return json_decode($this->contents);
    }

    public function putXml(\SimpleXMLElement $data)
    {
        $this->contents = $data->asXml();

        return $this;
    }

    public function getXml()
    {
        return new LoftXmlElement($this->contents);
    }

    /**
     * Copy the file at $source to $this->basename.
     *
     * @param $source
     *
     * @return \AKlump\LoftLib\Component\Storage\FilePath
     */
    public function copy($source)
    {
        return $this->_copyOrMove('copy', 'copy', $source);
    }

    /**
     * Move an uploaded file to $this->basename.
     *
     * @param $source
     *
     * @return \AKlump\LoftLib\Component\Storage\FilePath
     */
    public function upload($source)
    {
        return $this->_copyOrMove('move', 'move_uploaded_file', $source);
    }

    /**
     * Move $source to $this->basename
     *
     * @param $source
     *
     * @return \AKlump\LoftLib\Component\Storage\FilePath
     */
    public function move($source)
    {
        return $this->_copyOrMove('move', 'rename', $source);
    }

    /**
     * Determine if $this->basepath exists.
     *
     * @return bool
     */
    public function exists()
    {
        return file_exists($this->getPath());
    }

    /**
     * Return headers used to force a download.
     *
     * @return array
     */
    public function getDownloadHeaders()
    {
        $this->validateBasename();
        $headers = $this->getStreamHeaders() + array(
                'Content-Disposition' => 'attachment; filename="' . $this->getAlias() . '"',
            );

        return $headers;
    }

    /**
     * Return headers used to serve the file.
     *
     * @return array
     */
    public function getStreamHeaders()
    {
        $this->validateBasename();
        $path = $this->getPath();
        $headers = array();
        $headers['Content-Type'] = $this->getMimeType();
        $headers['Content-Length'] = filesize($path);

        return $headers;
    }

    /**
     * For files only, return the basename
     *
     * @return string|null
     */
    public function getBasename()
    {
        return !empty($this->basename) ? $this->basename : null;
    }

    /**
     * Return the mime type of the file.
     */
    public function getMimeType()
    {
        if (!isset($this->cache['mime'])) {
            // I've found this to be more reliable than both: mime_content_type and finfo class. 2017-03-25T09:11, aklump
            $test = new \Mimey\MimeTypes;
            $this->cache['mime'] = $test->getMimeType($this->getExtension());
        }

        return $this->cache['mime'];
    }

    protected function validateBasename()
    {
        if (empty($this->basename)) {
            throw new \RuntimeException("You must use to() to specify a basename for the file.");
        }
    }

    protected function setBasename($id)
    {
        $this->cache = array();
        if (strpos($id, '/') !== false) {
            throw new \InvalidArgumentException("\"$id\" cannot be a path, only a filename.");
        }
        $this->basename = $id;

        return $this;
    }

    /**
     * Helper, do not call directly.
     */
    protected function _copyOrMove($op, $function, $source)
    {
        $source = is_object($source) && method_exists($source, 'getPath') ? $source->getPath() : $source;
        if (!file_exists($source)) {
            throw new \RuntimeException("\"$source\" does not exist; can't $op.");
        }
        $this->validateBasename();
        if (!$function($source, ($d = $this->getPath()))) {
            throw new \RuntimeException("Could not $op \"$source\" to \"$d\"");
        }

        return $this;
    }
}
