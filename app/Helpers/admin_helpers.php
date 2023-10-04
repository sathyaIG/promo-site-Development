<?php

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;




/*
 * Admin Base URL
 */

if (!function_exists('getHost')) {

    function getHost()
    {

        return  env('APP_URL', "");
    }
}

/*
 * Menu bar start
 */

if (!function_exists('get_admin_menu')) {

    function get_admin_menu($menu, $is_home = FALSE)
    {

        $menu_array = array();
        $i = 0;

        foreach ($menu as $key => $value) {
            $menu_array[$value->parent_id][$i]['id'] = $value->id;
            $menu_array[$value->parent_id][$i]['name'] = $value->name;
            $menu_array[$value->parent_id][$i]['link'] = $value->link;
            $menu_array[$value->parent_id][$i]['icon'] = $value->icon;
            $menu_array[$value->parent_id][$i]['is_parent'] = $value->is_parent;
            $menu_array[$value->parent_id][$i]['parent_id'] = $value->parent_id;
            $menu_array[$value->parent_id][$i]['sort_order'] = $value->sort_order;
            $i++;
        }
        $html = "";

        $html .= '<ul id="side-menu" class="navbar-nav active">';


        if (count($menu_array) > 0) {

            foreach ($menu_array[0] as $key => $value) {

                $target = "_self";
                $href = "#";

                if ($value['is_parent'] != 0) {

                    $href = "javascript:void(0)";
                    $link_name = $value['name'];
                    $link_icon = $value['icon'];

                    $html .= '<li class="nav-item dropdown"><a class="nav-link arrow-none" id="topnav-dashboard link_' . encryptId($value['id']) . '" href="#' . encryptId($value['id']) . '" data-bs-toggle="collapse"><i class="' . $link_icon . '"></i><span > ' . $link_name . '</span> <span class="menu-arrow"></span></a>';

                    if ($value['is_parent'] == '1' && isset($menu_array[$value['id']])) {

                        $parentdetails = array(
                            'id' => $value['id'],
                            'name' => $value['name'],
                            'target_id' => encryptId($value['id'])
                        );

                        $html .= get_admin_menuchild($menu_array[$value['id']], $menu_array, $parentdetails);
                    }

                    $html .= '</li>';
                } else {

                    $href = admin_url($value['link']);
                    $link_name = $value['name'];
                    $link_icon = $value['icon'];

                    $html .= '<li class="nav-item dropdown"><a class="nav-link arrow-none" id="topnav-dashboard link_' . encryptId($value['id']) . '" href="' . $href . '"><i class="' . $link_icon . '"></i><span> ' . $link_name . '</span> </a></i>';
                }
            }
        }
        $html .= '</ul>';
        return $html;
    }
}

if (!function_exists('get_admin_menuchild')) {

    function get_admin_menuchild($menu, $menu_array, $parent)
    {

        $id = $parent['id'];

        $string = "";

        $string .= '<div class="collapse" id="' . $parent['target_id'] . '"><ul class="nav-second-level">';

        foreach ($menu as $key => $value) {

            $target = "_self";
            $href = "#";

            if ($value['is_parent'] == '1') {
                $string .= '<li><a  id="link_' . encryptId($value['id']) . '" href="' . $href . '"><span> ' .  $value["name"] . '</span><span class="menu-arrow"></span> </a></i>';


                $parentdetails = array(
                    'id' => $value['id'],
                    'name' => $value['name'],
                );
                $string .= get_admin_menuchild($menu_array[$value['id']], $menu_array, $parentdetails);
            } else {
                // dd( $string);
                $string .= '<li><a  id="link_' . encryptId($value['id']) . '" href="' . admin_url($value["link"]) . '"><span> ' . $value["name"] . '</span> </a></i>';
            }
            $string .= '</li>';
        }
        $string .= '</ul></div>';

        return $string;
    }
}



/*
 * Menu bar End
 */
