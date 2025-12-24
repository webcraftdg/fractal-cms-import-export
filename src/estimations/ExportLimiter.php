<?php
/**
 * ExportLimiter.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\services
 */
namespace fractalCms\importExport\estimations;

use fractalCms\importExport\models\ImportConfig;
use fractalCms\importExport\models\LimiterModel;
use yii\base\Component;
use Yii;
use Exception;

class ExportLimiter extends Component
{
    public int $maxRows = 100000;
    public int $maxColumns = 100;
    public int $maxEstimatedMb = 500;

    /**
     * @param LimiterModel $limiterModel
     * @return string|null
     * @throws Exception
     */
    public function assertAllowed(LimiterModel $limiterModel): string | null
    {
        try {
            $rows    = $limiterModel->rows ?? 0;
            $columns = $limiterModel->columns ?? 0;
            $mb      = $limiterModel->estimatedMb ?? 0;
            $statementName = $limiterModel->name;

            $message = null;
            if ($rows > $this->maxRows) {
                $message = $this->deny('Export trop volumineux : '.$rows.' lignes', $statementName);
            }
            if ($columns > $this->maxColumns) {
                $message = $this->deny('Export trop large : '.$columns.' colonnes', $statementName);
            }
            if ($mb > $this->maxEstimatedMb) {
                $message = $this->deny('Export estimé à {'.$mb.'} MB, trop lourd pour le navigateur', $statementName);
            }
            return $message;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @param string $reason
     * @param string $statementName
     * @return string
     */
    protected function deny(string $reason, string $statementName): string
    {
        return
             'Export impossible via l’interface web<br/>'
            .$reason.'<br/>'
            .'Merci d\'utiliser la commande CLI :<br/>'
            .'php yii.php fractalCmsImportExport:import-export/index  -name='.$statementName.' -version={versionActive}';
    }
}
