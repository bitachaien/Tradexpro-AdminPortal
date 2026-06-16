<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LanguageRequest;
use App\Http\Services\AdminLangService;
use App\Model\LangName;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class AdminLangController extends Controller
{
    private $service;
    function __construct()
    {
        $this->service = new AdminLangService();
    }
    /*
   *
   * adminLanguageList
   * Show the list of specified resource.
   * @return \Illuminate\Http\Response
   *
   */
    public function adminLanguageList()
    {
        $data['title'] = __('Language List');
        $data['items'] = LangName::get();

        return view('admin.lang.list', $data);
    }
    /*
     * New language Add
     *
     * Show the Empty form.
     * @return \Illuminate\Http\Response
     */
    public function adminLanguageAdd(){
        $data['title'] = __(' Add New Language');
        return view('admin.lang.addEdit',$data);
    }
    /*
     * Specific language Edit Form
     *
     * Show the Specific Currency form.
     * @return \Illuminate\Http\Response
     */
    public function adminLanguageEdit($id){
        $data['title'] = __('Language Edit');
        $data['item'] = LangName::find($id);
        return view('admin.lang.addEdit',$data);
    }

    public function adminLanguageDelete($id){
        $lang = LangName::find($id);
        if ($lang) {
            $lang->delete();
            return redirect()->back()->with("success",__('Deleted successfully'));
        } else {
            return redirect()->back()->with("dismiss",__('Data not found'));
        }

    }
    /*
     * language Add Edit
     *
     * @return \Illuminate\Http\Response
     */
    public function adminLanguageSave(LanguageRequest $request){
        $response = $this->service->languageAddEdit($request);
        if($response["success"]) return redirect()->route("adminLanguageList")->with("success",$response["message"]);
        return redirect()->route("adminLanguageList")->with("dismiss",$response["message"]);
    }
    /*
     * language item status update
     *
     * @return \Illuminate\Http\Response json
     */
    public function adminLangStatus(Request $request){
        $response = $this->service->languageStatusUpdate($request->active_id);
        return response()->json(["success" => $response]);
    }

    public function adminLanguageSynchronize()
    {
        try{
            // exec('php artisan translation:sync-missing-translation-keys');
            Artisan::call('translation:sync-missing-translation-keys');
            $message = __('All Language Key is synchronized successfully!');
            return redirect()->back()->with(['success' => $message]);
        }catch (Exception $e){
            storeException("adminLanguageSynchronize",$e->getMessage());
        }
    }
}
