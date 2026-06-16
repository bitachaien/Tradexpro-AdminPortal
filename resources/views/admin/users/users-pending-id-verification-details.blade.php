@extends('admin.master',['menu'=>'users' ,'sub_menu'=>'pending_id'])
@section('title', isset($title) ? $title : __('Id Verification'))
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li>{{__('User management')}}</li>
                    <li class="active-item">{{__('Pending ID verification')}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management pidverify">
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <?php
                    $nid_front = (isset($fields_name['nid_front'])) ? $pending[array_search('nid_front',array_keys($fields_name))]->photo : '';
                    $nid_back = (isset($fields_name['nid_back'])) ? $pending[array_search('nid_back',array_keys($fields_name))]->photo : '';
                    $nid_selfie = (isset($fields_name['nid_selfie'])) ? $pending[array_search('nid_selfie',array_keys($fields_name))]->photo : '';
                    
                    $drive_front = (isset($fields_name['drive_front'])) ? $pending[array_search('drive_front',array_keys($fields_name))]->photo : '';
                    $drive_back = (isset($fields_name['drive_back'])) ? $pending[array_search('drive_back',array_keys($fields_name))]->photo : '';
                    $drive_selfie = (isset($fields_name['drive_selfie'])) ? $pending[array_search('drive_selfie',array_keys($fields_name))]->photo : '';

                    $pass_front = (isset($fields_name['pass_front'])) ? $pending[array_search('pass_front',array_keys($fields_name))]->photo : '';
                    $pass_back = (isset($fields_name['pass_back'])) ? $pending[array_search('pass_back',array_keys($fields_name))]->photo : '';
                    $pass_selfie = (isset($fields_name['pass_selfie'])) ? $pending[array_search('pass_selfie',array_keys($fields_name))]->photo : '';

                    $voter_front = (isset($fields_name['voter_front'])) ? $pending[array_search('voter_front',array_keys($fields_name))]->photo : '';
                    $voter_back = (isset($fields_name['voter_back'])) ? $pending[array_search('voter_back',array_keys($fields_name))]->photo : '';
                    $voter_selfie = (isset($fields_name['voter_selfie'])) ? $pending[array_search('voter_selfie',array_keys($fields_name))]->photo : '';

                    ?>
                    @if(isset($fields_name['nid_front']))
                        <div class="header-bar">
                            <div class="table-title">
                                <h3>{{__('Pending NID Verification')}}</h3>
                            </div>
                            <ul class="id-verify-btn-group">
                                <li><a href="{{route('adminUserVerificationActive',[$user_id,'nid'])}}" class="approve-btn">{{__('Approve')}}</a></li>
                                <li><a href="javascript:;" data-toggle="modal" data-target="#pidverify" class="reject-btn">{{__('Reject')}}</a></li>
                            </ul>
                        </div>
                        <div class="id-varify">
                        <div class="row justify-content-center">
                            <div class="col-xl-4 mb-xl-0 mb-4">
                                <div class="card-wrapper w-auto">
                                    <div class="card-area">
                                        <img src="{{imageSrc($nid_front,IMG_USER_VIEW_PATH)}}" class="img-fluid" alt="">
                                    </div>
                                    <h4>{{__('NID Front Side')}}</h4>
                                </div>
                            </div>
                            <div class="col-xl-4">
                                <div class="card-wrapper w-auto">
                                    <div class="card-area">
                                        <img src="{{imageSrc($nid_back,IMG_USER_VIEW_PATH)}}" class="img-fluid" alt="">
                                    </div>
                                    <h4>{{__('NID Back Side')}}</h4>
                                </div>
                            </div>
                            <div class="col-xl-4">
                                <div class="card-wrapper w-auto">
                                    <div class="card-area">
                                        <img src="{{imageSrc($nid_selfie,IMG_USER_VIEW_PATH)}}" class="img-fluid" alt="">
                                    </div>
                                    <h4>{{__('NID Selfie')}}</h4>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Modal -->
                        <div class="modal fade" id="pidverify" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalCenterTitle">{{__('Rejected Cause')}}</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <i class="fa fa-times" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="idverifyrejection">
                                            <div class="row justify-content-center">
                                                <div class="col-lg-12">
                                                    <form action="{{route('varificationReject')}}">
                                                        <div class="form-group m-0">
                                                            <label>{{__('Cause of  Rejection')}}</label>
                                                            <input type="hidden" name="type" value="nid">
                                                            <input type="hidden" name="user_id" value="{{$user_id}}">
                                                            <input type="hidden" name="ids[]" value="{{$nid_front}}">
                                                            <input type="hidden" name="ids[]" value="{{$nid_back}}">
                                                            <input type="hidden" name="ids[]" value="{{$nid_selfie}}">
                                                            <textarea required name="couse" class="form-control"></textarea>
                                                        </div>
                                                        <button type="submit" class="btn">{{__('Send')}}</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if(isset($fields_name['drive_front']))
                            <div class="header-bar mt-5">
                                <div class="table-title">
                                    <h3>{{__('Driving licence Verification')}}</h3>
                                </div>
                                <ul class="id-verify-btn-group">
                                    <li><a href="{{route('adminUserVerificationActive',[$user_id,'driving'])}}" class="approve-btn">{{__('Approve')}}</a></li>
                                    <li><a href="javascript:;" data-toggle="modal" data-target="#diverify" class="reject-btn">{{__('Reject')}}</a></li>
                                </ul>
                            </div>
                            <div class="id-varify">
                                <div class="row justify-content-center">
                                    <div class="col-xl-4 mb-xl-0 mb-4">
                                        <div class="card-wrapper w-auto">
                                            <div class="card-area">
                                                <img src="{{imageSrc($drive_front,IMG_USER_VIEW_PATH)}}" class="img-fluid" alt="">
                                            </div>
                                            <h4>{{__('Driving licence Front Side')}}</h4>
                                        </div>
                                    </div>
                                    <div class="col-xl-4">
                                        <div class="card-wrapper w-auto">
                                            <div class="card-area">
                                                <img src="{{imageSrc($drive_back,IMG_USER_VIEW_PATH)}}" class="img-fluid" alt="">
                                            </div>
                                            <h4>{{__('Driving licence Back Side')}}</h4>
                                        </div>
                                    </div>
                                    <div class="col-xl-4">
                                        <div class="card-wrapper w-auto">
                                            <div class="card-area">
                                                <img src="{{imageSrc($drive_selfie,IMG_USER_VIEW_PATH)}}" class="img-fluid" alt="">
                                            </div>
                                            <h4>{{__('Driving Selfie')}}</h4>
                                        </div>
                                    </div>
                                </div>
                               
                                <!-- Modal -->
                                <div class="modal fade" id="diverify" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalCenterTitle">{{__('Rejected Cause')}}</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <img src="{{asset('assets/user/images/close.svg')}}" class="img-fluid" alt="">
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="idverifyrejection">
                                                    <div class="row justify-content-center">
                                                        <div class="col-lg-8">
                                                            <form action="{{route('varificationReject')}}">
                                                                <div class="form-group m-0">
                                                                    <label>{{__('Cause of  Rejection')}}</label>
                                                                    <input type="hidden" name="type" value="drive">
                                                                    <input type="hidden" name="user_id" value="{{$user_id}}">
                                                                    <input type="hidden" name="ids[]" value="{{$drive_front}}">
                                                                    <input type="hidden" name="ids[]" value="{{$drive_back}}">
                                                                    <input type="hidden" name="ids[]" value="{{$drive_selfie}}">
                                                                    <textarea required name="couse" class="form-control"></textarea>
                                                                </div>
                                                                <button type="submit" class="btn">{{__('Send')}}</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @if(isset($fields_name['pass_front']))
                            <div class="header-bar mt-5">
                                <div class="table-title">
                                    <h3>{{__('Passport Verification')}}</h3>
                                </div>
                                <ul class="id-verify-btn-group">
                                    <li><a href="{{route('adminUserVerificationActive',[$user_id,'passport'])}}" class="approve-btn">{{__('Approve')}}</a></li>
                                    <li><a href="javascript:;" data-toggle="modal" data-target="#passverify" class="reject-btn">{{__('Reject')}}</a></li>
                                </ul>
                            </div>
                            <div class="id-varify">
                                <div class="row justify-content-center">
                                    <div class="col-xl-4 mb-xl-0 mb-4">
                                        <div class="card-wrapper w-auto">
                                            <div class="card-area">
                                                <img id="zoom_01" data-zoom-image="{{imageSrc($pass_front,IMG_USER_VIEW_PATH)}}" src="{{imageSrc($pass_front,IMG_USER_VIEW_PATH)}}" class="img-fluid" alt="">
                                            </div>
                                            <h4>{{__('Passport Front Side')}}</h4>
                                        </div>
                                    </div>
                                    <div class="col-xl-4">
                                        <div class="card-wrapper w-auto">
                                            <div class="card-area">
                                                <img id="zoom_01" data-zoom-image="{{imageSrc($pass_back,IMG_USER_VIEW_PATH)}}" src="{{imageSrc($pass_back,IMG_USER_VIEW_PATH)}}" class="img-fluid" alt="">
                                            </div>
                                            <h4>{{__(' Passport Back Side')}}</h4>
                                        </div>
                                    </div>
                                    <div class="col-xl-4">
                                        <div class="card-wrapper w-auto">
                                            <div class="card-area">
                                                <img src="{{imageSrc($pass_selfie,IMG_USER_VIEW_PATH)}}" class="img-fluid" alt="">
                                            </div>
                                            <h4>{{__('Driving Selfie')}}</h4>
                                        </div>
                                    </div>
                                </div>
                               
                                <!-- Modal -->
                                <div class="modal fade" id="passverify" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalCenterTitle">{{__('Rejected Cause')}}</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <img src="{{asset('assets/user/images/close.svg')}}" class="img-fluid" alt="">
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="idverifyrejection">
                                                    <div class="row justify-content-center">
                                                        <div class="col-lg-8">
                                                            <form action="{{route('varificationReject')}}">
                                                                <div class="form-group m-0">
                                                                    <label>{{__('Cause of  Rejection')}}</label>
                                                                    <input type="hidden" name="type" value="passport">
                                                                    <input type="hidden" name="user_id" value="{{$user_id}}">
                                                                    <input type="hidden" name="ids[]" value="{{$pass_front}}">
                                                                    <input type="hidden" name="ids[]" value="{{$pass_back}}">
                                                                    <input type="hidden" name="ids[]" value="{{$pass_selfie}}">
                                                                    <textarea required name="couse" class="form-control"></textarea>
                                                                </div>
                                                                <button type="submit" class="btn">{{__('Send')}}</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @if(isset($fields_name['voter_front']))
                        <div class="header-bar mt-5">
                            <div class="table-title">
                                <h3>{{__('Voter Card Verification')}}</h3>
                            </div>
                            <ul class="id-verify-btn-group">
                                <li><a href="{{route('adminUserVerificationActive',[$user_id,'voter'])}}" class="approve-btn">{{__('Approve')}}</a></li>
                                <li><a href="javascript:;" data-toggle="modal" data-target="#voterfy" class="reject-btn">{{__('Reject')}}</a></li>
                            </ul> 
                        </div>
                        <div class="id-varify">
                            <div class="row justify-content-center">
                                <div class="col-xl-4 mb-xl-0 mb-4">
                                    <div class="card-wrapper w-auto">
                                        <div class="card-area">
                                            <img src="{{imageSrc($voter_front,IMG_USER_VIEW_PATH)}}" class="img-fluid" alt="">
                                        </div>
                                        <h4>{{__('Voter Card Front Side')}}</h4>
                                    </div>
                                </div>
                                 <div class="col-xl-4">
                                    <div class="card-wrapper w-auto">
                                        <div class="card-area">
                                            <img src="{{imageSrc($voter_back,IMG_USER_VIEW_PATH)}}" class="img-fluid" alt="">
                                        </div>
                                        <h4>{{__(' Voter Card Back Side')}}</h4>
                                    </div>
                                </div>
                                <div class="col-xl-4">
                                    <div class="card-wrapper w-auto">
                                        <div class="card-area">
                                            <img src="{{imageSrc($voter_selfie,IMG_USER_VIEW_PATH)}}" class="img-fluid" alt="">
                                        </div>
                                        <h4>{{__('Driving Selfie')}}</h4>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Modal -->
                             <div class="modal fade" id="voterfy" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="exampleModalCenterTitle">{{__('Rejected Cause')}}</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <img src="{{asset('assets/user/images/close.svg')}}" class="img-fluid" alt="">
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="idverifyrejection">
                                                <div class="row justify-content-center">
                                                    <div class="col-lg-8">
                                                        <form action="{{route('varificationReject')}}">
                                                            <div class="form-group m-0">
                                                                <label>{{__('Cause of  Rejection')}}</label>
                                                                <input type="hidden" name="type" value="voter">
                                                                <input type="hidden" name="user_id" value="{{$user_id}}">
                                                                <input type="hidden" name="ids[]" value="{{$voter_front}}">
                                                                <input type="hidden" name="ids[]" value="{{$voter_back}}">
                                                                <input type="hidden" name="ids[]" value="{{$voter_selfie}}">
                                                                <textarea required name="couse" class="form-control"></textarea>
                                                            </div>
                                                            <button type="submit" class="btn">{{__('Send')}} </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> 
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- /User Management -->
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/elevatezoom/3.0.8/jquery.elevatezoom.min.js"></script>
<script>
    $(document).ready(function() {
        $('.card-area img').elevateZoom({
            zoomType: "inner",
            cursor: "crosshair"
        });
       
    });
 </script>
@endsection
