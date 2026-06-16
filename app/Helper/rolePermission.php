<?php

use App\Model\Permission;
use Illuminate\Support\Facades\Auth;

if(!function_exists("mainMenuRenderAddon")){
    function mainMenuRenderAddon($route_name,$title,$tab,$tab_compare,$icon,$route_param=NULL): string
    {
        try{
            $active = isset($tab) && $tab == $tab_compare ? 'active-page' : '';
            $route = !empty($route_param) ? route($route_name, $route_param) : route($route_name);
            $imageIcon = asset('assets/admin/images/sidebar-icons/'.$icon);
            return '<li class="'.$active.'">
                        <a href="'.$route.'">
                            <span class="icon"><img src="'.$imageIcon.'" class="img-fluid" alt=""></span>
                            <span class="name">'.$title.'</span>
                        </a>
                    </li>';

        } catch (\Exception $e) {
            storeException('mainMenuRenderAddon', $e->getMessage());
        }
        return '';
    }
}

if(!function_exists("subMenuRendererAddon")){
    function subMenuRendererAddon($menutitle,$tab,$tab_compare,$icon, $sub_menu = []): string
    {
        try{
            if (true) {
                $mainMenuActive = isset($tab) && $tab == $tab_compare ? 'active-page' : '';
                $subMenuActive = isset($tab) && $tab == $tab_compare ? 'mm-show' : '';
                $imageIcon = asset('assets/admin/images/sidebar-icons/'.$icon);
                $subMenus = '';
                if(is_array($sub_menu) && !empty($sub_menu)){
                    foreach($sub_menu as $menu){
                        $route = (isset($menu['route']) ? (isset($menu['route_param']) ?  route($menu['route'],$menu['route_param']) : route($menu['route'])) : '');
                        $title = isset($menu['title']) ? $menu['title'] : '';
                        $tab = isset($menu['tab']) ? $menu['tab'] : '';
                        $tab_compare = isset($menu['tab_compare']) ? $menu['tab_compare'] : '';
                        $active = $tab == $tab_compare ? 'submenu-active' : '';

                        $subMenus .=    '<li class="'.$active.'">
                                            <a href="'.$route.'">'.$title.'</a>
                                        </li>';
                    }
                }
                if($subMenus == '') return '';
                return '<li class="'.$mainMenuActive.'">
                            <a href="#" aria-expanded="true">
                                <span class="icon"><img src="'.$imageIcon.'" class="img-fluid" alt=""></span>
                                <span class="name">'.$menutitle.'</span>
                            </a>
                            <ul class="'.$subMenuActive.'">
                            '.$subMenus.'
                            </ul>
                        </li>';
            }
        } catch (\Exception $e) {
            storeException('subMenuRendererAddon', $e->getMessage());
        }
        return '';
    }
}

if(!function_exists("mainMenuRenderer")){
    function mainMenuRenderer($route_name,$title,$tab,$tab_compare,$icon,$route_param=NULL): string
    {
        try{
            if (checkAdminPermission($route_name,$tab_compare ?? '')) {
                $active = isset($tab) && $tab == $tab_compare ? 'active-page' : '';
                $route = !empty($route_param) ? route($route_name, $route_param) : route($route_name);
                $imageIcon = asset('assets/admin/images/sidebar-icons/'.$icon);
                return '<li class="'.$active.'">
                            <a href="'.$route.'">
                                <span class="icon"><img src="'.$imageIcon.'" class="img-fluid" alt=""></span>
                                <span class="name">'.$title.'</span>
                            </a>
                        </li>';
            }
        } catch (\Exception $e) {
            storeException('mainMenuRenderer', $e->getMessage());
        }
        return '';
    }
}

if(!function_exists("subMenuRenderer")){
    function subMenuRenderer($menutitle,$tab,$tab_compare,$icon, $sub_menu = []): string
    {   // $sub_menu = [
        //      ['route' => 'adminDashboard', 'title' => __('Title'),'tab' => 'Tab', 'tab_compare' => 'Tab', 'route_param' => NULL ]
        // ]
        try{
            if (true) {
                $mainMenuActive = isset($tab) && $tab == $tab_compare ? 'active-page' : '';
                $subMenuActive = isset($tab) && $tab == $tab_compare ? 'mm-show' : '';
                $imageIcon = asset('assets/admin/images/sidebar-icons/'.$icon);
                $subMenus = '';
                if(is_array($sub_menu) && !empty($sub_menu)){
                    foreach($sub_menu as $menu){
                        if(checkAdminPermission(isset($menu['route']) ? $menu['route'] : '',isset($menu['tab']) ? $menu['tab'] : '')){
                            $route = (isset($menu['route']) ? (isset($menu['route_param']) ?  route($menu['route'],$menu['route_param']) : route($menu['route'])) : '');
                            $title = isset($menu['title']) ? $menu['title'] : '';
                            $tab = isset($menu['tab']) ? $menu['tab'] : '';
                            $tab_compare = isset($menu['tab_compare']) ? $menu['tab_compare'] : '';
                            $active = $tab == $tab_compare ? 'submenu-active' : '';

                            $subMenus .=    '<li class="'.$active.'">
                                                <a href="'.$route.'">'.$title.'</a>
                                            </li>';
                        }

                    }
                }
                if($subMenus == '') return '';
                return '<li class="'.$mainMenuActive.'">
                            <a href="#" aria-expanded="true">
                                <span class="icon"><img src="'.$imageIcon.'" class="img-fluid" alt=""></span>
                                <span class="name">'.$menutitle.'</span>
                            </a>
                            <ul class="'.$subMenuActive.'">
                            '.$subMenus.'
                            </ul>
                        </li>';
            }
        } catch (\Exception $e) {
            storeException('subMenuRenderer', $e->getMessage());
        }
        return '';
    }
}

if (!function_exists("checkAdminPermission")) {
    function checkAdminPermission($route,$group){
        if (Auth::user()->super_admin && Auth::user()->role == 1)
            return true;
        $role_id = Auth::user()->role_id;
        if (in_array($group, allowedGroup()))
            return true;
        $permission = Permission::where([
            'role_id' => $role_id,
            'action' => 'View',
            'route' => $route,
        ])->get()->count();
        return ($permission > 0);
    }
}

if (!function_exists("allowedGroup")){
    function allowedGroup()
    {
        return ['dashboard','profile'];
    }
}
