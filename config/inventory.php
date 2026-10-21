<?php

return [

    /*
    | Stock from 1 up to this value is "low stock". Zero is "out of stock".
    | The frontend no longer needs its own copy: the API returns
    | a computed `stock_status` for every product.
    */
    'low_stock_threshold' => (int) env('LOW_STOCK_THRESHOLD', 5),

];
