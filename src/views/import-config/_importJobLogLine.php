<?php
/**
 * _importJobLogLine.php
 *
 * PHP Version 8.2+
 *
 * @version XXX
 * @package webapp\views\dafault
 *
 * @var $this yii\web\View
 * @var fractalCms\importExport\exceptions\ImportError $error
 */

use fractalCms\importExport\exceptions\ImportError;

?>
<?php if($error instanceof ImportError):?>
<div class="fc-row-inline">
    <div class="sm:w-1/2">
        <?php
            echo 'ligne n° : '.$error->rowNumber;
        ?>
    </div>
    <div class="sm:w-1/2 overflow-x-auto">
        <?php
        echo $error->message;
        ?>
    </div>
</div>
<?php endif;?>