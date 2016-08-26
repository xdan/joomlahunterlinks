<?php
/**
 * @copyright	Copyright (c) 2016 editors. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('JPATH_BASE') or die;
jimport('joomla.form.helper');

class JFormFieldSomeText extends JFormField{
	public function attr($attr_name, $default = null){
		if (isset($this->element[$attr_name])) {
			return $this->element[$attr_name];
		} else {
			return $default;
		}
	}

	function getInput() {
		$sometexts = !empty($this->value) ? array_filter(array_unique(explode('&-|-&', rawurldecode($this->value)))) : [];
        if (!count($sometexts)) {
            $sometexts[] = '';
        }

        $style  = '<style>
            #jhl_sometext {
                margin-top:10px;
                width: 100%;
            }
            .jhl_sometext_item {
                margin-bottom: 10px;
            }
            .jhl_sometext_item textarea{
                width: 100%;
                min-height: 100px;
            }
        </style>';

        $script  = '<script>
            var id = "' . $this->id . '";

            (function ($) {
                $(".jhl_sometext_btn").click(function () {
                    $("#jhl_sometext").append("<div class=\"jhl_sometext_item\"><textarea></textarea></div>");
                });
                $("#jhl_sometext").on("change", "textarea",function () {
                    var text = [];
                    $("#jhl_sometext textarea").each(function () {
                        if (this.value) {                            
                            text.push(this.value);
                        }
                    });
                    $("#" + id).val(encodeURIComponent(text.join("&-|-&")));
                });
            }(jQuery))
        </script>';

        $variants = '';
        foreach ($sometexts as $text) {
            $variants[] = '<div class="jhl_sometext_item"><textarea>' . htmlspecialchars($text) . '</textarea></div>';
        }

        return '
            <div class="btn_box">
                <button type="button" class="btn jhl_sometext_btn" data-class="add">' . jtext::_('PLG_SYSTEM_JOOMLAHUNTERLINKS_ADD') . '</button>
            </div>
            <input type="hidden" value="'. rawurlencode($this->value) .'" name="'.$this->name.'" id="'.$this->id.'"/>
            <div id="jhl_sometext">
                <div id="jhl_sometext_variants">
                    ' . implode(PHP_EOL, $variants) .'
                </div>
            </div>
            ' . $script . $style;
	}
}
