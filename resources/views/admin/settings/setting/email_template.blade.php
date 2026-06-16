<div class="header-bar">
    <div class="table-title">
        <h3>{{__('Email Template Setup')}}</h3>
    </div>
</div>
<div class="profile-info-form">
    <form action="{{route('adminSaveEmailTemplateSettings')}}" method="post"
          enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-6 col-12  mt-20">
                <div class="form-group">
                    <label for="#">{{__('Email Template')}}</label>
                    <select id="choose_email_template" name="email_template_number" class="form-control">
                        @foreach (emailTemplateList() as $template_no=>$template_value)
                            <option value="{{ $template_no }}"
                            {{(isset($settings['email_template_number']) && $settings['email_template_number'] == $template_no)? 'selected':''}}>{{ $template_value}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="row p-3 {{(isset($settings['email_template_number']) && 
                    $settings['email_template_number'] == EMAIL_TEMPLATE_NUMBER_ONE)? 'd-block':'d-none'}}" 
                    id="template_number_one">
                    <img style="width: -webkit-fill-available;"
                        alt="Tenable Network Security"
                        src="{{asset('assets/img/email-template/template-one.png')}}" >
                </div>
                <div class="row p-3 {{(isset($settings['email_template_number']) && 
                    $settings['email_template_number'] == EMAIL_TEMPLATE_NUMBER_TWO)? 'd-block':'d-none'}}" 
                    id="template_number_two" >
                    <img style="width: -webkit-fill-available;"
                         alt="Tenable Network Security"
                        src="{{asset('assets/img/email-template/template-two.png')}}">
                </div>
                <div class="row p-3 {{(isset($settings['email_template_number']) && 
                    $settings['email_template_number'] == EMAIL_TEMPLATE_NUMBER_THREE)? 'd-block':'d-none'}}" 
                    id="template_number_three" >
                    <img style="width: -webkit-fill-available;"
                        alt="Tenable Network Security"
                        src="{{asset('assets/img/email-template/template-three.png')}}">
                </div>
                <div class="row p-3 {{(isset($settings['email_template_number']) && 
                    $settings['email_template_number'] == EMAIL_TEMPLATE_NUMBER_FOUR)? 'd-block':'d-none'}}" 
                    id="template_number_four" >
                    <img style="width: -webkit-fill-available;"
                        alt="Tenable Network Security"
                        src="{{asset('assets/img/email-template/template-four.png')}}">
                </div>
            </div>
            
        </div>
        <div class="row">
            <div class="col-lg-2 col-12 mt-20">
                <button type="submit" class="button-primary theme-btn">{{__('Update')}}</button>
            </div>
        </div>
    </form>
</div>




