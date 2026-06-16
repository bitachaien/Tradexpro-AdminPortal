<?php

namespace App\Http\Controllers\admin;

use App\Exceptions\InvalidRequestException;
use App\User;
use Carbon\Carbon;
use App\Exports\Users;
use App\Jobs\MailSend;
use App\Model\UserSecretKey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Services\Logger;
use App\Model\UserApiWhiteList;
use App\Http\Services\AuthService;
use App\Http\Services\MailService;
use App\Http\Services\UserService;
use App\Model\VerificationDetails;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\AdminCreateUser;
use App\Http\Requests\Api\ApiWhiteListRequest;
use App\Http\Requests\Admin\TransactionExportRequest;

class UserController extends Controller
{
    public $service;
    public function __construct()
    {
        $this->service = new AuthService;
    }
    // user list
    public function adminUsers(Request $request)
    {
        $data['title'] = __('Users');
        if (!$request->ajax()) {
            return view('admin.users.users', $data);
        } else {

            $users = [];
            $user = new User;
            if ($request->type == 'active_users') {
            }
            $users = $user->where('status', STATUS_SUCCESS)->where('id', '<>', Auth::user()->id);
            if ($request->type == 'suspend_user')
                $users = $user->where('status', STATUS_SUSPENDED)->where('id', '<>', Auth::user()->id);
            if ($request->type == 'deleted_user')
                $users = $user->where('status', STATUS_DELETED)->where('id', '<>', Auth::user()->id);
            if ($request->type == 'email_pending')
                $users = $user->where('is_verified', '!=', STATUS_SUCCESS)
                    ->where('id', '<>', Auth::user()->id)
                    ->where('status', '<>', STATUS_FORCE_DELETED);
            if ($request->type == 'phone_pending')
                $users = $user->where('phone_verified', '!=', STATUS_SUCCESS)
                    ->where('id', '<>', Auth::user()->id)
                    ->where('status', '<>', STATUS_FORCE_DELETED);
            if ($request->type == 'delete_request')
                $users = $user->where('delete_request', '<>', 0)->where('status', '<>', STATUS_FORCE_DELETED);

            return datatables($users)

                ->addColumn('status', function ($item) {
                    return statusAction($item->status);
                })
                ->addColumn('type', function ($item) {
                    return userRole($item->role);
                })
                ->addColumn('first_name', function ($item) {
                    return $item->first_name . ' ' . $item->last_name;
                })
                ->addColumn('online_status', function ($item) {
                    $response = lastSeenStatus($item->id);
                    if ($response['success'] == true) {
                        return onlineStatus($response['data']['online_status']);
                    }
                    return;
                })
                ->editColumn('created_at', function ($item) {
                    return $item->created_at ? with(new Carbon($item->created_at))->format('d M Y') : '';
                })
                ->filterColumn('created_at', function ($query, $keyword) {
                    $query->whereRaw("DATE_FORMAT(created_at,'%d %M %Y') like ?", ["%$keyword%"]);
                })
                ->addColumn('activity', function ($item) use ($request) {
                    return getActionHtml($request->type, $item->id, $item);
                })
                ->rawColumns(['activity', 'status', 'online_status'])
                ->make(true);
        }
    }

    // generate verification key
    private function generate_email_verification_key()
    {
        $key = randomNumber(6);
        return $key;
    }

    // create and edit user
    public function UserAddEdit(AdminCreateUser $request)
    {
        try {
            if (!filter_var($request['email'], FILTER_VALIDATE_EMAIL)) {
                return redirect()->back()->withInput()->with('dismiss', __('Invalid email address'));
            }
            $response = $this->service->addNewUser($request);
            if ($response['success'] == true) {
                return redirect()->back()->with('success', $response['message']);
            } else {
                return redirect()->back()->with('dismiss', $response['message']);
            }
        } catch (\Exception $e) {
            storeException('add new user by admin', $e->getMessage());
            return redirect()->back()->with('dismiss', __("Something went wrong"));
        }

    }

    // user edit page
    public function adminUserProfile(Request $request)
    {
        $data['title'] = __('User Profile');
        $data['user'] = User::find(decrypt($request->id));
        $data['type'] = $request->type;

        return view('admin.users.profile', $data);
    }

    // user edit page
    public function UserEdit(Request $request)
    {
        $data['title'] = __('User Edit');
        $data['user'] = User::find(decrypt($request->id));
        $data['type'] = $request->type;
        return view('admin.users.edit', $data);
    }

    // delete user
    public function adminUserDelete($id)
    {
        $user = User::find(decrypt($id));
        $user->status = STATUS_DELETED;
        $user->save();
        return redirect()->back()->with('success', __('User deleted successfully'));
    }


    public function adminUserForceDelete($id)
    {
        try {
            $user = User::find(decrypt($id));
            $user->status = STATUS_FORCE_DELETED;
            $user->email = $user->email . '_deleted_' . uniqid();
            $user->phone = $user->phone . '_deleted_' . uniqid();
            $user->save();
        } catch (\Exception $e) {
            storeException('adminUserForceDelete c', $e->getMessage());
            return redirect()->back()->with('dismiss', __('User deleted failed'));
        }
        return redirect()->back()->with('success', __('User deleted successfully'));
    }

    // suspend user
    public function adminUserSuspend($id)
    {
        $user = User::find(decrypt($id));
        $user->status = STATUS_SUSPENDED;
        $user->save();
        return redirect()->back()->with('success', __('User suspended successfully'));
    }

    // remove user gauth
    public function adminUserRemoveGauth($id)
    {
        $user = User::find(decrypt($id));
        $user->google2fa_secret = '';
        $user->g2f_enabled = '0';
        $user->save();
        return redirect()->back()->with('success', __('User gauth removed successfully'));
    }
    // verify user phone
    public function adminUserPhoneVerified($id)
    {
        $user = User::find(decrypt($id));
        if (empty($user->phone)) {
            return redirect()->back()->with('dismiss', __('User phone number is empty'));
        }
        $user->phone_verified = STATUS_SUCCESS;
        $user->save();
        return redirect()->back()->with('success', __('Phone verified successfully'));
    }
    // activate user
    public function adminUserActive($id)
    {
        $user = User::find(decrypt($id));
        $user->status = STATUS_SUCCESS;
        $user->save();
        return redirect()->back()->with('success', __('User activated successfully'));
    }

    // verify user email
    public function adminUserEmailVerified($id)
    {
        $user = User::find(decrypt($id));
        $user->is_verified = STATUS_SUCCESS;
        $user->save();
        return redirect()->back()->with('success', __('Email verified successfully'));
    }

    //ID Verification
    public function adminUserIdVerificationPending(Request $request)
    {
        if ($request->ajax()) {
            $data['items'] = VerificationDetails::join('users', 'users.id', 'verification_details.user_id')
                ->select('users.id', 'users.updated_at', 'users.first_name', 'users.last_name', 'users.email')
                ->groupBy('user_id')
                ->where('verification_details.status', STATUS_PENDING)
                ->where(function ($query) {
                });

            return datatables()->of($data['items'])
                ->addColumn('actions', function ($item) {
                    return '<ul class="d-flex activity-menu">
                        <li class="viewuser">
                        <a title="' . __('Details') . '" href="' . route('adminUserDetails', encrypt($item->id)) . '?tab=photo_id" class="btn btn-info btn-sm">
                        <i class="fa fa-eye"></i>
                        </a></li>
                        </ul>';
                })->rawColumns(['actions'])
                ->make(true);
        }

        return view('admin.users.users-pending-id-verification');
    }

    // verification details
    public function VerificationDetails($id)
    {
        $data['user_id'] = $id;
        $data['pending'] = VerificationDetails::where('user_id', decrypt($id))->where('status', STATUS_PENDING)->get();
        $data['fields_name'] = VerificationDetails::where('user_id', decrypt($id))->where('status', STATUS_PENDING)->get()->pluck('id', 'field_name')->toArray();
        if (!empty($data['pending'])) {
            return view('admin.users.users-pending-id-verification-details', $data);
        }

        return redirect()->route('adminUserIdVerificationPending');
    }

    // activate user verification
    public function adminUserVerificationActive($id, $type)
    {
        try {
            if ($type == 'nid') {
                $verified = ['nid_front', 'nid_back', 'nid_selfie'];
                VerificationDetails::where('user_id', decrypt($id))
                    ->whereIn('field_name', $verified)->update(['status' => STATUS_SUCCESS]);

                return redirect()->route('adminUserIdVerificationPending')->with(['success' => __('Successfully Updated')]);
            } elseif ($type == 'driving') {
                $verified = ['drive_front', 'drive_back', 'drive_selfie'];
                VerificationDetails::where('user_id', decrypt($id))
                    ->whereIn('field_name', $verified)->update(['status' => STATUS_SUCCESS]);

                return redirect()->route('adminUserIdVerificationPending')->with(['success' => __('Successfully Updated')]);
            } elseif ($type == 'passport') {
                $verified = ['pass_front', 'pass_back', 'pass_selfie'];
                VerificationDetails::where('user_id', decrypt($id))
                    ->whereIn('field_name', $verified)->update(['status' => STATUS_SUCCESS]);

                return redirect()->route('adminUserIdVerificationPending')->with(['success' => __('Successfully Updated')]);
            } elseif ($type == 'voter') {
                $verified = ['voter_front', 'voter_back', 'voter_selfie'];
                VerificationDetails::where('user_id', decrypt($id))
                    ->whereIn('field_name', $verified)->update(['status' => STATUS_SUCCESS]);

                return redirect()->route('adminUserIdVerificationPending')->with(['success' => __('Successfully Updated')]);
            }
        } catch (\Exception $exception) {
            return redirect()->route('adminUserIdVerificationPending')->with(['dismiss' => __('Something went wrong')]);
        }
    }

    // verification reject process
    public function varificationReject(Request $request)
    {
        try {
            $companyName = env('APP_NAME');
            $data['data'] = User::find(decrypt($request->user_id));
            $data['cause'] = $request->couse;
            $data['email'] = $data['data']->email;
            $user = $data['data'];

            $this->sendIdVerificationEmail($data, $user);

            if (isset($request->ids[0])) {
                foreach ($request->ids as $key => $value) {
                    deleteFile(IMG_USER_PATH, $value);
                }
            }
            VerificationDetails::whereIn('photo', $request->ids)->update(['status' => STATUS_REJECTED, 'photo' => '']);

            return redirect()->route('adminUserIdVerificationPending')->with('success', __('Rejected successfully'));
        } catch (\Exception $e) {
            storeException('varificationReject', $e->getMessage());
            return redirect()->back()->with('dismiss', __('Something went wrong'));
        }

    }

    // send rejection mail to user
    public function sendIdVerificationEmail($data, $user)
    {
        try {
            $mailService = new MailService();
            $userName = $user->first_name . ' ' . $user->last_name;
            $userEmail = $user->email;
            $companyName = isset(allsetting()['app_title']) && !empty(allsetting()['app_title']) ? allsetting()['app_title'] : __('Company Name');
            $subject = __('KYC Verification | :companyName', ['companyName' => $companyName]);
            $template = emailTemplateName('verification_fields');
            $mailService->send($template, $data, $userEmail, $userName, $subject);

            return true;
        } catch (\Exception $e) {
            storeException('sendIdVerificationEmail', $e->getMessage());
        }
    }

    public function userExport(TransactionExportRequest $request)
    {
        try {
            return Excel::download(new Users($request), 'UserList' . ($request->export_to ?? '.csv'));
        } catch (\Exception $e) {
            storeException('userExport from admin', $e->getMessage());
            return redirect()->back()->with('dismiss', __('Something went wrong'));
        }
    }

    public function adminUserDeleteRequestRejected($id)
    {
        try {
            $user = User::find(decrypt($id));
            $user->delete_request = USER_DELETE_REQUEST_STATUS_REJECTED;
            $user->save();

            $title = __('Profile delete request');
            $message = __('Your account delete request is rejected by admin');
            $emailData = [
                'to' => $user->email,
                'name' => $user->first_name . ' ' . $user->last_name,
                'subject' => $title,
                'email_header' => $title,
                'email_message' => $message,
                'mailTemplate' => emailTemplateName('genericemail')
            ];
            dispatch(new MailSend($emailData))->onQueue('send-mail');

        } catch (\Exception $e) {
            storeException('adminUserDeleteRequestRejected', $e->getMessage());
            return redirect()->back()->with('success', __('Something went wrong'));
        }
        return redirect()->back()->with('dismiss', __('User deleted request rejected'));
    }

    public function adminUserDeleteRequestDeactive($id)
    {
        try {
            $user = User::find(decrypt($id));
            $user->status = STATUS_USER_DEACTIVATE;
            $user->delete_request = USER_DELETE_REQUEST_STATUS_ACCEPTED;
            $user->save();

            $user->tokens()->delete();

            $title = __('Profile delete request');
            $message = __('Your account delete request is accepted by admin. Your account status is deactivated.');
            $emailData = [
                'to' => $user->email,
                'name' => $user->first_name . ' ' . $user->last_name,
                'subject' => $title,
                'email_header' => $title,
                'email_message' => $message,
                'mailTemplate' => emailTemplateName('genericemail')
            ];
            dispatch(new MailSend($emailData))->onQueue('send-mail');

        } catch (\Exception $e) {
            storeException('adminUserDeleteRequestDeactive', $e->getMessage());
            return redirect()->back()->with('success', __('Something went wrong'));
        }
        return redirect()->back()->with('dismiss', __('User deleted request is deactivate'));
    }

    public function adminUserDeleteRequestSoftDelete($id)
    {
        try {
            $user = User::find(decrypt($id));
            $user->status = STATUS_DELETED;
            $user->delete_request = USER_DELETE_REQUEST_STATUS_ACCEPTED;
            $user->save();

            $user->tokens()->delete();

            $title = __('Profile delete request');
            $message = __('Your account delete request is accepted by admin. Your account status is deleted.');
            $emailData = [
                'to' => $user->email,
                'name' => $user->first_name . ' ' . $user->last_name,
                'subject' => $title,
                'email_header' => $title,
                'email_message' => $message,
                'mailTemplate' => emailTemplateName('genericemail')
            ];
            dispatch(new MailSend($emailData))->onQueue('send-mail');

        } catch (\Exception $e) {
            storeException('adminUserDeleteRequestSoftDelete', $e->getMessage());
            return redirect()->back()->with('success', __('Something went wrong'));

        }
        return redirect()->back()->with('dismiss', __('User deleted request is soft delete'));
    }

    public function adminUserDeleteRequestForceDelete($id)
    {
        try {
            $user = User::find(decrypt($id));
            $user->email = $user->email . '_deleted_' . uniqid();
            $user->status = STATUS_FORCE_DELETED;
            $user->delete_request = USER_DELETE_REQUEST_STATUS_ACCEPTED;
            $user->save();

            $user->tokens()->delete();

            $title = __('Profile delete request');
            $message = __('Your account delete request is accepted by admin. Your account status is deleted.');
            $emailData = [
                'to' => $user->email,
                'name' => $user->first_name . ' ' . $user->last_name,
                'subject' => $title,
                'email_header' => $title,
                'email_message' => $message,
                'mailTemplate' => emailTemplateName('genericemail')
            ];
            dispatch(new MailSend($emailData))->onQueue('send-mail');

        } catch (\Exception $e) {
            storeException('adminUserDeleteRequestForceDelete', $e->getMessage());
            return redirect()->back()->with('success', __('Something went wrong'));

        }
        return redirect()->back()->with('dismiss', __('User deleted request is force delete'));
    }

    // user api access setting
    public function adminUserApiAccess($id)
    {
        return $this->handlerResponseAndRedirect(function () use ($id) {
            $user = User::find(decrypt($id));
            if (!$user)
                throw new InvalidRequestException("Invalid user");

            $data = [
                'title' => __('User Api Access Setting'),
                'user' => $user,
                'api_secret' => UserSecretKey::where(['user_id' => $user->id])->first(),
                'whitelists' => UserApiWhiteList::where(['user_id' => $user->id])->get(),
                'status1' => [STATUS_ACTIVE => __('Yes'), STATUS_INACTIVE => __('No')],
                'status2' => [STATUS_ACTIVE => __('ON'), STATUS_INACTIVE => __('OFF')],
                'redirectView' => view('admin.users.api_access')
            ];

            return $this->responseData(true, __('Success'), $data);
        });
    }

    // user white list add
    public function addUserWhiteList(ApiWhiteListRequest $request): JsonResponse
    {
        return $this->handlerResponseAndRedirect(
            function () use ($request): array {
                return (new UserService)->addApiWhiteList($request);
            }
        );
    }

    public function deleteUserWhiteList($id)
    {
        try {
            $service = new UserService();
            $response = $service->deleteApiWhiteList($id);

            if (isset($response['success']) && $response['success']) {
                return redirect()->back()->with('success', $response['message'] ?? __("Success"));
            }
            return redirect()->back()->with('dismiss', $response['message'] ?? __("Failed"));
        } catch (\Exception $e) {
            storeException('deleteUserWhiteList ex', $e->getMessage());
            return redirect()->back()->with('dismiss', __('Something went wrong'));
        }
    }

    public function updateUserWhiteListStatus(Request $request)
    {
        try {
            if (!isset($request->id))
                return response()->json(responseData(false, __("White list id not found")));
            if (!isset($request->type))
                return response()->json(responseData(false, __("White list status type not found")));
            if (!isset($request->value))
                return response()->json(responseData(false, __("White list status not found")));

            $service = new UserService();
            return response()->json(
                $service->changeApiWhiteListStatus(
                    $request->id,
                    $request->type,
                    $request->value
                )
            );
        } catch (\Exception $e) {
            storeException('deleteUserWhiteList ex', $e->getMessage());
            return redirect()->back()->with('dismiss', __('Something went wrong'));
        }
    }

    public function userApiAccessUpdate(Request $request)
    {
        return $this->handlerResponseAndRedirect(function () use ($request) {
            return (new UserService())->userApiAccessUpdate($request);
        });
    }
}
