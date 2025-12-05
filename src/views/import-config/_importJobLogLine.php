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
<div class="row mt-1 border border-danger">
    <div class="col-sm-6">
        <?php
            echo 'colonne n° : '.$importJobLog['row'];
        ?>
    </div>
    <div class="col-sm-6">
        <?php
        echo $importJobLog['message'];
        ?>
    </div>
</div>
