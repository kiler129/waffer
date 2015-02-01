<?php
namespace noFlash\Waffer;

use InvalidArgumentException;
use RuntimeException;

/**
 * Waffers can be fat (by default, but I like them anyway) or diet.
 * Note: This class intentionaly left XML & INI unimplemented. First one I personally hate (but if you wish I kindly
 * accept pull request for it), second one is buggy and limited.
 *
 * @package noFlash\Waffer
 * @todo YML
 */
class Waffer extends DietWaffer
{
    const FORMAT_JSON   = 0;
    const FORMAT_SERIAL = 1;

    /**
     * Restores configuration from file of given format
     *
     * @param string $path Path to file
     * @param int $format File format. It can be one of the following: Waffer::FORMAT_JSON, Waffer::FORMAT_SERIAL
     * @param bool $overwriteConfiguration Whatever to overwrite current configuration stored inside object (by default
     *     it's merged)
     *
     * @throws InvalidArgumentException File format is invalid or data cannot be decoded
     * @throws RuntimeException In case of file cannot be read or it's empty
     */
    public function fromFile($path, $format = self::FORMAT_JSON, $overwriteConfiguration = false)
    {
        $file = file_get_contents($path);
        if (!$file) {
            throw new RuntimeException("Failed to read file $path, or it's empty");
        }

        if ($format === self::FORMAT_JSON) {
            $this->fromJSON($file, $overwriteConfiguration);

        } elseif ($format === self::FORMAT_SERIAL) {
            $this->unserialize($file, $overwriteConfiguration);

        } else {
            throw new InvalidArgumentException("Invalid format specified");
        }
    }

    /**
     * Restores configuration from JSON string
     *
     * @param string $json JSON
     * @param bool $overwriteConfiguration Whatever to overwrite current configuration stored inside object (by default
     *     it's merged)
     *
     * @throws InvalidArgumentException JSON provided cannot be decoded
     */
    public function fromJSON($json, $overwriteConfiguration = false)
    {
        $json = json_decode($json, true);
        if ($json === null) {
            throw new InvalidArgumentException("JSON provided is not valid");
        }

        $this->storage = ($overwriteConfiguration) ? $json : array_replace_recursive($this->storage, $json);
    }

    /**
     * Restores configuration from serialized string
     * Warning: DO NOT pass untrusted input to this method!
     *
     * @param string $serialized Serialized string
     * @param bool $overwriteConfiguration Whatever to overwrite current configuration stored inside object (by default
     *     it's merged)
     *
     * @return string Serialized array
     *
     * @throws InvalidArgumentException Invalid serialized string provided.
     */
    public function unserialize($serialized, $overwriteConfiguration = false)
    {
        $serialized = unserialize($serialized);
        if ($serialized === false) {
            throw new InvalidArgumentException("Serialized string provided is not valid");
        }

        $this->storage = ($overwriteConfiguration) ? $serialized : array_replace_recursive($this->storage, $json);
    }

    /**
     * Dumps configuratio to file in given format
     *
     * @param string $path Path to file
     * @param int $format File format. It can be one of the following: Waffer::FORMAT_JSON, Waffer::FORMAT_SERIAL
     *
     * @return bool
     * @throws InvalidArgumentException File format is invalid
     * @throws RuntimeException In case of file cannot be written
     */
    public function toFile($path, $format = self::FORMAT_JSON)
    {
        if ($format === self::FORMAT_JSON) {
            $data = $this->toJSON();

        } elseif ($format === self::FORMAT_SERIAL) {
            $data = $this->serialize();

        } else {
            throw new InvalidArgumentException("Invalid format specified");
        }

        if (!file_put_contents($path, $data)) {
            throw new RuntimeException("Failed to write file $path");
        }

        return true;
    }

    /**
     * Provides full configuration as JSON string
     *
     * @return string JSON
     */
    public function toJSON()
    {
        return json_encode($this->storage);
    }

    /**
     * Returns serialized configuration array
     *
     * @return string Serialized array
     */
    public function serialize()
    {
        return serialize($this->storage);
    }

    /**
     * Return human-readable configuration view
     * Note exported text are PHP-valid code ;)
     *
     * @return string
     * @see var_export()
     */
    public function __toString()
    {
        return var_export($this->storage, true);
    }
}