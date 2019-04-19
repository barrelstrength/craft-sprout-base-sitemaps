<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbasesitemaps;

use barrelstrength\sproutbase\base\BaseSproutTrait;
use barrelstrength\sproutbasesitemaps\controllers\SitemapsController;
use barrelstrength\sproutbasesitemaps\controllers\XmlSitemapController;
use barrelstrength\sproutbasesitemaps\services\App;

use barrelstrength\sproutbasesitemaps\web\twig\variables\SproutSitemapVariable;
use craft\events\RegisterTemplateRootsEvent;
use Craft;
use craft\web\twig\variables\CraftVariable;
use craft\web\View;
use yii\base\Module;
use craft\helpers\ArrayHelper;
use craft\i18n\PhpMessageSource;
use yii\base\Event;

/**
 *
 * @property mixed $cpNavItem
 * @property array $cpUrlRules
 * @property array $siteUrlRules
 */
class SproutBaseSitemaps extends Module
{
    use BaseSproutTrait;

    /**
     * @var string
     */
    public $handle;

    /**
     * Enable use of SproutSeo::$app-> in place of Craft::$app->
     *
     * @var \barrelstrength\sproutbasesitemaps\services\App
     */
    public static $app;

    /**
     * Identify our plugin for BaseSproutTrait
     *
     * @var string
     */
    public static $pluginHandle = 'sprout-base-sitemaps';

    /**
     * @var string|null The translation category that this module translation messages should use. Defaults to the lowercase plugin handle.
     */
    public $t9nCategory;

    /**
     * @var string The language that the module messages were written in
     */
    public $sourceLanguage = 'en-US';

    /**
     * @inheritdoc
     */
    public function __construct($id, $parent = null, array $config = [])
    {
        // Set some things early in case there are any settings, and the settings model's
        // init() method needs to call Craft::t() or Plugin::getInstance().

        $this->handle = 'sprout-base-sitemaps';
        $this->t9nCategory = ArrayHelper::remove($config, 't9nCategory', $this->t9nCategory ?? strtolower($this->handle));
        $this->sourceLanguage = ArrayHelper::remove($config, 'sourceLanguage', $this->sourceLanguage);

        if (($basePath = ArrayHelper::remove($config, 'basePath')) !== null) {
            $this->setBasePath($basePath);
        }

        // Translation category
        $i18n = Craft::$app->getI18n();
        /** @noinspection UnSafeIsSetOverArrayInspection */
        if (!isset($i18n->translations[$this->t9nCategory]) && !isset($i18n->translations[$this->t9nCategory.'*'])) {
            $i18n->translations[$this->t9nCategory] = [
                'class' => PhpMessageSource::class,
                'sourceLanguage' => $this->sourceLanguage,
                'basePath' => $this->getBasePath().DIRECTORY_SEPARATOR.'translations',
                'allowOverrides' => true,
            ];
        }

        // Set this as the global instance of this plugin class
        static::setInstance($this);

        parent::__construct($id, $parent, $config);
    }

    public function init()
    {
        self::$app = new App();
        Craft::setAlias('@sproutbasesitemaps', $this->getBasePath());

        // Setup Controllers
        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            $this->controllerNamespace = 'sproutbasesitemaps\\console\\controllers';
        } else {
            $this->controllerNamespace = 'sproutbasesitemaps\\controllers';

            $this->controllerMap = [
                'sitemaps' => SitemapsController::class,
                'xml-sitemap' => XmlSitemapController::class
            ];
        }

        // Setup Template Roots
        Event::on(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, function(RegisterTemplateRootsEvent $e) {
            $e->roots['sprout-base-sitemaps'] = $this->getBasePath().DIRECTORY_SEPARATOR.'templates';
        });

        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $event) {
            $event->sender->set('sproutSitemap', SproutSitemapVariable::class);
        });

        parent::init();
    }
}
