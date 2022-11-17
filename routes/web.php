<?php

use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Api\V1\StripeWebHookController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::post('stripe/webhook', [StripeWebHookController::class, 'handleWebHook']);


Route::get('/', function () {
    return view('welcome');
});
Route::get('/mail', function () {

    dd(ucwords(\Jenssegers\Date\Date::parse('2023-01-09 13:23:01')->locale('es')->format('F d Y')));
////    return view('mails.activation-email');
//    return \Illuminate\Support\Facades\Notification::send(new \App\Notifications\SendInvoiceNotification('Mauro', 'so', 'sp', 's'));
});
Route::get('/update-doctor', function () {
    $doctor = \App\Models\Doctor::where('id', 2)->first();
//   ss
    $schedule = '
        {"schedules":[
        {"date": "2022-11-25","hours":[
                {
                    "hour": "08:00"
                },
                {
                    "hour": "09:00"
                },
                {
                    "hour": "10:00"
                },
                {
                    "hour": "11:00"
                },
                {
                    "hour": "12:00"
                }
            ]
        },
        {
           "date": "2022-11-26",
           "hours":[
                {
                    "hour": "14:00"
                },
                {
                    "hour": "15:00"
                }

            ]
        },
        {
           "date": "2022-11-27",
           "hours":[
                {
                    "hour": "13:00"
                },
                {
                    "hour": "14:00"
                },
                {
                    "hour": "15:00"
                }

            ]
        },
        {
           "date": "2022-11-28",
           "hours":[

                {
                    "hour": "09:00"
                }

            ]

        }
    ]
}';

    $schudleTwo = '{
   "schedules":[
      {
         "date":"2022-11-23",
         "hours":[
            {
               "hh":"10",
               "mm":"00"
            },
            {
               "hh":"11",
               "mm":"00"
            },
            {
               "hh":"16",
               "mm":"00"
            }
         ]
      },
      {
         "date":"2022-11-27",
         "hours":[
            {
               "hh":"10",
               "mm":"00"
            },
            {
               "hh":"15",
               "mm":"00"
            }
         ]
      },
      {
         "date":"2022-11-28",
         "hours":[
            {
               "hh":"12",
               "mm":"00"
            },
            {
               "hh":"14",
               "mm":"00"
            },
            {
               "hh":"13",
               "mm":"00"
            }
         ]
      },
      {
         "date":"2022-11-30",
         "hours":[
            {
               "hh":"09",
               "mm":"00"
            }
         ]
      }
   ]
}';
    $doctor->schedule = $schudleTwo;
    $doctor->save();
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
