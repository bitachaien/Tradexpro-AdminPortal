<?php

namespace Database\Seeders;

use App\Model\ThemeColor;
use Illuminate\Database\Seeder;

class ThemeColorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

     public $defaultPrimaryColor = [
        THEME_GREEN => '#108059',
        THEME_YELLOW=> '#f0b90b',
        THEME_BLUE  => '#4b72d4',
    ];

    public $colorSlug = [
        "user_primary_color" => null,
        "user_text_primary_color" => "#000000",
        "user_text_primary_color_2" => "#23262f",
        "user_text_primary_color_3" => "#414347",
        "user_text_primary_color_4" => "#6e6e6e",
        "user_border_color" => "#dedede",
        "user_border_color_1" => "#e6e8ec",
        "user_border_color_2" => "#353535",
        "user_hover_color" => null,
        "user_font_color" => "#2a2a2d",
        "user_bColor" => "#454545",
        "user_title_color" => "#070000",
        "user_white" => "#ffffff",
        "user_black" => "#000000",
        "user_color_pallet_1" => "#b4b8d7",
        "user_background_color" => "#ffffff",
        "user_background_color_trade" => "#ffffff",
        "user_main_background_color" => "#ffffff",
        "user_card_background_color" => "#ffffff",
        "user_table_background_color" => "#dad6d6",
        "user_footer_background_color" => "#03150f",
        "user_background_color_hover" => "#fafafa",
        "authentication_page_text_color" => "#ffffff",
        "user_primary_background_color" => "#f5f5f5",
        
        "user_dark_primary_color" => null,
        "user_dark_text_primary_color" => "#ffffff",
        "user_dark_text_primary_color_2" => "#ffffff",
        "user_dark_text_primary_color_3" => "#ffffff",
        "user_dark_text_primary_color_4" => "#cbcfd7",
        "user_dark_border_color" => "#535353",
        "user_dark_border_color_1" => "#535353",
        "user_dark_border_color_2" => "#ffffff",
        "user_dark_hover_color" => null,
        "user_dark_font_color" => "#ffffff",
        "user_dark_bColor" => "#ffffff",
        "user_dark_title_color" => "#ffffff",
        "user_dark_white" => "#ffffff",
        "user_dark_black" => "#000000",
        "user_dark_color_pallet_1" => "#535353",
        "user_dark_background_color" => "#151515",
        "user_dark_background_color_trade" => "#2a2e37",
        "user_dark_main_background_color" => "#070000",
        "user_dark_card_background_color" => "#282828",
        "user_dark_table_background_color" => "#353535",
        "user_dark_footer_background_color" => "#03150f",
        "user_dark_background_color_hover" => "#3a3a3a",
        "authentication_dark_page_text_color" => "#ffffff",
        "user_dark_primary_background_color" => "#282828",
    ];

    public function run()
    {
        try {
            $settings = allsetting();

            foreach (themeColors() as $theme => $_) {
                foreach ($this->colorSlug as $slug => $color) {
                    $slugName = $slug . "_" .$theme;
                    if(
                        $slug == 'user_primary_color' || $slug == 'user_dark_primary_color' 
                        || $slug == 'user_hover_color' || $slug == 'user_dark_hover_color'
                    )
                    {
                        ThemeColor::firstOrCreate(['slug' => $slugName], [
                            'value' => $this->defaultPrimaryColor[$theme],
                        ]);
                        continue;
                    }
                    ThemeColor::firstOrCreate(['slug' => $slugName], ['value' => $settings[$slug] ?? $color]);
                }
            }
        } catch (\Exception $exception){
            storeException("ThemeColorSeeder", $exception->getMessage());
        }
    }
}
