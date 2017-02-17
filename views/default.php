<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 13.10.2015
 */
/* @var $this yii\web\View */
/* @var $widget \skeeks\cms\contentElementFiltersWidget\ContentElementFiltersWidget */
?>

<?
$this->registerJs(<<<JS
(function(sx, $, _)
{
    sx.classes.FiltersForm = sx.classes.Component.extend({
        _onDomReady: function()
        {
            var self = this;
            this.JqueryForm = $("#sx-filters-form");

            $("input, checkbox, select", this.JqueryForm).on("change", function()
            {
                self.JqueryForm.submit();
            });
        },
    });

    new sx.classes.FiltersForm();
})(sx, sx.$, sx._);
JS
)
?>
<? $form = \yii\widgets\ActiveForm::begin([
    'options' =>
    [
        'id' => 'sx-filters-form',
        'data-pjax' => '1'
    ],
    'method' => 'get',
    'action' => "/" . \Yii::$app->request->getPathInfo(),
]); ?>

    <? if ($widget->searchRelatedPropertiesModel) : ?>
        <? if ($properties = $widget->searchRelatedPropertiesModel->properties) : ?>

            <? foreach ($properties as $property) : ?>
                <? if ($widget->isShowRelatedProperty($property)) : ?>

                <? if (in_array($property->property_type, [\skeeks\cms\relatedProperties\PropertyType::CODE_ELEMENT, \skeeks\cms\relatedProperties\PropertyType::CODE_LIST]) ) : ?>

                        <?= $form->field($widget->searchRelatedPropertiesModel, $property->code)->checkboxList(
                            $widget->getRelatedPropertyOptions($property)
                            , ['class' => 'sx-filters-checkbox-options']); ?>

                <? elseif ($property->property_type == \skeeks\cms\relatedProperties\PropertyType::CODE_NUMBER) : ?>
                    <?
                        $nameProperty = $widget->searchRelatedPropertiesModel->getAttributeNameRangeFrom($property->code);
                        if (!$widget->searchRelatedPropertiesModel->$nameProperty)
                        {
                            $widget->searchRelatedPropertiesModel->$nameProperty = $widget->getMinValue($property);
                        }

                        $namePropertyTo = $widget->searchRelatedPropertiesModel->getAttributeNameRangeTo($property->code);
                        if (!$widget->searchRelatedPropertiesModel->$namePropertyTo)
                        {
                            $widget->searchRelatedPropertiesModel->$namePropertyTo = $widget->getMaxValue($property);
                        }
                    ?>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($widget->searchRelatedPropertiesModel, $nameProperty)->textInput([
                                    'placeholder' => \Yii::t('skeeks/content-element-filters', 'From')
                                ])->label(
                                    $property->name . ""
                                ); ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($widget->searchRelatedPropertiesModel, $namePropertyTo)->textInput([
                                    'placeholder' => \Yii::t('skeeks/content-element-filters', 'To'),
                                    'value' => $widget->getMaxValue($property)
                                ])->label("&nbsp;"); ?>
                            </div>
                        </div>
                    </div>

                <? else : ?>

                    <? $propertiesValues = \skeeks\cms\models\CmsContentElementProperty::find()->select(['value'])->where([
                        'property_id' => $property->id,
                        'element_id'  => $widget->elementIds
                    ])->all(); ?>

                    <? if ($propertiesValues) : ?>
                        <div class="row">
                            <div class="col-md-12">

                            <?= $form->field($widget->searchRelatedPropertiesModel, $property->code)->dropDownList(
                                \yii\helpers\ArrayHelper::merge(['' => ''], \yii\helpers\ArrayHelper::map(
                                    $propertiesValues, 'value', 'value'
                                )))
                            ; ?>

                            </div>
                        </div>
                    <? endif; ?>
                <? endif; ?>
            <? endif; ?>


            <? endforeach; ?>
        <? endif; ?>
    <? endif; ?>

    <button class="btn btn-primary"><?= \Yii::t('skeeks/content-element-filters', 'Apply');?></button>

<? \yii\widgets\ActiveForm::end(); ?>
