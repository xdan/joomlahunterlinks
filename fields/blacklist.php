<?php
/**
 * @copyright	Copyright (c) 2016 editors. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('JPATH_BASE') or die;
jimport('joomla.form.helper');

class JFormFieldBlacklist extends JFormField{
	public function attr($attr_name, $default = null){
		if (isset($this->element[$attr_name])) {
			return $this->element[$attr_name];
		} else {
			return $default;
		}
	}

	function getInput() {
		$data = explode(';|;', $this->value);
		$links = isset($data[0]) ? array_filter(array_unique(explode('&|&', $data[0]))) : [];
		$blacklist = isset($data[1]) ?  array_filter(array_unique(explode('&|&', $data[1]))) : [];

        $linkslist = array();
        $blslist = array();

        $style  = '<style>
            #jhl_blacklist {
                display: table;
                width: 100%;
            }
            #jhl_blacklist_manage,
            #jhl_blacklist_links,
            #jhl_blacklist_blaclist {
                display: table-cell;
                width: 45%;
            }
            #jhl_blacklist_manage {
                width: 10%;
                text-align: center;
            }
            .jhl_blacklist_item {
                padding: 5px;
                border-bottom:1px solid rgba(204, 204, 204, 0.51);
                cursor: pointer;
            }

            .jhl_blacklist_item:hover {
                background: rgba(3, 151, 244, 0.17);
            }

            .jhl_blacklist_item.active {
                background: rgba(3, 244, 3, 0.17);
            }
        </style>';

        $script  = '<script>
            var id = "' . $this->id . '";

            (function ($) {
                var setData = function (data) {
                        var value = data.links.join("&|&") + ";|;" + data.black.join("&|&");
                        $("#" + id).val(value);
                    },
                    getData = function () {
                        var value = $("#" + id).val();
                        value = value.split(";|;");
                        return {
                            links: value[0] !== undefined ? value[0].split("&|&") : [],
                            black: value[1] !== undefined ? value[1].split("&|&") : [],
                        };
                    };
                $(".jhl_blacklist_item").on("click", function () {
                    $(this).toggleClass("active");
                });
                $(".jhl_blacklist_btn").on("click", function () {
                    switch ($(this).data("class")) {
                    case "select_all":
                        $(".jhl_blacklist_item").addClass("active");
                    break;
                    case "clear_all":
                        $(".jhl_blacklist_item").removeClass("active");
                    break;
                    case "to":
                        var data = getData();
                        $("#jhl_blacklist_links .jhl_blacklist_item.active").each(function () {
                            var link = decodeURIComponent($(this).data("link"));
                            if (data.black.indexOf(link) === -1) {
                                debugger;
                                data.black.push(link);
                                data.links.splice(data.links.indexOf(link), 1);
                                $("#jhl_blacklist_blaclist").append(this);
                            }
                        });
                        setData(data);
                    break;
                    case "remove":
                        var data = getData();
                        $(".jhl_blacklist_item.active").each(function () {
                            var link = decodeURIComponent($(this).data("link"));
                            if (this.parentNode.id === "jhl_blacklist_blaclist") {
                                data.black.splice(data.black.indexOf(link), 1);
                            } else {
                                data.links.splice(data.links.indexOf(link), 1);
                            }
                            $(this).remove();
                        });
                        setData(data);
                    break;
                    case "from":
                        var data = getData();
                        $("#jhl_blacklist_blaclist .jhl_blacklist_item.active").each(function () {
                            var link = decodeURIComponent($(this).data("link"));
                            if (data.links.indexOf(link) === -1) {
                                debugger;
                                data.links.push(link);
                                data.black.splice(data.black.indexOf(link), 1);
                                $("#jhl_blacklist_links").append(this);
                            }
                        });
                        setData(data);
                    break;
                    }
                });
            }(jQuery))
        </script>';

        foreach ($links as $link) {
            $linkslist[] = '<div data-link="'.rawurlencode($link).'" class="jhl_blacklist_item">' . $link . '</div>';
        }
        foreach ($blacklist as $link) {
            $blslist[] = '<div data-link="'.rawurlencode($link).'" class="jhl_blacklist_item">' . $link . '</div>';
        }

        return '
            <div class="btn_box">
                <button type="button" class="btn jhl_blacklist_btn" data-class="select_all">' . jtext::_('JALL') . '</button>
                <button type="button" class="btn jhl_blacklist_btn" data-class="clear_all">' . jtext::_('JCLEAR') . '</button>
            </div>

            <input type="hidden" value="'. addslashes($this->value) .'" name="'.$this->name.'" id="'.$this->id.'"/>
            <div id="jhl_blacklist">
                <div id="jhl_blacklist_links">
                    ' . implode(PHP_EOL, $linkslist) .'
                </div>
                <div id="jhl_blacklist_manage">
                    <button type="button" class="btn jhl_blacklist_btn" data-class="to">&gt;&gt;</button><br><br>
                    <button type="button" class="btn jhl_blacklist_btn" data-class="remove">&times;</button><br><br>
                    <button type="button" class="btn jhl_blacklist_btn" data-class="from">&lt;&lt;</button><br><br>
                </div>
                <div id="jhl_blacklist_blaclist">
                    ' . implode(PHP_EOL, $blslist) .'
                </div>
            </div>
            ' . $script . $style;
	}
}
