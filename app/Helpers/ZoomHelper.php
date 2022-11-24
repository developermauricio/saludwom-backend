<?php
namespace App\Helpers;

class ZoomHelper
{
    public static function createMeeting($zoomClientKey, $zoomClientSecret)
    {
        return response()->json(['zoomClientKey' => $zoomClientKey, 'zoomClientSecret'=> $zoomClientSecret]);
    }
}

?>
