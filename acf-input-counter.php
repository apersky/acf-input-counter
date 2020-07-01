<?php
/*
    Plugin Name: ACF Input Counter
    Plugin URI: https://github.com/apersky/acf-input-counter
    Description: Show character count for limited text and textarea fields in ACF. This is a fork of https://github.com/Hube2/acf-input-counter, specifically modified to suite the needs of 5 Fold Marketing.
    Version: 1.0.0
    Author: Alexander Persky
    Author URI: https://github.com/apersky
    Text-domain: acf-counter
    License: GPL
*/

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class acf_input_counter
{
    private $version = '1.0.0';

    public function __construct()
    {
        add_action('acf/render_field/type=text', [$this, 'render_field'], 20, 1);
        add_action('acf/render_field/type=textarea', [$this, 'render_field'], 20, 1);
        add_action('acf/input/admin_enqueue_scripts', [$this, 'scripts']);
    } // end public function __construct

    private function run()
    {
        // Cannot run on field group editor or it will add code to every ACF field in the editor
        $run = true;
        global $post;

        if ($post && $post->ID && get_post_type($post->ID) == 'acf-field-group') {
            $run = false;
        }

        return $run;
    } // end private function run

    public function scripts()
    {
        if (!$this->run()) {
            return;
        }

        wp_enqueue_script('acf-input-counter', plugin_dir_url(__FILE__) . 'acf-input-counter.js', ['acf-input'], $this->version, false);
    } // end public function scripts

    public function render_field($field)
    {
        // Only run on text and text area fields when maxlength is set
        if (!$this->run() || !$field['maxlength'] || ($field['type'] != 'text' && $field['type'] != 'textarea')) {
            return;
        }

        if (function_exists('mb_strlen')) {
            $len = mb_strlen($field['value']);
        } else {
            $len = strlen($field['value']);
        }

        $max = $field['maxlength'];
        $display = sprintf(__('Chars: %1$s/%2$s', 'acf-counter'), '%%len%%', '%%max%%');
        $display = str_replace('%%len%%', '<span class="count">' . $len . '</span>', $display);
        $display = str_replace('%%max%%', $max, $display);
        $display = str_replace('%%remain%%', ($max - $len), $display);
        ?>

        <span class="char-count" style="font-size: 10px; font-style: italic; line-height: 1;">
			<?php echo $display; ?>
		</span>
    <?php
    } // end public function render_field
} // end class acf_input_counter

new acf_input_counter();
