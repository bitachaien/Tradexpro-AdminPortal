<?php

namespace App\Http\Controllers;

use App\Http\Repositories\CoinPairRepository;
use App\Http\Requests\ContactRequest;
use App\Model\Announcement;
use App\Model\Coin;
use App\Model\CoinPair;
use App\Model\ContactUs;
use App\Model\CustomPage;
use App\Model\Faq;
use App\Model\LandingBanner;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    // landing home page
    public function home()
    {
        $coinRepo = new CoinPairRepository(CoinPair::class);
        $data['content'] = allsetting();
        $data['custom_links'] = CustomPage::orderBy('data_order','asc')->get();
        $data['faqs'] = Faq::where('status',1)->orderBy('created_at', 'desc')->get();
        $data['coins'] = Coin::where('status',1)->orderBy('created_at', 'desc')->get();
        $data['asset_coin_pairs'] = $coinRepo->getLandingCoinPairs('asset');
        $data['hourly_coin_pairs'] = $coinRepo->getLandingCoinPairs('24hour');
        $data['latest_coin_pairs'] = $coinRepo->getLandingCoinPairs('latest');
        $data['landing_banners'] = LandingBanner::where(['status' => STATUS_ACTIVE])->get();
        $data['announcements'] = Announcement::where(['status' => STATUS_ACTIVE])->get();

        return view('landing.landing',$data);
    }

    // custom page
    public function getCustomPage($id,$key){
        $data['content'] = allsetting();
        $data['custom_links'] = CustomPage::orderBy('data_order','asc')->get();
        $data['item'] = CustomPage::find($id);

        return view('landing.custom_page',$data);
    }

    // custom page
    public function getLandingPage($type,$slug)
    {
        $data['content'] = allsetting();
        $data['custom_links'] = CustomPage::orderBy('data_order','asc')->get();
        $data['key'] = $type;
        $data['item'] = '';
        if ($type == 'banner') {
            $data['item'] = LandingBanner::where(['slug' => $slug])->first();
        } elseif ($type == 'announcement') {
            $data['item'] = Announcement::where(['slug' => $slug])->first();
        }
        if (isset($data['item'])) {
            return view('landing.custom_page',$data);
        } else {
            return redirect()->back()->with('dismiss', __('Page not found'));
        }
    }

    // contact us
    public function contactUs(ContactRequest $request)
    {
        $data = [
            'name'=> $request->name,
            'email'=> $request->email,
            'phone' => $request->phone,
            'address'=> $request->address,
            'description'=> $request->description
        ];
        try{
            ContactUs::create($data);
            return redirect()->back()->with('success', __("Contact form submitted successfully"));
        }catch (\Exception $e){
            return redirect()->back()->with('dismiss',$e->getMessage());
        }
    }

}
