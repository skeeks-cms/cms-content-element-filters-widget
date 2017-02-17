Widget filters content element for SkeekS CMS
===================================

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist skeeks/cms-content-element-filters-widget "*"
```

or add

```
"skeeks/cms-content-element-filters-widget": "*"
```

add

```
"repositories": [
    {
        "type": "git",
        "url":  "https://github.com/skeeks-cms/cms-content-element-filters-widget.git"
    }
]
```

Example filter elements list
----------

```php

<? $filters = \skeeks\cms\contentElementFiltersWidget\ContentElementFiltersWidget::beginWidget('filters', [
    'only_exists_filters' => true,
]);
    $filters->viewFile = '@app/views/widgets/ContentElementFiltersWidget/second';
?>

    <?
        $list = new \skeeks\cms\cmsWidgets\contentElements\ContentElementsCmsWidget([
            'namespace'         => 'campaign',
            'viewFile'          => '@app/views/widgets/ContentElementsCmsWidget/photos',
        ]);

        $filters->search($list->dataProvider);
        $listContent = $list->run();
    ?>

<? \skeeks\cms\contentElementFiltersWidget\ContentElementFiltersWidget::end(); ?>

```

Example filter elements list
----------

```php

<? $filters = \skeeks\cms\contentElementFiltersWidget\ContentElementFiltersWidget::beginWidget('filters-home', [
    'only_exists_filters' => true,
]);
    $ids = \skeeks\cms\models\CmsContentElement::find()
        ->where(['content_id' => \skeeks\cms\models\CmsContent::find()->where(['code' => 'campaign'])->one()->id])
        ->asArray()->select('id')->indexBy('id')->all();

    if ($ids)
    {
        $ids = array_keys($ids);
        $filters->elementIds = $ids;
    }

    $filters->viewFile = '@app/views/widgets/ContentElementFiltersWidget/home';
?>
<? \skeeks\cms\contentElementFiltersWidget\ContentElementFiltersWidget::end(); ?>

```


##Links
* [Web site](https://cms.skeeks.com)
* [Author](https://skeeks.com)

___

> [![skeeks!](https://gravatar.com/userimage/74431132/13d04d83218593564422770b616e5622.jpg)](https://skeeks.com)
<i>SkeekS CMS (Yii2) — quickly, easily and effectively!</i>  
[skeeks.com](https://skeeks.com) | [cms.skeeks.com](https://cms.skeeks.com)


