<div class="sidebar">
    <!-- logo -->
    <div class="logo">
        <a href="{{route('adminDashboard')}}">
            <img src="{{show_image(Auth::user()->id,'logo')}}" class="img-fluid" alt="">
        </a>
    </div><!-- /logo -->

    <!-- sidebar menu -->
    <div class="sidebar-menu">
        <nav>
            <ul id="metismenu">


{!! mainMenuRenderer('giftCardDashboard',__('Dashboard'),$menu ?? '','dashboard','dashboard.svg') !!}
{!! mainMenuRenderer('giftCardCategoryListPage',__('Category'),$menu ?? '','category','staking.svg') !!}
{!! mainMenuRenderer('giftCardBannerListPage',__('Banner'),$menu ?? '','banner','Transaction-1.svg') !!}
{{-- {!! mainMenuRenderer('learnMoreGiftCard',__('Learn more page'),$menu ?? '','page','logs.svg') !!} --}}
{!! mainMenuRenderer('giftCardHistory',__('Gift Card History'),$menu ?? '','history','coin.svg') !!}
{!! mainMenuRenderer('giftCardHeader',__('Settings'),$menu ?? '','header','settings.svg') !!}
{!! mainMenuRenderer('adminDashboard',__('Admin Dashboard'),$menu ?? '','adminDashboard','dashboard.svg') !!}

            </ul>
        </nav>
    </div><!-- /sidebar menu -->

</div>
