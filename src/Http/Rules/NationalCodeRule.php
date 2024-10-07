<?php

namespace Nhd\Foundation\Rules;

use Illuminate\Contracts\Validation\Rule;

class NationalCodeRule implements Rule
{
    public function passes($attribute, $value)
    {
        if(is_numeric($value) && strlen($value) == 10) {
            $sum = 0;
            for($i = 0 ; $i <= 8 ; $i++) {
                $num = is_numeric($value[$i]) ? ((int)$value[$i]) : 0;
                $num *= (10 - $i);
                $sum += $num;
            }

            $control = is_numeric($value[9]) ? ((int)$value[9]) : 0;

            $mod = $sum % 11;
            if($mod < 2) {
                return $mod == $control;
            }
            else if($mod >= 2) {
                return (11 - $mod) == $control;
            }
        }

        return false;
    }

    public function message()
    {
        return 'کدملی نادرست میباشد.';
    }
}
