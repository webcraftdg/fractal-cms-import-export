<?php
/**
 * AbstractContext.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\contexts
 */
namespace fractalCms\importExport\contexts;

use Exception;
use Yii;

class AbstractContext
{
    /**
     * @param object $config
     * @param bool $stopOnError
     * @param bool $dryRun
     * @param int $rowNumber
     * @param array $params
     */
    public function __construct(
        public readonly object $config,
        public readonly bool $stopOnError,
        public readonly bool $dryRun,
        public int $rowNumber,
        protected array $params = []
    ) {}

    /**
     * Retourne une nouvelle instance avec un autre numéro de ligne
     *
     * @param int $rowNumber
     * @return $this
     */
    public function withRowNumber(int $rowNumber): static
    {
        try {
            $clone = clone $this;
            $clone->rowNumber = $rowNumber;
            return $clone;
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * Paramètres métier (date d’import, version, etc.)
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function getParam(string $key, mixed $default = null): mixed
    {
        return $this->params[$key] ?? $default;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasParam(string $key): bool
    {
        return array_key_exists($key, $this->params);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function withParam(string $key, mixed $value): static
    {
        try {
            $clone = clone $this;
            $clone->params[$key] = $value;
            return $clone;
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
