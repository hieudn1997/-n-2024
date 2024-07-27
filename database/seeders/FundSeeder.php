<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FundSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            DB::table('funds')->insert([
                'code' => 'TCBF',
                'name' => 'Quỹ đầu tư trái phiếu TCBF',
                'description' => 'Quỹ đầu tư trái phiếu TCBF chuyên trái phiếu doanh nghiệp, chứng chỉ tiền gửi, tín phiếu tốt nhất thị trường để tạo nguồn thu nhập ổn định dài hạn.',
                'historical_data_url' => '',
                'credential_id' => 1,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);

            DB::table('funds')->insert([
                'code' => 'TCFF',
                'name' => 'FlexiCa$h',
                'description' => 'FlexiCa$h - Quỹ đầu tư Trái phiếu linh hoạt giúp nhà đầu tư tối đa hóa lãi suất từ dòng tiền lưu động ngắn hạn.',
                'historical_data_url' => '',
                'credential_id' => 1,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);

            DB::table('funds')->insert([
                'code' => 'TCEF',
                'name' => 'Quỹ Đầu tư Cổ phiếu Techcom',
                'description' => 'Quỹ Đầu tư Cổ phiếu Techcom đầu tư vào top 30 doanh nghiệp hàng đầu trên sàn giao dịch chứng khoán Việt Nam (VN30), phù hợp với các khoản đầu tư dài hạn trên 1 năm.',
                'historical_data_url' => '',
                'credential_id' => 1,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);

            DB::table('funds')->insert([
                'code' => 'TCFIN',
                'name' => 'Quỹ Đầu tư Ngân hàng và Tài chính Techcom',
                'description' => 'Quỹ Đầu tư Ngân hàng và Tài chính Techcom đầu tư vào các ngân hàng và công ty tài chính niêm yết, phù hợp với những khoản đầu tư dài hạn trên 1 năm.',
                'historical_data_url' => '',
                'credential_id' => 1,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
