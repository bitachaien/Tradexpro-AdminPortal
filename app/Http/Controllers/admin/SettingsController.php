<?php

namespace App\Http\Controllers\admin;

use App\Model\Buy;
use App\Model\Faq;
use App\Model\Coin;
use App\Model\Sell;
use App\Model\FaqType;
use App\Model\LangName;
use App\Model\ContactUs;
use App\Model\CustomPage;
use App\Model\AdminSetting;
use Illuminate\Http\Request;
use App\Http\Services\MailService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CaptchaRequest;
use App\Http\Services\FaqTypeService;
use Illuminate\Support\Facades\Validator;
use App\Http\Services\AdminSettingService;
use App\Http\Repositories\SettingRepository;
use App\Services\TradingBotServices\BotOrderService;

class SettingsController extends Controller
{
    private $settingRepo;
    private $faqTypeService;
    private $faqService;
    private $adminSettingService;
    public function __construct()
    {
        $this->settingRepo = new SettingRepository();
        $this->faqTypeService = new FaqTypeService();
        $this->adminSettingService = new AdminSettingService();
    }

    // admin setting
    public function adminSettings(Request $request)
    {
        $data['tab'] = 'general';
        if (isset($_GET['tab'])) {
            $data['tab'] = $_GET['tab'];
        }
        $data['title'] = __('General Settings');
        $data['settings'] = allsetting();
        $data['languages'] = LangName::where(['status' => STATUS_ACTIVE])->get();
        $data['coins'] = Coin::whereStatus(STATUS_ACTIVE)->get();

        return view('admin.settings.general', $data);
    }

    // admin coin api setting
    public function adminCoinApiSettings(Request $request)
    {
        $data['tab'] = 'payment';
        if (isset($_GET['tab'])) {
            $data['tab'] = $_GET['tab'];
        }
        $data['title'] = __('Coin Api Settings');
        $data['settings'] = allsetting();

        return view('admin.settings.api.general', $data);
    }


    // admin common settings save process
    public function adminCommonSettings(Request $request)
    {
        $file_size = (ADMIN_SETTINGS_ARRAY['upload_max_size'] ?? 2) * 1024;
        $rules = [
            'company_name' => 'required',
            'exchange_url' => 'required',
        ];
        //        $messages=[];
        if (!empty($request->logo)) {
            $rules['logo'] = "image|mimes:jpg,jpeg,png|max:$file_size";
        }
        if (!empty($request->favicon)) {
            $rules['favicon'] = "image|mimes:jpg,jpeg,png|max:$file_size";
        }
        if (!empty($request->login_logo)) {
            $rules['login_logo'] = "image|mimes:jpg,jpeg,png|max:$file_size";
        }
        if (!empty($request->coin_price)) {
            $rules['coin_price'] = 'numeric';
        }
        if (!empty($request->number_of_confirmation)) {
            $rules['number_of_confirmation'] = 'integer';
        }
        if (!empty($request->trading_price_tolerance)) {
            $rules['trading_price_tolerance'] = 'numeric';
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = [];
            $e = $validator->errors()->all();
            foreach ($e as $error) {
                $errors[] = $error;
            }
            $data['message'] = $errors;

            return redirect()->route('adminSettings', ['tab' => 'general'])->with(['dismiss' => $errors[0]]);
        }
        try {
            if ($request->post()) {
                $response = $this->settingRepo->saveCommonSetting($request);
                if ($response['success'] == true) {
                    return redirect()->route('adminSettings', ['tab' => 'general'])->with('success', $response['message']);
                } else {
                    return redirect()->route('adminSettings', ['tab' => 'general'])->withInput()->with('success', $response['message']);
                }
            }
        } catch (\Exception $e) {
            storeException('adminCommonSettings', $e->getMessage());
            return redirect()->back()->with(['dismiss' => $e->getMessage()]);
        }
        return back();
    }

    // admin email setting save
    public function adminSaveEmailSettings(Request $request)
    {
        if ($request->post()) {
            $rules = [
                'mail_driver' => 'required',
                'mail_host' => 'required'
                ,
                'mail_port' => 'required'
                ,
                'mail_username' => 'required'
                ,
                'mail_password' => 'required'
                ,
                'mail_encryption' => 'required'
                ,
                'mail_from_address' => 'required'
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $errors = [];
                $e = $validator->errors()->all();
                foreach ($e as $error) {
                    $errors[] = $error;
                }
                $data['message'] = $errors;
                return redirect()->route('adminSettings', ['tab' => 'email'])->with(['dismiss' => $errors[0]]);
            }

            try {
                $response = $this->settingRepo->saveEmailSetting($request);
                if ($response['success'] == true) {
                    return redirect()->route('adminSettings', ['tab' => 'email'])->with('success', $response['message']);
                } else {
                    return redirect()->route('adminSettings', ['tab' => 'email'])->withInput()->with('success', $response['message']);
                }
            } catch (\Exception $e) {
                return redirect()->back()->with(['dismiss' => $e->getMessage()]);
            }
        }
    }


    // admin twillo setting save
    public function adminSaveSmsSettings(Request $request)
    {
        if ($request->post()) {
            $rules = [
                'twillo_secret_key' => 'required'
                ,
                'twillo_auth_token' => 'required'
                ,
                'twillo_number' => 'required'
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $errors = [];
                $e = $validator->errors()->all();
                foreach ($e as $error) {
                    $errors[] = $error;
                }
                $data['message'] = $errors;
                return redirect()->route('adminSettings', ['tab' => 'sms'])->with(['dismiss' => $errors[0]]);
            }

            try {
                $response = $this->settingRepo->saveTwilloSetting($request);
                if ($response['success'] == true) {
                    return redirect()->route('adminSettings', ['tab' => 'sms'])->with('success', $response['message']);
                } else {
                    return redirect()->route('adminSettings', ['tab' => 'sms'])->withInput()->with('success', $response['message']);
                }
            } catch (\Exception $e) {
                return redirect()->back()->with(['dismiss' => $e->getMessage()]);
            }
        }
    }
    // admin cron setting save
    public function adminSaveCronSettings(Request $request)
    {
        if ($request->post()) {
            $rules = [
                'cron_coin_rate' => 'required|integer|min:1',
                'cron_token_deposit' => 'required|integer|min:1',
                // , 'cron_token_deposit_adjust' => 'required|integer|min:1',
                'cron_coin_rate_status' => 'required'
                ,
                'cron_token_deposit_status' => 'required'
                // , 'cron_token_adjust_deposit_status' => 'required'
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $errors = [];
                $e = $validator->errors()->all();
                foreach ($e as $error) {
                    $errors[] = $error;
                }
                $data['message'] = $errors;
                return redirect()->route('adminSettings', ['tab' => 'cron'])->with(['dismiss' => $errors[0]]);
            }

            try {
                $response = $this->settingRepo->saveCronSetting($request);
                if ($response['success'] == true) {
                    return redirect()->route('adminSettings', ['tab' => 'cron'])->with('success', $response['message']);
                } else {
                    return redirect()->route('adminSettings', ['tab' => 'cron'])->withInput()->with('success', $response['message']);
                }
            } catch (\Exception $e) {
                return redirect()->back()->with(['dismiss' => $e->getMessage()]);
            }
        }
    }
    // admin cron setting save
    public function adminSaveWalletOverviewSettings(Request $request)
    {
        if ($request->post()) {
            if (!isset($request->wallet_overview_selected_coins)) {
                return redirect()->route('adminSettings', ['tab' => 'wallet_overview'])->withInput()->with('success', __("Select coins"));
            }

            foreach ($request->wallet_overview_selected_coins as $key => $value) {
                if (!(Coin::where("coin_type", $value)->first())) {
                    return redirect()->route('adminSettings', ['tab' => 'wallet_overview'])->withInput()->with('success', __("Invalid coin :name", ['name' => $value]));
                }
            }

            try {
                $response = $this->settingRepo->adminSaveWalletOverviewSettings($request);
                if ($response['success'] == true) {
                    return redirect()->route('adminSettings', ['tab' => 'wallet_overview'])->with('success', $response['message']);
                } else {
                    return redirect()->route('adminSettings', ['tab' => 'wallet_overview'])->withInput()->with('success', $response['message']);
                }
            } catch (\Exception $e) {
                storeException("adminSaveWalletOverviewSettings", $e->getMessage());
                return redirect()->back()->with(['dismiss' => $e->getMessage()]);
            }
        }
    }

    // admin cron setting save
    public function adminSaveFiatWithdrawalSettings(Request $request)
    {
        if ($request->post()) {
            $rules = [
                'fiat_withdrawal_type' => 'required',
                'fiat_withdrawal_value' => 'required|numeric'
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $errors = [];
                $e = $validator->errors()->all();
                foreach ($e as $error) {
                    $errors[] = $error;
                }
                $data['message'] = $errors;
                return redirect()->route('adminSettings', ['tab' => 'fiat_withdrawal'])->with(['dismiss' => $errors[0]]);
            }

            try {
                $response = $this->settingRepo->saveFiatWithdrawSetting($request);
                if ($response['success'] == true) {
                    return redirect()->route('adminSettings', ['tab' => 'fiat_withdrawal'])->with('success', $response['message']);
                } else {
                    return redirect()->route('adminSettings', ['tab' => 'fiat_withdrawal'])->withInput()->with('success', $response['message']);
                }
            } catch (\Exception $e) {
                return redirect()->back()->with(['dismiss' => $e->getMessage()]);
            }
        }
    }


    // admin referral setting save
    public function adminReferralFeesSettings(Request $request)
    {
        if ($request->post()) {
            $rules = [];
            if ($request->fees_level1) {
                $rules['fees_level1'] = 'numeric|min:0|max:100';
            }
            if ($request->fees_level2) {
                $rules['fees_level2'] = 'numeric|min:0|max:100';
            }
            if ($request->fees_level3) {
                $rules['fees_level3'] = 'numeric|min:0|max:100';
            }
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $errors = [];
                $e = $validator->errors()->all();
                foreach ($e as $error) {
                    $errors[] = $error;
                }
                $data['message'] = $errors;
                return redirect()->route('adminSettings', ['tab' => 'referral'])->with(['dismiss' => $errors[0]]);
            }

            try {
                $response = $this->settingRepo->saveReferralSetting($request);
                if ($response['success'] == true) {
                    return redirect()->route('adminSettings', ['tab' => 'referral'])->with('success', $response['message']);
                } else {
                    return redirect()->route('adminSettings', ['tab' => 'referral'])->withInput()->with('success', $response['message']);
                }
            } catch (\Exception $e) {
                return redirect()->back()->with(['dismiss' => $e->getMessage()]);
            }
        }
    }

    // admin withdrawal setting save
    public function adminWithdrawalSettings(Request $request)
    {
        if ($request->post()) {
            $rules = [
                'minimum_withdrawal_amount' => 'required|numeric',
                'maximum_withdrawal_amount' => 'required|numeric',
                'max_send_limit' => 'required|numeric',
                'send_fees_type' => 'required|numeric',
                'send_fees_fixed' => 'required|numeric',
                'send_fees_percentage' => 'required|numeric',
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $errors = [];
                $e = $validator->errors()->all();
                foreach ($e as $error) {
                    $errors[] = $error;
                }
                $data['message'] = $errors;
                return redirect()->route('adminSettings', ['tab' => 'withdraw'])->with(['dismiss' => $errors[0]]);
            }

            try {
                $response = $this->settingRepo->saveWithdrawSetting($request);
                if ($response['success'] == true) {
                    return redirect()->route('adminSettings', ['tab' => 'withdraw'])->with('success', $response['message']);
                } else {
                    return redirect()->route('adminSettings', ['tab' => 'withdraw'])->withInput()->with('success', $response['message']);
                }
            } catch (\Exception $e) {
                return redirect()->back()->with(['dismiss' => $e->getMessage()]);
            }
        }
    }

    // admin referral setting save
    public function adminSavePaymentSettings(Request $request)
    {
        if ($request->post()) {
            $rules = [
                'COIN_PAYMENT_PUBLIC_KEY' => 'required',
                'COIN_PAYMENT_PRIVATE_KEY' => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $errors = [];
                $e = $validator->errors()->all();
                foreach ($e as $error) {
                    $errors[] = $error;
                }
                $data['message'] = $errors;
                return redirect()->route('adminCoinApiSettings', ['tab' => 'payment'])->with(['dismiss' => $errors[0]]);
            }

            try {
                $response = $this->settingRepo->savePaymentSetting($request);
                if ($response['success'] == true) {
                    return redirect()->route('adminCoinApiSettings', ['tab' => 'payment'])->with('success', $response['message']);
                } else {
                    return redirect()->route('adminCoinApiSettings', ['tab' => 'payment'])->withInput()->with('success', $response['message']);
                }
            } catch (\Exception $e) {
                return redirect()->back()->with(['dismiss' => $e->getMessage()]);
            }
        }
    }

    // admin captcha setting save
    public function adminCapchaSettings(CaptchaRequest $request)
    {
        try {
            $response = $this->settingRepo->saveCapchaSetting($request);
            if ($response['success'] == true) {
                return redirect()->route('adminSettings', ['tab' => 'capcha'])->with('success', $response['message']);
            } else {
                return redirect()->route('adminSettings', ['tab' => 'capcha'])->withInput()->with('success', $response['message']);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with(['dismiss' => $e->getMessage()]);
        }

    }


    // admin node setting save
    public function adminNodeSettings(Request $request)
    {
        if ($request->post()) {
            $rules = [
                'coin_api_user' => 'required',
                'coin_api_pass' => 'required',
                'coin_api_host' => 'required',
                'coin_api_port' => 'required'
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $errors = [];
                $e = $validator->errors()->all();
                foreach ($e as $error) {
                    $errors[] = $error;
                }
                $data['message'] = $errors;
                return redirect()->route('adminSettings', ['tab' => 'node'])->with(['dismiss' => $errors[0]]);
            }

            try {
                $response = $this->settingRepo->saveNodeSetting($request);
                if ($response['success'] == true) {
                    return redirect()->route('adminSettings', ['tab' => 'node'])->with('success', $response['message']);
                } else {
                    if ($request->ajax()) {
                        $data['items'] = Faq::orderBy('id', 'desc');
                        return datatables()->of($data['items'])
                            ->addColumn('status', function ($item) {
                                return status($item->status);
                            })
                            ->addColumn('actions', function ($item) {
                                return '<ul class="d-flex activity-menu">
                                <li class="viewuser"><a href="' . route('adminFaqEdit', $item->id) . '"><i class="fa fa-pencil"></i></a> </li>
                                <li class="deleteuser"><a href="' . route('adminFaqDelete', $item->id) . '"><i class="fa fa-trash"></i></a></li>
                                </ul>';
                            })
                            ->rawColumns(['actions', 'status'])
                            ->make(true);
                    }
                    return redirect()->route('adminSettings', ['tab' => 'node'])->withInput()->with('success', $response['message']);
                }
            } catch (\Exception $e) {
                return redirect()->back()->with(['dismiss' => $e->getMessage()]);
            }
        }
    }


    //Contact Us Email List
    public function contactEmailList(Request $request)
    {
        $items = ContactUs::select('*');
        return datatables($items)
            ->addColumn('details', function ($item) {
                $html = '<button class="btn btn-info show_details" data-toggle="modal" data-target="#descriptionModal" data-id="' . $item->id . '">Details</button>';
                return $html;
            })
            ->removeColumn(['created_at', 'updated_at'])
            ->rawColumns(['details'])
            ->make(true);
    }

    public function getDescriptionByID(Request $request)
    {
        $response = ContactUs::where('id', $request->id)->first();
        return response()->json($response);
    }

    // Faq List
    public function adminFaqList(Request $request)
    {
        $data['title'] = __('FAQs');
        if ($request->ajax()) {
            $data['items'] = Faq::orderBy('id', 'desc');
            return datatables()->of($data['items'])
                ->addColumn('type', function ($item) {
                    return $item->faq_type_id ? faqType($item->faq_type_id) : "";
                })
                ->addColumn('status', function ($item) {
                    return status($item->status);
                })
                ->addColumn('actions', function ($item) {
                    return '<ul class="d-flex align-items-center activity-menu justify-content-center">
                        <li class="viewuser"><a href="' . route('adminFaqEdit', $item->id) . '"><i class="fa fa-pencil" style="font-size: 16px"></i></a> </li>
                        <li class="deleteuser"><a href="' . route('adminFaqDelete', $item->id) . '"><i class="fa fa-trash" style="font-size: 16px"></i></a></li>
                        </ul>';
                })
                ->rawColumns(['actions', 'status'])
                ->make(true);
        }

        return view('admin.faq.list', $data);
    }

    // View Add new faq page
    public function adminFaqAdd()
    {
        $data['title'] = __('Add FAQs');
        $data['faq_type_list'] = $this->faqTypeService->getFaqTypeActiveList()['data'];
        return view('admin.faq.addEdit', $data);
    }

    //view add faq type
    public function adminFaqTypeAdd(Request $request)
    {
        $data['title'] = __('Add FAQs Type');

        if ($request->ajax()) {
            $data['items'] = FaqType::orderBy('id', 'desc');
            return datatables()->of($data['items'])
                ->addColumn('status', function ($item) {
                    return status($item->status);
                })
                ->addColumn('actions', function ($item) {
                    return '<ul class="d-flex activity-menu">
                        <li class="viewuser"><a href="' . route('adminFaqTypeEdit', $item->id) . '"><i class="fa fa-pencil"></i></a> </li>
                        <li class="deleteuser"><a href="' . route('adminFaqTypeDelete', $item->id) . '"><i class="fa fa-trash"></i></a></li>
                        </ul>';
                })
                ->rawColumns(['actions', 'status'])
                ->make(true);
        }
        return view('admin.faq.addEditFaqType', $data);
    }
    //create new faq type
    public function adminFaqTypeSave(Request $request)
    {
        $rules = [
            'name' => 'required',
            'status' => 'required',
        ];
        $messages = [
            'name.required' => __('Question field can not be empty'),
            'status.required' => __('Status field can not be empty'),
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            $errors = [];
            $e = $validator->errors()->all();
            foreach ($e as $error) {
                $errors[] = $error;
            }
            return redirect()->back()->withInput()->with(['dismiss' => $errors[0]]);
        }

        $data = [
            'name' => $request->name
            ,
            'status' => $request->status
        ];
        if (!empty($request->edit_id)) {
            $data['id'] = $request->edit_id;
            $response = $this->faqTypeService->saveFaqType($data);
        } else {

            $response = $this->faqTypeService->saveFaqType($data);
        }

        if ($response['success']) {
            return redirect()->route('adminFaqTypeAdd')->with(['success' => $response['message']]);
        } else {
            return redirect()->back()->with(['dismiss' => $response['message']]);
        }
    }

    // Create New faq
    public function adminFaqSave(Request $request)
    {
        $rules = [
            'question' => 'required',
            'answer' => 'required',
            'faq_type_id' => 'required',
            'status' => 'required',
        ];
        $messages = [
            'question.required' => __('Question field can not be empty'),
            'answer.required' => __('Answer field can not be empty'),
            'faq_type_id.required' => __('Faq type can not be empty'),
            'status.required' => __('Status field can not be empty'),
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            $errors = [];
            $e = $validator->errors()->all();
            foreach ($e as $error) {
                $errors[] = $error;
            }
            return redirect()->back()->withInput()->with(['dismiss' => $errors[0]]);
        }

        $data = [
            'question' => $request->question
            ,
            'answer' => $request->answer
            ,
            'faq_type_id' => $request->faq_type_id
            ,
            'status' => $request->status
            ,
            'author' => Auth::id()
        ];

        if (!empty($request->edit_id)) {
            Faq::where(['id' => $request->edit_id])->update($data);
            return redirect()->route('adminFaqList')->with(['success' => __('Faq Updated Successfully!')]);
        } else {
            Faq::create($data);
            return redirect()->route('adminFaqList')->with(['success' => __('Faq Added Successfully!')]);
        }
    }

    // Edit Faqs
    public function adminFaqEdit($id)
    {
        $data['title'] = __('Update FAQs');
        $data['item'] = Faq::findOrFail($id);
        $data['faq_type_list'] = $this->faqTypeService->getFaqTypeActiveList()['data'];
        return view('admin.faq.addEdit', $data);
    }

    //edit faq type
    public function adminFaqTypeEdit($id)
    {
        $data['title'] = __('Update FAQ Type');
        $data['item'] = FaqType::findOrFail($id);

        return view('admin.faq.addEditFaqType', $data);
    }

    // Delete Faqs
    public function adminFaqDelete($id)
    {

        if (isset($id)) {
            Faq::where(['id' => $id])->delete();
        }

        return redirect()->back()->with(['success' => __('Deleted Successfully!')]);
    }
    // Delete Faq type
    public function adminFaqTypeDelete($id)
    {

        if (isset($id)) {
            $response = $this->faqTypeService->delete($id);

            if ($response['success']) {
                return redirect()->back()->with(['success' => $response['message']]);
            } else {
                return redirect()->back()->with(['dismiss' => $response['message']]);
            }
        }
        return redirect()->back()->with(['dismiss' => __('FAQ type Id not found!!')]);
    }

    // admin payment setting
    public function adminPaymentSetting()
    {
        $data['title'] = __('Payment Method');
        $data['settings'] = allsetting();
        $data['payment_methods'] = paymentMethods();

        return view('admin.settings.payment-method', $data);
    }

    // chnage payment method status
    public function changePaymentMethodStatus(Request $request)
    {
        $settings = allsetting();
        if (!empty($request->active_id)) {
            $value = 1;
            $item = isset($settings[$request->active_id]) ? $settings[$request->active_id] : 2;
            if ($item == 1) {
                $value = 2;
            } elseif ($item == 2) {
                $value = 1;
            }
            AdminSetting::updateOrCreate(['slug' => $request->active_id], ['value' => $value]);
        }
        return response()->json(['message' => __('Status changed successfully')]);
    }


    // admin node setting save
    public function adminSaveBitgoSettings(Request $request)
    {
        if ($request->post()) {
            $rules = [
                'bitgo_api' => 'required|max:255',
                'bitgoExpess' => 'required|max:255',
                'BITGO_ENV' => 'required|max:255|in:test,live',
                'bitgo_token' => 'required|max:255'
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $errors = [];
                $e = $validator->errors()->all();
                foreach ($e as $error) {
                    $errors[] = $error;
                }
                $data['message'] = $errors;
                return redirect()->route('adminCoinApiSettings', ['tab' => 'bitgo'])->with(['dismiss' => $errors[0]]);
            }

            try {
                $response = $this->settingRepo->saveBitgoSetting($request);
                if ($response['success'] == true) {
                    return redirect()->route('adminCoinApiSettings', ['tab' => 'bitgo'])->with('success', $response['message']);
                } else {
                    return redirect()->route('adminCoinApiSettings', ['tab' => 'bitgo'])->withInput()->with('success', $response['message']);
                }
            } catch (\Exception $e) {
                return redirect()->back()->with(['dismiss' => $e->getMessage()]);
            }
        }
    }

    // admin api setting save
    public function adminSaveOtherApiSettings(Request $request)
    {
        if ($request->post()) {
            try {
                $response = $this->settingRepo->saveAdminSetting($request);
                if ($response['success'] == true) {
                    return redirect()->route('adminCoinApiSettings', ['tab' => 'crypto'])->with('success', $response['message']);
                } else {
                    return redirect()->route('adminCoinApiSettings', ['tab' => 'crypto'])->withInput()->with('success', $response['message']);
                }
            } catch (\Exception $e) {
                return redirect()->back()->with(['dismiss' => $e->getMessage()]);
            }
        }
    }

    // admin api setting save
    public function adminSaveStripeApiSettings(Request $request)
    {
        if ($request->post()) {
            try {
                $response = $this->settingRepo->saveAdminSetting($request);
                if ($response['success'] == true) {
                    return redirect()->route('adminCoinApiSettings', ['tab' => 'stripe'])->with('success', $response['message']);
                } else {
                    return redirect()->route('adminCoinApiSettings', ['tab' => 'stripe'])->withInput()->with('success', $response['message']);
                }
            } catch (\Exception $e) {
                return redirect()->back()->with(['dismiss' => $e->getMessage()]);
            }
        }
    }

    public function adminSaveRazorpayApiSettings(Request $request)
    {
        if ($request->post()) {
            try {
                $response = $this->settingRepo->saveAdminSetting($request);
                if ($response['success'] == true) {
                    return redirect()->route('adminCoinApiSettings', ['tab' => 'razorpay'])->with('success', $response['message']);
                } else {
                    return redirect()->route('adminCoinApiSettings', ['tab' => 'razorpay'])->withInput()->with('success', $response['message']);
                }
            } catch (\Exception $e) {
                return redirect()->back()->with(['dismiss' => $e->getMessage()]);
            }
        }
    }

    public function adminSavePaystackApiSettings(Request $request)
    {
        if ($request->post()) {
            try {
                $response = $this->settingRepo->saveAdminSetting($request);
                if ($response['success'] == true) {
                    return redirect()->route('adminCoinApiSettings', ['tab' => 'paystack'])->with('success', $response['message']);
                } else {
                    return redirect()->route('adminCoinApiSettings', ['tab' => 'paystack'])->withInput()->with('success', $response['message']);
                }
            } catch (\Exception $e) {
                return redirect()->back()->with(['dismiss' => $e->getMessage()]);
            }
        }
    }

    public function adminSaveCurrencyExchangeApiSettings(Request $request)
    {
        if ($request->post()) {
            try {
                $response = $this->settingRepo->saveAdminSetting($request);
                if ($response['success'] == true) {
                    return redirect()->route('adminCoinApiSettings', ['tab' => 'currency_exchange_api'])->with('success', $response['message']);
                } else {
                    return redirect()->route('adminCoinApiSettings', ['tab' => 'currency_exchange_api'])->withInput()->with('success', $response['message']);
                }
            } catch (\Exception $e) {
                return redirect()->back()->with(['dismiss' => $e->getMessage()]);
            }
        }
    }

    // admin node setting save
    public function adminSaveERC20ApiSettings(Request $request)
    {
        if ($request->post()) {
            $rules = [
                'erc20_app_url' => 'required|max:255',
                'erc20_app_key' => 'required|max:255',
                'erc20_app_port' => 'required|integer',
                'previous_block_count' => 'required|integer:gt:0',
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $errors = [];
                $e = $validator->errors()->all();
                foreach ($e as $error) {
                    $errors[] = $error;
                }
                $data['message'] = $errors;
                return redirect()->route('adminCoinApiSettings', ['tab' => 'erc20'])->with(['dismiss' => $errors[0]]);
            }

            try {
                $response = $this->settingRepo->saveAdminSetting($request);
                if ($response['success'] == true) {
                    return redirect()->route('adminCoinApiSettings', ['tab' => 'erc20'])->with('success', __('Api setting updated successfully'));
                } else {
                    return redirect()->route('adminCoinApiSettings', ['tab' => 'erc20'])->withInput()->with('success', __('Api setting updated successfully'));
                }
            } catch (\Exception $e) {
                return redirect()->back()->with(['dismiss' => $e->getMessage()]);
            }
        }
    }

    // admin cookie settings
    public function adminCookieSettings()
    {
        $data['tab'] = 'cookie';
        if (isset($_GET['tab'])) {
            $data['tab'] = $_GET['tab'];
        }
        $data['title'] = __('Cookie Settings');
        $data['settings'] = allsetting();
        $data['pages'] = CustomPage::where(['status' => STATUS_ACTIVE])->get();

        return view('admin.settings.cookies.cookie_settings', $data);
    }

    // save cookie settings
    public function adminCookieSettingsSave(Request $request, BotOrderService $botOrderService)
    {
        try {
            $rules = [];
            if (!empty($request->cookie_image)) {
                $file_size = (ADMIN_SETTINGS_ARRAY['upload_max_size'] ?? 2) * 1024;
                $rules['cookie_image'] = "image|mimes:jpg,jpeg,png|max:$file_size";
            }

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $errors = [];
                $e = $validator->errors()->all();
                foreach ($e as $error) {
                    $errors[] = $error;
                }
                $data['message'] = $errors;

                return redirect()->route('adminFeatureSettings', ['tab' => $this->cookieTab($request)])->with(['dismiss' => $errors[0]]);
            }

            $shouldStartBot = false;

            if(allsetting('enable_bot_trade') == STATUS_PENDING && $request->get('enable_bot_trade') == STATUS_ACTIVE) {
                $shouldStartBot = true;
            }

            $response = $this->settingRepo->saveAdminSetting($request);
            if ($response['success'] == true) {
                return redirect()->route('adminFeatureSettings', ['tab' => $this->cookieTab($request)])->with('success', __('Setting updated successfully'));
            } else {
                return redirect()->route('adminFeatureSettings', ['tab' => $this->cookieTab($request)])->withInput()->with('success', $response['message']);
            }
        } catch (\Exception $e) {
            storeException('adminCookieSettingsSave', $e->getMessage());
            return redirect()->route('adminFeatureSettings', parameters: ['tab' => $this->cookieTab($request)])->with(['dismiss' => $e->getMessage()]);
        }
    }

    // cookiee tab
    public function cookieTab($request)
    {
        $tab = 'cookie';
        if (isset($request->cookie_status)) {
            $tab = 'cookie';
        } elseif (isset($request->live_chat_status)) {
            $tab = 'live_chat';
        } elseif (isset($request->swap_status)) {
            $tab = 'swap';
        } elseif (isset($request->currency_deposit_status)) {
            $tab = 'currency_deposit';
        } elseif (isset($request->currency_deposit_faq_status)) {
            $tab = 'faq_setting';
        } elseif (isset($request->enable_bot_trade)) {
            $tab = 'bot_setting';
        } elseif (isset($request->enable_future_trade)) {
            $tab = 'future_trade';
        }
        return $tab;
    }

    public function testMail(Request $request)
    {
        $template = emailTemplateName('test_mail');
        $mailService = new MailService();
        $companyName = isset(allsetting()['app_title']) && !empty(allsetting()['app_title']) ? allsetting()['app_title'] : __('Company Name');
        $subject = __(' Test Mail | :companyName', ['companyName' => $companyName]);
        $test = $mailService->sendTest($template, [], $request->email, "Name", $subject);

        return redirect()->route('adminSettings', ['tab' => 'email'])->with("success", $test['message']);
    }

    // admin feature setting
    public function adminFeatureSettings(Request $request)
    {
        $data['tab'] = 'cookie';
        if (isset($_GET['tab'])) {
            $data['tab'] = $_GET['tab'];
        }
        $data['title'] = __('Feature Settings');
        $data['settings'] = allsetting();
        $data['pages'] = CustomPage::where(['status' => STATUS_ACTIVE])->get();

        return view('admin.settings.feature.general', $data);
    }

    // save cookie settings
    public function adminSettingsSaveCommon(Request $request)
    {
        try {
            $rules = [];
            if (!empty($request->maintenance_mode_img)) {
                $file_size = (ADMIN_SETTINGS_ARRAY['upload_max_size'] ?? 2) * 1024;
                $rules['maintenance_mode_img'] = "image|mimes:jpg,jpeg,png|max:$file_size";
            }

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $errors = [];
                $e = $validator->errors()->all();
                foreach ($e as $error) {
                    $errors[] = $error;
                }
                $data['message'] = $errors;

                return redirect()->route('adminSettings', ['tab' => settingTab($request)])->with(['dismiss' => $errors[0]]);
            }

            $response = $this->settingRepo->saveAdminSetting($request);
            if ($response['success'] == true) {
                return redirect()->route('adminSettings', ['tab' => settingTab($request)])->with('success', __('Setting updated successfully'));
            } else {
                return redirect()->route('adminSettings', ['tab' => settingTab($request)])->withInput()->with('success', $response['message']);
            }
        } catch (\Exception $e) {
            storeException('adminSettingsSaveCommon', $e->getMessage());
            return redirect()->route('adminSettings', ['tab' => settingTab($request)])->with(['dismiss' => $e->getMessage()]);
        }
    }

    public function adminExchangeLayoutSettings(Request $request)
    {
        try {
            $rules = [];
            if (!empty($request->maintenance_mode_img)) {
                $file_size = (ADMIN_SETTINGS_ARRAY['upload_max_size'] ?? 2) * 1024;
                $rules['maintenance_mode_img'] = "image|mimes:jpg,jpeg,png|max:$file_size";
            }

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $errors = [];
                $e = $validator->errors()->all();
                foreach ($e as $error) {
                    $errors[] = $error;
                }
                $data['message'] = $errors;

                return redirect()->route('adminSettings', ['tab' => settingTab($request)])->with(['dismiss' => $errors[0]]);
            }

            $response = $this->settingRepo->saveAdminSetting($request);
            if ($response['success'] == true) {
                return redirect()->route('adminSettings', ['tab' => settingTab($request)])->with('success', __('Setting updated successfully'));
            } else {
                return redirect()->route('adminSettings', ['tab' => settingTab($request)])->withInput()->with('success', $response['message']);
            }
        } catch (\Exception $e) {
            storeException('adminSettingsSaveCommon', $e->getMessage());
            return redirect()->route('adminSettings', ['tab' => settingTab($request)])->with(['dismiss' => $e->getMessage()]);
        }
    }

    public function deleteBotOrders()
    {
        try {
            $enable_bot_trade = allsetting('enable_bot_trade');
            if ($enable_bot_trade == STATUS_ACTIVE) {
                $response = ['success' => false, 'message' => __('Please disable bot trading first to delete bot orders!')];
            } else {
                Buy::where('status', STATUS_INACTIVE)->where('is_bot', STATUS_ACTIVE)->forceDelete();

                Sell::where('status', STATUS_INACTIVE)->where('is_bot', STATUS_ACTIVE)->forceDelete();

                $response = ['success' => true, 'message' => __('Bot orders are deleted successfully!')];
            }
        } catch (\Exception $e) {
            storeException('deleteBotOrders', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong!')];
        }
        return response()->json($response);
    }

    public function adminTradeReferralFeesSettings(Request $request)
    {
        if ($request->post()) {
            $rules = [];
            if ($request->trade_fees_level1) {
                $rules['trade_fees_level1'] = 'numeric|min:0|max:100';
            }
            if ($request->trade_fees_level2) {
                $rules['trade_fees_level2'] = 'numeric|min:0|max:100';
            }
            if ($request->trade_fees_level3) {
                $rules['trade_fees_level3'] = 'numeric|min:0|max:100';
            }
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $errors = [];
                $e = $validator->errors()->all();
                foreach ($e as $error) {
                    $errors[] = $error;
                }
                $data['message'] = $errors;
                return redirect()->route('adminSettings', ['tab' => 'referral'])->with(['dismiss' => $errors[0]]);
            }

            try {
                $response = $this->settingRepo->adminTradeReferralFeesSettings($request);
                if ($response['success'] == true) {
                    return redirect()->route('adminSettings', ['tab' => 'referral'])->with('success', $response['message']);
                } else {
                    return redirect()->route('adminSettings', ['tab' => 'referral'])->withInput()->with('success', $response['message']);
                }
            } catch (\Exception $e) {
                return redirect()->back()->with(['dismiss' => $e->getMessage()]);
            }
        }
    }

    public function adminSaveEmailTemplateSettings(Request $request)
    {
        try {
            $rules = [];
            $templateNumber = [
                EMAIL_TEMPLATE_NUMBER_ONE,
                EMAIL_TEMPLATE_NUMBER_TWO,
                EMAIL_TEMPLATE_NUMBER_THREE,
                EMAIL_TEMPLATE_NUMBER_FOUR
            ];
            $rules['email_template_number'] = 'required|in:' . implode(',', $templateNumber);


            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $errors = [];
                $e = $validator->errors()->all();
                foreach ($e as $error) {
                    $errors[] = $error;
                }
                $data['message'] = $errors;

                return redirect()->route('adminSettings', ['tab' => settingTab($request)])->with(['dismiss' => $errors[0]]);
            }

            $response = $this->settingRepo->saveAdminSetting($request);
            if ($response['success'] == true) {
                return redirect()->route('adminSettings', ['tab' => settingTab($request)])->with('success', __('Setting updated successfully'));
            } else {
                return redirect()->route('adminSettings', ['tab' => settingTab($request)])->withInput()->with('success', $response['message']);
            }
        } catch (\Exception $e) {
            return redirect()->route('adminSettings', ['tab' => settingTab($request)])->with(['dismiss' => $e->getMessage()]);
        }
    }

    // save common settings
    public function saveAdminSettingsCommon(Request $request)
    {
        try {
            $response = $this->settingRepo->saveAdminSetting($request);
            if ($response['success'] == true) {
                return redirect()->back()->with('success', __('Setting updated successfully'));
            } else {
                return redirect()->back()->withInput()->with('success', $response['message']);
            }
        } catch (\Exception $e) {
            storeException('adminSettingsSaveCommon', $e->getMessage());
            return redirect()->back()->with(['dismiss' => $e->getMessage()]);
        }
    }

    //
    public function adminApiOverviewSettings(Request $request)
    {
        try {
            $rules = [];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $errors = [];
                $e = $validator->errors()->all();
                foreach ($e as $error) {
                    $errors[] = $error;
                }
                $data['message'] = $errors;

                return redirect()->route('adminSettings', ['tab' => settingTab($request)])->with(['dismiss' => $errors[0]]);
            }

            $response = $this->settingRepo->saveAdminSetting($request);
            if ($response['success'] == true) {
                return redirect()->route('adminSettings', ['tab' => settingTab($request)])->with('success', __('Setting updated successfully'));
            } else {
                return redirect()->route('adminSettings', ['tab' => settingTab($request)])->withInput()->with('success', $response['message']);
            }
        } catch (\Exception $e) {
            storeException('adminApiOverviewSettings', $e->getMessage());
            return redirect()->route('adminSettings', ['tab' => settingTab($request)])->with(['dismiss' => $e->getMessage()]);
        }
    }

    public function removeAdminImageSettings(Request $request)
    {
        try {
            $response = $this->settingRepo->removeAdminImageSettings($request);
            return response()->json($response);
        } catch (\Exception $e) {
            storeException('removeAdminImageSettings', $e->getMessage());
            return response()->json(responseData(false, $e->getMessage()));
        }
    }
}
