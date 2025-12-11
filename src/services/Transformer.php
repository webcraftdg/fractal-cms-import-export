<?php
/**
 * Transform.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\services
 */
namespace fractalCms\importExport\services;

use fractalCms\importExport\interfaces\Transformer as TransformInterface;
use Exception;
use Yii;

class Transformer
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
    public function apply(string $name, mixed $value, array $options = []): mixed
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
