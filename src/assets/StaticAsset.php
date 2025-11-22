<?php
/**
 * StaticAsset.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <david.ghysefree.fr>
 * @version XXX
 * @package app\config
 */

namespace fractalCms\importExport\assets;

use yii\web\AssetBundle;
use yii\web\View;

class StaticAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@fractalCms/importExport/assets/static';

    /**
     * @inheritdoc
     */
    public $css = [
    ];

    /**
     * @inheritdoc
     */
    public $js = [
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
    ];

    /**
     * @inheritdoc
     */
    public $jsOptions = [
        'position' => View::POS_HEAD
    ];
}
