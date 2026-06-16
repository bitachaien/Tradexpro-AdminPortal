<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\ProgressStatusRequest;
use App\Http\Services\ProgressStatusService;
use App\Model\ProgressStatus;
use App\Http\Repositories\SettingRepository;

class ProgressStatusController extends Controller
{
    private $adminSettingService;
    private $settingRepo;

    public function __construct()
    {
        $this->service = new ProgressStatusService();
        $this->settingRepo = new SettingRepository();
    }
    public function progressStatusList(Request $request)
    {
        $data['title'] = __('Progress Status List');
        if ($request->ajax()) {
            $data['items'] = ProgressStatus::orderBy('id', 'desc');
            return datatables()->of($data['items'])
                ->addColumn('type', function ($item) {
                    return !empty($item->progress_type_id)?progressStatusType($item->progress_type_id):'';
                })
                ->addColumn('status', function ($item) {
                    return status($item->status);
                })
                ->addColumn('actions', function ($item) {
                    return '<ul class="d-flex activity-menu">
                        <li class="viewuser"><a href="' . route('progressStatusEdit', $item->id) . '"><i class="fa fa-pencil"></i></a> </li>
                        <li class="deleteuser"><a href="' . route('progressStatusDelete', $item->id) . '"><i class="fa fa-trash"></i></a></li>
                        </ul>';
                })
                ->rawColumns(['actions','status'])
                ->make(true);
        }

        return view('admin.progress-status.list', $data);
    }

    public function progressStatusAdd()
    {
        $data['title']=__('Add New Progress Status');
        return view('admin.progress-status.addEdit',$data);
    }

    public function progressStatusSave(ProgressStatusRequest $request)
    {
        $data = [
            'title' => $request->title,
            'progress_type_id' => $request->progress_type_id,
            'status' => $request->status,
            'description' => $request->description,
        ];
        $id = null;
        if(!empty($request->edit_id))
        {
            $id = $request->edit_id;
        }
        $response = $this->service->saveProgressStatus($id, $data);
        
        return redirect()->route('progressStatusList')->with(['success'=>$response['message']]);
    }

    public function progressStatusEdit($id)
    {
        $data['title']=__('Update Progress Status');
        $data['item']=ProgressStatus::findOrFail($id);
        return view('admin.progress-status.addEdit',$data);
    }

    public function progressStatusDelete($id)
    {
        $response = $this->service->deleteProgressStatus($id);
        return redirect()->route('progressStatusList')->with(['success'=>$response['message']]);
    }

    public function progressStatusSettings()
    {
        $data['title'] = __('Progress Status Settings');
        $data['settings'] = allsetting(); 
        return view('admin.progress-status.settings', $data);
    }

    public function progressStatusSettingsUpdate(Request $request)
    {
        try {
                $response = $this->settingRepo->saveAdminSetting($request);
                if ($response['success'] == true) {
                    return redirect()->route('progressStatusSettings')->with('success', __('Progress Status updated successfully'));
                } else {
                    return redirect()->route('progressStatusSettings')->withInput()->with('success', __('Progress Status not updated'));
                }
            } catch(\Exception $e) {
                storeException("saveProgressStatus",$e->getMessage());
                return redirect()->back()->with(['dismiss' => $e->getMessage()]);
            }
    }
}
