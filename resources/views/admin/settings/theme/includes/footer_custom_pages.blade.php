<div class="header-bar">
    <div class="table-title">
        <h3>{{__('Footer Custom Page Title')}}</h3>
    </div>
</div>
<div class="profile-info-form">
    <form action="{{route('themesSettingSave')}}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-6 mt-20">
                <div class="form-group">
                    <input type="text" name="user_footer_title_about" value="{{ $settings['user_footer_title_about'] ?? 'About Us' }}" class="form-control" />
                </div>
                
                <div class="form-group">
                    <input type="text" name="user_footer_title_product" value="{{ $settings['user_footer_title_product'] ?? 'Products' }}" class="form-control" />
                </div>
            
                <div class="form-group">
                    <input type="text" name="user_footer_title_service" value="{{ $settings['user_footer_title_service'] ?? 'Service' }}" class="form-control" />
                </div>
           
                <div class="form-group">
                    <input type="text" name="user_footer_title_support" value="{{ $settings['user_footer_title_support'] ?? 'Support' }}" class="form-control" />
                </div>
            
                <div class="form-group">
                    <input type="text" name="user_footer_title_community" value="{{ $settings['user_footer_title_community'] ?? 'Community' }}" class="form-control" />
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-2 col-12 mt-20">
                <button class="button-primary theme-btn">{{__('Update')}}</button>
            </div>
        </div>
    </form>
</div>
