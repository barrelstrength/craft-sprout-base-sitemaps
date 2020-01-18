<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbasesitemaps\models;


use barrelstrength\sproutbasesitemaps\SproutBaseSitemaps;
use barrelstrength\sproutbaseuris\models\UrlEnabledSection;
use craft\base\Model;
use craft\helpers\UrlHelper;
use Craft;
use craft\models\Site;
use yii\base\Exception;

/**
 * Class SitemapSection
 *
 * This class is used to manage the ajax updates of the sitemap settings on the
 * sitemap tab. The attributes are a subset of the Metadata
 *
 * @property null|Site              $site
 * @property null|UrlEnabledSection $urlEnabledSection
 */
class SitemapSection extends Model
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $siteId;

    /**
     * @var int
     */
    public $urlEnabledSectionId;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $uri;

    /**
     * @var int
     */
    public $changeFrequency;

    /**
     * @var string
     */
    public $priority;

    /**
     * @var int
     */
    public $enabled;

    // Attributes assigned from URL-Enabled Section integration
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $handle;

    /**
     * @var string
     */
    public $uniqueKey;

// @todo - do we need the following attributes or can we update things and remove them?

    /**
     * @var bool
     */
    public $isNew;

    /**
     * @var \DateTime
     */
    public $dateCreated;

    /**
     * @var \DateTime
     */
    public $dateUpdated;

    /**
     * @var int
     */
    public $uid;

    /**
     * @return Site|null
     */
    public function getSite()
    {
        return Craft::$app->sites->getSiteById($this->siteId);
    }

    /**
     * @return UrlEnabledSection|null
     * @throws \craft\errors\SiteNotFoundException
     */
    public function getUrlEnabledSection()
    {
        $urlEnabledSectionType = SproutBaseSitemaps::$app->sitemaps->getUrlEnabledSectionTypeByType($this->type);
        $urlEnabledSections = $urlEnabledSectionType->urlEnabledSections;

        foreach ($urlEnabledSections as $key => $urlEnabledSection) {
            if ($key === $this->type.'-'.$this->urlEnabledSectionId) {
                return $urlEnabledSection;
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function defineRules(): array
    {
        $rules = parent::defineRules();

        $rules [] = [['uri'], 'sectionUri', 'on' => 'customSection'];
        $rules [] = [['uri'], 'required', 'on' => 'customSection', 'message' => 'URI cannot be blank.'];

        return $rules;
    }

    /**
     * Check is the url saved on custom sections are URI's
     * This is the 'sectionUri' validator as declared in rules().
     *
     * @param $attribute
     *
     * @throws Exception
     */
    public function sectionUri($attribute)
    {
        if (UrlHelper::isAbsoluteUrl($this->$attribute)) {
            $this->addError($attribute, Craft::t('sprout-base-sitemaps', 'Invalid URI. The URI should only include valid segments of your URL that come after the base domain. i.e. {siteUrl}URI', [
                'siteUrl' => UrlHelper::siteUrl()
            ]));
        }
    }
}
