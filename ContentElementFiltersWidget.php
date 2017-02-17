<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link https://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 17.02.2017
 */
namespace skeeks\cms\contentElementFiltersWidget;
use skeeks\cms\base\WidgetRenderable;
use skeeks\cms\models\CmsContent;
use skeeks\cms\models\CmsContentProperty;
use skeeks\cms\models\searchs\SearchChildrenRelatedPropertiesModel;
use skeeks\cms\models\searchs\SearchRelatedPropertiesModel;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/**
 * @property CmsContent         $cmsContent;
 *
 * Class ContentElementFiltersWidget
 * @package skeeks\cms\contentElementFiltersWidget
 */
class ContentElementFiltersWidget extends WidgetRenderable
{
        /**
         * Настройки изменяемые через панель управления
         */
    /**
     * @var Фильтры по какому контенту нужны
     */
    public $content_id;

    /**
     * @var array по каким дополнительным свойствам
     */
    public $realated_properties                 = [];

    /**
     * @var array Дочерние дополнительные свойства
     */
    public $children_realated_properties        = [];

    
    
    
    
        /**
         * Настройки передаваемые в вызов виджета
         */
    
    /**
     * @var bool Учитывать только доступные фильтры для текущей выборки
     */
    public $only_exists_filters           = false;
    

    /**
     * @var SearchRelatedPropertiesModel
     */
    public $searchRelatedPropertiesModel  = null;

    /**
     * @var SearchChildrenRelatedPropertiesModel
     */
    public $searchOfferRelatedPropertiesModel  = null;

    
    
        /**
         * Служебные свойства
         */
    
    /**
     * @var array (Массив ids записей, для показа только нужных фильтров)
     */
    public $elementIds          = [];

    
    
        /**
         * Стандартная регистрация переводов
         */
    
    /**
     * @var bool
     */
    static public $isRegisteredTranslations = false;

    static public function registerTranslations()
    {
        if (self::$isRegisteredTranslations === false)
        {
            \Yii::$app->i18n->translations['skeeks/content-element-filters'] = [
                'class' => 'yii\i18n\PhpMessageSource',
                'sourceLanguage' => 'en',
                'basePath' => '@skeeks/cms/contentElementFiltersWidget/messages',
                'fileMap' => [
                    'skeeks/content-element-filters' => 'main.php',
                ],
            ];
            self::$isRegisteredTranslations = true;
        }
    }

    
        /**
         * Настройки для режима редактирования виджета
         */
    
    /**
     * @return array
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),
        [
            'content_id'                        => \Yii::t('skeeks/content-element-filters', 'Content'),
            'realated_properties'               => \Yii::t('skeeks/content-element-filters', 'Additional related properties'),
            'children_realated_properties'      => \Yii::t('skeeks/content-element-filters', 'Additional children related properties'),
        ]);
    }

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(),
        [
            [['content_id'], 'integer'],
            [['realated_properties'], 'safe'],
            [['children_realated_properties'], 'safe'],
        ]);
    }

    public function renderConfigForm(ActiveForm $form)
    {
        echo $form->fieldSelect($this, 'content_id', CmsContent::getDataForSelect());
        if ($this->cmsContent)
        {
            echo $form->fieldSelectMulti($this, 'realated_properties', \yii\helpers\ArrayHelper::map($this->cmsContent->cmsContentProperties, 'code', 'name'));
        }

        if ($this->cmsContent)
        {
            //echo $form->fieldSelect($this, 'children_realated_properties', \yii\helpers\ArrayHelper::map($this->cmsContent->cmsContentProperties, 'code', 'name'));
        }
    }

    
        /**
         * Логика работы виджета
         */
    
    
    public function init()
    {
        parent::init();
        static::registerTranslations();

        if (!$this->realated_properties)
        {
            $this->realated_properties = [];
        }

        if ($this->cmsContent)
        {
            $this->searchRelatedPropertiesModel = new SearchRelatedPropertiesModel();
            $this->searchRelatedPropertiesModel->initCmsContent($this->cmsContent);
            $this->searchRelatedPropertiesModel->load(\Yii::$app->request->get());
        }

        if ($this->cmsContent && $this->cmsContent->childrenContents)
        {
            /*$this->searchOfferRelatedPropertiesModel = new SearchChildrenRelatedPropertiesModel();
            $this->searchOfferRelatedPropertiesModel->initCmsContent($this->offerCmsContent);
            $this->searchOfferRelatedPropertiesModel->load(\Yii::$app->request->get());*/
        }
    }



    /**
     * @return CmsContent
     */
    public function getCmsContent()
    {
        return CmsContent::findOne($this->content_id);
    }
    
    /**
     * Рисовать это свойство?
     * 
     * @param $property
     * @return bool
     */
    public function isShowRelatedProperty($property)
    {
        if (!in_array($property->code, $this->realated_properties))
        {
            return false;
        }

        if ($this->only_exists_filters === false)
        {
            return true;
        }

        if (in_array($property->property_type, [\skeeks\cms\relatedProperties\PropertyType::CODE_ELEMENT, \skeeks\cms\relatedProperties\PropertyType::CODE_LIST]))
        {
            $options = $this->getRelatedPropertyOptions($property);
            if (count($options) > 1)
            {
                return true;
            } else
            {
                return false;
            }
        }

        return true;
    }

    /**
     *
     * Максимальное значение свойства
     *
     * @param $property
     * @return null
     */
    public function getMaxValue($property)
    {
        $value = [];

        if ($this->elementIds)
        {
            $value = \skeeks\cms\models\CmsContentElementProperty::find()
                ->select(['value_enum'])
                ->andWhere(['element_id' => $this->elementIds])
                ->andWhere(['property_id' => $property->id])
                ->asArray()
                ->orderBy(['value_enum' => SORT_DESC])
                ->limit(1)
                ->one()
            ;

            return (float) $value['value_enum'];
        }

        return null;
    }

    /**
     *
     * Минимальное значение свойства
     *
     * @param $property
     * @return null
     */
    public function getMinValue($property)
    {
        $value = [];

        if ($this->elementIds)
        {
            $value = \skeeks\cms\models\CmsContentElementProperty::find()
                ->select(['value_enum'])
                ->andWhere(['element_id' => $this->elementIds])
                ->andWhere(['property_id' => $property->id])
                ->asArray()
                ->orderBy(['value_enum' => SORT_ASC])
                ->limit(1)
                ->one()
            ;

            return (float) $value['value_enum'];
        }

        return null;
    }

    protected $_relatedOptions = [];

    /**
     *
     * Получение доступных опций для свойства
     *
     * @param CmsContentProperty $property
     * @return $this|array|\yii\db\ActiveRecord[]
     */
    public function getRelatedPropertyOptions($property)
    {
        $options = [];

        if (isset($this->_relatedOptions[$property->code]))
        {
            return $this->_relatedOptions[$property->code];
        }

        if ($property->property_type == \skeeks\cms\relatedProperties\PropertyType::CODE_ELEMENT)
        {
            $propertyType = $property->handler;

            $availables = [];
            if ($this->elementIds)
            {
                $availables = \skeeks\cms\models\CmsContentElementProperty::find()
                    ->select(['value_enum'])
                    ->indexBy('value_enum')
                    ->andWhere(['element_id' => $this->elementIds])
                    ->andWhere(['property_id' => $property->id])
                    ->asArray()
                    ->all()
                ;

                $availables = array_keys($availables);
            }

            if ($this->only_exists_filters && !$availables)
            {
                return [];
            }

            $options = \skeeks\cms\models\CmsContentElement::find()
                ->active()
                ->andWhere(['content_id' => $propertyType->content_id]);
                if ($this->elementIds)
                {
                    $options->andWhere(['id' => $availables]);
                }

            $options = $options->select(['id', 'name'])->asArray()->all();

            $options = \yii\helpers\ArrayHelper::map(
                $options, 'id', 'name'
            );

        } elseif ($property->property_type == \skeeks\cms\relatedProperties\PropertyType::CODE_LIST)
        {
            $options = $property->getEnums()->select(['id', 'value']);

            $availables = [];
            if ($this->elementIds)
            {
                $availables = \skeeks\cms\models\CmsContentElementProperty::find()
                    ->select(['value_enum'])
                    ->indexBy('value_enum')
                    ->andWhere(['element_id' => $this->elementIds])
                    ->andWhere(['property_id' => $property->id])
                    ->asArray()
                    ->all()
                ;

                $availables = array_keys($availables);
                $options->andWhere(['id' => $availables]);
            }

            if ($this->only_exists_filters && !$availables)
            {
                return [];
            }

            $options = $options->asArray()->all();

            $options = \yii\helpers\ArrayHelper::map(
                $options, 'id', 'value'
            );
        }

        $this->_relatedOptions[$property->code] = $options;

        return $options;
    }




    /**
     * @param ActiveDataProvider $activeDataProvider
     */
    public function search(ActiveDataProvider $activeDataProvider)
    {
        if ($this->only_exists_filters)
        {
            /**
             * @var $query \yii\db\ActiveQuery
             */
            $query  = clone $activeDataProvider->query;
            //TODO::notice errors
            $ids    = $query->select(['*', 'cms_content_element.id as mainId'])->indexBy('mainId')->asArray()->all();

            $this->elementIds = array_keys($ids);
        }

        if ($this->searchRelatedPropertiesModel)
        {
            $this->searchRelatedPropertiesModel->search($activeDataProvider);
        }
    }
}
