<?php
/**
 * RowTransformer.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\services
 */
namespace fractalCms\importExport\services;

use fractalCms\importExport\interfaces\RowTransformer as RowTransformInterface;
use Exception;
use Yii;

class RowTransformer
{
    /** @var RowTransformInterface[] */
    private array $rowTransformers = [];

    /**
     * @param iterable $rowTransformers
     * @throws Exception
     */
    public function __construct(iterable $rowTransformers)
    {
        try {
            foreach ($rowTransformers as $rowTransformer) {
                $this->rowTransformers[$rowTransformer->getName()] = $rowTransformer;
            }
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @param string $name
     * @return RowTransformInterface|null
     * @throws Exception
     */
    public function get(string $name): RowTransformInterface | null
    {
        try {
            $rowTransformer = null;
            if (empty($name) === false && isset($this->rowTransformers[$name]) === true) {
                $rowTransformer = $this->rowTransformers[$name];
            }
            return $rowTransformer;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getRowTransformers(): array
    {
        try {
            return $this->rowTransformers;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getRowTransformersToList(): array
    {
        try {
            $rowTransformersList = [];
            foreach ($this->rowTransformers as $rowTransformer) {
                $rowTransformersList[$rowTransformer->getName()] = $rowTransformer->getName();
            }
            return $rowTransformersList;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
