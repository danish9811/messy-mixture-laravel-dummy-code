<?php

namespace App\Http\Controllers\HackerRankChallenges;

use App\Http\Controllers\Controller;
use App\Traits\Utils;

class Challege1 extends Controller {
    use Utils;

    // challege one at hackerRank
    // https://www.hackerrank.com/challenges/sock-merchant/problem?isFullScreen=true&h_l=interview&playlist_slugs%5B%5D=interview-preparation-kit&playlist_slugs%5B%5D=warmup
    /**
     * and the number of each sock in the pile must be equal to the number of colors in the pile
     * it means that first parameter $ must be equal to the length of the second array paramter
     * @param int $n int n: the number of socks in the pile
     * @param array $array int ar[n]: the colors of each sock
     * @return int the number of pairs in the pile
     */
    public function sockMerchant(int $n, array $array): int {
        if (!count($array) === $n) {
            return 0;
        }

        $min = min($array);
        $max = max($array);

        sort($array);
        $totalNumbers = count($array);
        for ($i = 0; $i < $totalNumbers; $i++) {
            $temp = $array[$i];
            for ($j = $temp + 1; $temp < $totalNumbers; $temp++) {
                // compare the needle with all the other elements
            }
        }

    }


    public function numberSum(int $a, int $b) : int  {
        return $a + $b;
    }


    public function runChallenge() {
       echo  $this->numberSum();
    }



}