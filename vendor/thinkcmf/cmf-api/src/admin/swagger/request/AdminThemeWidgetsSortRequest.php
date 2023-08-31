<?php

namespace api\admin\swagger\request;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     required={},
 *     example={
 *            "file2": {
 *                "widget_block1": {
 *                    {
 *                        "file_id": "2",
 *                        "widget_id": "top_nav_202206300003"
 *                    }
 *                },
 *                "top": {
 *                    {
 *                        "file_id": "2",
 *                        "widget_id": "top_nav_202206300003"
 *                    }
 *                },
 *                "footer": {
 *                    {
 *                        "file_id": "2",
 *                        "widget_id": "home_footer_202206300003"
 *                    }
 *                }
 *            },
 *            "file3": {
 *                "widget_block1": {
 *                    {
 *                        "file_id": "3",
 *                        "widget_id": "top_nav_202206300003"
 *                    }
 *                }
 *            }
 *        }
 * )
 */
class AdminThemeWidgetsSortRequest
{


}
