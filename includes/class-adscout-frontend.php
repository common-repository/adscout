<?php
defined('ABSPATH') or die('No script kiddies please!');

class AdScout_Frontend {

    public static function include_partial($partial, $section, $data = true): void
    {

        extract($data);
        ob_start();
        include plugin_dir_path(ADSCOUT_ROOT_FILE) . $section . '/partials/' . $partial . '.php';
        $template = ob_get_clean();
        wp_kses_allowed_html();

        echo wp_kses( apply_filters( 'adscout_forms', $template ), array(
            'form' => array(
                'id' => true,
                'method' => true,
                'action' => true,
                'enctype' => true,
                'class' => true,
            ),
            'input' => array(
                'type' => true,
                'name' => true,
                'value' => true,
                'placeholder' => true,
                'id' => true,
                'class' => true,
                'required' => true,
                'data-init' => true,
                'disabled' => true,
                'checked' => true,
                'readonly' => true,
            ),
            'button' => array(
                'type' => true,
                'name' => true,
                'value' => true,
                'id' => true,
                'class' => true,
            ),
            'submit' => array(
                'type' => true,
                'name' => true,
                'value' => true,
                'id' => true,
                'class' => true,
            ),
            'div' => array(
                'id' => true,
                'class' => true,
            ),
            'h2' => array(
                'id' => true,
                'class' => true,
            ),
            'table' => array(
                'id' => true,
                'class' => true,
            ),
            'tbody' => array(
                'id' => true,
                'class' => true,
            ),
            'tr' => array(
                'id' => true,
                'class' => true,
            ),
            'td' => array(
                'id' => true,
                'class' => true,
            ),
            'th' => array(
                'id' => true,
                'class' => true,
            ),
            'p' => array(
                'id' => true,
                'class' => true,
            ),
        ) );

    }

}