<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EquipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $family = [
            'Arjun',
            'Robinhood',
            'Classic 40',
        ];

        $models = $this->generateModelCodes(10,7);

        $manufacturers = [
            'Acumen',
            'Creator',
            'Zahoransky'
        ];

        $units = ['shot', 'hour', 'hole'];

        $categoryIds = DB::table('categories')->pluck('uuid')->toArray();

        $admin = DB::table('users')->where('username', '=', 'admin')->first();

        $firstCharsMachineId = array_map(function($m) {
            return substr($m, 0, 1);
        }, $models);

        for ($i=1; $i <= 30; $i++) { 

            DB::table('equipments')->insert([
                'uuid' => Str::uuid()->toString(),
                'machine_id' => $firstCharsMachineId[array_rand($firstCharsMachineId,1)] . '0000' . strval($i),
                'family' => $family[array_rand($family, 1)],
                'model' => $models[array_rand($models, 1)],
                'cavity' => '16',
                'manufacturer' => $manufacturers[array_rand($manufacturers, 1)],
                'manufacturing_date' => Carbon::now()
                    ->subDays(rand(0, 365 * 5)) // lùi ngẫu nhiên tối đa 5 năm
                    ->format('Y-m-d H:i:s'),
                'history_count' => rand(0, 8) * 50000 + 100000,
                'unit' => $units[array_rand($units, 1)],
                'category_id' => $categoryIds[array_rand($categoryIds,1)],
            ]);
        }

    }

    private function generateModelCode(int $maxLen = 7): string {
        $maxLen = max(2, min($maxLen, 7)); // giới hạn an toàn [2..7]

        // Quyết định có dùng gạch ngang hay không (30% cơ hội)
        $useHyphen = (mt_rand(1, 100) <= 30) && $maxLen >= 3; // cần >=3 để có "A-1" tối thiểu

        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $digits  = '0123456789';
        $alnum   = $letters . $digits;

        // Hàm tạo đoạn ngẫu nhiên có ít nhất 1 chữ và 1 số (nếu độ dài >=2)
        $makePart = function (int $len) use ($letters, $digits, $alnum): string {
            if ($len <= 0) return '';
            if ($len == 1) {
                // 1 ký tự: chọn chữ hoặc số
                return $alnum[mt_rand(0, strlen($alnum)-1)];
            }
            // đảm bảo có ít nhất 1 chữ và 1 số
            $posLetter = mt_rand(0, $len-1);
            do { $posDigit = mt_rand(0, $len-1); } while ($posDigit === $posLetter);

            $chars = array_fill(0, $len, '');
            $chars[$posLetter] = $letters[mt_rand(0, strlen($letters)-1)];
            $chars[$posDigit]  = $digits[mt_rand(0, strlen($digits)-1)];

            for ($i = 0; $i < $len; $i++) {
                if ($chars[$i] === '') {
                    $pool = $alnum;
                    $chars[$i] = $pool[mt_rand(0, strlen($pool)-1)];
                }
            }
            return implode('', $chars);
        };

        if ($useHyphen) {
            // Chia độ dài cho 2 phần + 1 ký tự '-'
            $totalParts = $maxLen - 1; // trừ gạch
            // mỗi bên tối thiểu 1 ký tự
            $leftLen  = mt_rand(1, $totalParts - 1);
            $rightLen = $totalParts - $leftLen;

            $left  = $makePart($leftLen);
            $right = $makePart($rightLen);

            return $left . '-' . $right;
        } else {
            // Không có gạch: chuỗi liền, đảm bảo có chữ & số
            $len = mt_rand(2, $maxLen);
            return $makePart($len);
        }
    }

    // Tạo nhiều mã không trùng
    private function generateModelCodes(int $count = 10, int $maxLen = 7): array {
        $set = [];
        while (count($set) < $count) {
            $code = $this->generateModelCode($maxLen);
            $set[$code] = true;
        }
        return array_keys($set);
    }


}
