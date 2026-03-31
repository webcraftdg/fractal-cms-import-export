<?php
/**
 * ColumnTransformerService.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\pipeline\services
 */
namespace fractalCms\importExport\pipeline\services;

use fractalCms\importExport\interfaces\ColumnTransformer as TransformInterface;
use Exception;
use Yii;

class ColumnTransformerService
{
    /** @var TransformInterface[] */
    private array $transformers = [];

    /**
     * @param iterable $transformers
     * @throws Exception
     */
    public function __construct(iterable $transformers)
    {
        try {
            foreach ($transformers as $transformer) {
                $this->transformers[$transformer->getName()] = $transformer;
            }
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @param string $name
     * @param mixed $value
     * @param array $options
     * @return mixed
     * @throws Exception
     */
    public function apply(string $name, mixed $value, mixed $options = []): mixed
    {
        try {
            $newValue = $value;
            if (empty($name) === false && isset($this->transformers[$name]) === true) {
                $newValue = $this->transformers[$name]->transform($value, $options);
            }
            return $newValue;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getTransformers(): array
    {
        try {
            return $this->transformers;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
