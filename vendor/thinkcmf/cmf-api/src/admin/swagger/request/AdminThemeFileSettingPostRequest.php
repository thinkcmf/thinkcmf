<?php

namespace api\admin\swagger\request;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     required={},
 *     example={
 *       "files":{
 *         "1":{
 *              "vars":{
 *                  "var1":"var1_value",
 *                  "var1_text_":"var1_text_value"
 *              },
 *              "/widgets这个属性待实现这个属性待实现":"/",
 *              "widgets":{
 *                  "widget1":{
 *                      "display":1,
 *                      "title":"title1",
 *                      "vars":{
 *                         "var1":"var1_value",
 *                         "var1_text_":"var1_text_value"
 *                      },
 *                  }
 *              },
 *              "widget_vars":{
 *                  "widget1":{
 *                      "var1":"var1_value",
 *                      "var1_text_":"var1_text_value"
 *                  }
 *              },
 *              "widget":{
 *                  "widget1":{
 *                      "display":1,
 *                      "title":"title1",
 *                  }
 *              }
 *          }
 *
 *     }
 *     }
 * )
 */
class AdminThemeFileSettingPostRequest
{


}
