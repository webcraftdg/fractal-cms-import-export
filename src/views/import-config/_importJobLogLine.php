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
 * * @var array $importJobLog
 */

?>
<div class="fc-row-inline">
    <div class="sm:w-1/2">
        <?php
            echo 'colonne n° : '.$importJobLog['row'];
        ?>
    </div>
    <div class="sm:w-1/2">
        <?php
        echo $importJobLog['message'];
        ?>
    </div>
</div>
