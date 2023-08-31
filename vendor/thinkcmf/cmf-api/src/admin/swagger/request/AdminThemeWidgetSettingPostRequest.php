<?php

namespace api\admin\swagger\request;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     required={},
 *     example={
 *       "widget_id":"widget_id1",
 *       "block_name":"block_name1",
 *       "file_id":"1",
 *       "widget":{
*             "display":1,
*             "title":"title1",
*             "vars":{
*                 "var1":"var1_value",
*                 "var1_text_":"var1_text_value"
*              },
*         }
 *     }
 * )
 */
class AdminThemeWidgetSettingPostRequest
{


}
