<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DynamicMenuController extends Controller
{
    public function dynamicMenuSettings(Request $request)
    {
        $data['title'] = __('Dynamic Menu Settings');
        if ($request->ajax()) {
            $cp = DynamicMenu::select('*')->orderBy('data_order','ASC');
            return datatables($cp)
                ->addColumn('login_type', function ($item) {
                    return custom_page_type($item->type);
                })
                ->addColumn('actions', function ($item) {
                    $html = '<input type="hidden" value="'.$item->id.'" class="shortable_data">';
                    $html .= '<ul class="d-flex activity-menu">';

                    $html .= ' <li class="viewuser"><a title="Edit" href="' . route('adminCustomPageEdit', $item->id) . '"><i class="fa fa-pencil"></i></a> <span></span></li>';
                    $html .= delete_html('adminCustomPageDelete',encrypt($item->id));
                    $html .=' </ul>';
                    return $html;
                })
                ->rawColumns(['actions'])->make(true);
        }

        return view('admin.settings.dynamic-menu.index',$data);
    }
}
