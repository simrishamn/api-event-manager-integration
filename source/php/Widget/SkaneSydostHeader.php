<?php

namespace EventManagerIntegration\Widget;

class SkaneSydostHeader extends \WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'skandesydost_header',
            __('Skanesydost header widget', 'event-integration'),
            array(
                "description" => __('Display a clickable header with two text fields', 'event-integration')
            )
        );
    }

    /**
    * Outputs the content for the current Display Events widget instance.
    *
    * @param array $args     Widget arguments.
    * @param array $instance Saved values from database.
    */
    public function widget($args, $instance)
    {
        if ( ! isset( $args['widget_id'] ) ) {
            $args['widget_id'] = $this->id;
        }

        $mainTitle = !empty($instance['main-title']) ? $instance['main-title'] : '';
        $subTitle = !empty($instance['sub-title']) ? $instance['sub-title'] : '';
        $url = \home_url();

        echo "<div class=\"grid-xs-auto c-header__item widget widget_text skandesydost-header\">";
        echo "<a href=\"{$url}\" class=\"skandesydost-header__link\">";
        echo "<h3>{$mainTitle}</h3>";
        if (!empty($subTitle)) {
            echo "<div class=\"textwidget\"><p>{$subTitle}</p></div>";
        }
        echo '</a>';
        echo '</div>';
    }

    /**
    * Handles updating the settings for the current widget instance.
    *
    * @param array $new_instance Values just sent to be saved.
    * @param array $old_instance Previously saved values from database.
    *
    * @return array Updated safe values to be saved.
    */
    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['main-title'] = (!empty($new_instance['main-title'])) ? strip_tags($new_instance['main-title']) : '';
        $instance['sub-title'] = (!empty($new_instance['sub-title'])) ? strip_tags($new_instance['sub-title']) : '';
        return $instance;
    }

    /**
    * Outputs the settings form for the widget.
    *
    * @param array $instance Previously saved values from database.
    */
    public function form($instance)
    {
        $mainTitleId = 'main-title';
        $subTitleId = 'sub-title';

        $mainTitle = esc_attr(!empty( $instance[$mainTitleId] ) ? $instance[$mainTitleId] : '');
        $subTitle = esc_attr(!empty( $instance[$subTitleId] ) ? $instance[$subTitleId] : '');

        $mainTitleFieldMetaData = $this->formFieldInfo($mainTitleId, 'Main header title:');
        $subTitleFieldMetaData = $this->formFieldInfo($subTitleId, 'Sub header title:');

        $this->renderFormField($mainTitleFieldMetaData, $mainTitle);
        $this->renderFormField($subTitleFieldMetaData, $subTitle);
    }

    private function formFieldInfo($id, $labelId) {
        $fieldId = esc_attr($this->get_field_id($id));
        $fieldName = esc_attr($this->get_field_name($id));
        $fieldLabel = __($labelId, 'event-integration');

        return [$fieldId, $fieldName, $fieldLabel];
    }

    private function renderFormField($formFieldMetaData, $currentValue) {
        list($fieldId, $fieldName, $fieldLabel) = $formFieldMetaData;

        echo <<<EOWF
<p>
    <label for="{$fieldId}">
        {$fieldLabel}
    </label>
    <input class="widefat" id="{$fieldId}" name="{$fieldName}" type="text" value="{$currentValue}">
</p>
EOWF;
    }

}
