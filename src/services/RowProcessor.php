<?php
/**
 * RowProcessor.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\services
 */
namespace fractalCms\importExport\services;

use fractalCms\importExport\models\ImportConfig;
use yii\web\NotFoundHttpException;
use Exception;
use Yii;

class RowProcessor
{
    /** @var array $rowProcessors */
    private array $rowProcessors = [];

    /**
     * @param array $config
     * @throws Exception
     */
    public function __construct(array $config)
    {
        try {
            $this->rowProcessors['import'] = $config['import'] ?? [];
            $this->rowProcessors['export'] = $config['export'] ?? [];
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @param string $type
     * @return array
     * @throws Exception
     */
    public function getToList(string $type): array
    {
        try {
            $rowTransformers = [];
            if ($type === ImportConfig::TYPE_IMPORT) {
                $rowTransformers = $this->getImportList();
            } elseif ($type === ImportConfig::TYPE_EXPORT) {
                $rowTransformers = $this->getExportList();
            }
            return $rowTransformers;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getRowProcessors(): array
    {
        try {
            return $this->rowProcessors;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @return array
     */
    public function getImportList(): array
    {
        return $this->format($this->rowProcessors['import']);
    }

    /**
     * @return array
     */
    public function getExportList(): array
    {
        return $this->format($this->rowProcessors['export']);
    }

    private function format(array $items): array
    {
        $values = [];
        foreach ($items as $key => $item) {
            $values[$key] = $item['label'] ?? $key;
        }
        return $values;
    }

    /**
     * @param string $type
     * @param string $key
     * @return mixed
     * @throws Exception
     */
    public function create(string $type, string $key)
    {
        try {
            $item = $type === 'import'
                ? $this->rowProcessors['import'][$key] ?? null
                : $this->rowProcessors['export'][$key] ?? null;

            if ($item === null) {
                throw new NotFoundHttpException('Transformer '.$key.' not found');
            }

            return new $item['class']();
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
